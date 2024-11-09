<?php

/**
 * @author: Liew Kai Quan
 */
if (isAdmin()) {
    temp("danger", "Only guest and members can access this page");
    return redirect("/");
}

const PRICE_SORT_LIST = [
    0 => 'Default',
    1 => 'Price: Low to High',
    2 => 'Price: High to Low',
];

function get_sort_price_range($sort_price_range)
{
    switch ($sort_price_range) {
        case 1:
            $price_range_query = 'asc';
            break;
        case 2:
            $price_range_query = 'desc';
            break;
        default:
            $price_range_query = '';
    }
    return $price_range_query;
}
$category = db_select_all("category");
$category_id = req("category_id", 0);
$is_deleted = get("is_deleted");
$search = get('search');
$sort = req("sort", 0);
$page = req('page', 1);
$price_range = req('price_range', 0);
$productFound = false;

$price_range_query = get_price_range($price_range);
$sort_price_range_query = get_sort_price_range($sort);

$orderByQuery = $sort ? "ORDER BY p.price $sort_price_range_query" : " ";

$query = "SELECT p.* FROM product p JOIN category c ON p.category_id = c.id WHERE p.name LIKE ? 
    AND p.is_deleted = 0  
    AND c.is_deleted = 0
    AND p.category_id = " . ($category_id ?: 'p.category_id') . "
    AND p.price BETWEEN $price_range_query 
   $orderByQuery
";
$params = ["%$search%"];

$product = new SimplePager($query, $params, '8', $page);
$arrProduct = $product->result;

//check exist or not
$productFound = !empty($arrProduct);

$stmt = $_db->prepare('SELECT * FROM product LIMIT 5');
$stmt->execute();
$randomProduct = $stmt->fetchAll();

