<?php
global $_USER_DATA;
if (empty($_USER_DATA)) {
    temp('danger', 'You need to login first');
    return redirect('/login');
}
$wishlist = db_select('wishlist', 'user_id', $_USER_DATA->id);
$product_id = req('product_id');




if (is_post()) {
    if (isset($_POST['delete-wishlist'])) {
        $_db->query("DELETE FROM wishlist WHERE product_id = $product_id AND user_id = $_USER_DATA->id");
        temp('success', 'remove successful');
        return redirect("/wishlist");
    }

    if (isset($_POST['add-to-cart'])) {
        $product = db_select_single('product', 'id', $product_id);
        $data = [
            "user_id"      => $_USER_DATA->id,
            "product_id"   => $product_id,
            "quantity"     => 1,
            "total_amount" => ($product->price)
        ];
        db_insert("cart", $data);
        temp('success', 'Add to cart successfull');
        return redirect("/wishlist");
    }
}
?>

<style>

    body {
        background-color: #f5f5f5;
    }

    .wishlist-container {
        width: 90%;
        max-width: 1200px;
        margin: 30px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    }

    .wishlist-title {
        text-align: center;
        font-size: 36px;
        color: #333;
        margin-bottom: 30px;
    }

    .wishlist-title i {
        color: #d1455d;
        margin-right: 10px;
    }

    .wishlist-table {
        width: 100%;
        border-collapse: collapse;
    }

    .wishlist-table thead th {
        text-align: center;
        padding: 10px;
        background-color: #f8f8f8;
        color: #666;
        font-weight: normal;
    }

    .wishlist-table tbody tr {
        border-bottom: 1px solid #ddd;
    }

    .wishlist-table td {
        padding: 15px 10px;
        text-align: center;
    }

    .product-img {
        width: 50px;
        height: auto;
        margin-right: 10px;
        vertical-align: middle;
        box-shadow: none;
    }

    .original-price {
        text-decoration: line-through;
        color: #888;
        margin-right: 10px;
    }

    .discount-price {
        color: #d1455d;
        font-weight: bold;
    }

    td:nth-child(4) {
        color: #28a745;
    }

    .add-to-cart {
        background-color: #2c7ae4;
        color: #fff;
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s ease;
    }

    .add-to-cart:hover {
        background-color: #1a5fbf;
    }

    .delete-icon {
        cursor: pointer;
        color: #888;
        transition: color 0.3s ease;
    }

    .delete-icon:hover {
        color: #d1455d;
    }

    .delete-btn {
        background: none;
    }

    .delete-btn:hover {
        background: none;
    }

    .product-img-container {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .prodoct-img-href {
        background: none;
    }

    .prodoct-img-href:hover {
        background: none;
    }

    .cart-href{
        background: none;
    }

    .cart-href:hover{
        background: none;
    }

    .add-to-cart-btn{
        color: #28a745;
    }

    .add-to-cart-btn:hover{
        color:#1a5fbf;
    }



    @media (max-width: 768px) {
        .wishlist-table thead {
            display: none;
        }

        .wishlist-table tbody tr {
            display: block;
            margin-bottom: 10px;
            border-bottom: none;
        }

        .wishlist-table td {
            display: block;
            text-align: right;
            padding: 10px 0;
        }

        .wishlist-table td:before {
            content: attr(data-label);
            float: left;
            font-weight: bold;
            color: #666;
        }

        .product-img {
            display: inline-block;
        }
    }
</style>

<body>
    <div class="wishlist-container">
        <h1 class="wishlist-title">
            <i class="fa fa-heart"></i> My Wishlist
        </h1>
        <table class="wishlist-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Product Image</th>
                    <th>Product name</th>
                    <th>Unit price</th>
                    <th>Stock status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($wishlist as $w) {
                    $product        = db_select_single('product', 'id', $w->product_id);
                    $product_images = db_select_single('product_image', 'product_id', $w->product_id);
                    if ($product->is_deleted == 0) {
                        $cart =  $_db->query("SELECT * FROM cart WHERE product_id =  $w->product_id AND user_id = $_USER_DATA->id")->fetch();
                ?>
                        <tr>
                            <form method="POST">
                                <td><button type="submit" class="delete-btn" name="delete-wishlist"><i class="fa fa-trash delete-icon"></i></button></td>
                                <td>
                                    <div class="product-img-container">
                                        <a href="/product/view?product_id=<?= $product->id ?>" class="prodoct-img-href"><img src="<?= $product_images->image_url ?>" alt="Product Image" class="product-img"></a>
                                    </div>
                                </td>
                                <td><?= $product->name ?></td>
                                <td>
                                    <span class="discount-price">RM<?= $product->price ?></span>
                                </td>
                                <td>
                                    <p style="<?= $product->stock > 10 ? '' : 'color:red;' ?>"><?= $product->stock ?></p>
                                </td>
                                <td><?=
                                    $cart ? '<a href="/cart" class="cart-href"><p class="add-to-cart-btn"><strong>Added to cart</strong></p></a>' : ($product->stock > 0 ? '<button class="add-to-cart" name="add-to-cart">Add to cart</button>' : '<p style="color:red;"><strong>Sold out!</strong></p>');
                                    ?>
                                </td>
                                <?= html_hidden('product_id', $product->id) ?>
                            </form>
                        </tr>
                <?php
                    } else {
                        $_db->query("DELETE FROM wishlist WHERE product_id = $product->id AND user_id = $_USER_DATA->id");
                    }
                } ?>
            </tbody>
        </table>
    </div>
</body>