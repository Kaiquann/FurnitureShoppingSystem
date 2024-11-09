<?php
$_title = "Recover Category";

if (is_get()) {
    temp('danger', 'Invalid action');
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

            $stmt = $_db->prepare('UPDATE category SET is_deleted = 0 WHERE id = ?');
            $stmt->execute([$category_id]);
        }
        $_db->commit();
        temp('success', value: 'The selected category(s) has been recovered');
    }
    catch (Exception $e) {
        $_db->rollBack();
        temp('danger', $e->getMessage());
    }
}

return redirect('/admin/category/trash');
