<?php
$_title = "Trashed Category";

$fields = [
    ' '    => '<input type="checkbox" class="select-box-all">',
    'id'   => 'ID',
    'name' => 'Name',
    '  '   => 'Action',
];

$id         = req('id', 0);
$table_rows = req('table_rows', 0);
$search     = req('search');

$sort = req('sort');
key_exists($sort, $fields) || $sort = 'id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

$page = req('page', 1);

$table_rows_query = get_table_rows($table_rows);

$category = new SimplePager(
    "SELECT * FROM category 
    WHERE ((id LIKE ?) OR (name LIKE ?)) 
    AND is_deleted = 1
    ORDER BY $sort $dir",
    ["%$search%", "%$search%"],
    $table_rows_query,
    $page,
);

$arr = $category->result;
?>

<h1><?= $_title ?></h1>

<ul>
    <li>
        <a href="/admin/category" class="success">Go Back</a>
    </li>
</ul>

<ul>
    <li><b>Batch Action</b></li>
    <li>
        <button type="button" data-message="Are you sure to recover all the selected category?" form="select-form"
            data-action="/admin/category/recover" class="select-btn select-submit-btn warning">Recover</button>
    </li>
    <li>
        <button type="button" data-message="Are you sure to permanently delete all the selected category?"
            form="select-form" data-action="/admin/category/permanent_delete"
            class="select-btn select-submit-btn danger">Delete</button>
    </li>
</ul>

<ul>
    <li>
        <label for="table_rows">Rows: </label>
        <?= html_select("table_rows", TABLE_ROWS_LIST, $table_rows); ?>
    </li>
</ul>

<ul>
    <li>
        <?= $category->count ?> of <?= $category->item_count ?> record(s) |
        Page <?= $category->page ?> of <?= $category->page_count ?>
    </li>
</ul>

<form>
    <?= html_search("search", "data-search"); ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="sort" value="<?= $sort ?>">
    <input type="hidden" name="dir" value="<?= $dir ?>">
    <input type="hidden" name="table_rows" value="<?= $table_rows ?>">
</form>

<form action="" method="post" id="select-form"></form>

<table class="table detail">
    <thead>
        <tr>
            <?= table_headers($fields, $sort, $dir, "id=$id&search=$search&page=$page&table_rows=$table_rows") ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($arr)) : ?>
            <tr>
                <td colspan="<?= count($fields) ?>">No record found</td>
            </tr>
        <?php endif; ?>
        <?php foreach ($arr as $c) : ?>
            <tr>
                <td><?= html_checkbox('id[]', $c->id, null, "form='select-form' class='select-box'"); ?></td>
                <td><?= $c->id ?></td>
                <td><?= html_print($c->name) ?></td>
                <td>
                    <button data-post="recover?id=<?= $c->id ?>"
                        data-confirm="Are you sure to recover <?= html_print($c->name) ?>?" class="warning">Recover</button>
                    <button data-post="permanent_delete?id=<?= $c->id ?>"
                        data-confirm="Are you sure to permanently delete <?= html_print($c->name) ?>?"
                        class="danger">Delete</button>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?= $category->html("id=$id&search=$search&sort=$sort&dir=$dir&table_rows=$table_rows"); ?>
