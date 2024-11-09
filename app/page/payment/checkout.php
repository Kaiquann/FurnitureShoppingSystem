<?php
$_title = "Checkout";

global $_USER_DATA;

if ($_USER_DATA == null) {
    temp('danger', 'Please login first');
    redirect('/login');
}



$product_id = session('product_id');
$quantity      = session('quantity');
$delivery_type = session('delivery_type');
$discount_price = session('discount_price');


foreach ($product_id as $id) {
    $check_products = $_db->query("SELECT * FROM product WHERE id =$id AND is_deleted=0")->fetch();
    if (empty($check_products)) {
        temp('danger', 'Some products were not found or have been deleted.');
        return redirect('/cart');
    }
}

if ($product_id == null) {
    temp('danger', 'please select your product first');
    return redirect('/cart');
}


if (is_post()) {
    if (isset($_POST['delivery-type'])) {
        $delivery_type = req('delivery-type');
        if (!empty($delivery_type)) {
            session('delivery_type', $delivery_type);
        }
    }

    if (isset($_POST['address'])) {
        $userAddressId = req('userAddress');
        if (!empty($userAddressId)) {
            session('temp_user_address_id', $userAddressId);
        }
    }

    if (isset($_POST['deleteAddressBtn'])) {
        $userAddressId = req('userAddress');
        db_delete('address', 'id', $userAddressId);
        temp('success', 'delete successful');
        return redirect('/payment/checkout');
    }


    if (isset($_POST['checkOut'])) {
        if (!session("delivery_type")) {
            temp('danger', 'Please select your delivery option');
            return redirect('/payment/checkout');
        } else if (!session("temp_user_address_id")) {
            temp('danger', 'Please select your address first');
            return redirect('/payment/checkout');
        } else {
            temp('danger', 'Please complete address details');
            return redirect('/payment/checkout');
        }
    }

    if (isset($_POST['voucher'])) {
        $code          = req('code');
        $voucher       = db_select_single("voucher", "code", $code);
        $index         = 0;
        $totalQuantity = 0;

        foreach ($product_id as $id) :
            $cart           = $_db->query("
                SELECT * FROM cart
                WHERE user_id = $_USER_DATA->id
                AND product_id = $id
            ")->fetch();
            $pid            = $cart->product_id ?? $id;
            $pquantity      = $quantity ?: $cart->quantity;
            $product        = db_select_single('product', 'id', $pid);
            $product_images = db_select_single('product_image', 'product_id', $product->id);
            $discount_price = 0;
            $subtotalPrice += $pquantity * $product->price;
        endforeach;

        if (empty($code)) {
            temp('danger', 'Please Enter Voucher Code');
            return redirect('/payment/checkout');
        } elseif (!$voucher) {
            temp('danger', 'Please Enter Valid Voucher Code');
            return redirect('/payment/checkout');
        }

        if ($code === $voucher->code) {
            $voucherUsage        = db_select("voucher_usage", "voucher_id", $voucher->id);
            $voucherUsagePerUser = $_db->query("SELECT * FROM voucher_usage WHERE user_id = $_USER_DATA->id AND voucher_id = $voucher->id")->fetchAll();
            if ($voucher->is_deleted !== 0 || $voucher->is_active !== 1) {
                temp('danger', 'The Voucher Is Not Valid');
                return redirect('/payment/checkout');
            } elseif ($subtotalPrice < $voucher->min_spend) {
                temp('danger', 'Your Minimum Spend must over than RM' . $voucher->min_spend);
                return redirect('/payment/checkout');
            } elseif (count($voucherUsage) >= $voucher->usage_limit_per_coupon && $voucher->usage_limit_per_coupon != 0) {
                temp('danger', 'The Voucher is over limit already');
                return redirect('/payment/checkout');
            } elseif (count($voucherUsagePerUser) >= $voucher->usage_limit_per_user && $voucher->usage_limit_per_user != 0) {
                temp('danger', 'This Voucher you use ' . $voucher->usage_limit_per_user . ' times already');
                return redirect('/payment/checkout');
            } elseif (strtotime($voucher->expired_at) << time()) {
                temp('danger', 'This Voucher Has Expired');
                return redirect('/payment/checkout');
            }

            if ($subtotalPrice >= $voucher->min_spend) {
                if ($voucher->discount_type === "percentage") {
                    $percentage     = $voucher->amount / 100;
                    $discount_price = $subtotalPrice * $percentage;
                    if ($subtotalPrice >= $voucher->max_spend && $voucher->max_spend != 0) {
                        $discount_price = $voucher->max_spend;
                    }
                } elseif ($voucher->discount_type === "fixed") {
                    $discount_price = $voucher->amount;
                }
                session('voucher_id', $voucher->id);
                session('discount_price', $discount_price);
                temp('success', 'Apply Voucher Success');
                redirect("/payment/checkout");
            }
        }
    }

    if (isset($_POST['voucher_cancel'])) {
        if (session('voucher_id') && session('discount_price')) {
            unsetSession('voucher_id');
            unsetSession('discount_price');
            temp('success', 'Voucher Removed Successfully.');
        } else {
            temp('danger', 'No Valid Voucher Applied To Cancel.');
        }
        redirect('/payment/checkout');
    }
}

$user_address = db_select("address", "user_id", $_USER_DATA?->id);

if (isset($_POST['confirm-address-button'])) { //check for entering address
    $addressLine1 = req('address-line1');
    $addressLine2 = req('address-line2');
    $city         = req('city');
    $state        = req('state');
    $postkod      = req('postkod');

    if (empty($addressLine1)) {
        $_err["address-line1"] = "address cannot be empty";
    }

    if (empty($addressLine2)) {
        $_err["address-line2"] = "address cannot be empty";
    }

    if (empty($city)) {
        $_err["city"] = "city cannot be empty";
    }

    if (empty($state)) {
        $_err["state"] = "state cannot be empty";
    }

    if (empty($postkod)) {
        $_err["postkod"] = "postkod cannot be empty";
    } else if (!is_numeric($postkod)) {
        $_err["postkod"] = "postkod must in number";
    } else if (strlen($postkod) > 5) {
        $_err["postkod"] = "postkod number cannot have more than 5 digits";
    }

    if (empty($_err)) {
        $address_payload = [
            'user_id'  => $_USER_DATA?->id,
            'line1'    => $addressLine1,
            'line2'    => $addressLine2,
            'postcode' => $postkod,
            'city'     => $city,
            'state'    => $state
        ];
        db_insert("address", $address_payload);
        temp('success', 'Address added successfully');
        return redirect('/payment/checkout');
    }
}
?>


<style>
    main {
        margin: 0px;
        padding: 0px;
    }

    p {
        margin: 0px;
        padding: 0;
    }

    .container {
        display: flex;
        justify-content: space-between;
        max-width: 1000px;
        margin: 20px auto;
        gap: 20px;
    }

    .shipping-address,
    .order-summary {
        background-color: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        width: 48%;
    }

    .shopping-cart td {
        text-align: center;
        padding-top: 20px;
    }

    .shopping-cart {
        min-height: 200px;
        max-height: 300px;
        width: 100%;
        position: sticky;
        top: 0;
        height: 600px;
        overflow-y: auto;
        padding: 20px;
    }

    .shopping-cart-container {
        display: flex;
        justify-content: space-between;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        background-color: white;
        margin: 20px;
    }

    .product-line {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .product-line p {
        margin: 0 10px;
    }

    .product-line .image img {
        width: 50px;
        height: auto;
        display: block;
    }

    h2,
    h3,
    h4 {
        padding: 20px;
        margin-bottom: 20px;
        border-bottom: 1px solid #ddd;
    }

    input[type="text"],
    input[type="tel"],
    select {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    input[type="checkbox"] {
        margin-right: 10px;
    }

    .submit-btn {
        background-color: #252323;
        color: #fff;
        padding: 12px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .submit-btn:hover {
        background-color: #333;
    }

    .input-group {
        margin-bottom: 20px;
    }

    .input-group.checkbox-group {
        display: flex;
        align-items: center;
    }

    .hint {
        font-size: 12px;
        color: #777;
    }

    .order-summary p {
        margin-bottom: 10px;
    }

    .edit-shipping {
        display: block;
        margin-top: 20px;
        text-decoration: none;
        color: #3498db;
        background: none;
    }

    .edit-shipping:hover {
        background: none;
        color: #0099ff;
        background: none;
    }

    .standard-delivery-btn,
    .express-delivery-btn {
        background: none;
        color: black;
        padding: 10px;
        border: solid 1px black;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin: 10px;
    }

    .standard-delivery-btn:hover,
    .express-delivery-btn:hover {
        background: #272424;
        color: white;
        transition: all 0.5;
    }

    .error-message {
        color: red;
    }

    .confirm-address-button,
    .addMoreAddress {
        background-color: blue;
        padding: 10px;
        margin: 10px;
        text-align: center;
        color: white;
        border: none;
        border-radius: 10px;
    }

    .address-container {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        min-width: 400px;
        max-height: 600px;
        position: sticky;
        overflow-y: auto;
    }

    .goBack {
        display: block;
        text-decoration: none;
        color: #3498db;
        background: none;
        text-decoration: underline;
    }

    .goBack:hover {
        background: none;
    }

    .addNewAddress {
        padding: 10px;
    }

    .address-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .addressBtn,
    .activeAddressBtn {
        flex-grow: 1;
        text-align: left;
        padding: 10px;
        background-color: #f9f9f9;
        color: #272424;
    }

    .activeAddressBtn {
        background-color: #b5b1b1;
    }

    .addressBtn:hover,
    .activeAddressBtn:hover {
        background-color: #b5b1b1;
    }


    .deleteAddressBtn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
    }

    .deleteAddressBtn:hover {
        border: none;
        background: none;
    }

    .deleteAddressBtn img {
        width: 20px;
        height: 30px;
        box-shadow: none;
        border: none;
    }

    button {
        padding: 0px 0px;
    }

    .checkOut-img {
        box-shadow: none;
    }

    .delivery-buttons {
        width: 100%;
        justify-content: center;
        align-items: center;
    }

    .delivery-btn {
        width: 100%;
        margin: 10px;
        padding: 10px;
        background: none;
        color: #878282;
        border: 1px solid #878282;
        border-radius: 0px;
    }

    .delivery-btn:hover {
        width: 100%;
        margin: 10px;
        padding: 10px;
        background: none;
        color: #272424;
        border: 1px solid #272424;
        border-radius: 0px;
        box-shadow: 0 5px 8px 0 rgba(0, 0, 0, 0.2), 0 9px 30px 0 rgba(0, 0, 0, 0.19);
    }

    .active-delivery-btn {
        width: 100%;
        margin: 10px;
        padding: 10px;
        background-color: #555bf3;
        color: #fffbfb;
        border: 1px solid #fffbfb;
        border-radius: 0px;
    }

    .active-delivery-btn:hover {
        border: 1px solid #555bf3;
        background-color: #555bf3;
        box-shadow: 0 5px 8px 0 rgba(0, 0, 0, 0.2), 0 9px 30px 0 rgba(0, 0, 0, 0.19);
    }

    .voucher {
        padding: 10px;
        border-radius: 0px;
    }

    .voucher_cancel {
        padding: 10px;
        border-radius: 0px;
        background-color: red;
    }

    .voucher_cancel:hover {
        background-color: crimson;
    }
</style>

<body>
    <div class="container">
        <?php if (isset($_POST['addNewAddress']) || !$user_address || $_err) { ?>
            <div class="shipping-address">
                <form method="POST">
                    <h2>Shipping Address</h2>
                    <div class="input-group">
                        <label for="address-line1">Address Line 1*</label>
                        <input type="text" id="address-line1" name="address-line1" value="<?= $addressLine1 ?? '' ?>"
                            required>
                        <?= err('address-line1') ?>
                        <span id="error-address-line1" style="color:red;"></span>
                    </div>

                    <div class="input-group">
                        <label for="address-line2">Address Line 2*</label>
                        <input type="text" id="address-line2" name="address-line2" value="<?= $addressLine2 ?? '' ?>"
                            require>
                        <?= err('address-line2') ?>
                    </div>

                    <div class="input-group">
                        <label for="postkod">postkod*</label>
                        <input type="text" id="postkod" name="postkod" value="<?= $postkod ?? '' ?>" required>
                        <?= err('postkod') ?>
                        <span id="error-postkod" style="color:red;"></span>
                    </div>

                    <div class="input-group">
                        <label for="city">City*</label>
                        <select id="city" name="city" value="<?= $city ?? '' ?>" required>
                            <?php if (!empty($city)) { ?>
                                <option value="<?= $city ?>"><?= $city ?></option>
                            <?php } else { ?>
                                <option value="Kuala Lumpur">Kuala Lumpur</option>
                                <option value="George Town">George Town</option>
                                <option value="Ipoh">Ipoh</option>
                                <option value="Johor Bahru">Johor Bahru</option>
                                <option value="Shah Alam">Shah Alam</option>
                                <option value="Petaling Jaya">Petaling Jaya</option>
                                <option value="Kota Kinabalu">Kota Kinabalu</option>
                                <option value="Seremban">Seremban</option>
                                <option value="Malacca City">Malacca City</option>
                                <option value="Kuantan">Kuantan</option>
                                <option value="Putrajaya">Putrajaya</option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="state">State*</label>
                        <input type="text" id="state" name="state" value="<?= $state ?? '' ?>" required>
                        <?= err('state') ?>
                        <span id="error-state" style="color:red;"></span>
                    </div>
                    <button type="submit" class="confirm-address-button" name="confirm-address-button"
                        data-confirm="Are you confirm your address ?">Add address</button>
                </form>

                <?php if ($user_address) { ?>
                    <a href="/payment/checkout" class="goBack">Go Back</a>
                <?php } ?>
            </div>
        <?php
        } else {
        ?>
            <div class="address-container">
                <div class="address">
                    <h2 class="addressTitle">Your Address</h2>
                    <?php
                    $user_address = db_select("address", "user_id", $_USER_DATA?->id);
                    foreach ($user_address as $address) : ?>
                        <div class="address-line-container">
                            <form method="POST" class="address-line-form">
                                <div class="address-line">
                                    <button type="submit" name="address"
                                        class="<?= session('temp_user_address_id') == $address->id ? "activeAddressBtn" : "addressBtn" ?>">
                                        <p><?= $address->line1 ?></p>
                                        <p><?= $address->line2 ?></p>
                                        <p><?= $address->postcode ?></p>
                                        <p><?= $address->city ?></p>
                                        <p><?= $address->state ?></p>
                                    </button>
                                    <button type="submit" name="deleteAddressBtn" class="deleteAddressBtn">
                                        <img src="/images/rubbishBin.png" class="delete_img" alt="Delete" />
                                    </button>
                                    <input type="hidden" name="userAddress" value="<?= $address->id ?>" />
                                </div>
                            </form>
                        </div>
                    <?php endforeach ?>
                </div>

                <form method="POST">
                    <button type="submit" class="addNewAddress" name="addNewAddress">Add new address</button>
                </form>
            </div>
        <?php } ?>

        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="shopping-cart-container">
                <div class="shopping-cart">
                    <div class="product-head">
                        <h3>Your product</h3>
                    </div>
                    <?php
                    $index         = 0;
                    $subtotalPrice = 0;
                    $totalQuantity = 0;

                    foreach ($product_id as $id) :
                        $cart           = $_db->query("SELECT * FROM cart WHERE user_id = $_USER_DATA->id AND product_id = $id")->fetch();
                        $pid            = $cart->product_id ?? $id;
                        $pquantity      = $quantity ?: $cart->quantity;
                        $product        =  db_select_single('product', 'id', $pid);
                        $product_images = db_select_single('product_image', 'product_id', $product->id);
                    ?>
                        <div class="product-line">
                            <p><?= ++$index ?></p>
                            <div class="image">
                                <img src="<?= $product_images->image_url ?>" alt="Product Image" style="margin: auto;"
                                    class="checkOut-img">
                            </div>
                            <p><?= $product->name ?></p>
                            <p>x<?= $pquantity ?></p>
                            <p>RM<?= $pquantity * $product->price ?></p><br>
                        </div>
                    <?php
                        $subtotalPrice += $pquantity * $product->price;
                        $totalQuantity += $pquantity;
                    endforeach ?>
                </div>
            </div>
            <form method="POST">
                <h2>Delivery </h2>
                <div class="delivery-buttons">
                    <button type="submit" name="delivery-type" value="normal"
                        class="<?= $delivery_type == 'normal' ? 'active-delivery-btn' : 'delivery-btn' ?>">
                        Normal Delivery (RM10)
                    </button>
                    <button type="submit" name="delivery-type" value="express"
                        class="<?= $delivery_type == 'express' ? 'active-delivery-btn' : 'delivery-btn' ?>">
                        Express Delivery (RM20)
                    </button>
                </div>

                <h2>Voucher</h2>
                <label for="code">Voucher Code:</label>
                <div class="voucher">
                    <input type="text" id="code" name="code" value="<?= $code ?? '' ?>">
                    <button type="submit" class="voucher" name="voucher">Apply Voucher</button>
                    <button type="submit" class="voucher_cancel" name="voucher_cancel">Remove Voucher</button>
                    <?php if (!empty($_err["err-voucher"])) : ?>
                        <span class="error-message"><?= htmlspecialchars($_err["err-voucher"]) ?></span>
                    <?php endif; ?>
                </div>


                <h2>Order Details</h2>
                <p><strong>Total Items: </strong> <?= sizeof($product_id) ?></p>
                <p><strong>Total Quantity: </strong>x<?= $totalQuantity ?></p>
                <?php
                switch ($delivery_type) {
                    case 'express':
                        $deliveryFee = 20;
                        break;
                    case 'normal':
                        $deliveryFee = 10;
                        break;
                }
                ?>
                <p><strong>Sub total: </strong>RM <?= $subtotalPrice ?? 0 ?></p>
                <p><strong>Total Discount: </strong>RM - <?= $discount_price ?? 0 ?></p>

                <?php
                if ($discount_price >= $subtotalPrice) {
                    $checkOutPrice = 0;
                } else {
                    $checkOutPrice = $subtotalPrice - ($discount_price ?? 0);
                }
                $sst = $checkOutPrice * 0.08;

                ?>
                <p><strong>SST (8%): </strong>RM<?= $sst ?></p>
                <p><strong>Delivery fee: </strong>RM <?= $deliveryFee ?? 0 ?></p>
                <p><strong>Total amount:</strong>RM<?= $checkOutPrice + $sst + ($deliveryFee ?? 0) ?></p>
                <button type="submit" name="checkOut" class="submit-btn" <?= $user_address && session('temp_user_address_id') && $delivery_type ? 'data-post="/payment/stripe-session"' : '' ?>>Checkout With
                    Stripe</button>
                <input type="hidden" name="cartProduct_id" value="<?= $product_id ?>" />
            </form>
        </div>
    </div>
</body>