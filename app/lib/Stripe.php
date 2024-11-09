<?php
/**
 * @author: Chong Jun Xiang
 * This class is used for stripe api
 */
const STRIPE_SECRET_KEY = 'sk_test_51Px1FfFtbFkcCwNo0tMwvqFCn0KOmvyA5ZazbEwo678fVTMiYP0a8UE0ZD0G0awI9azjLSH2vRTlRS69Sgnd3RMU00X5moQ4Va';

$_stripe = new \Stripe\StripeClient(STRIPE_SECRET_KEY);

class Stripe
{
    public function getPaymentSession($session_id)
    {
        global $_stripe;

        return $_stripe->checkout->sessions->retrieve(
            $session_id,
            [],
        );
    }

    public function getPaymentIntent($session_id)
    {
        return $this->getPaymentSession($session_id)->payment_intent;
    }

    public function getPaymentDetails($session_id)
    {
        global $_stripe;
        $payment_intent = $this->getPaymentIntent($session_id);
        return $_stripe->paymentIntents->retrieve(
            $payment_intent,
            [],
        );
    }

    public function getLatestCharge($session_id)
    {
        global $_stripe;
        $payment = $this->getPaymentDetails($session_id);
        return $_stripe->charges->retrieve(
            $payment->latest_charge,
            [],
        );
    }

    public function getReceiptUrl($session_id)
    {
        return $this->getLatestCharge($session_id)->receipt_url;
    }

    public function getReceiptHtml($session_id)
    {
        $receipt_url = $this->getReceiptUrl($session_id);
        $curl        = curl_init($receipt_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $receipt_content = curl_exec($curl);
        curl_close($curl);
        return $receipt_content;
    }

    public function getAllLineItems($session_id)
    {
        global $_stripe;
        return $_stripe->checkout->sessions->allLineItems(
            $session_id,
            [],
        )->data;
    }

    public function getCustomer($session_id)
    {
        global $_stripe;
        $payment = $this->getPaymentDetails($session_id);
        return $_stripe->customers->retrieve(
            $payment->customer,
            [],
        );
    }

    public function getShippingAddress($session_id)
    {
        $customer = $this->getCustomer($session_id);
        return $customer->shipping->address;
    }

    public function getShippingOptions($session_id)
    {
        $shipping_options = $this->getPaymentSession($session_id)->shipping_options[0];
        return $shipping_options;
    }

    public function getShippingRate($session_id)
    {
        global $_stripe;
        $shipping_options = $this->getShippingOptions(session_id: $session_id);
        $shipping_rate    = $_stripe->shippingRates->retrieve($shipping_options->shipping_rate, []);
        return $shipping_rate;
    }

    public function getAmountTax($session_id)
    {
        return $this->getPaymentSession($session_id)->total_details?->amount_tax;
    }

    public function getAmountDiscount($session_id)
    {
        return $this->getPaymentSession($session_id)->total_details?->amount_discount;
    }
}
