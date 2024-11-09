<?php
$_title = "Manage Transaction";

$fields = [
    'id'         => 'ID',
    'payment_id' => 'Payment ID',
    'amount'     => 'Amount (RM)',
    'method'     => 'Method',
    'status'     => 'Status',
    'created_at' => 'Created At',
    ' '          => 'Action',
];

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
        ORDER BY $sort $dir",
    ["%$search%", "%$search%", $transaction_method, $transaction_method, $transaction_status, $transaction_status],
    $table_rows_query,
    $page
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
        <a href="/admin" class="success">Go Back</a>
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
            <?= table_headers($fields, $sort, $dir, "search=$search&page=$page&table_rows=$table_rows&transaction_method=$transaction_method&transaction_status=$transaction_status") ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($arr)) : ?>
            <tr>
                <td colspan="<?= count($fields) ?>">No record found</td>
            </tr>
        <?php endif; ?>
        <?php foreach ($arr as $t) :
            $user = db_select_single('users', 'id', $t->user_id); ?>
            <tr>
                <td><?= $t->id ?? '' ?></td>
                <td><?= $t->payment_id ?? '' ?></td>
                <td><?= $t->amount ?? '' ?></td>
                <td><?= $t->method ?? '' ?></td>
                <td><?= $t->status ?? '' ?></td>
                <td><?= $t->created_at ?? '' ?></td>
                <td>
                    <?php if ($t->status != 'cancelled') : ?>
                        <button
                            data-get="/transaction/receipt?payment_id=<?= $t->payment_id ?? '' ?>&forward=<?= getForwardUrl() ?>"
                            class="primary">View Receipt</button>
                    <?php endif; ?>
                    <?php
                    $stmt = $_db->prepare('SELECT * FROM users WHERE id = ? AND is_deleted IN (0, 1)');
                    $stmt->execute([$t->user_id]);
                    $user = $stmt->fetch();
                    if ($user) {
                        ?>
                        <button data-get="/admin/user/view?id=<?= $t->user_id ?? '' ?>&forward=<?= getForwardUrl() ?>"
                            class="primary">View User</button>
                    <?php } ?>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?= $transaction->html("search=$search&sort=$sort&dir=$dir&table_rows=$table_rows&transaction_method=$transaction_method&transaction_status=$transaction_status") ?>
