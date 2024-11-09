<?php
$_title = "Add Category";

if (is_post()) {
    $name = req('name');

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
        db_insert('category', $category_payload);
        temp('success', 'Category added successfully');
        return redirect('/admin/category');
    }
}
?>

<h1><?= $_title ?></h1>

<form method="post" class="form">
    <label for="name">Category Name: </label>
    <?= html_text("name"); ?>
    <?= err('name') ?>

    <section>
        <button type="submit" class="primary" data-confirm="Are you sure to add this category?">Add</button>
        <button type="reset" class="danger">Reset</button>
        <button type="button" data-get="/admin/category" class="success">Go Back</button>
    </section>
</form>