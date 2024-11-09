<!-- @author: Chong Jun Xiang -->
<?php
$_title         = "Verify Email";
$temp_user_data = session("temp_user_data");

if (is_get()) {
    if (empty($temp_user_data)) {
        temp("danger", "Invalid action");
        return redirect('/register');
    }
}

if (is_post()) {
    $otp_code = req("otp_code");

    if (empty($otp_code) || !is_numeric($otp_code) || strlen($otp_code) != 6) {
        $_err['otp_code'] = "Invalid OTP code";
    } else {
        $email = $temp_user_data['email'];
        $otp   = db_select_single("otp", "email", $email)->code;
        if (empty($otp) || $otp != $otp_code) {
            $_err['otp_code'] = "Invalid OTP code";
        } else {
            db_delete("otp", "email", $email);
            if (!is_unique($temp_user_data['email'], "users", "email")) {
                $payload = [
                    "is_deleted" => 0
                ];
                db_update("users", $payload, "email", $temp_user_data['email']);
            } else {
                db_insert("users", $temp_user_data);
            }
            session_unset();
            temp("success", "Account created successfully");
            return redirect('/login');
        }
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
</style>

<section>
    <div class="container">
        <form method="post" class="form">
            <div class="form-content">
                <h1 class="title"><?= $_title ?></h1><br>
                <div class="data-field">
                    <label for="otp_code" class="form-label">OTP Code: </label>
                    <?php html_text('otp_code', 'class="form-control" maxlength="6" required'); ?>
                    <?= err("otp_code") ?>
                </div>

                <div class="button-field">
                    <div class="btn">
                        <button class="submit-btn" data-confirm>Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>