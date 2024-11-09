<?php
$_title = "Order Manage";

$fields = [
    'order_id'       => 'Order ID',
    'transaction_id' => 'Transaction ID',
    'total_price'    => 'Total Price',
    'created_at'     => 'Created At',
    'status'         => 'Order Status',
    ' '              => 'Action',
];

$id     = req('id', 0);
$search = req('search');

$sort = req('sort');
key_exists($sort, $fields) || $sort = 'created_at';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'desc';

$page = req('page', 1);

global $_USER_DATA;

$orders = new SimplePager(
    "SELECT * FROM orders
        WHERE ((order_id LIKE ?)
        OR (transaction_id LIKE ?))
        ORDER BY $sort $dir",
    ["%$search%", "%$search%"],
    10,
    $page
);

$arr = $orders->result;

if (is_post()) {
    $order_id = req('order_id'); // Get the order ID from the form submission
    $status   = req('status'); // Get the selected status from the dropdown

    if ($order_id && in_array($status, ['pending', 'processing', 'shipping', 'delivered', 'cancelled'])) {
        // Update the status of the order
        $stm    = $_db->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $params = [$status, $order_id];

        $result = $stm->execute($params);

        if ($result) {
            temp('success', 'Order status updated successfully.');
        } else {
            temp('error', 'Failed to update the order status. Please try again.');
        }
    } else {
        temp('error', 'Invalid order or status.');
    }
    redirect("/admin/order");
}
?>

<h1><?= $_title ?></h1>

<ul>
    <li>
        <a href="/admin" class="success">Go Back</a>
    </li>
    <li>
        <?= $orders->count ?> of <?= $orders->item_count ?> record(s) |
        Page <?= $orders->page ?> of <?= $orders->page_count ?>
    </li>
</ul>

<form>
    <?= html_search("search", "data-search"); ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="sort" value="<?= $sort ?>">
    <input type="hidden" name="dir" value="<?= $dir ?>">
</form>

<table class="table detail">
    <thead>
        <tr>
            <?= table_headers($fields, $sort, $dir, "id=$id&search=$search&page=$page") ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($arr)) : ?>
            <tr>
                <td colspan="<?= count($fields) ?>">No record found</td>
            </tr>
        <?php endif; ?>
        <?php foreach ($arr as $o) : ?>
            <tr>
                <td><?= $o->order_id ?? '' ?></td>
                <td><?= $o->transaction_id ?? '' ?></td>
                <td><?= $o->total_price ?? '' ?></td>
                <td><?= $o->created_at ?? '' ?></td>
                <td>
                    <form method="post">
                        <select name="status">
                            <option value="pending" <?= $o->status == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $o->status == 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="shipping" <?= $o->status == 'shipping' ? 'selected' : '' ?>>Shipping</option>
                            <option value="delivered" <?= $o->status == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $o->status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <input type="hidden" name="order_id" value="<?= $o->order_id ?>">
                        <button type="submit" class="success">Update Status</button> <!-- Change here -->
                    </form>
                </td>
                <td>
                    <button data-get="/admin/order/view?order_id=<?= $o->order_id ?? '' ?>" class="primary">View
                        Order</button>
                    <?php
                    $stmt = $_db->prepare(
                        "SELECT * FROM transaction
                        WHERE payment_id = ?
                        AND status != 'cancelled'"
                    );
                    $stmt->execute([$o->transaction_id]);
                    $transaction = $stmt->fetch();
                    ?>
                    <?php if ($transaction) : ?>
                        <button
                            data-get="/transaction/receipt?payment_id=<?= $o->transaction_id ?? '' ?>&forward=<?= getForwardUrl() ?>"
                            class="primary">View Receipt</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?= $orders->html("id=$id&search=$search&sort=$sort&dir=$dir"); ?>
