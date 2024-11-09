<?php
$_title = "Permanent Delete category";

if (is_get()) {
    temp('danger', "Invalid action");
}

if (is_post()) {
    $id = req('id', []);

    if (!is_array($id)) $id = [$id];

    try {
        $_db->beginTransaction();
        foreach ($id as $category_id) {
            if (empty($category_id)) {
                throw new Exception('Category ID is required');
            }

            $stmt = $_db->prepare('SELECT * FROM category WHERE id = ?');
            $stmt->execute([$category_id]);
            $category = $stmt->fetch();

            if (!$category) {
                throw new Exception("Category ID: $category_id not found");
            }

            $deleted_category_name = "PD: {$category->name} (Deleted on " . getDateTime() . ")";

            $stmt = $_db->prepare('UPDATE category SET name = ?, is_deleted = 2 WHERE id = ?');
            $stmt->execute([$deleted_category_name, $category_id]);

            // Permanent Delete All The Product That Under This Category ID
            $stmt = $_db->prepare('SELECT * FROM product WHERE category_id = ? AND is_deleted IN (0, 1)');
            $stmt->execute([$category_id]);
            $product = $stmt->fetchAll();

            foreach ($product as $p) {
                $deleted_product_name = "PD: {$p->name} (Deleted on " . getDateTime() . ")";

                $stmt = $_db->prepare('UPDATE product SET name = ?, is_deleted = 2 WHERE id = ?');
                $stmt->execute([$deleted_product_name, $p->id]);
            }
        }
        $_db->commit();
        temp('success', value: 'The selected category(s) has been permanently deleted');
    }
    catch (Exception $e) {
        $_db->rollBack();
        temp('danger', $e->getMessage());
    }
}

return redirect('/admin/category/trash');
