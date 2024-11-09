<?php
global $_USER_DATA;
unsetSession("product_id");
unsetSession("quantity");
unsetSession("delivery_type");
unsetSession("temp_user_address_id");
unsetSession("discount_price");

$cart      = db_select("cart", "user_id", $_USER_DATA->id);

if (is_post()) {
    $cartId = post('cart_id');
    $cart   = db_select_single('cart', 'id', $cartId);
    $product_id = req('product_id');

    if (isset($_POST['checkout-btn'])) {
        if (!empty($product_id)) {
            session('product_id', $product_id);
            return redirect('/payment/checkout');
        } else {
            temp('warning', 'you need to choose the item');
            return redirect('/cart');
        }
    }

    if (isset($_POST['increase'])) {
        $cartQuantity  = $cart->quantity;
        $product_stock = req('product_stock');
        if ($cartQuantity >= $product_stock) {
            temp('danger', 'this is the maximum stock');
            return redirect('/cart');
        }
        $product = db_select_single('product', 'id', $cart->product_id);
        ++$cartQuantity;
        $total_amount = ($product->price * $cartQuantity);

        db_update('cart', ['quantity' => $cartQuantity, 'total_amount' => $total_amount], 'id', $cartId);
        return redirect('/cart');
    }

    if (isset($_POST['decrease'])) {
        $cartQuantity = $cart->quantity;
        if ($cartQuantity > 1) {
            $product = db_select_single('product', 'id', $cart->product_id);
            --$cartQuantity;
            $total_amount = ($product->price * $cartQuantity);
            db_update('cart', ['quantity' => $cartQuantity, 'total_amount' => $total_amount], 'id', $cartId);
            return redirect('/cart');
        } else {
            db_update('cart', ['quantity' => 1], 'id', $cartId);
            return redirect('/cart');
        }
    }

    if (isset($_POST['delete'])) {
        db_delete('cart', 'id', $cartId);
        temp('success', 'Delete Successfull !');
        return redirect('/cart');
    }
}
?>

