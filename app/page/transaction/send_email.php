<?php

/**
 * @author Chong Jun Xiang
 */
require_once root('lib/Payment.php');

$_title = "Send Receipt To Email";

if (!isLoggedIn()) {
    temp('danger', 'Unauthorized access!');
    return redirect('/login');
}

if (is_post()) {
    $forward = req('forward');

    if (empty($forward)) {
        $forward = '/transaction';
    }

    $payment_id = req('payment_id');

    if (empty($payment_id)) {
        temp('warning', 'Payment ID cannot be blank!');
        return redirect($forward);
    }

    $transaction = db_select_single('transaction', 'payment_id', $payment_id);

    if (!$transaction) {
        temp('danger', 'Transaction not found!');
        return redirect($forward);
    }

    $order = db_select_single('orders', 'transaction_id', $payment_id);

    if (!$order) {
        temp('danger', 'Order not found!');
        return redirect($forward);
    }

    $user_id = $order->user_id;
    $user    = db_select_single('users', 'id', $user_id);

    if (!$user) {
        temp('danger', 'User not found!');
        return redirect($forward);
    }

    $session_id = $transaction->session_id;
    $order_id   = $order->order_id;
    $email      = $user->email;

    $payment_receipt      = new Payment($session_id);
    $payment_receipt_body = $payment_receipt->generate_payment_receipt($order_id);
    sendEmail($email, PAYMENT_RECEIPT_SUBJECT, $payment_receipt_body);

    temp('success', "Receipt sent to $email successfully");
    return redirect($forward);
}

?>
