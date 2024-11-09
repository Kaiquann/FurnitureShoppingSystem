<?php
$_db->query('DELETE FROM login_attempt WHERE expired_at < NOW()');

if (is_post()) {
    $email           = req('email');
    $password        = req('password');
    $turnstile_token = req('cf-turnstile-response');
    $remember_me     = req('remember_me');

    if (empty($email)) {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }

    if (empty($password)) {
        $_err['password'] = 'Required';
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
        $password = encrypt($password);
        $user     = db_select_single('users', 'email', $email);

        if ($user) {
            if ($user->is_deleted) {
                temp('danger', value: 'The account details are incorrect');
                return redirect('/login');
            }

            if ($user->is_blocked) {
                temp('danger', 'Your account has been blocked');
                return redirect('/login');
            }

            $login_attempt = db_select_single('login_attempt', 'email', $email);

            if ($user->password != $password) {
                if ($login_attempt && $login_attempt->try_attempt >= 3) {
                    $expired_at = $login_attempt->expired_at;
                    if ($expired_at > getDateTime()) {
                        temp('danger', 'Please wait 15 minutes before trying again');
                        return redirect('/login');
                    }
                }
            }

            if ($user->password != $password) {
                if (!$login_attempt) {
                    $stmt = $_db->prepare('
                        INSERT INTO login_attempt (email, expired_at)
                        VALUES (?, ADDTIME(NOW(), "00:15:00"))
                    ');
                    $stmt->execute([$email]);
                } else {
                    $try_attempt = $login_attempt->try_attempt;
                    $expired_at  = $login_attempt->expired_at;
                    if ($try_attempt >= 3) {
                        temp('danger', 'Please wait 15 minutes before trying again');
                        return redirect('/login');
                    } else {
                        $try_attempt++;
                        $payload = [
                            'try_attempt' => $try_attempt,
                            'expired_at'  => $expired_at
                        ];
                        db_update('login_attempt', $payload, 'email', $email);
                    }
                    // ===================
                    /**
                     * Updated By: Chong Jun Xiang
                     */
                    if ($try_attempt >= 3) {
                        $subject = ILLEGAL_LOGIN_SUBJECT;
                        $url     = base('forgot_password');
                        $body    = str_replace(
                            ['{{ email }}', '{{ ip }}', '{{ link }}'],
                            [$email, $_CLIENT_IP, $url],
                            ILLEGAL_LOGIN_BODY
                        );
                        sendEmail($email, $subject, $body);
                        temp('danger', 'Please wait 15 minutes before trying again');
                        return redirect('/login');
                    }
                    // ===================
                }
                temp('danger', 'The account details are incorrect');
                return redirect('/login');
            } else {
                if ($login_attempt && $login_attempt->try_attempt >= 3) {
                    temp('danger', 'Please wait 15 minutes before trying again');
                    return redirect('/login');
                }
                if (!empty($remember_me)) {
                    $token               = remember_me();
                    $remember_me_payload = [
                        'token'   => $token,
                        'user_id' => $user->id,
                    ];
                    db_insert('remember_me', $remember_me_payload);
                }
                db_delete('login_attempt', 'email', $email);
                session(USER_SESSION, $user);
                temp('success', 'Login Successful');
                return redirect('/');
            }
        } else {
            temp('danger', 'The account details are incorrect');
            return redirect('/login');
        }
    }
}

?>

<head>
    <style>
        main {
            height: 100vh;
            display: flex;
        }

        .section {
            margin: auto;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 700px;
            min-width: 400px;
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
            min-width: 400px;
        }

        .login-form {
            padding: 20px;
            width: 80%;
        }

        .login-form h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin: 5%;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input:focus {
            border-color: #007bff;
            outline: none;
        }

        .form-group .error {
            color: red;
            font-size: 14px;
        }

        .btn {
            width: 50%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 20px;
        }

        .btn-group {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .login-href {
            display: block;
            margin-top: 5px;
            text-decoration: none;
            color: #3498db;
            background: none;
            text-align: center;
        }

        .login-href:hover {
            background: none;
            color: #0099ff;
            background: none;
        }

        .password-wrapper {
            position: relative;
            width: 100%;
            max-width: 400px;
        }

        .password-wrapper input {
            width: 100%;
            padding-right: 40px;
            box-sizing: border-box;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }

        .toggle-password i {
            font-size: 16px;
        }
    </style>
</head>

<body>
    <section class="section">
        <div class="container">
            <form method="post" class="form" id="loginForm">
                <div class="login-form">
                    <h2>Login</h2>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <?php html_email("email", "value='" . ($email ?? '') . "' required"); ?>
                        <?= err("email") ?>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <div class="password-wrapper">
                            <?php html_password("password", "value='' required"); ?>
                            <span id="togglePassword" class="toggle-password">
                                <i class="fa fa-eye"></i>
                            </span>
                        </div>
                        <?= err("password") ?>
                    </div>
                    <?= html_checkbox('remember_me', 1, 'Remember Me') ?>
                    <div class="btn-group">
                        <button type="submit" class="btn">Login</button>
                    </div>

                    <?= html_captcha() ?>
                    <div class="alternativeLink">
                        <a href="/forgot_password" class="login-href">Forgot Password?</a>
                        <a href="/register" class="login-href">No Account? Register Now!</a>
                    </div>
                </div>
            </form>
        </div>
    </section>
</body>
<script>
      $(document).ready(function () {
        $('#togglePassword').click(function () {
            let passwordField = $('#password');
            let type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);

            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });

    });
</script>