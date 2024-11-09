<?php
$_db->query('DELETE FROM reset_password WHERE expired_at < NOW()');

$_title = "Forgot Password";

if (is_post()) {
    $email           = req("email");
    $turnstile_token = req('cf-turnstile-response');

    if (empty($email)) {
        $_err['email'] = "Email is required";
    } else if (!is_exists($email, "users", "email")) {
        $_err['email'] = "Email does not exist";
    }

    // ===================
    /**
     * Updated By: Chong Jun Xiang
     */
    if (empty($turnstile_token)) {
        temp('danger', 'Please complete the captcha');
        return redirect('/login');
    }

    $captcha_response = verify_captcha($turnstile_token);

    if (!$captcha_response) {
        temp('danger', 'Invalid captcha');
        return redirect('/login');
    }
    // ===================

    if (!$_err) {
        $token = generate_token();
        $url   = base("session/reset_password?token= $token");
        $body  = str_replace("{{ url }}", $url, RESET_PASSWORD_BODY);
        $stmt  = $_db->prepare('
            DELETE FROM reset_password WHERE email = ?;
            
            INSERT INTO reset_password (email, token, expired_at) 
            VALUES (?, ?, ADDTIME(NOW(), "00:05:00"));
        ');
        $stmt->execute([$email, $email, $token]);
        sendEmail($email, RESET_PASSWORD_SUBJECT, $body);
        temp("success", "Password reset link has been sent to your email");
        return redirect('/login');
    }
}
?>

<style>
    .container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 700px;
        min-width: 900px;
    }

    .title {
        text-align: center;
        margin: 20px;
    }

    .form {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 16px 0 rgba(0, 0, 0, 0.2), 0 12px 40px 0 rgba(0, 0, 0, 0.19);
        background-color: white;
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40%;
    }

    .form-content {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        width: 80%;
        margin: 20px;
        padding: 10px;
    }

    .data-field {
        margin-top: 5%;
        margin-bottom: 5%;
        width: 100%;
        align-items: center;
        justify-content: center;
    }

    .button-field {
        margin-top: 5%;
        margin-bottom: 5%;
        width: 100%;
        align-items: center;
        justify-content: center;
    }

    .form-label {
        margin-bottom: 5px;
    }

    .form-control {
        display: block;
        width: 100%;
        margin-top: 5px;
        padding: 5px;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    input:focus {
        border-color: #007bff;
        outline: none;
    }

    .error {
        color: rgb(220, 79, 79);
    }

    .button-field {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        margin-top: 20px;
    }

    .btn {
        flex: 1;
    }

    .submit-btn {
        width: 100%;
        padding: 0.5rem 1rem;
        font-size: 1rem;
        color: #fff;
        background-color: #007bff;
        border: 1px solid #007bff;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
    }

    .submit-btn:hover {
        background-color: #0056b3;
        border-color: #004085;
    }

    .reset-btn {
        width: 100%;
        padding: 0.5rem 1rem;
        font-size: 1rem;
        color: #fff;
        background-color: #6c757d;
        border: 1px solid #6c757d;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
    }

    .reset-btn:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }
</style>

<section>
    <div class="container">
        <form method="post" class="form">
            <div class="form-content">
                <h1 class="title"><?= $_title ?></h1><br>
                <div class="data-field">
                    <label for="text" class="form-label">Enter your email: </label>
                    <?php html_email('email', 'class="form-control" required') ?>
                    <?= err("email") ?>
                </div>

                <?= html_captcha() ?>

                <div class="button-field">
                    <div class="btn">
                        <button class="submit-btn">Send password reset email</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>