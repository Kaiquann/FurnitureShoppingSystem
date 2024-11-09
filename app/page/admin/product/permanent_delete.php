<?php
$_title = "Permenant Delete Product";

if (is_get()) {
    temp("danger", "Invalid action");
}

if (is_post()) {
    $id = req('id', []);

    if (!is_array($id)) $id = [$id];

    try {
        $_db->beginTransaction();
        foreach ($id as $product_id) {
            if (empty($product_id)) {
                throw new Exception("Product ID is required");
            }
            $stmt = $_db->prepare('SELECT * FROM product WHERE id = ?');
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new Exception("Product ID: $product_id does not exist");
            }

            $deleted_product_name = "PD: {$product->name} (Deleted on " . getDateTime() . ")";

            $stmt = $_db->prepare('UPDATE product SET name = ?, is_deleted = 2 WHERE id = ?');
            $stmt->execute([$deleted_product_name, $product_id]);
        }
        $_db->commit();
        temp('success', "The selected product(s) has been permanently deleted");
    }
    catch (Exception $ex) {
        $_db->rollBack();
        temp('danger', $ex->getMessage());
    }
}

return redirect('/admin/product/trash');
