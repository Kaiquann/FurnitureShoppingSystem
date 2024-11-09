<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= COMPANY_NAME ?></title>
    <!-- Icon -->
    <link rel="shortcut icon" href="/images/favicon.png">
    <!-- Css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="/css/app.css">
    <?php if (isAdmin()) : ?>
        <link rel="stylesheet" href="/css/admin.css">
    <?php endif; ?>
    <!-- Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <script src="/js/app.js"></script>
</head>

<body>
    <!-- Flash message -->
    <div id="info"><?= temp('info') ?></div>
    <div id="success"><?= temp('success') ?></div>
    <div id="warning"><?= temp('warning') ?></div>
    <div id="danger"><?= temp('danger') ?></div>
    <header>
    </header>
    <style>
        .navbar-container {
            display: flex;
            justify-content: start;
            align-items: center;
            text-align: center;
            width: 100%;

        }

        .navbar-links {
            display: flex;
            gap: 20px;
        }

        .right-side-nav {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .dropdown-button {
            display: block;
            width: 30px;
            text-decoration: none;
            color: #333333;
            background: none;
            padding: 10px 15px;
            margin: 10px;
            font-size: 18px;
            box-shadow: none;
            cursor: pointer;
        }

        .dropdown {
            width: 100px;
        }

        .dropdown-button:hover {
            background: none;
            border-radius: 5px;
            color: black;
            text-decoration: underline;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 100;
        }

        .dropdown-content a {
            color: #fff;
            padding: 12px 16px;
            text-decoration: none;
            display: flex;
            border-radius: 0px;
        }

        .dropdown-content.show {
            display: block;
        }

        .navbar-links-mobile {
            display: none;
        }

        .active-nav {
            text-decoration: none;
            color: #484444;
            background: none;
            padding: 10px 15px;
            margin: 10px;
            font-size: 18px;
            position: relative;
            display: inline-block;
            text-shadow: 3px 3px 5px rgb(145 129 129);
        }

        .active-nav:hover {
            background: none;
        }

        .active-nav::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: 1px;
            border-bottom: 2px solid;
            box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }

        .nav {
            text-decoration: none;
            color: #707070;
            background: none;
            padding: 10px 15px;
            margin: 10px;
            font-size: 18px;
            width: 100px;
        }


        .nav:hover {
            background: none;
            border-radius: 5px;
            color: black;
        }

        .href>.cart-img {
            width: 100px;
            box-shadow: none;
        }

        .href>.profile-img {
            width: 40px;
            box-shadow: none;
        }

        .href {
            background: none;
            width: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .href:hover {
            background: none;
        }

        .cart-count {
            position: absolute;
            top: 20;
            right: 30;
            background-color: red;
            color: white;
            border-radius: 50px;
            padding: 3px 6px;
            font-size: 10px;
        }

        @media screen and (max-width: 768px) {
            .navbar-container {
                display: none;
            }

            .navbar-links {
                display: none;
                flex-direction: column;
                width: 100%;
                position: absolute;
                top: 50px;
                left: 0;
                background-color: #333;
            }

            .navbar-links-mobile {
                display: block;
            }


        }
    </style>

    <body>
        <nav class="navbar">
            <div class="navbar-container">
                <div class="navbar-links" id="navbar-links">
                    <a href="/" class="<?= isCurrentPage(base()) ? "active-nav" : "nav" ?>"><strong>Home</strong></a>
                    <?php if (!isAdmin()) { ?>
                        <a href="/product?is_deleted=0"
                            class="<?= isCurrentPage(base('product')) ? "active-nav" : "nav" ?>"><strong>Product</strong></a>
                        <a href="/about"
                            class="<?= isCurrentPage(base('about')) ? "active-nav" : "nav" ?>"><strong>About</strong></a>
                        <a href="/contact"
                            class="<?= isCurrentPage(base('contact')) ? "active-nav" : "nav" ?>"><strong>Contact</strong></a>
                    <?php } ?>
                    <?php if (isAdmin()) { ?>
                        <a href="/admin" class="<?= isCurrentPage(base('admin')) ? "active-nav" : "nav" ?>"><strong>Admin
                                DashBoard</strong></a>
                    <?php } ?>
                </div>
            </div>
            <div class="right-side-nav">
                <?php if (isLoggedIn() && isMember()) {
                    global $_USER_DATA;
                    $cartItem = db_select('cart', 'user_id', $_USER_DATA->id);
                    $wishlistItem=db_select('wishlist', 'user_id', $_USER_DATA->id);
                    ?>
                    <a href="/wishlist" class="href" style="position: relative;">
                        <img src="/images/wishlist-icon.png" class="cart-img" alt="error" />
                        <?php if (!empty($wishlistItem)) { ?>
                            <span class="cart-count"><?= sizeof($wishlistItem) ?></span>
                        <?php } ?>
                    </a>

                    <a href="/cart" class="href" style="position: relative;">
                        <img src="/images/cart_icon.png" class="cart-img" alt="error" />
                        <?php if (!empty($cartItem)) { ?>
                            <span class="cart-count"><?= sizeof($cartItem) ?></span>
                        <?php } ?>
                    </a>
                <?php } ?>
                <?php if (isLoggedIn()) { ?>
                    <a href="/user/profile" class="href">
                        <img src="<?= $_USER_DATA->image_url ?? '/images/profile-icon.png' ?>" class="profile-img"
                            alt="profile_image" />
                    </a>
                <?php } ?>
                <div class="dropdown">

                    <img src="/images/navigation-icon.png" alt="error" class="dropdown-button" id="toggle-button"
                        toggle-button />
                    <div class="dropdown-content" id="dropdown-content">
                        <?php if (!isLoggedIn()) { ?>
                            <a href="/register">Register</a>
                            <a href="/login">Login</a>
                            <!-- <a href="/adminlogin">Admin Login</a> -->
                        <?php } else { ?>
                            <a href="/logout" data-confirm="Are you sure you want to logout ?">Log out</a>
                            <?php if (isMember()) { ?>
                                <a href="/transaction">Transaction</a>
                                <a href="/order">Order History</a>
                            <?php }
                        } ?>
                        <div class="navbar-links-mobile" id="navbar-links">
                            <a href="/"><strong>Home</strong></a>
                            <?php if (!isAdmin()) : ?>
                                <a href="/product?is_deleted=0"><strong>Product</strong></a>
                                <a href="/about"><strong>About</strong></a>
                                <a href="/contact"><strong>Contact</strong></a>
                            <?php endif; ?>
                            <?php if (isAdmin()) { ?>
                                <a href="/admin"><strong>Admin DashBoard</strong></a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

        </nav>

        <main>