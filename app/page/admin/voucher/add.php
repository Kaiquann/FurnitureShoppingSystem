<?php
/**
 * @author: Mu Jun Yi
 */
$_title = "Add Voucher";

if (is_post()) {
    $code                   = req('code');
    $discount_type          = req('discount_type');
    $amount                 = req('amount');
    $description            = req('description');
    $min_spend              = req('min_spend');
    $max_spend              = req('max_spend');
    $usage_limit_per_coupon = req('usage_limit_per_coupon');
    $usage_limit_per_user   = req('usage_limit_per_user');
    $expired_at             = req('expired_at');


    if (empty($code)) {
        $_err['code'] = 'Required';
    } else if (strlen($code) > 50) {
        $_err['code'] = 'Maximum length is 50 characters';
    } else if (!is_unique($code, 'voucher', 'code')) {
        $_err['code'] = 'Already exists';
    }

    if (empty($discount_type)) {
        $_err['discount_type'] = 'Required';
    } else if (!in_array($discount_type, DISCOUNT_TYPE_LIST)) {
        $_err['discount_type'] = 'Invalid discount type';
    }

    if (empty($amount)) {
        $_err['amount'] = 'Required';
    } else if ($amount < 0) {
        $_err['amount'] = 'Invalid amount';
    } else if ($amount > 999999999) {
        $_err['amount'] = 'Maximum amount is 999999999';
    }

    if ($discount_type === "percentage"){
        if (empty($amount)) {
            $_err['amount'] = 'Required';
        } else if ($amount < 0) {
            $_err['amount'] = 'Invalid amount';
        } else if ($amount > 100) {
            $_err['amount'] = 'Maximum amount is 100';
        }
    
    }

    if ($description) {
        if (strlen($description) > 500) {
            $_err['description'] = 'Maximum length is 500 characters';
        }
    }

    if ($min_spend) {
        if ($min_spend < 0) {
            $_err['min_spend'] = 'Invalid amount';
        } else if ($min_spend > 999999999) {
            $_err['min_spend'] = 'Maximum amount is 999999999';
        } else if ($min_spend > $max_spend) {
            $_err['min_spend'] = 'Minimum spend cannot be greater than maximum spend';
        }
    }

    if ($max_spend) {
        if ($max_spend < 0) {
            $_err['max_spend'] = 'Invalid amount';
        } else if ($max_spend > 999999999) {
            $_err['max_spend'] = 'Maximum amount is 999999999';
        } else if ($max_spend < $min_spend) {
            $_err['max_spend'] = 'Maximum spend cannot be less than minimum spend';
        }
    }

    if ($usage_limit_per_coupon) {
        if ($usage_limit_per_coupon < 0) {
            $_err['usage_limit_per_coupon'] = 'Invalid amount';
        } else if ($usage_limit_per_coupon > 999999999) {
            $_err['usage_limit_per_coupon'] = 'Maximum amount is 999999999';
        }
    }

    if ($usage_limit_per_user) {
        if ($usage_limit_per_user < 0) {
            $_err['usage_limit_per_user'] = 'Invalid amount';
        } else if ($usage_limit_per_user > 999999999) {
            $_err['usage_limit_per_user'] = 'Maximum amount is 999999999';
        }
    }

    if (empty($expired_at)) {
        $_err['expiration'] = 'Required';
    } else if ($expired_at < date('Y-m-d')) {
        $_err['expiration'] = 'Expiration date cannot be before today.';
    }

    $is_active = 0;

    if (!$_err) {
        $voucher_payload = [
            'code'                   => $code,
            'discount_type'          => $discount_type,
            'amount'                 => $amount,
            'description'            => $description,
            'min_spend'              => $min_spend,
            'max_spend'              => $max_spend,
            'usage_limit_per_coupon' => $usage_limit_per_coupon,
            'usage_limit_per_user'   => $usage_limit_per_user,
            'expired_at'             => $expired_at,
            'is_active'              => $is_active
        ];
        db_insert('voucher', $voucher_payload);
        temp('success', 'Voucher added successfully.');
        return redirect("/admin/voucher");
    }
}
?>

<style>
    section {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 20px;
    }

    h1 {
        text-align: center;
        color: #333;
    }

    .form-container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 100000px;
    }

    label {
        display: block;
        margin: 10px 0 5px;
    }

    input[type="text"],
    input[type="number"],
    input[type="datetime-local"],
    textarea,
    select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    button {
        width: 100%;
        padding: 10px;
        background-color: #5cb85c;
        border: none;
        color: white;
        font-size: 16px;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background-color: #4cae4c;
    }

    .toggle-field {
        display: none;
    }

    .err {
        color: red;
        font-size: 0.9em;
        margin-top: -10px;
        margin-bottom: 10px;
    }
</style>

<section>
    <div class="form-container">
        <h1><?= $_title; ?></h1>
        <form method="POST">
            <label for="code">Code:</label>
            <?= html_text('code', 'maxlength=50 required'); ?>
            <?= err('code'); ?>

            <label for="discount_type">Discount Type:</label>
            <?= html_select('discount_type', DISCOUNT_TYPE_LIST); ?>
            <?= err('discount_type'); ?>

            <label for="amount">Amount</label>
            <?= html_number('amount', 0, 999999999, 1, 'required'); ?>
            <?= err('amount'); ?>

            <label for="description">Description:</label>
            <?= html_textarea('description', 'maxlength=500'); ?>
            <?= err('description'); ?>

            <label for="min_spend">Minimum Spend:</label>
            <?= html_number('min_spend', 0, 999999999, 1); ?>
            <?= err('min_spend'); ?>

            <label for="max_spend">Maximum Spend:</label>
            <?= html_number('max_spend', 0, 999999999, 1); ?>
            <?= err('max_spend'); ?>

            <label for="usage_limit_per_coupon">Usage Limit Per Coupon:</label>
            <?= html_number('usage_limit_per_coupon', 0, 999999999, 1); ?>
            <?= err('usage_limit_per_coupon'); ?>

            <label for="usage_limit_per_user">Usage Limit Per User:</label>
            <?= html_number('usage_limit_per_user', 0, 999999999, 1); ?>
            <?= err('usage_limit_per_user'); ?>

            <label for="expired_at">Expiration Date:</label>
            <?= html_datetime('expired_at', 'required'); ?>
            <?= err('expired_at'); ?>

            <button type="submit" class="primary" data-confirm="Are you sure to add the voucher?">Add</button>
        </form>
        <button data-get="/admin/voucher" class="success">Back</button>
    </div>
</section>
