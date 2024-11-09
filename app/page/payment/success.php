<?php

/**
 * @author: Chong Jun Xiang
 */
$_title = "Thanks for your order!";

if (is_get()) {
    $payment_id = req('payment_id');

    if (empty($payment_id)) {
        temp('warning', 'Payment ID cannot be blank!');
        return redirect('/');
    }

    $transaction = db_select_single('transaction', 'payment_id', $payment_id);

    if (!$transaction) {
        temp('danger', 'Transaction not found!');
        return redirect('/');
    }

    extract((array) $transaction);

    $order = db_select_single('orders', 'transaction_id', $payment_id);
}
?>

<h1><?= $_title ?></h1>

<section>
    <h3>Payment Details</h3>

    <p><strong>Payment ID:</strong> <?= $payment_id ?? '' ?></p>
    <p><strong>Order ID:</strong> <?= $order->order_id ?? '' ?></p>
    <p><strong>Amount:</strong> <?= $amount ?? '' ?></p>
    <p><strong>Created:</strong> <?= $created_at ?? '' ?></p>
    <p><strong>Payment Method Types:</strong> <?= $method ?? '' ?></p>
    <p><strong>Status:</strong> <?= $status ?? '' ?></p>
</section>

<section>
    <p>
        We appreciate your business!
        If you have any questions, please email
        <a href="mailto:contactus@tarumt.com">contactus@tarumt.com</a>.
    </p>
</section>

<a href="/transaction/receipt?payment_id=<?= $payment_id ?? '' ?>" class="success">Print Receipt</a>