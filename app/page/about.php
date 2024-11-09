<?php
if (isAdmin()) {
    temp("danger", "Only guest and members can access this page");
    return redirect("/");
}
?>
<style>
    * {
        margin: 0;
        padding: 0;
    }

    .banner {
        min-height: 100vh;
        background-image: url(images/aboutUsBanner.png);
        background-position: center;
        background-size: cover;
        position: relative;
    }

    .scroll-text {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        color: #7e604a;
        font-size: 20px;
        padding: 10px 20px;
        border-radius: 5px;
    }

    .collection {
        width: 80%;
        margin: auto;
        text-align: center;
        padding-top: 100px;
    }

    h1 {
        font-size: 36px;
        font-weight: 600;
    }

    p {
        color: #777;
        font-size: 14px;
        font-weight: 300;
        line-height: 22px;
        padding: 10px;
    }

    .collection {
        width: 80%;
        margin: auto;
        text-align: center;
        padding-top: 50px;
    }

    .collect-row {
        display: flex;
        justify-content: space-between;
    }

    .collect-col {
        flex-basis: 32%;
        border-radius: 10px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .collect-col img {
        width: 100%;
        display: block;
    }

    .layer {
        background: transparent;
        height: 100%;
        width: 100%;
        position: absolute;
        top: 0;
        left: 0;
        transition: 0.5s;
    }

    .layer:hover {
        background: rgba(0, 0, 0, 0.7);
    }

    .layer h3 {
        width: 100%;
        font-weight: 500;
        color: #fff;
        font-size: 26px;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        position: absolute;
        opacity: 0;
        transition: 0.5s;
    }

    .layer:hover h3 {
        bottom: 49%;
        opacity: 1;
    }

    .video-container {
        width: 80%;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        text-align: center;
    }

    .video-container h2 {
        margin-bottom: 15px;
        color: #333;
        font-size: 24px;
    }

    #player {
        width: 100%;
        height: 450px;
        border-radius: 10px;
        overflow: hidden;
    }

    .button {
        margin-top: 15px;
    }

    .button button {
        background-color: #FFE8E8;
        color: black;
        padding: 10px 20px;
        margin: 5px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .button button:hover {
        background-color: #fff3f3;
    }


    .benefit {
        width: 80%;
        margin: auto;
        text-align: center;
        padding-top: 100px;
    }

    h1 {
        font-size: 36px;
        font-weight: 600;
    }

    p {
        color: #777;
        font-size: 14px;
        font-weight: 300;
        line-height: 22px;
        padding: 10px;
    }

    .row {
        margin-top: 5%;
        display: flex;
        justify-content: space-between;
    }

    .benefit-col {
        flex-basis: 31%;
        background: #fff3f3;
        border-radius: 10px;
        margin-bottom: 5%;
        padding: 20px 12px;
        box-sizing: border-box;
        transition: 0.5s;
    }

    h3 {
        text-align: center;
        font-weight: 600;
        margin: 10px 0;
    }

    .benefit-col:hover {
        box-shadow: 0 0 20px 0px rgba(0, 0, 0, 0.2);
    }

    .strength {
        width: 80%;
        margin: auto;
        text-align: center;
        padding-top: 100px;
    }

    .strength-col {
        flex-basis: 31%;
        border-radius: 10px;
        margin-bottom: 5%;
        text-align: left;
    }

    .strength-col img {
        width: 100%;
        border-radius: 10px;
    }

    .strength-col p {
        padding: 0;
    }

    .strength-col h3 {
        margin-top: 16px;
        margin-bottom: 15px;
        text-align: left;
    }

    .review {
        width: 80%;
        margin: auto;
        padding-top: 100px;
        text-align: center;
    }

    .review-col {
        flex-basis: 44%;
        border-radius: 10px;
        margin-bottom: 5%;
        text-align: left;
        background: #fff3f3;
        padding: 25px;
        cursor: pointer;
        display: flex;
    }

    .review-col img {
        height: 80px;
        width: 80px;
        margin-left: 5px;
        margin-right: 30px;
        border-radius: 50%;
    }

    .review-col p {
        padding: 0;
    }

    .review-col h3 {
        margin-top: 15px;
        text-align: left;
    }

    .review-col .bx {
        color: #f44336;
    }

    .aboutUs {
        width: 100%;
        text-align: center;
        padding: 5% 0;
    }

    .aboutUs h4 {
        margin-bottom: 25px;
        margin-top: 20px;
        font-weight: 600;
    }

    .icon-container {
        display: flex;
        justify-content: center;
        gap: 20px;
    }

    .icon-container .bx {
        width: 50px;
        height: 20px;
        margin: 0 13px;
        cursor: pointer;
        padding: 18px 0;
        border-radius: 3px 3px 3px 3px;
        background-color: #f44336;
    }

    .icon-container a {
        margin: 0;
        padding: 0;
        text-decoration: none;
        background-color: #fff;
    }
</style>


<body>
    <!-- ---------- banner -------------- -->
    <section class="banner">
        <div class="scroll-text">Scroll Down To View More</div>
    </section>
    <!-- ---------- collection -------------- -->
    <section class="collection">
        <h1>Furniture Collection</h1>
        <p>Simple elegant minimal furniture collection</p>

        <div class="collect-row">
            <div class="collect-col">
                <img src="images/collect1.jpg">
                <div class="layer">
                    <h3>Sofa</h3>
                </div>
            </div>

            <div class="collect-col">
                <img src="images/collect2.jpg">
                <div class="layer">
                    <h3>Chair</h3>
                </div>
            </div>

            <div class="collect-col">
                <img src="images/collect3.jpg">
                <div class="layer">
                    <h3>Table</h3>
                </div>
            </div>

        </div>
    </section>
    <!-- ----------product display-------------- -->
    <section class="video-container">
        <h2>Our Product</h2>
        <video id="player" width="345" height="714" controls>
            <source src="images/productDisplay.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="button">
            <button id="muteButton">Mute</button>
            <button id="unmuteButton">Unmute</button>
        </div>
    </section>

    <!-- ----------benefit-------------- -->
    <section class="benefit">
        <h1> Benefits</h1>
        <p>We wanted to create furniture that not only serves a purpose but also inspires and elevates the atmosphere of any room.</p>
        <div class="row">
            <div class="benefit-col">
                <h3>Wide Range of Furniture</h3>
                <p>We offer a wide range of products to suit every room in your home.
                    For those with unique needs, we provide custom and modular furniture options tailored to fit any space and preference.</p>
            </div>

            <div class="benefit-col">
                <h3>Personalization</h3>
                <p>We offer custom designs that allow you to create furniture pieces that perfectly match your space, style, and functional requirements.
                    You can choose from a wide variety of materials, fabrics, and finishes to ensure that each piece reflects your taste. </p>
            </div>

            <div class="benefit-col">
                <h3>Quality and Sustainability</h3>
                <p>Our furniture is crafted from premium materials, such as solid wood and durable metals, ensuring longevity and durability.
                    We adhere to sustainable practices, using eco-friendly materials and processes to minimize our environmental impact.</p>
            </div>
        </div>
    </section>

    <!-- ---------- strength -------------- -->
    <section class="strength">
        <h1>Why Choose Us </h1>

        <div class="row">
            <div class="strength-col">
                <img src="images/eco.png">
                <h3>Eco-Friendly</h3>
                <p>eco-friendly materials and responsible manufacturing processes</p>
            </div>

            <div class="strength-col">
                <img src="images/money.png">
                <h3>Value For Money</h3>
                <p>While we focus on quality, we also believe that great furniture should be receive exceptional value for your investment.</p>
            </div>

            <div class="strength-col">
                <img src="images/furniture.png">
                <h3>Innovative Design</h3>
                <p>Our collection features a blend of modern trends and timeless designs, ensuring that you find pieces that enhance your home’s aesthetic while being functional and stylish.</p>
            </div>
        </div>
    </section>

    <!-- ---------- review -------------- -->
    <section class="review">
        <h1>What Our Customer Says</h1>
        <p>Better Experience Starts Here</p>

        <div class="row">
            <div class="review-col">
                <img src="images/user1.jpg">
                <div>
                    <p>"I recently purchased a living room set from this furniture company, and I couldn't be happier with my decision.
                        The quality of the furniture is outstanding, and the pieces are both stylish and comfortable.
                        The customization options allowed me to choose the perfect fabric and finish to match my home decor.
                        I highly recommend this company to anyone looking for high-quality, beautiful furniture!"</p>
                    <h3>Tiffany</h3>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                </div>
            </div>

            <div class="review-col">
                <img src="images/user2.jpg">
                <div>
                    <p>"I was in search of eco-friendly furniture, and this company exceeded my expectations.
                        Not only do they use sustainable materials, but the craftsmanship is also top-notch.
                        I bought a dining table and chairs, and they are both sturdy and elegant.
                        The entire experience, from the personalized consultation to the delivery, was seamless."</p>
                    <h3>Johnson</h3>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star'></i>
                    <i class='bx bxs-star-half'></i>
                </div>
            </div>

        </div>
    </section>

    <section class="aboutUs">
        <h4>About Us</h4>
        <p>TARUMT Furniture began wants to transform everyday living spaces into extraordinary experiences.
            <br>Our journey began with a passion for design and a deep respect for tradition.
            <br>We wanted to create furniture that not only serves a purpose but also inspires and elevates the atmosphere of any room.
        </p>

        <div class="icon-container">
            <a href="https://www.facebook.com">
                <i class='bx bxl-facebook-square'></i>
            </a>
            <a href="https://www.instagram.com">
                <i class='bx bxl-instagram-alt'></i>
            </a>
            <a href="https://www.linkedin.com">
                <i class='bx bxl-linkedin-square'></i>
            </a>
        </div>

    </section>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script>
        $(document).ready(function() {
            var video = $('#player')[0];

            $('#muteButton').on('click', function() {
                video.muted = true;
            });

            $('#unmuteButton').on('click', function() {
                video.muted = false;
            });
        });
    </script>
</body>