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

extract((array) $user);

$name_regex         = '/^[a-zA-Z ]+$/';
$phone_number_regex = "/^01[0-9]{1}-[0-9]{7,8}$/";

if (is_post()) {
    $first_name   = req('first_name');
    $last_name    = req('last_name');
    $email        = req('email');
    $phone_number = req('phone_number');
    $user_image   = req_file('user_image');

    if (empty($first_name)) {
        $_err["first_name"] = "Required";
    } else if (strlen($first_name) > 50) {
        $_err["first_name"] = "Maximum length is 50 characters";
    } else if (!isValid($first_name, $name_regex)) {
        $_err["first_name"] = "Only letters and spaces are allowed";
    }

    if (empty($last_name)) {
        $_err["last_name"] = "Required";
    } else if (strlen($last_name) > 50) {
        $_err["last_name"] = "Maximum length is 50 characters";
    } else if (!isValid($last_name, $name_regex)) {
        $_err["last_name"] = "Only letters and spaces are allowed";
    }

    if (empty($email)) {
        $_err["email"] = "Required";
    } else if (!is_email($email)) {
        $_err["email"] = "Invalid format";
    } else if (is_exists($email, "users", "email") && $email != $user->email) {
        $_err["email"] = "Already exists";
    }

    if (!empty($phone_number)) {
        if (!isValid($phone_number, $phone_number_regex)) {
            $_err['phone_number'] = "Format: [01x-xxxxxxx]";
        }
    }

    if ($user_image) {
        $image_result = check_image($user_image);
        if (!empty($image_result)) {
            $_err['user_image'] = $image_result;
        }
    }

    if (!$_err) {
        if ($user_image) {
            file_delete($user->image_url);
            $image_url = save_photo($user_image, '/images/user');
        } else {
            $image_url = $user->image_url;
        }
        db_update("users", [
            "first_name"   => $first_name,
            "last_name"    => $last_name,
            "email"        => $email,
            "phone_number" => $phone_number,
            "image_url"    => $image_url
        ], "id", $id);
        temp('success', 'User updated successfully');
        return redirect("/user/profile");
    }
}
?>

<style>
    body {
        min-height: 100vh;
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

    /*user profile*/
    .update-profile-container {
        flex: 1;
        padding: 24px;
        background-color: #fff;
        border-radius: 30px;
        margin: 10px;
        margin-left: 100px;
        max-width: 800px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        overflow: auto;
        transition: all 0.3s ease;
    }

    .sidebar:not(.close)~.update-profile-container {
        margin-left: 260px;
    }

    .update-profile-container .update-profile-header h1 {
        font-size: 24px;
        font-weight: 600;
        color: #333;
    }

    .form-group {
        margin: 20px;
    }

    .update-profile-container .form-group {
        margin-bottom: 16px;
    }

    .update-profile-container .form-group label {
        display: block;
        font-size: 14px;
        color: #757575;
        margin-bottom: 8px;
    }

    .update-profile-container .form-group input,
    .update-profile-container .form-group textarea {
        width: 100%;
        padding: 10px;
        font-size: 14px;
        border: 1px solid #ddd;
        border-radius: 8px;
    }

    .update-profile-container .form-group img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 12px;
    }

    .update-profile-container .submit-btn {
        display: inline-block;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 500;
        color: #fff;
        background-color: #28a745;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
    }

    .update-profile-container .submit-btn:hover {
        background-color: #218838;
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

    <div class="update-profile-container">
        <div class="update-profile-header">
            <h1>Update Profile</h1>
        </div>

        <form method="post" enctype="multipart/form-data">
            <label for="user_image">User Image:</label>
            <label class="upload" tabindex="0">
                <?= html_file('user_image', 'image/*', 'hidden') ?>
                <img src="<?= $user->image_url ?? '/images/profile-icon.png' ?>"
                    alt="<?= "$user->first_name $user->last_name" ?>" class="img-25">
                <span></span>
            </label>
            <?= err('user_image') ?>

            <div class="form-group">
                <label for="first_name">First Name: </label>
                <?= html_text("first_name", "maxlength=50"); ?>
                <?= err('first_name') ?>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name: </label>
                <?= html_text("last_name", "maxlength=50"); ?>
                <?= err('last_name') ?>
            </div>

            <div class="form-group">
                <label for="email">Email: </label>
                <?= html_text("email", "maxlength=50"); ?>
                <?= err('email') ?>
            </div>
            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <?= html_text("phone_number", "maxlength=20 placeholder='e.g. xxx-xxxxxxx'"); ?>
                <?= err('phone_number') ?>
            </div>
            <section>
                <button type="submit" class="submit-btn" data-confirm>Save Changes</button>
                <button type="reset">Reset</button>
            </section>
        </form>
    </div>

</body>

<script>
    // Dark mode toggle
    $('.toggle-switch').on('click', function() {
        $(this).toggleClass('active');
        $('body').toggleClass('dark-mode');

        if ($('body').hasClass('dark-mode')) {
            $('.moon-sun').html('<i class="bx bx-sun"></i>');
        } else {
            $('.moon-sun').html('<i class="bx bx-moon"></i>');
        }
    });

    // Sidebar toggle functionality
    $('.toggle').on('click', function() {
        $('.sidebar').toggleClass('close');
    });

    // Initialize the moon icon for light mode
    $('.moon-sun').html('<i class="bx bx-moon"></i>');
</script>