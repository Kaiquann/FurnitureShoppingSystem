<?php
$_title = "Transaction";

$fields = [
    'payment_id' => 'Payment ID',
    'amount'     => 'Amount (RM)',
    'method'     => 'Method',
    'status'     => 'Status',
    'created_at' => 'Created At',
    ' '          => 'Action',
];

if (!isLoggedIn() || isAdmin()) {
    temp("danger", "Only members can access this page");
    return redirect("/");
}

global $_USER_DATA;

$transaction_method = req('transaction_method', '');
$transaction_status = req('transaction_status', '');
$table_rows         = req('table_rows', 0);
$search             = req('search');

$sort = req('sort');
key_exists($sort, $fields) || $sort = 'created_at';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'desc';

$page = req('page', 1);

$table_rows_query = get_table_rows($table_rows);

$transaction = new SimplePager(
    "SELECT * FROM transaction
        WHERE ((payment_id LIKE ?) 
        OR (amount LIKE ?))
        AND (method = ? OR ? = '')
        AND (status = ? OR ? = '')
        AND user_id = ?
        ORDER BY $sort $dir",
    ["%$search%", "%$search%", $transaction_method, $transaction_method, $transaction_status, $transaction_status, $_USER_DATA->id],
    $table_rows_query,
    $page,
);

$arr = $transaction->result;

$transaction_method_list = $_db->query('SELECT * FROM transaction WHERE method IS NOT NULL GROUP BY method')->fetchAll();
$transaction_method_list = array_column($transaction_method_list, 'method', 'method');
$transaction_status_list = $_db->query('SELECT * FROM transaction WHERE status IS NOT NULL GROUP BY status')->fetchAll();
$transaction_status_list = array_column($transaction_status_list, 'status', 'status');
?>

<h1><?= $_title ?></h1>

<ul>
    <li>
        <a href="/" class="success">Go Back</a>
    </li>
</ul>

<ul>
    <li>
        <label for="transaction_method">Method: </label>
        <?= html_select("transaction_method", $transaction_method_list, ''); ?>
    </li>
    <li>
        <label for="transaction_status">Status: </label>
        <?= html_select("transaction_status", $transaction_status_list, ''); ?>
    </li>
    <li>
        <label for="table_rows">Rows: </label>
        <?= html_select("table_rows", TABLE_ROWS_LIST, $table_rows); ?>
    </li>
</ul>

<ul>
    <li>
        <?= $transaction->count ?> of <?= $transaction->item_count ?> record(s) |
        Page <?= $transaction->page ?> of <?= $transaction->page_count ?>
    </li>
</ul>

<form>
    <?= html_search("search", "data-search"); ?>
    <input type="hidden" name="transaction_method" value="<?= $transaction_method ?>">
    <input type="hidden" name="transaction_status" value="<?= $transaction_status ?>">
    <input type="hidden" name="sort" value="<?= $sort ?>">
    <input type="hidden" name="dir" value="<?= $dir ?>">
    <input type="hidden" name="table_rows" value="<?= $table_rows ?>">
</form>

<table class="table detail">
    <thead>
        <tr>
            <?= table_headers($fields, $sort, $dir, "page=$page&search=$search&table_rows=$table_rows&transaction_method=$transaction_method&transaction_status=$transaction_status") ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($arr)) : ?>
            <tr>
                <td colspan="<?= count($fields) ?>">No record found</td>
            </tr>
        <?php endif; ?>
        <?php foreach ($arr as $t) : ?>
            <tr>
                <td><?= $t->payment_id ?? '' ?></td>
                <td><?= $t->amount ?? '' ?></td>
                <td><?= $t->method ?? '' ?></td>
                <td><?= $t->status ?? '' ?></td>
                <td><?= $t->created_at ?? '' ?></td>
                <td style="text-align: center;">
                    <?php if ($t->status != 'cancelled') : ?>
                        <button data-get="/transaction/receipt?payment_id=<?= $t->payment_id ?? '' ?>" class="primary">View
                            Receipt</button>
                    <?php else : ?>
                        N/A
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?php
$transaction->html("sort=$sort&dir=$dir&search=$search&table_rows=$table_rows&transaction_method=$transaction_method&transaction_status=$transaction_status");
?>
