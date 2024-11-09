<?php

$fields = [
    ' '           => 'Product Picture',
    '  '          => 'Quantity',
    'total_price' => 'Total Price',
    'created_at'  => 'Created At',
    ' review' => 'Review'
];

$fields2 = [
    'shipping_address' => 'Shipping Address',
    'delivery_type'    => 'Delivery Type',
    'status'           => 'Status',
    ''                 => 'Action'
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
    // Check if the action is for canceling the order
    if (isset($_POST['cancel_order'])) {
        // Update the status of the order to "cancel"
        $stm    = $_db->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $params = ['cancelled', $order_id];

        $result = $stm->execute($params); // Execute the query

        if ($result) {
            temp('success', 'Cancel Order Successful');
        } else {
            temp('error', 'Failed to cancel the order. Please try again.');
        }

        // Redirect to the orders page
        redirect("/order");

        // Check if the action is for submitting a review
    } elseif (isset($_POST['action']) && $_POST['action'] === 'submit_review') {
        $product_id = req('product_id');
        $rating     = req('rating');
        $comment    = req('comment');
        $user_id    = $_USER_DATA->id;
        $stmt   = $_db->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$product_id, $user_id, $rating, $comment]);

        if ($result) {
            temp('success', 'Review submitted successfully.');
            redirect("/product/view?product_id=$product_id");
        } else {
            temp('error', 'Failed to submit your review. Please try again.');
        }
    }
}

if (is_post()) {
    // Update the status of the order to "cancel"
    $stm    = $_db->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $params = ['cancel', $order_id];

    // Assuming you have a method to execute a query like this
    $stm->execute($params);

    if ($result) {
        temp('success', 'Cancel Order Successful');
    } else {
        temp('error', 'Failed to cancel the order. Please try again.');
    }

    // Redirect to the orders page
    redirect("/order");
}

if (is_post()) {
    // Update the status of the order to "cancel"
    $stm = $_db->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $params = ['cancel', $order_id];

    // Assuming you have a method to execute a query like this
    $stm->execute($params);

    if ($result) {
        temp('success', 'Cancel Order Successful');
    } else {
        temp('error', 'Failed to cancel the order. Please try again.');
    }

    // Redirect to the orders page
    redirect("/order");
}

if (is_post()) {
    // Update the status of the order to "cancel"
    $stm = $_db->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $params = ['cancel', $order_id];

    // Assuming you have a method to execute a query like this
    $stm->execute($params);

    if ($result) {
        temp('success', 'Cancel Order Successful');
    } else {
        temp('error', 'Failed to cancel the order. Please try again.');
    }

    // Redirect to the orders page
    redirect("/order");
}

if (is_post()) {
    foreach ($arr as $o) :
        db_delete($item_delete, $order_id_delete, $o->order_id);
    endforeach;
    foreach ($arr1 as $or) :
        db_delete($orders_delete, $order_id_delete, $or->order_id);
    endforeach;
    temp('success', 'Cancel Order Successful');
    redirect("/order");
}

?>

<style>
    #order_cancel_table {
        margin-top: 1em;
    }

    /* rating*/
    .star-rating {
        direction: rtl;
        display: inline-block;
    }

    .star {
        font-size: 2em;
        color: #ccc;
        cursor: pointer;
    }

    .star:hover,
    .star:hover~.star {
        color: gold;
    }

    input[type="hidden"] {
        display: none;
    }
</style>

<body>
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
                    <td><?= $order->shipping_address ?? '' ?></td>
                    <td><?= $order->delivery_type ?? '' ?></td>
                    <td><?= $order->status ?? '' ?></td>
                    <td>
                        <form method="post">
                            <?php if ($order->status === 'delivered' || $order->status === 'cancelled') : ?>
                                <button type="button">Cancel</button>
                            <?php endif; ?>
                            <?php if ($order->status !== 'delivered' && $order->status !== 'cancelled') : ?>
                                <button data-confirm="Are you sure you want to Cancel the Order?" class="danger">Cancel</button>
                            <?php endif; ?>
                        </form>

                    </td>
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
                    <td>
                        <h2>Leave a Review</h2>
                        <?php if ($arr1 && $arr1[0]->status === 'delivered') : ?>
                            <form method="post">
                                <input type="hidden" name="action" value="submit_review">
                                <input type="hidden" name="product_id" value="<?= $product_id ?>">

                                <div class="star-rating" id="star-rating-<?= $product_id ?>">
                                    <input type="hidden" name="rating" id="rating-<?= $product_id ?>">
                                    <label class="star" data-value="5" data-product-id="<?= $product_id ?>">★</label>
                                    <label class="star" data-value="4" data-product-id="<?= $product_id ?>">★</label>
                                    <label class="star" data-value="3" data-product-id="<?= $product_id ?>">★</label>
                                    <label class="star" data-value="2" data-product-id="<?= $product_id ?>">★</label>
                                    <label class="star" data-value="1" data-product-id="<?= $product_id ?>">★</label>
                                </div>

                                <br>
                                <label for="comment">Review:</label>
                                <textarea name="comment" required></textarea>
                                <br>
                                <button type="submit">Submit Review</button>
                            </form>

                        <?php else : ?>
                            <p>You can only leave a review after you received your parcel.</p>
                        <?php endif; ?>
                    </td>
                    </tr>

                <?php
                    $previousOrderId = $ov->order_id; // Update the previous order ID
                endforeach; ?>
            <?php endif; ?>



            <button data-get="/order" class="success">Back</button>
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
                        <td><?= $order->status ?? '' ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="cancel_order" value="1"> <!-- Add this line -->
                                <?php if ($order->status === 'delivered' || $order->status === 'cancelled') : ?>
                                    <button type="button">Cancel</button>
                                <?php endif; ?>
                                <?php if ($order->status !== 'delivered' && $order->status !== 'cancelled') : ?>
                                    <button data-confirm="Are you sure you want to Cancel the Order?" class="danger">Cancel</button>
                                <?php endif; ?>
                            </form>
                        </td>

                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>




</body>
<script>
    document.querySelectorAll('.star').forEach(star => {
        star.addEventListener('mouseover', function() {
            const value = this.getAttribute('data-value');
            const productId = this.getAttribute('data-product-id');
            document.querySelectorAll(`#star-rating-${productId} .star`).forEach(s => {
                s.style.color = s.getAttribute('data-value') <= value ? 'gold' : '#ccc';
            });
        });

        star.addEventListener('mouseout', function() {
            const productId = this.getAttribute('data-product-id');
            const selectedRating = document.getElementById(`rating-${productId}`).value;
            document.querySelectorAll(`#star-rating-${productId} .star`).forEach(s => {
                s.style.color = s.getAttribute('data-value') <= selectedRating ? 'gold' : '#ccc';
            });
        });

        star.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            const productId = this.getAttribute('data-product-id');
            document.getElementById(`rating-${productId}`).value = value;
            document.querySelectorAll(`#star-rating-${productId} .star`).forEach(s => {
                s.style.color = s.getAttribute('data-value') <= value ? 'gold' : '#ccc';
            });
        });
    });
</script>