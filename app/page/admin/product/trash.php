<?php
$_title = "Trashed Product";

$fields = [
    ' '           => '<input type="checkbox" class="select-box-all">',
    'id'          => 'ID',
    'name'        => 'Name',
    'stock'       => 'Stock',
    'price'       => 'Price (RM)',
    'category_id' => 'Category',
    '  '          => 'Action',
];

$category_sort = req('category_sort', 0);
$price_range   = req('price_range', 0);
$table_rows    = req('table_rows', 0);
$search        = req('search');

$sort = req('sort');
key_exists($sort, $fields) || $sort = 'id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

$page = req('page', 1);

$price_range_query = get_price_range($price_range);
$table_rows_query  = get_table_rows($table_rows);

$product = new SimplePager(
    "SELECT p.* FROM product p
    JOIN category c ON p.category_id = c.id
    WHERE (p.category_id = ? OR ? = 0) AND ((p.id LIKE ?) OR (p.name LIKE ?))
    AND p.price BETWEEN $price_range_query
    AND p.is_deleted = 1
    AND c.is_deleted IN (0, 1)
    ORDER BY p.$sort $dir",
    [$category_sort, $category_sort, "%$search%", "%$search%"],
    $table_rows_query,
    $page
);

$option_category = $_db->query("SELECT * FROM category WHERE is_deleted IN (0, 1)")->fetchAll();
$option_category = array_column($option_category, 'name', 'id');

$arr = $product->result;
?>

<h1><?= $_title ?></h1>

<ul>
    <li>
        <a href="/admin/product" class="success">Go Back</a>
    </li>
</ul>

<ul>
    <li><b>Batch Action</b></li>
    <li>
        <label for="batch_action_category">Category: </label>
        <?= html_select("batch_action_category", $option_category, '', 'form="select-form"'); ?>
        <button type="button" data-message="Are you sure to apply the selected category?" form="select-form"
            data-action="/admin/product/update_category?forward=<?= getForwardUrl() ?>"
            class="select-btn select-submit-btn primary">Apply</button>
    </li>
    <li>
        <button type="button" data-message="Are you sure to recover all the selected product?" form="select-form"
            data-action="/admin/product/recover" class="select-btn select-submit-btn warning">Recover</button>
    </li>
    <li>
        <button type="button" data-message="Are you sure to permanent delete all the selected product?"
            form="select-form" data-action="/admin/product/permanent_delete"
            class="select-btn select-submit-btn danger">Delete</button>
    </li>
</ul>

<ul>
    <li>
        <label for="category_sort">Category: </label>
        <?= html_select("category_sort", $option_category, $category_sort); ?>
    </li>
    <li>
        <label for="price_range">Price Range: </label>
        <?= html_select("price_range", PRICE_RANGE_LIST, $price_range); ?>
    </li>
    <li>
        <label for="table_rows">Rows: </label>
        <?= html_select("table_rows", TABLE_ROWS_LIST, $table_rows); ?>
    </li>
</ul>

<ul>
    <li>
        <?= $product->count ?> of <?= $product->item_count ?> record(s) |
        Page <?= $product->page ?> of <?= $product->page_count ?>
    </li>
</ul>

<form>
    <?= html_search("search", "data-search"); ?>
    <input type="hidden" name="category_sort" value="<?= $category_sort ?>">
    <input type="hidden" name="sort" value="<?= $sort ?>">
    <input type="hidden" name="dir" value="<?= $dir ?>">
    <input type="hidden" name="table_rows" value="<?= $table_rows ?>">
</form>

<form action="" method="post" id="select-form"></form>

<table class="table detail">
    <thead>
        <tr>
            <?= table_headers($fields, $sort, $dir, "category_sort=$category_sort&search=$search&page=$page&price_range=$price_range&table_rows=$table_rows") ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($arr)) : ?>
            <tr>
                <td colspan="<?= count($fields) ?>">No record found</td>
            </tr>
        <?php endif; ?>
        <?php foreach ($arr as $p) : ?>
            <tr>
                <td><?= html_checkbox('id[]', $p->id, null, "form='select-form' class='select-box'"); ?></td>
                <td><?= $p->id ?></td>
                <td><?= html_print($p->name) ?></td>
                <td><?= html_print($p->stock) ?></td>
                <td><?= html_print($p->price) ?></td>
                <td><?= html_print(db_select_single('category', 'id', $p->category_id)->name) ?></td>
                <td>
                    <button data-get="/admin/product/view?id=<?= $p->id ?>&forward=<?= getForwardUrl() ?>"
                        class="primary">View</button>
                    <button data-get="/admin/product/update?id=<?= $p->id ?>&forward=<?= getForwardUrl() ?>"
                        class="success">Update</button>
                    <button data-post="/admin/product/recover?id=<?= $p->id ?>"
                        data-confirm="Are you sure to recover <?= html_print($p->name) ?>?" class="warning">Recover</button>
                    <button data-post="/admin/product/permanent_delete?id=<?= $p->id ?>"
                        data-confirm="Are you sure to permanent delete <?= html_print($p->name) ?>?"
                        class="danger">Delete</button>
                    <div class="popup">
                        <?php
                        $product_image = db_select('product_image', 'product_id', $p->id);
                        foreach ($product_image as $pi) {
                            html_image($p->id, $pi->image_url);
                        }
                        ?>
                    </div>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?= $product->html("category_sort=$category_sort&search=$search&sort=$sort&dir=$dir&price_range=$price_range&table_rows=$table_rows") ?>
