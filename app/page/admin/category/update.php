<?php
$_title = "Update Category";

$id       = req('id');
$category = $_db->prepare('
    SELECT * FROM category
    WHERE id = ?
    AND is_deleted = 0
');
$category->execute([$id]);
$category = $category->fetch();

if (!$category) {
    temp('danger', 'Category not found');
    return redirect('/admin/category');
}

extract((array) $category);

if (is_post()) {
    $id   = req('id');
    $name = req('name');

    if (empty($id)) {
        $_err['id'] = 'Required';
    } else if (!is_numeric($id)) {
        $_err['id'] = 'Must be a number';
    }

    if (empty($name)) {
        $_err['name'] = 'Required';
    } else if (strlen($name) > 50) {
        $_err['name'] = 'Maximum length is 50 characters';
    } else if (!isValid($name)) {
        $_err['name'] = 'Only letters, numbers and spaces are allowed';
    } else if (!is_unique($name, 'category', 'name')) {
        $_err['name'] = 'Already exists';
    }

    if (!$_err) {
        $category_payload = [
            'name' => $name
        ];
        db_update('category', $category_payload, 'id', $id);
        temp('success', 'Category updated successfully');
        return redirect("/admin/category/update?id=$id");
    }
}
?>

<h1><?= "$_title : $category->name " ?></h1>

<form method="post" class="form">
    <label for="name">Category Name: </label>
    <?= html_text("name"); ?>
    <?= err('name') ?>

    <section>
        <button type="submit" class="primary" data-confirm>Update</button>
        <button type="reset" class="danger">Reset</button>
        <button type="button" data-get="/admin/category" class="success">Go Back</button>
    </section>
</form>