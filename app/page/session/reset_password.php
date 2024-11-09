<!-- @author: Chong Jun Xiang -->
<?php
$_db->query('DELETE FROM reset_password WHERE expired_at < NOW()');

$_title = "Reset Password";

if (is_get()) {
    $token = req("token");

    if (empty($token)) {
        temp("danger", "Invalid token");
        return redirect('/login');
    } else {
        if (!is_exists($token, "reset_password", "token")) {
            temp("danger", "Invalid token");
            return redirect('/login');
        }
        $reset_password_data = db_select_single("reset_password", "token", $token);
        if (empty($reset_password_data)) {
            temp("danger", "Invalid token");
            return redirect('/login');
        }
        $email = $reset_password_data->email;
        session("email", $email);
    }
}

if (is_post()) {
    $email              = session("email");
    $newPassword        = req("newPassword");
    $confirmNewPassword = req("confirmNewPassword");

    if (empty($email)) {
        temp("danger", "Invalid action");
        return redirect('/login');
    }

    if (empty($newPassword)) {
        $_err["newPassword"] = "Required";
    } else if (strlen($newPassword) < 8) {
        $_err["newPassword"] = "Minimum 8 characters";
    }

    if (empty($confirmNewPassword)) {
        $_err["confirmNewPassword"] = "Required";
    } else if ($newPassword != $confirmNewPassword) {
        $_err["confirmNewPassword"] = "Not match";
    }

    if ($newPassword !== $confirmNewPassword) {
        $_err["newPassword"]        = "Not match";
        $_err["confirmNewPassword"] = "Not match";
    }

    if (!$_err) {
        $user_data = [
            "password" => encrypt($newPassword)
        ];
        db_update("users", $user_data, "email", $email);
        db_delete('login_attempt', 'email', $email);
        db_delete("reset_password", "email", $email);
        $subject = "Password Changed";
        $body    = generateBodyWithTitleMessage(
            $subject,
            "Your password has been changed successfully on date " . getDateTime()
        );
        sendEmail($email, $subject, $body);
        session_unset();
        temp("success", "Password changed successfully");
        return redirect('/login');
    }
}

?>

<style>
    .section {
        width: 60%;
        margin: auto;
    }

    .title {
        text-align: center;
        margin: 20px;
    }

    .content-container {
        display: block;
        width: 100%;
        text-align: center;
    }

    .form {
        display: block;
        box-shadow: 0 10px 16px 0 rgba(0, 0, 0, 0.2), 0 12px 40px 0 rgba(0, 0, 0, 0.19);
        background-color: white;
        border-radius: 20px;
        padding: 30px;
    }

    .form-group {
        margin: 20px;
    }

    .content {
        margin: 10px;
    }
</style>

<section class="section">
    <h1 class="title">RESET PASSWORD</h1>
    <form method="post" class="form">
        <div class="content-container">
            <div class="form-group">
                <div class="content">
                    <label for="email">Email:</label>
                    <b><?= $email ?? '' ?></b><br>
                    <?= err('email') ?>
                </div>

                <div class="content">
                    <label for="newPassword">New Password: </label>
                    <?php html_password("newPassword", "required"); ?>
                    <?= err('newPassword') ?>
                </div><br>

                <div class="content">
                    <label for="confirmNewPassword">Confirm New Password: </label>
                    <?php html_password("confirmNewPassword", "required"); ?>
                    <?= err('confirmNewPassword') ?>
                </div>
            </div>

            <div class="button">
                <button class="primary" data-confirm>Reset Password</button>
            </div>
        </div>
    </form>
</section>