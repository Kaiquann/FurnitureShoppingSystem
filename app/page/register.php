<?php
if (is_post()) {
    $first_name      = req('first_name');
    $last_name       = req('last_name');
    $userPassword    = req('password');
    $confirmPassword = req('confirmPassword');
    $email           = req('email');
    $phone_number    = req('phone_number');
    $turnstile_token = req('cf-turnstile-response');

    if (empty($first_name)) {
        $_err["first_name"] = "Username cannot be empty";
    } else if (strlen($first_name) > 20) {
        $_err["first_name"] = "First Name cannot be more than 20 characters";
    } else if (!preg_match("/^[A-Za-z\s]+/", $first_name)) {
        $_err["first_name"] = "At least one big letter";
    }

    if (empty($last_name)) {
        $_err["last_name"] = "Last Name cannot be empty";
    } else if (strlen($last_name) > 20) {
        $_err["last_name"] = "Last Name cannot be more than 20 characters";
    }

    if (empty($userPassword)) {
        $_err["password"] = "Password cannot be blank!";
    } else if (strlen($userPassword) < 8) {
        $_err["password"] = "Password must at least 8 letters!";
    } else if ($userPassword != $confirmPassword) {
        $_err["password"] = "Confirm password not same !";
    }


    if (empty($confirmPassword)) {
        $_err["confirmPassword"] = "Confirm password cannot be blank!";
    } else if (strlen($confirmPassword) < 8) {
        $_err["confirmPassword"] = "Confirm password must at least 8 letters!";
    } else if ($userPassword != $confirmPassword) {
        $_err["confirmPassword"] = "Confirm password not same !";
    }

    if (empty($email)) {
        $_err["email"] = "Email cannot be blank!";
    } else if (!is_unique($email, "users", "email")) {
        $user_data = db_select_single("users", "email", $email);
        if ($user_data) {
            if ($user_data->is_deleted != 1) {
                if ($user_data->is_blocked == 1) {
                    $_err["email"] = "This account has been blocked";
                } else {
                    $_err["email"] = "This email cannot be use";
                }
            }
        }
    }

    if (empty($phone_number)) {
        $_err["phone_number"] = "Mobile phone cannot be empty";
    } else if (!preg_match("/^01[0-9]{1}-[0-9]{7,8}$/", $phone_number)) {
        $_err["phone_number"] = "Mobile phone must follow format: [01x-xxxxxxx]";
    }

    // ===================
    /**
     * Updated By: Chong Jun Xiang
     */
    if (empty($turnstile_token)) {
        temp('danger', 'Please complete the captcha');
        return redirect('/register');
    }

    $captcha_response = verify_captcha($turnstile_token);

    if (!$captcha_response) {
        temp('danger', 'Invalid captcha');
        return redirect('/register');
    }
    // ===================

    if (!$_err) {
        $otp  = generate_otp(6);
        $body = str_replace("{otpvalue}", $otp, OTP_BODY);
        sendEmail($email, OTP_SUBJECT, $body);
        if (is_exists($email, "otp", "email")) {
            db_update(
                "otp",
                ["code" => $otp],
                "email",
                $email,
            );
        } else {
            db_insert("otp", [
                "email" => $email,
                "code"  => $otp
            ]);
        }
        $temp_user_data = [
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'email'        => $email,
            'password'     => encrypt($userPassword),
            'phone_number' => $phone_number
        ];
        session('temp_user_data', $temp_user_data);
        return redirect('/session/register_verify_email');
    }
}
?>
<style>
    main {
        display: flex;
    }

    .section {
        margin: auto;
    }

    body {
        overflow: auto;
    }

    .container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 700px;
        min-width: 400px;
        margin: 20px;
    }

    .registerTitle {
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
        min-width: 400px;
        width: 40%;
    }

    .register-form {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        width: 80%;
        margin: 20px;
        padding: 10px;
    }

    .container-username {
        display: flex;
        justify-content: space-between;
        gap: 50px;
        width: 100%;
    }

    .username-dataField {
        width: 100%;
    }

    .dataField {
        margin-top: 5%;
        margin-bottom: 5%;
        width: 100%;
        align-items: center;
        justify-content: center;
    }

    .buttonField {
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

    .buttonField {
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

    .register-href {
        display: block;
        margin-top: 5px;
        text-decoration: none;
        color: #3498db;
        background: none;
        text-align: center;
    }

    .register-href:hover {
        background: none;
        color: #0099ff;
        background: none;
    }

    .password-wrapper {
        position: relative;
        width: 100%;
    }

    .toggle-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #666;
    }
</style>

<body>
    <section class="section">
        <div class="container">
            <form method="post" class="form">
                <div class="register-form">
                    <h1 class="registerTitle">Register</h1><br>
                    <div class="container-username">
                        <div class="username-dataField">
                            <label for="first_name" class="form-label">First Name:</label>
                            <input type="text" class="form-control" name="first_name" value="<?= $first_name ?? '' ?>"
                                required>
                            <?= err("first_name") ?>
                        </div>

                        <div class="username-dataField">
                            <label for="last_name" class="form-label">Last Name:</label>
                            <input type="text" class="form-control" name="last_name" value="<?= $last_name ?? '' ?>"
                                required>
                            <?= err("last_name") ?>
                        </div>
                    </div>

                    <div class="dataField">
                        <label for="password" class="form-label">Password :</label>
                        <div class="password-wrapper">
                            <?php html_password("password", "class='form-control' required") ?>
                            <span id="togglePassword" class="toggle-password">
                                <i class="fa fa-eye"></i>
                        </div>
                        <?= err("password") ?>
                    </div>

                    <div class="dataField">
                        <label for="confirmpasword" class="form-label">Confirm password :</label>
                        <div class="password-wrapper">
                            <?php html_password("confirmPassword", "class='form-control' required") ?>
                            <span id="confirmTogglePassword" class="toggle-password">
                                <i class="fa fa-eye"></i>
                        </div>
                        <?= err("confirmpassword") ?>
                    </div>

                    <div class="dataField">
                        <label for="email" class="form-label">Email :</label>
                        <input type="email" class="form-control" name="email" value="<?= $email ?? '' ?>" required>
                        <?= err("email") ?>
                    </div>

                    <div class="dataField">
                        <label for="phone_number" class="form-label">Phone number :</label>
                        <input type="tel" class="form-control" name="phone_number" placeholder='e.g. 999-9999999'
                            value="<?= $phone_number ?? '' ?>" required>
                        <?= err("phone_number") ?>
                    </div>

                    <?= html_captcha() ?>

                    <div class="buttonField">
                        <div class="btn">
                            <button type="submit" class="submit-btn"
                                data-confirm="Are you confirm your personal details ?">Register</button>
                        </div>
                        <div class="btn">
                            <input type="reset" class="reset-btn" value="Reset form">
                        </div>
                    </div>

                    <div class="alternativeLink">
                        <a href="/login" class="register-href">Already have account? Login Now!</a>
                    </div>
                </div>
            </form>
        </div>
    </section>
</body>
<script>
    $(document).ready(function() {
        $('#togglePassword').click(function() {
            let passwordField = $('#password');
            let type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });

        $('#confirmTogglePassword').click(function() {
            let confirmPasswordField = $('#confirmPassword');
            let type = confirmPasswordField.attr('type') === 'password' ? 'text' : 'password';
            confirmPasswordField.attr('type', type);
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });
    });
</script>