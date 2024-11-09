<?php
$_title = "Update Product";

$id   = req('id');
$stmt = $_db->prepare(
    "SELECT p.* FROM product p
    JOIN category c ON p.category_id = c.id
    WHERE p.id = ?
    AND p.is_deleted IN (0, 1)
    AND c.is_deleted IN (0, 1)"
);
$stmt->execute([$id]);
$product = $stmt->fetch();

if (empty($product)) {
    temp('danger', 'Product not found');
    return redirect('/admin/product');
}

extract((array) $product);

$forward = req('forward');
if (empty($forward)) {
    $forward = "/admin/product/view?id=$id";
}

$option_category = $_db->query("SELECT * FROM category WHERE is_deleted IN (0, 1)")->fetchAll();
$option_category = array_column($option_category, 'name', 'id');

if (is_post()) {
    $id            = req('id');
    $name          = req('name');
    $description   = req('description');
    $stock         = req('stock');
    $price         = req('price');
    $category      = req('category');
    $product_image = req_file('product_image');

    if (empty($id)) {
        $_err['id'] = 'Required';
    } else if (!is_numeric($id)) {
        $_err['id'] = 'Must be a number';
    } else if ($id != $product->id) {
        $_err['id'] = 'Invalid ID';
    }

    if (empty($name)) {
        $_err['name'] = 'Required';
    } else if (strlen($name) > 50) {
        $_err['name'] = 'Maximum length is 50 characters';
    }

    if (empty($description)) {
        $_err['description'] = 'Required';
    } else if (strlen($description) > 800) {
        $_err['description'] = 'Maximum length is 800 characters';
    }

    if (empty($stock)) {
        $_err['stock'] = 'Required';
    } else if (!is_numeric($stock)) {
        $_err['stock'] = 'Invalid stock';
    } else if ($stock < 1) {
        $_err['stock'] = 'Minimum stock is 1';
    } else if ($stock > 99999) {
        $_err['stock'] = 'Maximum stock is 99999';
    }

    if (empty($price)) {
        $_err['price'] = 'Required';
    } else if (!is_money($price)) {
        $_err['price'] = 'Invalid price';
    } else if ($price < 1) {
        $_err['price'] = 'Minimum price is 1';
    } else if ($price > 99999.99) {
        $_err['price'] = 'Maximum price is 99999.99';
    }

    if (empty($category)) {
        $_err['category'] = 'Required';
    } else if (!array_key_exists($category, $option_category)) {
        $_err['category'] = 'Invalid category';
    }

    if ($product_image) {
        foreach ($product_image as $file) {
            $image_result = check_image($file);
            if (!empty($image_result)) {
                $_err['product_image'] = $image_result;
            }
        }
    }

    if (!$_err) {
        if ($product_image) {
            $image_url_list   = [];
            $product_image_db = db_select('product_image', 'product_id', $id);
            foreach ($product_image_db as $pi) {
                file_delete($pi->image_url);
            }
            db_delete('product_image', 'product_id', $id);
            foreach ($product_image as $file) {
                $image_url        = save_photo($file, '/images/product');
                $image_url_list[] = $image_url;
            }
            $_db->beginTransaction();
            $stmt = $_db->prepare('
                INSERT INTO product_image (product_id, image_url)
                VALUES (?, ?)
            ');
            foreach ($image_url_list as $image_url) {
                $stmt->execute([$id, $image_url]);
            }
            $_db->commit();
        }
        db_update("product", [
            "name"        => $name,
            "description" => $description,
            "stock"       => $stock,
            "price"       => $price,
            "category_id" => $category
        ], "id", $id);
        temp('success', 'Product updated successfully');
        return redirect("/admin/product/view?id=$id");
    }
}
?>

<h1><?= "$_title : $name " ?></h1>

<form method="post" class="form" enctype="multipart/form-data">
    <label for="product_image">Product Image:</label>
    <label class="upload" tabindex="0">
        <?= html_file('product_image[]', 'image/*', 'hidden multiple') ?>
        <?php $product_image = db_select('product_image', 'product_id', $id); ?>
        <?= html_image($id, $image_url ?? '/images/noimage.jpg', "alt='$name' class='img-25'"); ?>
        <span></span>
        <?php foreach ($product_image as $pi) {
            html_image($id, $pi->image_url, "alt='$name' class='img-20'");
        } ?>
    </label>
    <?= err('product_image') ?>

    <label for="id">Id</label>
    <b><?= $id ?></b>
    <?= err('id') ?>

    <label for="name">Product Name: </label>
    <?= html_text("name", "maxlength=50"); ?>
    <?= err('name') ?>

    <label for="description">Description: </label>
    <?= html_textarea("description", "maxlength=500"); ?>
    <?= err('description') ?>

    <label for="stock">Stock: </label>
    <?= html_number("stock", 1, 99999, 1); ?>
    <?= err('stock') ?>

    <label for="price">Price: </label>
    <?= html_number("price", 1, 99999, 0.01); ?>
    <?= err('price') ?>

    <label for="category">Category: </label>
    <?= html_select("category", $option_category, $option_category[$category_id]); ?>
    <?= err('category') ?>

    <section>
        <button type="submit" class="primary" data-confirm>Update</button>
        <button type="reset" class="danger">Reset</button>
        <button type="button" data-get="<?= $forward ?>" class="success">Go Back</button>
    </section>
</form>