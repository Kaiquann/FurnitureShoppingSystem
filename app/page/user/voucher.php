<?php
global $_USER_DATA;

$_title = "Voucher";

if ($_USER_DATA == null) {
    temp('danger', 'Please login first');
    redirect('/login');
}

$id = $_USER_DATA->id;

$stmt = $_db->prepare('
    SELECT * FROM users 
    WHERE id = ?
');

$stmt->execute([$id]);
$user = $stmt->fetch();

$search = req('search');
$sort   = req('sort');
$dir    = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

$page = req('page', 1);

$voucher = $_db->query('SELECT * FROM voucher WHERE is_active = 1 AND is_deleted = 0');

$user_id = $_USER_DATA->id;
?>

<style>
    body {
        font-family: Arial, sans-serif;
        height: 100vh;
        background-color: #c0d3da;
        margin: 0;
        transition: all 0.3s ease;
    }

    /* sidebar */
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

    .container {
        transition: margin-left 0.3s ease;
        margin-left: 230px;
    }

    .sidebar.close~.container {
        margin-left: 80px;
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

    .dark-mode h1 {
        color: white;
    }

    .dark-mode .voucher {
        color: #000;
    }


    /* voucher */
    h1 {
        text-align: center;
        color: #333;
    }

    .voucher {
        background: white;
        border: 1px solid gray;
        border-radius: 20px;
        padding: 20px;
        margin: 20px 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .voucher img {
        max-width: 500px;
        margin-bottom: 10px;
    }

    .voucher p {
        margin: 20px 0;
    }

    .no-voucher {
        text-align: center;
        font-size: 18px;
        color: red;
        /* You can change the color as needed */
    }
</style>

<body>
    <h1><?= htmlspecialchars($_title) ?></h1>

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
                    <p class="title"><?= $user->role ?></p>
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

                    <li class="nav-link">
                        <a href="/user/voucher">
                            <i class='bx bx-gift icon'></i>
                            <span class="nav-text">Voucher</span>
                        </a>
                    </li>

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

    <div class="container">
        <?php if (empty($voucher)) : ?>
            <div class="voucher no-voucher">
                <p><b>No Voucher Now</b></p>
            </div>
        <?php else : ?>
            <?php foreach ($voucher as $v) : ?>
                <?php
                $voucher_usage_counts = $_db->query("SELECT COUNT(*) AS count FROM voucher_usage WHERE user_id = $user_id AND voucher_id = $v->id")->fetchObject();

                $usage_limit_per_coupon = $v->usage_limit_per_coupon;
                $usage_limit_per_user   = $v->usage_limit_per_user;
                $usage_counts           = $voucher_usage_counts->count;
                $remain_coupon_usage    = $usage_limit_per_coupon == 0 ? 1 : $usage_limit_per_coupon - $_db->query("SELECT COUNT(*) AS count FROM voucher_usage WHERE voucher_id = $v->id")->fetchObject()->count;
                $remain_usage           = $usage_limit_per_user == 0 ? 1 : $usage_limit_per_user - $usage_counts;
                if (($remain_coupon_usage > 0) && ($remain_usage > 0)) : ?>
                    <div class="voucher">
                        <?php if ($v->discount_type === "percentage") : ?>
                            <img src="../../images/vouper.png" alt="Percentage Voucher">
                        <?php else : ?>
                            <img src="../../images/voufixed.png" alt="Fixed Voucher">
                        <?php endif ?>

                        <p><strong>Voucher Code:</strong> <?= htmlspecialchars($v->code ?? '') ?></p>
                        <p><strong>Amount Discount:</strong>
                            <?php if ($v->discount_type === "percentage") : ?>
                                <?= htmlspecialchars($v->amount ?? '') ?> %
                            <?php else : ?>
                                RM <?= htmlspecialchars($v->amount ?? '') ?>
                            <?php endif ?>
                        </p>
                        <p><strong>Description:</strong> <?= htmlspecialchars($v->description ?? '') ?></p>
                        <p><strong>Minimum Spend:</strong> <?= htmlspecialchars($v->min_spend == 0 ? 'No Limit' : "RM $v->min_spend") ?></p>
                        <p><strong>Maximum Spend:</strong> <?= htmlspecialchars($v->max_spend == 0 ? 'No Limit' : "RM $v->max_spend") ?></p>

                        <?php if ($v->usage_limit_per_coupon === 0) : ?>
                            <p><strong>Voucher Left:</strong> Unlimited </p>
                        <?php else : ?>
                            <p><strong>Voucher Left:</strong>
                                <?= htmlspecialchars($v->usage_limit_per_coupon == 0 ? 'Unlimited' : $usage_limit_per_coupon - $usage_counts) ?>
                            </p>
                        <?php endif ?>
                        <p><strong>You Can Apply:</strong> <?= $usage_limit_per_user == 0 ? 'Unlimited' : $usage_limit_per_user - $usage_counts ?></p>
                        <p><strong>Expired Date:</strong> <?= htmlspecialchars($v->expired_at ?? '') ?></p>
                        <button class="success" onclick="copyCode('<?= htmlspecialchars($v->code) ?>')">Copy Voucher Code</button>
                    </div>
                <?php endif; ?>
            <?php endforeach ?>
        <?php endif ?>
    </div>

    <script>
        function copyCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                alert('Voucher code copied: ' + code);
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }

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
</body>