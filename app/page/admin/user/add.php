<?php
$_title = "Add User";

$name_regex = '/^[a-zA-Z ]+$/';

if (is_post()) {
    $first_name = req('first_name');
    $last_name  = req('last_name');
    $email      = req('email');
    $role       = req('role');

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
    } else if (strlen($email) > 50) {
        $_err["email"] = "Email cannot be more than 50 characters";
    } else if (!is_email($email)) {
        $_err["email"] = "Invalid format";
    } else if (is_exists($email, 'users', 'email')) {
        $user_db = db_select_single('users', 'email', $email);
        if ($user_db) {
            if (!$user_db->is_deleted) {
                $_err["email"] = "Already exists";
            }
        }
    }

    if (empty($role)) {
        $_err["role"] = "Required";
    } else if (!in_array($role, roles_can_access())) {
        $_err['role'] = 'Not Enough Permissions / Invalid Role';
    }

    if (!$_err) {
        $password     = generate_password(16);
        $user_payload = [
            "first_name" => $first_name,
            "last_name"  => $last_name,
            "email"      => $email,
            "role"       => $role,
            "password"   => encrypt($password)
        ];
        if (is_exists($email, 'users', 'email')) {
            $user_payload['is_deleted'] = 0;
            db_update('users', $user_payload, 'email', $email);
        } else {
            db_insert('users', $user_payload);
        }
        $subject = "Account Created";
        $body    = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>Account Created</title><style>body{font-family:Arial,sans-serif}.container{width:100%;max-width:600px;margin:0 auto}.card{border:1px solid #ddd;border-radius:5px;margin-top:50px;padding:20px;text-align:center}.card-title{font-size:24px;margin-bottom:20px}.card-text{font-size:18px;margin-bottom:20px}.btn{display:inline-block;color:#fff;background-color:#0d6efd;border-color:#0d6efd;padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:.25rem;text-decoration:none}.btn:hover{color:#fff;background-color:#0b5ed7;border-color:#0a58ca}</style></head><body><div class='container'><div class='card'><h5 class='card-title'>Account Created</h5><p class='card-text'>Your account has been created by the admin.</p><p class='card-text'>First name: <b>{{ first_name }}</b></p><p class='card-text'>Last name: <b>{{ last_name }}</b></p><p class='card-text'>Email: <b>{{ email }}</b></p><p class='card-text'>System generated password is: <b>{{ password }}</b></p><a href='{{ url }}' class='btn'>Login</a></div></div></body></html>";
        $body    = str_replace(["{{ first_name }}", "{{ last_name }}", "{{ email }}", "{{ password }}", "{{ url }}"], [$first_name, $last_name, $email, $password, base("login")], $body);
        sendEmail($email, $subject, $body);
        temp("success", "User $email added successfully");
        return redirect("/admin/user");
    }
}
?>

<h1><?= $_title ?></h1>

<form method="post" class="form">
    <label for="first_name">First Name: </label>
    <?= html_text("first_name", "maxlength=50"); ?>
    <?= err('first_name') ?>

    <label for="last_name">Last Name: </label>
    <?= html_text("last_name", "maxlength=50"); ?>
    <?= err('last_name') ?>

    <label for="email">Email: </label>
    <?= html_text("email", "maxlength=50"); ?>
    <?= err('email') ?>

    <label for="role">Role</label>
    <?= html_select("role", roles_can_access(), 'member'); ?>
    <?= err('role') ?>

    <section>
        <span>The password will auto-generated and sent to the user's email.</span>
    </section>

    <section>
        <button type="submit" class="primary" data-confirm>Add</button>
        <button type="reset" class="danger">Reset</button>
        <button type="button" data-get="/admin/user" class="success">Go Back</button>
    </section>
</form>