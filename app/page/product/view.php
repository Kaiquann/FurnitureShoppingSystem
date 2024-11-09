<?php

use chillerlan\QRCode\QRCode;

if (isAdmin()) {
    temp("danger", "Only guest and members can access this page");
    return redirect("/");
}

global $_USER_DATA;
$product_id = get("product_id");
if (!$product_id) {
    temp('danger', 'Invalid action');
    return redirect('/product');
}

$product = $_db->query("SELECT * FROM product WHERE id = $product_id")->fetch();
if (empty($product)) {
    temp('warning', 'No product found !');
    return redirect('/product?is_deleted=0');
}

if ($product->stock <= 0) {
    temp('warning', 'product out of stock !');
    return redirect('/product?is_deleted=0');
}

if ($product->is_deleted != 0) {
    temp('warning', 'product not found !');
    return redirect('/product?is_deleted=0');
}

$product_images = db_select('product_image', 'product_id', $product->id);

if (isLoggedIn()) {
    $wishlist = $_db->query("SELECT * FROM wishlist WHERE product_id = $product_id AND user_id = $_USER_DATA->id ")->fetch();
}


if (is_post()) {
    if (!isLoggedIn()) {
        temp('danger', 'you must login first');
        return redirect("/login");
    }
    $cart     = $_db->query("SELECT * FROM cart WHERE user_id = $_USER_DATA->id AND product_id = $product_id")->fetch();
    $quantity = post('quantity');

    if ($quantity > $product->stock) {
        temp('warning', 'Your quantity is over than stock');
        return redirect("view?product_id=$product_id");
    }

    if (isset($_POST['checkOut'])) {
        session('product_id', [$product_id]);
        session('quantity', $quantity);
        return redirect("/payment/checkout");
    }

    if (isset($_POST['add-to-wishlist'])) {
        if (empty($wishlist)) {
            $data = ['product_id' => $product_id, 'user_id' => $_USER_DATA->id];
            db_insert('wishlist', $data);
            temp('success', 'Add to wish list successfull');
            return redirect("view?product_id=$product_id");
        } else {
            $_db->query("DELETE FROM wishlist WHERE product_id = $product_id AND user_id = $_USER_DATA->id");
            temp('success', 'remove successful');
            return redirect("view?product_id=$product_id");
        }
    }

    if (isset($_POST['add-to-cart'])) {
        if ($cart) {
            $newQuantity = $cart->quantity + $quantity;
            if ($newQuantity > $product->stock) {
                temp("warning", "Your cart quantity is over than stock!");
                return redirect("view?product_id=$product_id");
            }
            $newQuantity = $cart->quantity + $quantity;
            $stmt        = $_db->prepare("UPDATE cart SET quantity = ?, total_amount = ? WHERE product_id = ? AND user_id = ?");
            $stmt->execute([
                $newQuantity,
                $newQuantity * $product->price,
                $product_id,
                $_USER_DATA->id
            ]);
        } else {
            $data = [
                "user_id"      => $_USER_DATA->id,
                "product_id"   => $product_id,
                "quantity"     => $quantity,
                "total_amount" => ($product->price * $quantity)
            ];
            db_insert("cart", $data);
        }
        temp('success', 'Add to cart successfull');
        return redirect("view?product_id=$product_id");
    }
}
$reviews = db_select('reviews','product_id',$product_id);
?>