<style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f8f8f8;
    }

    .container {
        display: flex;
        justify-content: space-between;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        background-color: white;
    }

    .shopping-cart td {
        text-align: center;
        padding-top: 20px;
    }

    .shopping-cart th {
        position: sticky;
        text-align: center;
        padding-bottom: 20px;
        border-bottom: 1px solid #ddd;

    }

    .shopping-cart,
    .order-summary {
        background-color: white;
        padding: 20px;
        border-radius: 0px;
    }

    .shopping-cart {
        max-height: 100%;
        flex: 2;
        position: sticky;
        top: 0;
        height: 600px;
        overflow-y: auto;
        padding-right: 20px;
    }

    .title {
        text-align: center;
        font-size: 24px;
        margin-bottom: 10px;
        padding: 20px;
        background-color: #e8edf3;
    }

    .shopping-cart table {
        width: 100%;
        margin-top: 20px;
    }

    .continue-shopping {
        display: inline-block;
        margin-top: 20px;
        text-decoration: none;
        color: #6e6e6e;
    }

    .order-summary {
        flex: 1;
        background-color: #f1f1f1;
        height: 600px;
    }

    .order-summary h3 {
        font-size: 20px;
        margin-bottom: 20px;
        padding: 20px;
        border-bottom: 1px solid #ddd;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .summary-row select,
    .summary-row input {
        width: 60%;
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .apply-btn {
        background-color: #FF4E4E;
        border: none;
        padding: 10px;
        color: white;
        cursor: pointer;
        margin-left: 10px;
    }

    .total-cost {
        justify-content: space-between;
        font-weight: bold;
        margin-top: 30px;
    }

    .checkout-btn {
        background-color: #252323;
        color: white;
        padding: 15px;
        border: none;
        width: 100%;
        margin-top: 20px;
        cursor: pointer;
    }

    .continue-shopping {
        display: block;
        margin-top: 20px;
        text-decoration: none;
        color: #3498db;
        background: none;
    }

    .continue-shopping:hover {
        background: none;
        color: #0099ff;
        background: none;
    }

    .deleteBtn {
        background: none;
        color: white;
        padding: 10px;
    }

    .deleteBtn:hover {
        background: none;
        color: white;
        padding: 10px;
    }

    .deleteBtn>img {
        width: 20px;
        height: 30px;
        box-shadow: none;
        border: none;
    }

    .noItem-img {
        margin: 50px 0px;
        box-shadow: none;
    }

    .cart-img {
        box-shadow: none;
    }

    .price-checkbox,
    .checkAll {
        appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #ccc;
        border-radius: 3px;
        transition: background-color 0.3s ease, border-color 0.3s ease;
        cursor: pointer;
        margin-right: 10px;
    }

    .price-checkbox:checked,
    .checkAll:checked {
        background-color: #ff5b00;
        border-color: #ff5b00;
    }

    .price-checkbox:checked::after,
    .checkAll:checked::after {
        content: '\2713';
        display: block;
        text-align: center;
        color: white;
        font-size: 14px;
        line-height: 20px;
    }

    .checkbox-container {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .product-image-href {
        background: none;
    }

    .product-image-href:hover {
        background: none;
    }
</style>

<body>
    <h2 class="title">SHOPPING CART</h2>
    <div class="container">
        <?php if ($cart) { ?>
            <div class="shopping-cart">
                <table>
                    <thead>
                        <tr>
                            <div class="checkbox-container">
                                <th><?= html_checkbox("checkAll", null, null, "class='checkAll'") ?></th>
                            </div>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($cart as $c) :
                            $product        = db_select_single('product', 'id', $c->product_id);
                            $product_images = db_select_single('product_image', 'product_id', $product->id);

                            //handle if product stock low than cart quantity
                            if ($product->stock < $c->quantity) {
                                $total_amount = ($product->price * $product->stock);
                                db_update('cart', ['quantity' => $product->stock, 'total_amount' => $total_amount], 'id', $c->id);
                                temp('danger', 'your product  "' . $product->name . '"  quantity have some update');
                                return redirect('/cart');
                            }

                            //handle if product out of stock or is deleted
                            if ($product->is_deleted != 0 || $product->stock == 0) {
                                db_delete('cart', 'id', $c->id);
                                if ($product->is_deleted != 0) {
                                    $message = "Dear ,<br><br>We wanted to inform you that the product '" . $product->name . "' is currently out of stock. 
                                    As a result, it has been automatically removed from your cart.<br><br>
                                    Thank you for your understanding, and we hope you find other items you love.<br><br>Best regards,<br>TARUMT FURNITURE";
                                } else {
                                    $message = "Dear,<br><br>We wanted to inform you that the product '" . $product->name . "' has been removed from our store and is no longer available. 
                                    As a result, it has been automatically removed from your cart.<br><br>
                                    We apologize for any inconvenience this may cause. 
                                    Please feel free to browse our store for other great items.<br><br>Best regards,<br>TARUMT FURNITURE";
                                }
                                sendEmail($_USER_DATA->email, "Cart Update: Product Removed", $message);
                                return redirect('/cart');
                            }
                            $checkOutPrice = 0;
                            $totalItem     = 0;
                        ?>

                            <tr>

                                <form method="POST">
                                    <div class="checkbox-container">
                                        <td><?= html_checkbox('product_id[]', $product->id, null, "form='select-form' class='price-checkbox' data-price='$c->total_amount'") ?></td>
                                    </div>
                                    <td><a href="/product/view?product_id=<?= $product->id ?>" class="product-image-href"><img src="<?= $product_images->image_url ?>" alt="Product Image" style="margin: auto;" class="cart-img"></a></td>
                                    <td><?= $product->name ?></td>
                                    <td>RM<?= $product->price ?></td>
                                    <td>
                                        <button type="submit" class="quantityBtn" type="button" name="decrease" decrease>-</button>
                                        <?= html_hidden('product_stock', $product->stock) ?>
                                        <label class="quantity" name="quantity" style="width: 100px;" value="<?= $c->quantity ?>" min=1 max=<?= $product->stock ?>><?= $c->quantity ?></label>
                                        <button type="submit" class="quantityBtn" type="button" name="increase" increase>+</button>
                                    </td>

                                    <td>RM<?= $c->total_amount ?></td>
                                    <td>
                                        <button type="submit" class="deleteBtn" name="delete"
                                            data-confirm="Are you sure you want to delete ?"><img src="/images/rubbishBin.png" alt="error" /></button>
                                        <input type="hidden" name="cart_id" value="<?= $c->id ?>" />
                                    </td>
                                </form>
                            </tr>
                        <?php $checkOutPrice = $checkOutPrice + $c->total_amount;
                            $totalItem     = $totalItem + $c->quantity;
                        endforeach ?>
                    </tbody>
                </table>
            </div>



            <div class="order-summary">
                <h3>Order Summary</h3>
                <div class="total-cost">
                    <h4>Total Items :<span id="total-item">0</span></h4>
                    <h4>Total Price: RM<span id="total-price">0</span></h4>
                </div>
                <form method="post" id="select-form">
                    <button class="checkout-btn" name="checkout-btn">Checkout</button>
                </form>
                <a href="/product/?category_id=1" class="continue-shopping">← Continue Shopping</a>
            </div>
        <?php } else { ?>
            <div style="height: 100%; width:100%;">
                <img src="/images/noitemfound.png" class="noItem-img" alt="error" style="margin: 60px auto;" />
                <h3 class="noRecordInfo" style="text-align: center;">No items found</h3>
                <a href="/product/?category_id=1" class="continue-shopping" style="text-align: center;">← Continue
                    Shopping</a>
            </div>
        <?php } ?>
    </div>
</body>

<script>
    $(document).ready(function() {


        function updatePriceAndItems() {
            let totalPrice = 0;
            let totalItems = 0;

            $('.price-checkbox:checked').each(function() {
                let price = parseFloat($(this).data('price'));
                totalPrice += price;
                totalItems += 1;
            });
            $('#total-price').text(totalPrice.toFixed(2));
            $('#total-item').text(totalItems);
        }

        $('.price-checkbox').on('click', function() {
            updatePriceAndItems();
        });

        $('.checkAll').on('click', function() {
            $('.price-checkbox').prop('checked', $(this).prop('checked'));
            updatePriceAndItems();
        });
    });
</script>