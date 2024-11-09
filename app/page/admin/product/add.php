<?php
$_title = "Add Product";

$option_category = array_column(db_select('category', 'is_deleted', 0), 'name', 'id');

if (is_post()) {
    $name          = req('name');
    $description   = req('description');
    $stock         = req('stock');
    $price         = req('price');
    $category      = req('category');
    $product_image = req_file('product_image');

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

    $product_data = db_select_single('product', 'name', $name);
    if ($product_data) {
        if ($product_data->category_id == $category) {
            $_err['name'] = 'This product already exists in this category';
        }
    }

    if (!$_err) {
        if ($product_image) {
            $image_url_list = [];
            foreach ($product_image as $file) {
                $image_url        = save_photo($file, '/images/product');
                $image_url_list[] = $image_url;
            }
        }

        $_db->beginTransaction();
        $stmt = $_db->prepare('
            INSERT INTO product (name, description, stock, price, category_id)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([$name, $description, $stock, $price, $category]);

        $product_id = $_db->lastInsertId();

        if ($product_image) {
            $stmt = $_db->prepare('
                INSERT INTO product_image (product_id, image_url)
                VALUES (?, ?)
            ');
            foreach ($image_url_list as $image_url) {
                $stmt->execute([$product_id, $image_url]);
            }
        }

        $_db->commit();

        temp('success', "Product $name added successfully");
        return redirect('/admin/product');
    }
}
?>

<h1><?= $_title ?></h1>

<form method="post" class="form" enctype="multipart/form-data">
    <label for="product_image">Product Image:</label>
    <label class="upload" tabindex="0">
        <?= html_file('product_image[]', 'image/*', 'hidden multiple') ?>
        <img src="/images/noimage.jpg">
        <span></span>
    </label>
    <?= err('product_image') ?>

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

    <label for="category">Category</label>
    <?= html_select('category', $option_category) ?>
    <?= err('category') ?>

    <section>
        <button type="submit" class="primary" data-confirm>Add</button>
        <button type="reset" class="danger">Reset</button>
        <button type="button" data-get="/admin/product" class="success">Go Back</button>
    </section>
</form>