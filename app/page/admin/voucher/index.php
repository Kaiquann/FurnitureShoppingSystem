<?php
/**
 * @author: Mu Jun Yi
 * @modified: Chong Jun Xiang
 */
$_title = "Manage Voucher";

$fields = [
    'id'                     => 'ID',
    'code'                   => 'Code',
    'discount_type'          => 'Discount Type',
    'amount'                 => 'Amount',
    'description'            => 'Description',
    'min_spend'              => 'Min Spend (RM)',
    'max_spend'              => 'Max Spend (RM)',
    'usage_limit_per_coupon' => 'Qty Voucher',
    'usage_limit_per_user'   => 'Limit Per User',
    'is_active'              => 'Status',
    'expired_at'             => 'Expired At',
    ' '                      => 'Action',
];

$table_rows = req('table_rows', 0);
$search     = req('search');

$sort = req('sort');
key_exists($sort, $fields) || $sort = 'id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

$page = req('page', 1);

$table_rows_query = get_table_rows($table_rows);

$voucher = new SimplePager(
    "SELECT * FROM voucher
    WHERE ((id LIKE ?) OR (code LIKE ?))
    AND is_deleted = 0
    ORDER BY $sort $dir",
    ["%$search%", "%$search%"],
    $table_rows_query,
    $page
);

$arr = $voucher->result;
?>

<h1><?= $_title ?></h1>

<ul>
    <li>
        <a href="/admin" class="success">Go Back</a>
    </li>
    <li>
        <a href="/admin/voucher/add">Add Voucher</a>
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
        <?= $voucher->count ?> of <?= $voucher->item_count ?> record(s) |
        Page <?= $voucher->page ?> of <?= $voucher->page_count ?>
    </li>
</ul>

<form>
    <?= html_search("search", "data-search"); ?>
    <input type="hidden" name="sort" value="<?= $sort ?>">
    <input type="hidden" name="dir" value="<?= $dir ?>">
    <input type="hidden" name="table_rows" value="<?= $table_rows ?>">
</form>

<form action="/admin/voucher/delete" method="post" id="select-form"></form>

<table class="table detail">
    <thead>
        <tr>
            <?= table_headers($fields, $sort, $dir, "search=$search&page=$page&table_rows=$table_rows") ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($arr)) : ?>
            <tr>
                <td colspan="<?= count($fields) ?>">No record found</td>
            </tr>
        <?php endif; ?>
        <?php foreach ($arr as $v) : ?>
            <tr>
                <td><?= $v->id ?? 'N/A' ?></td>
                <td><?= $v->code ?? 'N/A' ?></td>
                <td><?= $v->discount_type ?? 'N/A' ?></td>
                <td><?= $v->amount ?? 'N/A' ?></td>
                <td><?= $v->description ?: 'N/A' ?></td>
                <td><?= $v->min_spend == 0 ? 'No Limit' : $v->min_spend ?></td>
                <td><?= $v->max_spend == 0 ? 'No Limit' : $v->max_spend ?></td>
                <td><?= $v->usage_limit_per_coupon == 0 ? 'Unlimited' : $v->usage_limit_per_coupon ?></td>
                <td><?= $v->usage_limit_per_user == 0 ? 'Unlimited' : $v->usage_limit_per_user ?></td>
                <td><?= $v->is_active ? 'Active' : 'Inactive' ?></td>
                <td><?= $v->expired_at ?? 'N/A' ?></td>
                <td>
                    <button data-get="/admin/voucher/update?id=<?= $v->id ?? '' ?>" class="success">Update</button>
                    <button data-post="/admin/voucher/delete?id=<?= $v->id ?? '' ?>"
                        data-confirm="Are you sure to delete <?= $v->name ?? '' ?>?" class="danger">Delete</button>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?= $voucher->html("search=$search&sort=$sort&dir=$dir&table_rows=$table_rows") ?>
