<?php
global $_USER_DATA;

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

if (is_post()) {
    $current_password = req('current_password');
    $new_password     = req('new_password');
    $confirm_password = req('confirm_password');

    $user_id = session(USER_SESSION)->id;

    if (empty($current_password)) {
        $_err['current_password'] = 'Current password is required';
    } else if (strlen($current_password) < 8) {
        $_err["current_password"] = "Current password must at least 8 letters!";
    } else if ($_USER_DATA->password != encrypt($current_password)) {
        $_err['current_password'] = 'Your current password is incorrect';
    }

    if (empty($new_password)) {
        $_err['new_password'] = 'New password is required';
    } else if (strlen($new_password) < 8) {
        $_err["new_password"] = "New password must at least 8 letters!";
    }

    if (empty($confirm_password)) {
        $_err['confirm_password'] = 'Please confirm your new password';
    } else if (strlen($confirm_password) < 8) {
        $_err["confirm_password"] = "Confirm password must at least 8 letters!";
    }

    if ($new_password !== $confirm_password) {
        $_err['confirm_password'] = 'Passwords do not match';
    }

    if (!$_err) {
        $user_payload = [
            'password' => encrypt($new_password)
        ];
        db_update('users', $user_payload, 'id', $user_id);
        $body = str_replace(
            ['{{ date }}', '{{ email }}', '{{ ip }}', '{{ link }}'],
            [getDateTime(), $_USER_DATA->email, $_CLIENT_IP, base('forgot_password')],
            PASSWORD_CHANGED_BODY
        );
        sendEmail($_USER_DATA->email, PASSWORD_CHANGED_SUBJECT, $body);
        temp('success', 'Password updated successfully');
        return redirect('/user/profile');
    }
}
?>

<style>
    body {
        height: 100vh;
        background-color: #c0d3da;
        margin: 0;
        transition: all 0.3s ease;
    }

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

    /*Change Password*/
    .update-password-container {
        margin-left: 250px;
        padding: 20px;
    }

    .form-group {
        margin: 15px 0;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
    }

    .form-group input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .form-group button {
        padding: 10px 15px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .form-group button:hover {
        background-color: #0056b3;
    }
</style>

<body>
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

    <div class="update-password-container">
        <h1>Change Password</h1>
        <?php if (isset($error)) : ?>
            <div style="color: red;"><?= $error ?></div>
        <?php elseif (isset($success)) : ?>
            <div style="color: green;"><?= $success ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="current_password">Current Password:</label>
                <?php html_password("current_password", "value='' required"); ?>
                <?= err("current_password") ?>
            </div>

            <div class="form-group">
                <label for="new_password">New Password:</label>
                <?php html_password("new_password", "value='' required"); ?>
                <?= err("new_password") ?>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <?php html_password("confirm_password", "value='' required"); ?>
                <?= err("confirm_password") ?>
            </div>

            <div class="form-group">
                <button type="submit" data-confirm="Are you sure to change the password?">Change Password</button>
            </div>
        </form>
    </div>
</body>

<script>
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