//take the hot sales product
$stmt = $_db->prepare('
SELECT product_id, SUM(quantity) AS total_quantity, MAX(created_at) AS latest_date
FROM item
GROUP BY product_id
ORDER BY total_quantity DESC, latest_date DESC
LIMIT 8;
');
$stmt->execute();
$hotSalesProduct = $stmt->fetchAll();
?>

<style>
    main {
        margin: 0px;
        padding: 0px;
    }

    .topContent {
        width: 30%;
        min-width: 400px;
        text-align: center;
        margin-top: 100px;
        padding: 10px;
    }

    .heading {
        font-size: 48px;
        font-weight: bold;
        color: #000;
        margin-bottom: 20px;
        text-shadow: 2px 2px 2px gray;
    }

    .description {
        font-size: 18px;
        color: #555;
        line-height: 1.6;
    }

    .category {
        text-align: center;
        margin: 3%;
        font-size: 25px;
    }

    .category-button {
        background: none;
        color: black;
    }

    .category-button:hover {
        background: none;
        text-decoration: underline;
    }

    .active-category-button {
        text-decoration: none;
        font-weight: bold;
        color: #484444;
        background: none;
        padding: 10px 15px;
        margin: 10px;
        font-size: 18px;
        position: relative;
        display: inline-block;
    }

    .active-category-button:hover {
        background: none;
    }

    .active-category-button::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 1px;
        border-bottom: 2px solid;
        box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
    }

    .bottomContainer {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        flex-direction: row;

    }

    .productDetails {
        padding: 10px;
        margin: 15px;
        width: 20%;
        min-width: 300px;
        height: 100%;
        border-radius: 0px;
    }

    .productDetails:hover {
        box-shadow: 0 5px 8px 0 rgba(0, 0, 0, 0.2), 0 9px 30px 0 rgba(0, 0, 0, 0.19);
    }

    .outOfStockProduct {
        padding: 10px;
        margin: 15px;
        width: 20%;
        min-width: 300px;
        height: 300px;
        border-radius: 0px;
        position: relative;
    }

    .outOfStockOverlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(169 157 157 / 60%);
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 24px;
        font-weight: bold;
        text-transform: uppercase;
        pointer-events: none;
    }

    .product-container {
        background: none;
        z-index: -1;
        color: black;
    }

    .product-container:hover {
        background: none;
    }

    .image-container {
        height: 150px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .image-container>img {
        width: 100%;
        height: 100%;
        border: none;
        box-shadow: none;
    }

    .noProductFound-img {
        box-shadow: none;
    }

    .sortAndSearch-container {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .filter-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 30px;
    }

    .sortContainer,
    .priceRange-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 20px;
    }

    .sort-by-label,
    .priceRange-label {
        margin-right: 10px;
        font-family: Arial, sans-serif;
        font-size: 16px;
    }

    select {
        padding: 8px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-family: Arial, sans-serif;
    }

    select:focus {
        border-color: #007bff;
        outline: none;
    }

    .section1 {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 600px;
        background-attachment: scroll;
        border-radius: 0px;
        box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.3);
        text-align: center;
        width: 100%;
    }

    .slider-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 500px;
        padding: 20px;
        position: relative;
    }

    .slider-container img,
    .slider-container a {
        position: absolute;
        padding: 10px;
        width: 90%;
        height: 100%;
        transition: transform 2s ease-in-out 1s;
        z-index: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        background: none;
        box-shadow: none;
        transform: translateX(50px);
        transition: opacity 1s ease, transform 1s ease;
    }

    .slider-container img.inactive {
        opacity: 0;
    }

    .slider-container img.active {
        opacity: 1;
    }

    .slider-container a.inactive {
        opacity: 0;
        pointer-events: none;
        z-index: 1;
    }

    .slider-container a.active {
        opacity: 1;
        pointer-events: auto;
        z-index: 2;
    }

    .dots-container {
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        text-align: center;
        z-index: 2;
    }

    .dot {
        display: inline-block;
        width: 15px;
        height: 15px;
        margin: 0 5px;
        background-color: #bbb;
        border-radius: 50%;
        cursor: pointer;
    }

    .active-dot {
        background-color: red;
    }

    .header-container {
        padding: 20px;
        height: 560px;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .header-container h1 {
        color: #635a5a;
        font-size: 35px;
        margin: 20px;
        text-align: center;
    }

    .hotsales-img-container {
        width: 100%;
        height: 70%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .hotsales-img-container img {
        width: 100%;
        height: 100%;
        box-shadow: none;
        border-radius: 0px;
    }

    .explore-button {
        padding: 10px;
        border-radius: 0px;
        background-color: #007bff;
        min-width: 120px;
    }
</style>




<section class="section1">
    <div class="header-container">
        <?php if (!empty($hotSalesProduct)) { ?>
            <div class="hotsales-container">
                <div class="hotsales-img-container">
                    <img src="/images/hotsalesImage.jpg" alt="error" />
                </div>
                <h1>Hot sales Product🔥</h1>
            </div>
        <?php } else { ?>
            <div class="random-product-container">
                <h1>Our Product</h1>
                <p class="section-description" style="text-align: center;">Explore our meticulously curated collection of
                    premium furniture pieces, designed to enhance both the beauty and functionality of your home. Each
                    product in our lineup is hand-picked for its exceptional quality, comfort, and timeless style.</p>
                <button class="explore-button">Explore</button>
            </div>
        <?php } ?>
    </div>
    <div class="slider-container">
        <?php $products = !empty($hotSalesProduct) ? $hotSalesProduct : $randomProduct;
        foreach ($products as $index => $p) {
            $product_images = db_select_single('product_image', 'product_id', $p->product_id ?? $p->id); ?>
            <a href="/product/view?product_id=<?= $product_images->product_id ?>" class="product-container">
                <img src="<?= $product_images->image_url ?>" class="<?= $index === 0 ? 'active' : 'inactive' ?>"
                    alt="Image <?= $index + 1 ?>">
            </a>
        <?php } ?>
        <div class="dots-container">
            <?php foreach ($products as $index => $p) { ?>
                <span class="dot <?= $index === 0 ? 'active-dot' : '' ?>" data-slide="<?= $index ?>"></span>
            <?php } ?>
        </div>
    </div>
</section>


<h1 style="text-align: center;margin:30px;text-shadow: 2px 2px 4px rgba(0, 0.2, 0.2, 0.3);">FURNITURE PRODUCTS</h1>
<div class="reponseHtml" id="reponseHtml">
    <section class="section2">
        <div class="category" id="category">
            <div class="categoryButton" id="categoryButton">
                <button type="submit" data-url="?is_deleted=0"
                    class="<?= isCurrentPage(baseUri() . '?is_deleted=0', "?is_deleted=", $is_deleted) ? "active-category-button" : "category-button" ?>">All</button>
                <?php foreach ($category as $s):
                    $categoryPage = baseUri() . '?category_id=' . $s->id;
                    ?>
                    <?php if ($s->is_deleted == 0) { ?>
                        <button type="submit" data-url="?category_id=<?= $s->id ?>"
                            class="<?= isCurrentPage($categoryPage, "?category_id=", get("category_id")) ? "active-category-button" : "category-button" ?>"><?= $s->name ?>
                        </button>
                    <?php } ?>
                <?php endforeach ?>

                <div class="filter-container">

                    <div class="sortContainer">
                        <label class="sort-by-label" for="sortOptions">Sort by</label>
                        <?= html_select("sort_price_range", PRICE_SORT_LIST, PRICE_SORT_LIST[$sort] ?? 0); ?>
                    </div>

                    <div class="priceRange-container">
                        <label class="priceRange-label" for="priceRangeOption">Range :</label>
                        <?= html_select("price_range", PRICE_RANGE_LIST, $price_range); ?>
                    </div>
                </div>


                <form>
                    <?= html_search("search", "data-search"); ?>
                    <?php if ($is_deleted != 0) { ?>
                        <input type="hidden" name="category_id" value="<?= $category_id ?>">
                    <?php } else { ?>
                        <input type="hidden" name="is_deleted" value="<?= 0 ?>">
                    <?php } ?>
                    <?= html_hidden("sort", $sort) ?>
                    <?= html_hidden("price_range", $price_range) ?>
                </form>
            </div>
        </div>
    </section>


    <section class="section3">
        <div class="bottomContainer" id="productContainer">
            <?php
            if ($productFound) {
                foreach ($arrProduct as $s):
                    $product_image = db_select_single('product_image', 'product_id', $s->id);
                    if ($s->stock != 0) {
                        ?>
                        <div class="productDetails">
                            <a href="/product/view?product_id=<?= $s->id ?>" class="product-container">
                                <div class="image-container">
                                    <img src="<?= $product_image->image_url ?>" alt="Description of the image" class="image"
                                        loading="lazy">
                                </div>
                                <h2 class="productName" style="text-align: center;"><?= $s->name ?></h2>
                                <p class="productPrice" style="text-align: center;"><strong>RM<?= $s->price ?></strong></p>
                                <?php foreach ($hotSalesProduct as $hotProduct) {
                                    if ($hotProduct->product_id == $s->id) { ?>
                                        <strong>
                                            <p class="hotsales-text" style="text-align: center;color:#ef7c00;">Hot sales🔥</p>
                                        </strong>
                                    <?php }
                                } ?>
                                <?php
                                $rating = $_db->query("SELECT AVG(rating) as average_rating FROM reviews WHERE product_id = $s->id");
                                $average = $rating->fetch(PDO::FETCH_ASSOC);
                                $avgRating = $average['average_rating'] ? round($average['average_rating']) : 0;
                                ?>
                                <div class="star-rating" style="text-align: center;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <label class="star" style="<?php echo $i <= $avgRating ? 'color: gold;' : ''; ?>">★</label>
                                    <?php endfor; ?>
                                    <label > <?= sizeof(db_select('reviews', 'product_id', $s->id)) ?> reviews</label>
                                </div>
                            </a>
                        </div>

                    <?php } else { ?>
                        <div class="outOfStockProduct">
                            <div class="image-container">
                                <img src="<?= $product_image->image_url ?>" alt="Description of the image" class="image"
                                    loading="lazy">
                            </div>
                            <h2 class="productName" style="text-align: center;"><?= $s->name ?></h2>
                            <p class="productPrice" style="text-align: center;"><strong>RM<?= $s->price ?></strong></p>
                            <div class="outOfStockOverlay">Out of Stock</div>
                        </div>
                    <?php }
                endforeach; ?>
            </div>
            <h4 style="text-align: center;">
                <?= $product->count ?> of <?= $product->item_count ?> record(s) |
                Page <?= $product->page ?> of <?= $product->page_count ?>
            </h4>
            <?php
            $product->html("category_id=$category_id&search=$search&is_deleted=$is_deleted&sort=$sort&price_range=$price_range");
            } else {
                ?>
            <div class="noProductFound">
                <img src="/images/noProductFound.png" alt="error" class="noProductFound-img" />
            <?php } ?>
        </div>

    </section>
</div>

<script>
    $(document).ready(function () {

        function updateUrlAndFetchData(paramName, selectedValue) {
            var currentUrlParams = new URLSearchParams(window.location.search);

            var search = currentUrlParams.get('search');
            var currentCategoryId = currentUrlParams.get('category_id');
            var currentIsDeleted = currentUrlParams.get('is_deleted');
            var price_range = currentUrlParams.get('price_range');
            var currentSort = currentUrlParams.get('sort');

            var updatedUrl = currentIsDeleted !== null ? "?is_deleted=0" : "?category_id=" + currentCategoryId;
            updatedUrl += "&" + paramName + "=" + selectedValue;

            if (paramName !== 'price_range' && price_range !== null) {
                updatedUrl += "&price_range=" + price_range;
            }
            if (paramName !== 'sort' && currentSort !== null) {
                updatedUrl += "&sort=" + currentSort;
            }
            if (search !== null) {
                updatedUrl += "&search=" + search;
            }

            history.pushState(null, '', updatedUrl);

            $.ajax({
                url: updatedUrl,
                type: 'GET',
                success: function (response) {
                    var reponseHtml = $(response).find('#reponseHtml').html();
                    if (reponseHtml) {
                        $('#reponseHtml').html(reponseHtml);
                    } else {
                        console.error('No product content found in response.');
                    }
                },
                error: function () {
                    console.log('Error fetching products.');
                }
            });
        }

        $(document).on('change', '#sort_price_range', function () {
            var selectedValue = $(this).val();
            updateUrlAndFetchData('sort', selectedValue);
        });

        $(document).on('change', '#price_range', function () {
            var selectedValue = $(this).val();
            updateUrlAndFetchData('price_range', selectedValue);
        });




        $(document).on('click', '.category-button', function (event) {
            event.preventDefault();
            var url = $(this).data('url');
            history.pushState(null, '', url);
            $.ajax({
                url: url,
                type: 'GET',
                success: function (response) {
                    var reponseHtml = $(response).find('#reponseHtml').html();
                    if (reponseHtml) {
                        $('#reponseHtml').html(reponseHtml);
                        history.pushState(null, '', url);
                    } else {
                        console.error('No product content found in response.');
                    }
                },
                error: function () {
                    console.log('Error fetching products.');
                }
            });
        });




    });
</script>