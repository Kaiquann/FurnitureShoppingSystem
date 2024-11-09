<?php
const PAYMENT_RECEIPT_SUBJECT          = "Payment Receipt";
const PAYMENT_RECEIPT_BODY             = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>Payment Receipt</title><style>body{font-family:Arial,sans-serif}.container{width:100%;max-width:600px;margin:0 auto}.card{border:1px solid #ddd;border-radius:5px;margin-top:50px;padding:20px;text-align:center}.card-title{font-size:24px;margin-bottom:20px}.card-text{font-size:18px;margin-bottom:20px}.btn{display:inline-block;color:#fff;background-color:#0d6efd;border-color:#0d6efd;padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:.25rem;text-decoration:none}.btn:hover{color:#fff;background-color:#0b5ed7;border-color:#0a58ca}.table{width:100%;border-collapse:collapse;margin-bottom:20px}.table td{padding:10px;border:1px solid #ddd}.table th{padding:10px;border:1px solid #ddd}.table tr:nth-child(even){background-color:#f2f2f2}.table tr:hover{background-color:#ddd}.table tr td:first-child{text-align:left}.table tr td:last-child{text-align:left}</style></head><body><div class='container'><div class='card'><h4 style='color:gray'>Receipt from TARUMT FURNITURE</h4><h3>RM{{ total_amount }}</h3><h4 style='color:gray'>Paid on {{ created_at }}</h4><table class='table'><tr><td>Transaction ID</td><td>:</td><td>{{ payment_id }}</td></tr><tr><td>Order ID</td><td>:</td><td>{{ order_id }}</td></tr><tr><td>Amount</td><td>:</td><td>RM{{ payment_amount }}</td></tr><tr><td>Payment method</td><td>:</td><td>{{ payment_method }}</td></tr><tr><td>Payment Status</td><td>:</td><td>{{ payment_status }}</td></tr><tr><td>Shipping Type</td><td>:</td><td>{{ delivery_type }}</td></tr></table><table class='table'><thead><th>Product</th><th></th><th>Total</th></thead><tbody>{{ order_list }}</tbody><tfoot><tr><td><b>Subtotal</b></td><td>:</td><td><b>RM{{ subtotal }}</b></td></tr><tr><td><b>Total Discount</b></td><td>:</td><td><b>- RM{{ amount_discount }}</b></td></tr><tr><td><b>Shipping Fee</b></td><td>:</td><td><b>RM{{ delivery_amount }}</b></td></tr><tr><td><b>SST 8%</b></td><td>:</td><td><b>RM{{ amount_tax }}</b></td></tr><tr><td><b>Total</b></td><td>:</td><td><b>RM{{ total_amount }}</b></td></tr><tr><td><b>Amount paid</b></td><td>:</td><td><b>RM{{ total_amount }}</b></td></tr></tfoot></table><table class='table'><thead><tr><th>Shipping Address</th></tr></thead><tbody>{{ shipping_address }}</tbody></table></div></div></body></html>";
const PAYMENT_RECEIPT_ORDER_LIST       = "<tr><td><b>{{ product_name }}</b><br>Qty {{ product_quantity }}</td><td>:</td><td><b>RM{{ product_price }}</b></td></tr>";
const PAYMENT_RECEIPT_SHIPPING_ADDRESS = "<tr><td>{{ line1 }} {{ line2 }} {{ postal_code }} {{ city }} {{ state }}</td></tr>";

class Payment
{
    public $stripe;
    public $session_id;
    public $payment;
    public $all_line_items;
    public $shipping_rate;
    public $amount_discount;
    public $amount_tax;
    public $shipping_address;
    public $subtotal;
    public $order_list;
    public function __construct($session_id)
    {
        $this->stripe           = new Stripe();
        $this->session_id       = $session_id;
        $this->payment          = $this->stripe->getPaymentDetails($session_id);
        $this->all_line_items   = $this->stripe->getAllLineItems($session_id);
        $this->shipping_rate    = $this->stripe->getShippingRate($session_id);
        $this->amount_discount  = $this->stripe->getAmountDiscount($session_id);
        $this->amount_tax       = $this->stripe->getAmountTax($session_id);
        $this->shipping_address = $this->stripe->getShippingAddress($session_id);
        $this->subtotal         = $this->calculate_subtotal();
        $this->order_list       = $this->generate_order_list();
    }

    function calculate_subtotal()
    {
        $subtotal = 0;
        foreach ($this->all_line_items as $lineItem) {
            $subtotal += $lineItem['amount_subtotal'] / 100;
        }
        return $subtotal;
    }

    function generate_order_list()
    {
        $order_list = [];
        foreach ($this->all_line_items as $lineItem) {
            $order_list[] = str_replace(
                [
                    '{{ product_name }}',
                    '{{ product_quantity }}',
                    '{{ product_price }}'
                ],
                [
                    $lineItem['description'],
                    $lineItem['quantity'],
                    $lineItem['amount_subtotal'] / 100
                ],
                PAYMENT_RECEIPT_ORDER_LIST
            );
        }
        return implode(PHP_EOL, $order_list);
    }

    function generate_shipping_address()
    {
        $shipping_address = str_replace(
            [
                '{{ line1 }}',
                '{{ line2 }}',
                '{{ postal_code }}',
                '{{ city }}',
                '{{ state }}',
            ],
            [
                $this->shipping_address->line1,
                $this->shipping_address->line2,
                $this->shipping_address->postal_code,
                $this->shipping_address->city,
                $this->shipping_address->state
            ],
            PAYMENT_RECEIPT_SHIPPING_ADDRESS
        );
        return $shipping_address;
    }

    public function generate_payment_receipt($order_id)
    {
        $payment_id           = $this->payment->id;
        $amount               = $this->payment->amount / 100;
        $created              = date('Y-m-d H:i:s', $this->payment->created);
        $payment_method_types = implode(', ', $this->payment->payment_method_types);
        $status               = $this->payment->status;
        $delivery_type        = $this->shipping_rate->display_name;
        $delivery_amount      = $this->shipping_rate->fixed_amount->amount / 100;
        $amount_discount      = $this->amount_discount / 100;
        $amount_tax           = $this->amount_tax / 100;
        $subtotal             = $this->subtotal;
        $order_list           = $this->order_list;
        $shipping_address     = $this->generate_shipping_address();
        $payment_receipt      = str_replace(
            [
                '{{ payment_id }}',
                '{{ order_id }}',
                '{{ payment_amount }}',
                '{{ payment_method }}',
                '{{ payment_status }}',
                '{{ order_list }}',
                '{{ delivery_type }}',
                '{{ subtotal }}',
                '{{ amount_discount }}',
                '{{ delivery_amount }}',
                '{{ amount_tax }}',
                '{{ total_amount }}',
                '{{ created_at }}',
                '{{ shipping_address }}'
            ],
            [
                $payment_id,
                $order_id,
                $amount,
                $payment_method_types,
                $status,
                $order_list,
                $delivery_type,
                $subtotal,
                $amount_discount,
                $delivery_amount,
                $amount_tax,
                $amount,
                $created,
                $shipping_address
            ],
            PAYMENT_RECEIPT_BODY
        );
        return $payment_receipt;
    }
}

?>
