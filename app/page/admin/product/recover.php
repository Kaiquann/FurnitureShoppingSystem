<?php
$_title = "Recover Product";

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

            $stmt = $_db->prepare('SELECT is_deleted FROM product WHERE id = ?');
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new Exception("Product ID: $product_id does not exist");
            }

            $stmt = $_db->prepare('UPDATE product SET is_deleted = 0 WHERE id = ?');
            $stmt->execute(params: [$product_id]);
        }
        $_db->commit();
        temp('success', "The selected product(s) have been recovered");
    }
    catch (Exception $ex) {
        $_db->rollBack();
        temp('danger', $ex->getMessage());
    }
}

return redirect('/admin/product/trash');
