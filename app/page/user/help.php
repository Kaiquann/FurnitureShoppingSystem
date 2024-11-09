<?php
global $_USER_DATA;

$id = $_USER_DATA->id;

$stmt = $_db->prepare('
    SELECT * FROM users 
    WHERE id = ?
');
$stmt->execute([$id]);
$user = $stmt->fetch();
?>

<style>
    body {
        height: 100vh;
        background-color: #c0d3da;
        margin: 0;
        transition: all 0.3s ease;
    }

    /*--------sidebar-----------*/
    .sidebar .text {
        font-size: 16px;
        font-weight: 500;
        color: #000;
        transition: color 0.3s;
    }

    .sidebar .user-img {
        justify-content: center;
        min-width: 60px;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .sidebar {
        display: flex;
        flex-direction: column;
        position: absolute;
        top: 70px;
        left: 0;
        height: calc(100% - 70px);
        width: 230px;
        padding: 10px 14px;
        background-color: #fff;
        transition: width 0.3s;
    }

    .sidebar .nav-text {
        color: #000;
    }

    .sidebar.close {
        width: 80px;
    }

    .sidebar.close .user-img img {
        width: 40px;
        height: 40px;
        border-radius: 6px;
    }

    .sidebar.close .text {
        display: none;
    }

    .sidebar .icon {
        font-size: 24px;
        margin-right: 10px;
    }

    .sidebar:not(.close) header .toggle {
        transform: translateY(-50%) rotate(180deg);
    }

    .sidebar.close .menu-links .icon {
        margin-right: 0;
    }

    .sidebar.close .user-img img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
    }

    .sidebar .image-text img {
        width: 50px;
        height: 50px;
        border-radius: 6px;
    }

    .sidebar.close header .image-text {
        opacity: 1;
    }

    .sidebar.close .nav-text {
        display: none;
    }

    .sidebar header .image-text {
        display: flex;
        align-items: center;
        opacity: 1;
        transition: all 0.3s ease;
    }

    .sidebar .icon {
        font-size: 24px;
        margin-right: 10px;
        color: #000;
    }

    header .image-text .user-details {
        display: flex;
        flex-direction: column;
        margin-left: 10px;
    }

    .user-details .name {
        font-weight: 600;
    }

    .user-details .title {
        margin-top: -2px;
        color: #777;
    }

    .sidebar header .toggle {
        position: absolute;
        top: 30px;
        right: -15px;
        transform: translateY(-50%);
        height: 25px;
        width: 25px;
        background-color: #7d91a6;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #000;
        cursor: pointer;
    }

    .sidebar li a {
        height: 100%;
        background-color: #fff;
        display: flex;
        align-items: center;
        border-radius: 6px;
    }

    .sidebar li a:hover {
        background-color: #c0d3da;
        color: #000;
    }

    .menu-bar {
        margin-top: 20px;
        flex-grow: 1;
    }

    .sidebar li {
        height: 50px;
        margin-top: 10px;
        list-style: none;
        display: flex;
        align-items: center;
        transition: color 0.3s;
    }

    .sidebar .nav-link {
        color: #fff;
    }

    .bottom-content {
        margin-top: auto;
    }

    .bottom-content li {
        margin-top: 10px;
    }

    .bottom-content .nav-text {
        color: #ffffff;
    }

    .toggle-switch {
        position: absolute;
        width: 34px;
        height: 18px;
        background: #ccc;
        border-radius: 50px;
        position: relative;
        cursor: pointer;
        margin-left: 10px;
    }

    .toggle-switch .switch {
        position: absolute;
        top: 2px;
        left: 2px;
        width: 14px;
        height: 14px;
        background: white;
        border-radius: 50%;
        transition: 0.3s;
    }

    .toggle-switch.active .switch {
        left: calc(100% - 16px);
    }

    /*dark mode style */
    .dark-mode .sidebar,
    .dark-mode .sidebar li a {
        background-color: #1e1e1e;
    }

    .dark-mode {
        background-color: #121212;
        color: #ffffff;
    }

    .dark-mode .sidebar .text,
    .dark-mode .sidebar .nav-text,
    .dark-mode .sidebar .icon {
        color: #fff;
    }

    .dark-mode .sidebar .user-details .title {
        color: #ccc;
    }

    /*-------- care tips-----------*/
    .care-tips {
        background-color: #fff;
        border-radius: 30px;
        padding: 20px;
        margin: 10px;
        margin-left: 100px;
        max-width: 100%;
    }

    .tips-title {
        font-size: 24px;
        font-weight: bold;
        color: #4b7c9a;
        margin-bottom: 15px;
        text-align: center;
    }

    .tips-container {
        display: flex;
        flex-wrap: wrap;
        margin-left: 100px;
        max-width: 100%;
        gap: 15px;
        justify-content: center;
    }

    .sidebar:not(.close)~.care-tips {
        margin-left: 260px;
    }

    .tip-card {
        background-color: #fff;
        border-radius: 8px;
        padding: 15px;
        width: 250px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .tip-card:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .tip-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .tip {
        font-weight: 600;
        color: #4b7c9a;
        margin: 0;
    }

    .tip-description {
        color: #575757;
        margin-top: 8px;
    }

    /*--------faq-----------*/
    .accordion .image-box {
        height: 360px;
        width: 300px;
    }

    .accordion .image-box img {
        height: 100%;
        width: 100%;
        object-fit: contain;
        box-shadow: none;
    }

    .accordion {
        display: flex;
        padding: 24px;
        align-items: center;
        background-color: #fff;
        border-radius: 30px;
        margin: 10px;
        margin-left: 100px;
        max-width: 100%;
        justify-content: space-between;
        transition: all 0.3s ease;
    }

    .sidebar:not(.close)~.accordion {
        margin-left: 260px;
    }

    .accordion .accordion-text {
        width: 60%;
    }

    .accordion .accordion-text .title {
        font-size: 35px;
        font-weight: 600;
        color: #4b7c9a;
    }

    .accordion .accordion-text .faq-text {
        margin-top: 25px;
    }

    .accordion .accordion-text .faq-text li {
        color: #fff;
        cursor: pointer;
    }

    .accordion-text li .question-arrow {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .accordion-text li .question-arrow .question {
        font-family: 18px;
        font-weight: 700;
        color: #575757;
        transition: all 0.3s ease;
    }

    .accordion-text li .question-arrow .arrow {
        font-size: 20px;
        color: #595959;
        transition: all 0.3s ease;
    }

    .accordion-text li .line {
        display: block;
        height: 2px;
        width: 100%;
        margin-left: 10px 0;
        background-color: rgba(0, 0, 0, 0.1);
    }

    .accordion-text li:hover .question-arrow .question,
    .accordion-text li:hover .question-arrow .arrow {
        color: #4b7c9a;
    }

    .accordion-text li p {
        width: 92%;
        font-size: 15px;
        color: #575757;
    }

    .faq-text p {
        display: none;
    }

    .arrow.rotate {
        transform: rotate(180deg);
    }

    /*--------QR code-----------*/
    .qr-code {
        text-align: center;
        margin: 30px 0;
    }

    .qr-code h3 {
        color: #4b7c9a;
        font-size: 20px;
        margin-bottom: 10px;
    }

    .qr-code p {
        color: #575757;
    }
</style>

<body>
    <div class="care-tips">
        <div class="tips-title">Tips for Taking Care of Your Furniture</div>
        <div class="tips-container">
            <div class="tip-card">
                <img src="/images/dusting.png" alt="Dusting" class="tip-image">
                <h3 class="tip">Regular Dusting</h3>
                <p class="tip-description">Dust your furniture regularly with a soft cloth to maintain its shine and
                    prevent buildup.</p>
            </div>
            <div class="tip-card">
                <img src="/images/noSunlight.png" alt="Avoid Direct Sunlight" class="tip-image">
                <h3 class="tip">Avoid Direct Sunlight</h3>
                <p class="tip-description">Keep your furniture out of direct sunlight to prevent fading and damage.</p>
            </div>
            <div class="tip-card">
                <img src="/images/coasters.png" alt="Use Coasters" class="tip-image">
                <h3 class="tip">Use Coasters</h3>
                <p class="tip-description">Always use coasters under drinks to avoid stains and damage to the surface.
                </p>
            </div>
            <div class="tip-card">
                <img src="/images/vacuum.png" alt="Vacuum Regularly" class="tip-image">
                <h3 class="tip">Vacuum Regularly</h3>
                <p class="tip-description">Use a vacuum with a brush attachment to remove dirt and debris from
                    upholstered furniture.</p>
            </div>
            <div class="tip-card">
                <img src="/images/gentleCleanser.png" alt="Use Gentle Cleaners" class="tip-image">
                <h3 class="tip">Use Gentle Cleaners</h3>
                <p class="tip-description">When cleaning, opt for mild soap and water or specialized cleaners to avoid
                    damage.</p>
            </div>
        </div>
    </div>

    <nav class="sidebar close">
        <header>
            <div class="image-text">
                <span class="user-img">
                    <?= html_file('user_image', 'image/*', 'hidden') ?>
                    <img src="<?= $user->image_url ?? '/images/profile-icon.png' ?>"
                        alt="<?= "$user->first_name $user->last_name" ?>">
                </span>

                <div class="text user-details">
                    <p class="name"><?= "$user->first_name $user->last_name" ?></p>
                    <p class="title"><?= "$user->role" ?></p>
                </div>
            </div>

            <i class='bx bx-chevron-right toggle'></i>
        </header>

        <div class="menu-bar">
            <div class="menu">
                <ul class="menu-links">
                    <li class="nav-link">
                        <a href="/user/profile">
                            <i class='bx bx-user icon'></i>
                            <span class="nav-text">Profile</span>
                        </a>
                    </li>

                    <?php if (!isAdmin()) : ?>
                        <li class="nav-link">
                            <a href="/user/voucher">
                                <i class='bx bx-gift icon'></i>
                                <span class="nav-text">Voucher</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-link">
                        <a href="/user/help">
                            <i class='bx bx-help-circle icon'></i>
                            <span class="nav-text">Help</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="/user/security">
                            <i class='bx bx-lock icon'></i>
                            <span class="nav-text">Security</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="bottom-content">
                <li class="mode">
                    <div class="moon-sun">
                        <i class='bx bx-moon icon'></i>
                        <i class='bx bx-sun icon'></i>
                    </div>
                    <span class="mode-text text">Theme Mode</span>

                    <div class="toggle-switch">
                        <span class="switch"></span>
                    </div>
                </li>
            </div>
        </div>
    </nav>

    <div class="accordion">
        <div class="image-box">
            <img src="/images/faq.png" alt="FAQ">
        </div>

        <div class="accordion-text">
            <div class="title">FAQ</div>
            <ul class="faq-text">
                <li>
                    <div class="question-arrow">
                        <span class="question">[Order tracking] How to check order status</span>
                        <i class='bx bx-chevron-down arrow'></i>
                    </div>
                    <p>You can track your orders by going to the <b>'order history'</b> section under the <b>"menu"</b>
                        tab. Your orders are organised based on their current status.</p>
                    <span class="line"></span>
                </li>

                <li>
                    <div class="question-arrow">
                        <span class="question">[Profile] Can I change my username ?</span>
                        <i class='bx bx-chevron-down arrow'></i>
                    </div>
                    <p>To change your username, go to the <b>profile icon</b> on the menu bar > Select the <i
                            class='bx bx-user icon'></i> > Enter new username > <b>Save Changes</b></p>
                    <span class="line"></span>
                </li>

                <li>
                    <div class="question-arrow">
                        <span class="question">[Delivery] How long does delivery take?</span>
                        <i class='bx bx-chevron-down arrow'></i>
                    </div>
                    <p>Delivery times vary based on your location and the type of furniture ordered.
                        Generally, <b>standard delivery</b>> takes <b>between 5 to 10 business days</b>.
                        If you need expedited shipping, please contact our customer service for more options.</p>
                    <span class="line"></span>
                </li>

                <li>
                    <div class="question-arrow">
                        <span class="question">[Returns] What is the return policy?</span>
                        <i class='bx bx-chevron-down arrow'></i>
                    </div>
                    <p>We offer a return policy on <b>all furniture items</b> if you're change of your mind,
                        you only can return it <b>before the items are packed</b> for a full refund.
                        Please contact our support team to initiate a return.</p>
                    <span class="line"></span>
                </li>

                <li>
                    <div class="question-arrow">
                        <span class="question">[Assembly] Do I need to assemble my furniture?</span>
                        <i class='bx bx-chevron-down arrow'></i>
                    </div>
                    <p>Some of our furniture items require assembly, while others come pre-assembled.
                        Detailed assembly instructions are included with items that require it. If you prefer,
                        we also offer an <b>assembly service</b> for an <b>additional fee</b> during delivered.</p>
                    <span class="line"></span>
                </li>
            </ul>
        </div>
    </div>

    <!-- QR Code Section -->
    <div class="qr-code">
        <h3>Contact Admin</h3>
        <p>Scan the QR code below to email the admin:</p>
        <img src="/images/qrcode.jpeg" alt="QR Code to Email Admin"
            style="max-width: 150px; margin: 20px auto; display: block;">
    </div>
</body>

<script>
    //dropdown
    $('.question-arrow').on('click', function () {
        $(this).next('p').slideToggle();
        $(this).find('.arrow').toggleClass('rotate');
    });

    // Dark mode toggle
    $('.toggle-switch').on('click', function () {
        $(this).toggleClass('active');
        $('body').toggleClass('dark-mode');

        if ($('body').hasClass('dark-mode')) {
            $('.moon-sun').html('<i class="bx bx-sun"></i>');
        } else {
            $('.moon-sun').html('<i class="bx bx-moon"></i>');
        }
    });

    // Sidebar toggle functionality
    $('.toggle').on('click', function () {
        $('.sidebar').toggleClass('close');

    });

    // Initialize the moon icon for light mode
    $('.moon-sun').html('<i class="bx bx-moon"></i>');
</script>