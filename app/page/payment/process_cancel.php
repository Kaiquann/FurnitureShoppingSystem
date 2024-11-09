<?php

/**
 * @author: Chong Jun Xiang
 */
require_once root('lib/Payment.php');

$_title = "Process Cancel Transaction";

if (is_get()) {
    $user_id        = req('user_id');
    $session_id     = req('session_id');
    $product_id     = session('product_id');
    $delivery_type  = session('delivery_type');
    $temp_quantity  = session('quantity');
    $discount_price = session('discount_price');
    $voucher_id     = session('voucher_id') ?? null;

    if (empty($user_id) || empty($session_id) || empty($product_id) || empty($delivery_type)) {
        temp('danger', 'Invalid action');
        return redirect('/');
    }

    $stripe = new Stripe();
    try {
        $payment = new Payment($session_id);
    }
    catch (Exception $e) {
        temp('danger', 'The transaction did not created successfully!');
        return redirect('/');
    }
    $payment_id  = $payment->payment->id;
    $transaction = db_select_single('transaction', 'payment_id', $payment_id);
    if (!is_unique($payment_id, 'transaction', 'payment_id')) {
        temp('danger', 'The transaction has been processed!');
        return redirect('/cart');
    }
    $order_id                = "O" . uniqid() . getDateTime('ymdHis');
    $amount                  = $payment->payment->amount / 100;
    $payment_method_types    = implode(', ', $payment->payment->payment_method_types);
    $status                  = 'cancelled';
    $subtotal                = $payment->subtotal;
    $amount_discount         = ($payment->amount_discount / 100) ?? 0;
    $amount_tax              = ($payment->amount_tax / 100) ?? 0;
    $created                 = date('Y-m-d H:i:s', $payment->payment->created);
    $shipping_address        = $payment->shipping_address;
    $shipping_address_string = "$shipping_address->line1, $shipping_address->line2, $shipping_address->postal_code, $shipping_address->city, $shipping_address->state";

    // Create the transaction payload
    $transaction_payload = [
        'user_id'    => $user_id,
        'session_id' => $session_id,
        'payment_id' => $payment_id,
        'amount'     => $amount,
        'method'     => $payment_method_types,
        'created_at' => $created,
        'status'     => $status
    ];
    db_insert('transaction', $transaction_payload);

    $user_data = db_select_single('users', 'id', $user_id);

    $order_payload = [
        "order_id"         => $order_id,
        "transaction_id"   => $payment_id,
        "user_id"          => $user_id,
        "voucher_id"       => $voucher_id,
        "discount_price"   => $amount_discount,
        "amount_tax"       => $amount_tax,
        "subtotal"         => $subtotal,
        "total_price"      => $amount,
        "shipping_address" => $shipping_address_string,
        "delivery_type"    => $delivery_type,
        "status"           => $status
    ];
    db_insert("orders", $order_payload);

    $_db->beginTransaction();
    foreach ($product_id as $id) :
        $stmt = $_db->prepare('
            SELECT * FROM cart
            WHERE user_id = ?
            AND product_id = ?
        ');
        $stmt->execute([$user_data->id, $id]);
        $cart = $stmt->fetch();

        $stmt = $_db->prepare('
            SELECT * FROM product
            WHERE id = ?
        ');
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        $quantity    = $temp_quantity ?: $cart->quantity;
        $total_price = $temp_quantity ? $product->price * $quantity : $cart->total_amount;

        $stmt = $_db->prepare('
            INSERT INTO item
            (order_id, product_id, quantity, total_price)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([$order_id, $id, $quantity, $total_price]);
    endforeach;
    $_db->commit();

    unsetSession('temp_user_address_id');
    unsetSession('delivery_type');
    unsetSession('quantity');
    unsetSession('product_id');
    unsetSession('voucher_id');
    unsetSession('discount_price');

    return redirect("/payment/cancel?payment_id=$payment_id");
}

if (is_post()) {
    temp("danger", "Invalid action");
    return redirect('/');
}
