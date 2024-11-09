<?php
/**
 * @author Chong Jun Xiang
 */
$_title = "Payment Receipt";

if (!isLoggedIn()) {
    temp('danger', 'Unauthorized access!');
    return redirect('/login');
}

if (is_get()) {
    $forward = req('forward');

    if (empty($forward)) {
        $forward = '/transaction';
    }

    $payment_id = req('payment_id');

    if (empty($payment_id)) {
        temp('warning', 'Payment ID cannot be blank!');
        return redirect('/');
    }

    $stmt = $_db->prepare(
        'SELECT * FROM transaction
        WHERE payment_id = ?
        AND status = ?'
    );
    $stmt->execute([$payment_id, "succeeded"]);
    $transaction = $stmt->fetch();

    if (!$transaction) {
        temp('danger', 'Transaction Receipt not found!');
        return redirect($forward);
    }

    extract((array) $transaction);

    $user_data = db_select_single('users', 'id', $user_id);
    $order     = db_select_single('orders', 'transaction_id', $payment_id);

    if (!$order) {
        temp('danger', 'The transaction order details not found!');
        return redirect($forward);
    }

    $order_item = db_select('item', 'order_id', $order->order_id);

    if (!$order_item) {
        temp('danger', 'The transaction order details not found!');
        return redirect($forward);
    }

    $_title = "Payment Receipt - $created_at";
}
?>

<section>
    <div class="container card">
        <div printable>
            <title><?= $_title ?></title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                }

                .container {
                    width: 100%;
                    max-width: 600px;
                    margin: 0 auto;
                }

                .card {
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    margin-top: 50px;
                    padding: 20px;
                    text-align: center;
                }

                .card-title {
                    font-size: 24px;
                    margin-bottom: 20px;
                }

                .card-text {
                    font-size: 18px;
                    margin-bottom: 20px;
                }

                .btn {
                    display: inline-block;
                    color: #fff;
                    background-color: #0d6efd;
                    border-color: #0d6efd;
                    padding: 0.375rem 0.75rem;
                    font-size: 1rem;
                    line-height: 1.5;
                    border-radius: 0.25rem;
                    text-decoration: none;
                }

                .btn:hover {
                    color: #fff;
                    background-color: #0b5ed7;
                    border-color: #0a58ca;
                }

                .table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }

                .table td {
                    padding: 10px;
                    border: 1px solid #ddd;
                }

                .table th {
                    padding: 10px;
                    border: 1px solid #ddd;
                }

                .table tr:nth-child(even) {
                    background-color: #f2f2f2;
                }

                .table tr:hover {
                    background-color: #ddd;
                }

                .table tr td:first-child {
                    text-align: left;
                }

                .table tr td:last-child {
                    text-align: left;
                }
            </style>
            <div class="card">
                <h4 style="color: gray">Receipt from <?= COMPANY_NAME ?></h4>
                <h3>RM<?= $amount ?></h3>
                <h4 style="color: gray">Paid on <?= $created_at ?> </h4>
                <table class="table">
                    <tr>
                        <td>Payment ID</td>
                        <td>:</td>
                        <td><?= html_print($payment_id) ?></td>
                    </tr>
                    <tr>
                        <td>Order ID</td>
                        <td>:</td>
                        <td><?= html_print($order->order_id) ?></td>
                    </tr>
                    <tr>
                        <td>Amount</td>
                        <td>:</td>
                        <td>RM<?= html_print($amount) ?></td>
                    </tr>
                    <tr>
                        <td>Payment method</td>
                        <td>:</td>
                        <td><?= html_print($method) ?></td>
                    </tr>
                    <tr>
                        <td>Payment Status</td>
                        <td>:</td>
                        <td><?= html_print($status) ?></td>
                    </tr>
                    <tr>
                        <td>Shipping Type</td>
                        <td>:</td>
                        <td><?= html_print(ucwords($order->delivery_type)) ?></td>
                    </tr>
                </table>

                <table class="table">
                    <thead>
                        <th>Product</th>
                        <th></th>
                        <th>Total</th>
                    </thead>
                    <tbody>
                        <?php foreach ($order_item as $item) :
                            $product = db_select_single('product', 'id', $item->product_id);
                            ?>
                            <tr>
                                <td>
                                    <b><?= html_print($product->name) ?></b>
                                    <br />
                                    Qty <?= html_print($item->quantity) ?>
                                </td>
                                <td>:</td>
                                <td>
                                    <b>RM<?= html_print($item->total_price) ?></b>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>
                                <b>Subtotal</b>
                            </td>
                            <td>:</td>
                            <td>
                                <b>RM<?= html_print($order->subtotal) ?></b>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Total Discount</b>
                            </td>
                            <td>:</td>
                            <td>
                                <b>- RM<?= html_print($order->discount_price) ?></b>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Shipping Fee</b>
                            </td>
                            <td>:</td>
                            <td>
                                <?php
                                match ($order->delivery_type) {
                                    'express' => $delivery_amount = 20,
                                    'normal'  => $delivery_amount = 10
                                };
                                ?>
                                <b>RM<?= html_print($delivery_amount) ?></b>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>SST 8%</b>
                            </td>
                            <td>:</td>
                            <td>
                                <b>RM<?= html_print($order->amount_tax) ?></b>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Total</b>
                            </td>
                            <td>:</td>
                            <td>
                                <b>RM<?= html_print($amount) ?></b>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Amount paid</b>
                            </td>
                            <td>:</td>
                            <td>
                                <b>RM<?= html_print($amount) ?></b>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Shipping Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <?= html_print($order->shipping_address); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <section>
            <button class="btn primary" data-get="<?= $forward ?>">Go Back</button>
            <button id="print_btn" class="btn">Print Receipt</button>
            <?php if (in_array($user_data->is_deleted, [0, 1])) : ?>
                <button id="send_email" class="btn" data-confirm="Are you sure to send receipt to email?"
                    data-post="/transaction/send_email?payment_id=<?= $payment_id ?? '' ?>&forward=<?= getForwardUrl() ?>">Send
                    Receipt To Email</button>
            <?php endif; ?>
        </section>
    </div>
</section>