<style>
    main {
        padding: 0px;
        background-color: #d5d5d5;
        height: 100%;
    }

    .section {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 20px;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        background-color: #fff;
        box-shadow: 3px 3px 5px rgba(0, 0, 0, 0.3);
        border-radius: 20px;
    }

    .product-details {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        height: auto;
    }

    .product-image {
        flex: 1;
        min-width: 300px;
        padding: 10px;
    }

    .product-image img {
        width: 100%;
    }

    .product-info {
        flex: 1;
        min-width: 300px;
        padding: 10px;
    }

    .product-info h1 {
        font-size: 28px;
        margin-bottom: 10px;
        color: #333;
    }

    .product-info p {
        font-size: 16px;
        line-height: 1.6;
        color: #555;
    }

    .price {
        font-size: 24px;
        font-weight: bold;
        margin: 20px 0;
        color: #e74c3c;
    }



    .back-link {
        display: block;
        margin-top: 20px;
        text-decoration: none;
        color: #3498db;
        background: none;
    }

    .back-link:hover {
        color: #0099ff;
        background: none;
    }

    .number-input-container {
        display: flex;
        align-items: center;
        justify-content: start;
        margin: 10px;
    }

    .quantityBtn {
        background-color: #555;
        color: white;
        border: none;
        padding: 10px;
        font-size: 18px;
        cursor: pointer;
        width: 40px;
        height: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    input.quantity {
        text-align: center;
        width: 60px;
        height: 40px;
        font-size: 18px;
        border: 1px solid #ccc;
        margin: 0 5px;
    }

    button:active {
        background-color: #333;
    }

    .product-slider {
        display: flex;
        justify-content: center;
        align-self: center;
    }

    .product-image-container {
        width: 100%;
        height: auto;
        display: block;
        justify-content: center;
    }

    .product-image img {
        max-width: 100%;
        height: auto;
        box-shadow: none;
    }

    button.prev-btn,
    button.next-btn {
        background: none;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 20px;
        padding: 20px;
        border: none;
        cursor: pointer;
        transform: translateY(-50%);
    }

    button.prev-btn {
        left: 10px;
    }

    button.next-btn {
        right: 10px;
    }

    .slide-btn-container {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .leftArrow-img {
        width: 100px;
    }

    .btn-container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: start;
        margin: 20px 10px;
    }


    .actionBtn {
        background-color: #3498db;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
        font-size: 16px;
        margin-bottom: 10px;
        padding: 10px 20px;
        width: 100%;
        max-width: 200px;
    }

    .actionBtn:hover {
        background-color: #2980b9;
    }

    .wishlist-btn {
        background-color: #d5d5d5;
    }

    .active-wishlist-btn {
        background-color: #333;
    }

    .loveIcon-btn {
        background: none;
        color: black;
    }

    .loveIcon-btn:hover {
        background: none;
        text-decoration: underline;
    }

    .loveIcon-btn-container {
        display: flex;
        justify-content: end;
        margin: 20px;
    }

    @media (max-width: 768px) {
        .product-details {
            flex-direction: column;
        }
    }

    .reviews-section {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        max-height: 400px;
        overflow-y: auto;
    }

    .review-list {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }

    .review-item {
        padding: 15px;
        border-bottom: 1px solid #ddd;
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        border-bottom: 1px solid black;

    }

    .review-info {
        flex-grow: 1;
    }

    .review-separator {
        width: 2px;
        background-color: #ddd;
        height: 100%;
    }

    .review-user {
        font-weight: bold;
        color: #333;
    }

    .review-date {
        font-size: 0.9em;
        color: #777;
    }

    .review-comment {
        margin: 10px 0;
        font-size: 1.1em;
        color: #555;
    }

    .review-rating {
        font-size: 1em;
        color: #ffa500;
    }

    .rating-stars {
        font-size: 1.2em;
        color: #ffa500;
    }

    .review-separator {
        display: none;
    }

    @media screen and (max-width: 768px) {
        .review-header {
            flex-direction: column;
        }

        .review-separator {
            display: none;
        }
    }
</style>

<body>
    <section class="section">
        <div class="container">
            <div class="product-details">
                <div class="product-slider">
                    <div class="slide-btn-container">
                        <button class="prev-btn"><img src="/images/leftArrow.png" class="leftArrow-img" /></button>
                    </div>
                    <div class="product-image-container">
                        <?php foreach ($product_images as $index => $product_image) : ?>
                            <div class="product-image" style="<?= $index === 0 ? '' : 'display: none;' ?>">
                                <img src="<?= $product_image->image_url ?>" alt="Product Image">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="slide-btn-container">
                        <button class="next-btn"><img src="/images/rightArrow.png" class="leftArrow-img" /></button>
                    </div>
                </div>

                <div class="product-info">
                    <form method="post" class="btnForm">
                        <div class="loveIcon-btn-container">
                            <img src="<?= (new QRCode)->render(baseUriQuery()) ?>" alt="QR Code" class="img-20" />
                        </div>
                        <?php if (isLoggedIn()) {

                        ?>
                            <div class="loveIcon-btn-container">
                                <button class="loveIcon-btn" name="add-to-wishlist"> <i
                                        class="<?= $wishlist ? 'fa-solid fa-heart' : 'fa-regular fa-heart' ?>"></i></button>
                            </div>
                        <?php } ?>
                        <h1><?= $product->name ?></h1>
                        <p><strong>Description: </strong> <?= $product->description ?></p>
                        <div class="stock"><strong>Stock :<?= $product->stock ?></strong></div>
                        <div class="price">RM<?= $product->price ?></div>

                        <div class="number-input-container">
                            <button class="quantityBtn" type="button" decrease>-</button>
                            <input type="number" class="quantity" name="quantity" value="1" min="1" />
                            <button class="quantityBtn" type="button" increase>+</button>
                        </div>

                        <div class="btn-container">
                            <button type="submit" data-confirm="Are your sure you want fo Add this product ?"
                                class="actionBtn" name="add-to-cart">Add to Cart</button>
                            <button type="submit" name="checkOut" class="actionBtn"
                                style="background-color: #db654c;">Buy Now</button>
                        </div>
                    </form>

                    <section class="reviews-section">
                        <h2>User Reviews</h2>
                        <?php if (empty($reviews)) : ?>
                            <p>No reviews yet. Be the first to review this product!</p>
                        <?php else : ?>
                            <ul class="review-list">
                                <?php 
                                
                                foreach ($reviews as $review) :
                                    $user = db_select_single('users', 'id', $review->user_id);
                                ?>
                                    <li class="review-item">
                                        <div class="review-header">
                                            <div class="review-info">
                                                <strong class="review-user"><?= html_print($user->first_name . ' ' . $user->last_name) ?>:</strong>
                                                <p class="review-comment"><?= html_print($review->comment) ?></p>
                                                <div class="review-rating">
                                                    Rating:
                                                    <span class="rating-stars"><?= str_repeat('★', $review->rating) . str_repeat('☆', 5 - $review->rating) ?></span>
                                                </div>
                                            </div>
                                            <div class="review-separator"></div>
                                            <small class="review-date">Reviewed on <?= date('Y-m-d', strtotime($review->created_at)) ?></small>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </section>



                    <a href="/product/?category_id=1" class="back-link">← Back to Products</a>
                </div>

            </div>
        </div>
    </section>
</body>

<script>
    $(document).ready(function() {
        let currentIndex = 0;
        const images = $('.product-image');
        const totalImages = images.length;

        $('.next-btn').click(function() {
            images.eq(currentIndex).hide();
            currentIndex = (currentIndex + 1) % totalImages;
            images.eq(currentIndex).show();
        });

        $('.prev-btn').click(function() {
            images.eq(currentIndex).hide();
            currentIndex = (currentIndex - 1 + totalImages) % totalImages;
            images.eq(currentIndex).show();
        });
    });
</script>