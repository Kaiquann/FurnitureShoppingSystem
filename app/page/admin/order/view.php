<?php

$fields = [
    ' '           => 'Product Picture',
    '  '          => 'Quantity',
    'total_price' => 'Total Price',
    'created_at'  => 'Created At'
];

$fields2 = [
    'shipping_address' => 'Shipping Address',
    'delivery_type'    => 'Delivery Type',
    'status'           => 'Status'
];

$id       = req('id', 0);
$search   = req('search');
$order_id = req('order_id'); // Retrieve order_id from the URL

$sort = req('sort');
key_exists($sort, $fields) || $sort = 'created_at';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

$page = req('page', 1);

global $_USER_DATA;

// Filter the ordersItem based on the order_id from the URL
$ordersItem = new SimplePager(
    "SELECT * FROM item
        WHERE order_id = ?
        ORDER BY $sort $dir",
    [$order_id],
    10,
    $page
);

$orders = new SimplePager(
    "SELECT * FROM orders
       WHERE order_id = ?
        ORDER BY $sort $dir",
    [$order_id],
    10,
    $page
);

$arr    = $ordersItem->result;
$arr1   = $orders->result;
$_title = "Order: $order_id ";

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

<style>
    #order_cancel_table {
        margin-top: 1em;
    }
</style>

<h1><?= $_title ?></h1>

<table class="table detail">
    <thead>
        <tr>
            <?= table_headers($fields, $sort, $dir, "order_id=$order_id&search=$search&page=$page") ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($arr)) : ?>
            <tr>
                <td colspan="<?= count($fields) ?>">No record found</td>
            </tr>
        <?php else : ?>
            <?php
            foreach ($arr as $index => $ov) : ?>

                <?php
                $product_id      = $ov->product_id;
                $product_picture = new SimplePager(
                    "SELECT image_url FROM product_image
                            WHERE product_id = ?",
                    [$product_id],
                    1,
                    1
                );

                $arr2 = $product_picture->result;

                if (!empty($arr2)) {
                    // Display only the first image
                    $pp = $arr2[0]; // Get the first image
                    ?>
                    <td><img src="<?= $pp->image_url ?? '' ?>" /></td>
                    <?php
                } else {
                    // Optional: Display a placeholder if no images are available
                    ?>
                    <td><img src="path/to/placeholder.png" alt="No image available" /></td>
                    <?php
                }
                ?>

                <td><?= $ov->quantity ?? '' ?></td>
                <td><?= $ov->total_price ?? '' ?></td>
                <td><?= $ov->created_at ?? '' ?></td>
                </tr>
                <?php
                $previousOrderId = $ov->order_id; // Update the previous order ID
            endforeach; ?>
        <?php endif; ?>

        <button data-get="/admin/order" class="success">Back</button>
    </tbody>
</table>

<table class="table detail" id="order_cancel_table">
    <thead>
        <tr>
            <?= table_headers($fields2, $sort, $dir, "order_id=$order_id&search=$search&page=$page") ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($arr1)) : ?>
            <tr>
                <td colspan="<?= count($fields2) ?>">No record found</td>
            </tr>
        <?php else : ?>
            <?php foreach ($arr1 as $order) : ?>
                <tr>
                    <td><?= $order->shipping_address ?? '' ?></td>
                    <td><?= $order->delivery_type ?? '' ?></td>
                    <td>
                    <form method="post">
                        <select name="status">
                            <option value="pending" <?= $order->status == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $order->status == 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="shipping" <?= $order->status == 'shipping' ? 'selected' : '' ?>>Shipping</option>
                            <option value="delivered" <?= $order->status == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $order->status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <input type="hidden" name="order_id" value="<?= $order->order_id ?>">
                        <button type="submit" class="success">Update Status</button> <!-- Change here -->
                    </form></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>