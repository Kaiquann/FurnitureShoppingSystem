<?php
$_title = "View Product";

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
    $forward = '/admin/product';
}
?>

<div class="admin-container">
    <h1><?= "$_title : $name " ?></h1>
    <p class="admin-image">
        <?php
        $product_image = db_select('product_image', 'product_id', $id);
        foreach ($product_image as $pi) {
            html_image($id, $pi->image_url, "alt='$name'");
        }
        ?>
    </p>

    <section class="admin-content">
        <table class="admin-table">
            <tbody>
                <tr>
                    <td><strong>Id:</strong></td>
                    <td><?= $id ?></td>
                </tr>
                <tr>
                    <td><strong>Name:</strong></td>
                    <td><?= html_print($name) ?></td>
                </tr>
                <tr>
                    <td><strong>Description:</strong></td>
                    <td><?= html_print(values: $description) ?></td>
                </tr>
                <tr>
                    <td><strong>Stock:</strong></td>
                    <td><?= html_print(values: $stock) ?></td>
                </tr>
                <tr>
                    <td><strong>Price:</strong></td>
                    <td><?= html_print(values: $price) ?></td>
                </tr>
                <tr>
                    <td><strong>Category:</strong></td>
                    <td><?= html_print(db_select_single('category', 'id', $category_id)->name) ?></td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="admin-action">
        <button data-get="/admin/product/update?id=<?= $id ?>" class="primary">Update</button>
        <?php if ($is_deleted) : ?>
            <button data-post="/admin/product/recover?id=<?= $id ?>"
                data-confirm="Are you sure to recover <?= html_print($name) ?>?" class="warning">Recover</button>
        <?php else : ?>
            <button data-post="/admin/product/delete?id=<?= $id ?>"
                data-confirm="Are you sure to delete <?= html_print($name) ?>?" class="danger">Delete</button>
        <?php endif ?>
        <button type="button" data-get="<?= $forward ?>" class="success">Go Back</button>
    </section>
</div>