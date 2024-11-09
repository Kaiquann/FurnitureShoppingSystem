<?php

/**
 * @author: Chong Jun Xiang
 */
$_title = "Stripe Payment";

if (!isLoggedIn()) {
    return redirect('/login');
}

if (is_get()) {
    temp("danger", "Invalid action");
    return redirect('/');
}

if (is_post()) {
    try {
        global $_stripe;
        global $_USER_DATA;
        $temp_user_address_id = session('temp_user_address_id');
        $temp_product_id      = session('product_id');
        $delivery_type        = session('delivery_type');
        $quantity             = session('quantity');
        $voucher_id           = session('voucher_id');
        $discount_price       = session('discount_price');

        if ($_USER_DATA == null) {
            temp('danger', 'Please login first');
            return redirect('/login');
        }

        if (empty($temp_user_address_id) || empty($temp_product_id) || empty($delivery_type)) {
            temp('danger', 'There is something wrong with your checkout');
            return redirect('/');
        }

        $tax_rate = $_stripe->taxRates->create([
            'display_name' => 'SST 8%',
            'description'  => 'SST 8%',
            'jurisdiction' => 'MY',
            'percentage'   => 8,
            'inclusive'    => false
        ]);

        $line_items = [];

        foreach ($temp_product_id as $id) :
            $cart         = $_db->query("SELECT * FROM cart WHERE user_id = '$_USER_DATA->id' AND product_id = '$id'")->fetch();
            $product_data = db_select_single('product', 'id', $cart->product_id ?? $id);
            $line_items[] = [
                'price_data' => [
                    'currency'     => 'myr',
                    'product_data' => [
                        'name'        => $product_data->name,
                        'description' => $product_data->description
                    ],
                    'unit_amount'  => $product_data->price * 100
                ],
                'quantity'   => $quantity ?: $cart->quantity,
                'tax_rates'  => [$tax_rate->id]
            ];
        endforeach;

        $billing_address = db_select_single('address', 'id', $temp_user_address_id);

        $customer = $_stripe->customers->create([
            'email'    => $_USER_DATA->email,
            'shipping' => [
                'name'    => "$_USER_DATA->first_name $_USER_DATA->last_name",
                'address' => [
                    'line1'       => $billing_address->line1,
                    'line2'       => $billing_address->line2,
                    'city'        => $billing_address->city,
                    'state'       => $billing_address->state,
                    'postal_code' => $billing_address->postcode,
                    'country'     => 'MY',
                ],
            ],
        ]);

        match ($delivery_type) {
            'express' => $delivery_amount = 20,
            'normal'  => $delivery_amount = 10
        };

        $shipping_rate = $_stripe->shippingRates->create([
            'display_name' => ucwords($delivery_type),
            'type'         => 'fixed_amount',
            'fixed_amount' => [
                'amount'   => $delivery_amount * 100,
                'currency' => 'myr',
            ],
        ]);

        $coupon = null;
        if ($voucher_id) {
            $voucher = db_select_single('voucher', 'id', $voucher_id);
            if ($voucher->discount_type === 'percentage') {
                $coupon = $_stripe->coupons->create([
                    'percent_off' => $voucher->amount,
                    'currency'    => 'myr',
                    'duration'    => 'once'
                ]);
            } else if ($voucher->discount_type === 'fixed') {
                $coupon = $_stripe->coupons->create([
                    'amount_off' => $voucher->amount * 100,
                    'currency'   => 'myr',
                    'duration'   => 'once'
                ]);
            }
        }

        $checkout_session = $_stripe->checkout->sessions->create([
            'customer'                     => $customer->id,
            'line_items'                   => $line_items,
            'shipping_options'             => [
                [
                    'shipping_rate' => $shipping_rate->id
                ]
            ],
            'discounts'                    => $coupon ? [
                [
                    'coupon' => $coupon->id
                ]
            ] : [],
            'saved_payment_method_options' => ['payment_method_save' => 'enabled'],
            'invoice_creation'             => ['enabled' => true],
            'mode'                         => 'payment',
            'success_url'                  => base("payment/process?session_id={CHECKOUT_SESSION_ID}&user_id={$_USER_DATA->id}"),
            'cancel_url'                   => base("payment/process_cancel?session_id={CHECKOUT_SESSION_ID}&user_id={$_USER_DATA->id}")
        ]);

        $url = $checkout_session->url;

        if ($url == null || empty($url)) {
            throw new Exception('Failed to create checkout session');
        }

        return redirect($url);
    }
    catch (Exception $e) {
        unsetSession('temp_user_address_id');
        unsetSession('delivery_type');
        unsetSession('quantity');
        unsetSession('product_id');
        unsetSession('voucher_id');
        unsetSession('discount_price');
        temp("danger", $e->getMessage());
        return redirect('/cart');
    }
}
