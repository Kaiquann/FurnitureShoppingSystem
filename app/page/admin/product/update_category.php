<?php
$_title = "Update Product Category";

$forward = req('forward') ?: '/admin/product';

if (is_get()) {
    temp("danger", "Invalid action");
}

if (is_post()) {
    $id                    = req('id', []);
    $batch_action_category = req('batch_action_category');

    if (!is_array($id)) $id = [$id];

    try {
        $_db->beginTransaction();
        $category = array_column(db_select_all('category'), 'id', 'id');
        if (!$batch_action_category || !in_array($batch_action_category, $category)) throw new Exception('Invalid Category');
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

            $stmt = $_db->prepare('UPDATE product SET category_id = ? WHERE id = ?');
            $stmt->execute([$batch_action_category, $product_id]);
        }
        $_db->commit();
        temp('success', "The selected product(s) category have been updated");
    }
    catch (Exception $ex) {
        $_db->rollBack();
        temp('danger', $ex->getMessage());
    }
}

return redirect($forward);
