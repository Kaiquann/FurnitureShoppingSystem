<?php
$stmt = $_db->prepare('
SELECT product_id, SUM(quantity) AS total_quantity
FROM item
GROUP BY product_id
ORDER BY total_quantity DESC
LIMIT 5;
');
$stmt->execute();
$topProduct = $stmt->fetchAll();
?>

<style>

    main {
        margin: 0px;
        padding: 0px;
    }

    .aLink {
        padding: 0;
    }

    .section1 {
        background: url('/images/test.jpg') no-repeat center center fixed;
        background-size: cover;
        display: flex;
        justify-content: start;
        align-items: start;
        height: 800px;
        /* background-attachment: scroll; */
    }

    .section3 {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 600px;
        background-attachment: scroll;
        border-radius: 0px;
        box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.3);
        text-align: center;
        margin: 40px;
    }

    .wrap {
        white-space: normal;
        word-wrap: break-word;
        text-align: left;
        width: 50%;
        max-height: 500px;
        margin: 50px;
    }

    .title {
        width: 100%;
        font-size: 75px;
        font-weight: bold;
        margin: 20px;
        color: #453B3B;
        text-shadow: 4px 2px 2px gray;

    }

    .description {
        margin: 20px;
        font-size: 18px;
        color: #453f3f;
        text-align: left;
        max-width: 60%;
    }

    .shopNowButton {
        padding: 10px;
        background-color: #554d4d;
        border: none;
        color: aliceblue;
        width: 20%;
        min-width: 150px;
        font-size: 20px;
        font-weight: bolder;
        margin: 20px;
    }

    .shopNowButton:hover {
        background-color: #241f1f;
        transition: all 0.5s;
    }

    .slider-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 70%;
        height: 500px;
        padding: 20px;
        position: relative;
    }

    .slider-container img,
    .slider-container a {
        position: absolute;
        padding: 10px;
        width: 100%;
        height: 100%;
        transition: transform 2s ease-in-out;
        z-index: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        background: none;
        box-shadow: none;
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
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #c3c3c3;
    }

    .header-container h1 {
        color: #635a5a;
        font-size: 35px;
        margin: 20px;
    }

    .header-container .section-description {
        color: #5f5f5f;
        font-size: 18px;
        margin: 30px;
    }

    .content-button {
        background-color: #5454b7;
        padding: 15px;
        min-width: 150px;
        text-align: center;
        border-radius: 0px;
    }

    .content-button:hover {
        background-color: #5a5ad5;
    }

    .section2 {
        margin: 20px;
    }

    .features {
        display: flex;
        justify-content: space-around;
        padding: 50px;
        background-color: white;
    }

    .feature {
        text-align: center;
        max-width: 250px;
        padding: 20px;
        border-radius: 20px;
        box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.3);
    }

    .icon {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .icon img {
        width: 60px;
        height: 60px;
        margin-bottom: 20px;
        box-shadow: none;
        border: solid #292929 1px;
        border-radius: 100px;
        padding: 5px;
    }

    h3 {
        font-size: 18px;
        margin-bottom: 10px;
    }

    p {
        font-size: 14px;
        color: #777;
    }

    .section4 {
        width: 100%;
        padding: 0px;
        text-align: center;
    }

    .section4 h2 {
        margin-bottom: 15px;
        color: #333;
        font-size: 24px;
    }

    .explore-btn{
        background:none;
    }

    .explore-btn:hover{
        background: none;
    }
</style>

<body>
    <section class="section1">
        <div class="wrap">
            <h1 class="title"><strong>TARUMT FURNITURE</strong></h1>
            <div class="description">
                <strong>ARTICLE 2024</strong> <br><br>
                From they fine john he give of rich he. They age and draw mrs like. Improving end distrusts may
                instantly was household applauded incommode.
            </div>
            <div class="button">
                <a href="/product?is_deleted=0" style="background:none;"><strong><input type="button" class="shopNowButton"
                            value="GET SRART"></strong></a>
            </div>
        </div>
    </section>

    <section class="section2">
        <div class="features">
            <div class="feature">
                <div class="icon">
                    <img src="/images/fastDeliveryIcon.png" alt="Fast Delivery">
                </div>
                <h3>Fast Delivery</h3>
                <p>Get your furniture delivered to your doorstep quickly and efficiently within just a few days.
                    We partner with trusted shipping companies to ensure. </p>
            </div>
            <div class="feature">
                <div class="icon">
                    <img src="/images/optionIcon.png" alt="Customizable Options">
                </div>
                <h3>Customizable Options</h3>
                <p>Personalize your furniture to suit your individual taste and lifestyle.
                    Choose from an extensive range of colors, fabrics, finishes, and sizes.
                </p>
            </div>
            <div class="feature">
                <div class="icon">
                    <img src="/images/qualityIcon.jpg" alt="Premium Quality">
                </div>
                <h3>Premium Quality</h3>
                <p>Our furniture is built to last, using only the finest materials and craftsmanship.
                    Each piece is carefully constructed from durable woods, high-quality fabrics.</p>
            </div>
        </div>
    </section>



    <?php if (!empty($topProduct)) { ?>
        <section class="section3">
            <div class="header-container">
                <div class="content">
                    <h1>Top 5 Furniture Product</h1>
                    <p class="section-description">Explore our hand-picked collection of premium furniture pieces that blend style and comfort.</p>
                    <a href="/product?is_deleted=0" class="explore-btn"><button class="content-button">Explore</button></a>
                </div>
            </div>
            <div class="slider-container">
                <?php foreach ($topProduct as $index => $p) {
                    $product_images = db_select_single('product_image', 'product_id', $p->product_id); ?>
                    <a href="/product/view?product_id=<?= $p->product_id ?>" class="<?= $index === 0 ? 'active' : 'inactive' ?>">
                        <img src="<?= $product_images->image_url ?>" class="<?= $index === 0 ? 'active' : 'inactive' ?>" alt="Image <?= $index + 1 ?>">
                    </a>
                <?php } ?>
                <div class="dots-container">
                    <?php foreach ($topProduct as $index => $p) { ?>
                        <span class="dot <?= $index === 0 ? 'active-dot' : '' ?>" data-slide="<?= $index ?>"></span>
                    <?php } ?>
                </div>
            </div>
        </section>
    <?php } ?>




    <section class="section4">
        <video id="player" width="1500" height="714" autoplay muted loop>
            <source src="/images/homepageVideo.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="videoButton">
            <button id="muteButton" class="videoActive" style="background:none;color:black;">Mute</button>
            <button id="unmuteButton" style="background:none;color:black;">Unmute</button>
        </div>
    </section>


</body>

<script>
    $(document).ready(function() {

        var video = $('#player')[0];
        $('#muteButton').on('click', function() {
            video.muted = true;
            $('#muteButton').css('color', 'black'); // Set active state for Mute button
            $('#unmuteButton').css('color', 'grey'); // Set inactive state for Unmute button
        });

        // Unmute button
        $('#unmuteButton').on('click', function() {
            video.muted = false;
            $('#unmuteButton').css('color', 'black'); // Set active state for Unmute button
            $('#muteButton').css('color', 'grey'); // Set inactive state for Mute button
        });
    });
</script>