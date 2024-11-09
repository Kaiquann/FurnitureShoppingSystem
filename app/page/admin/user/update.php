<?php
$_title = "Update User";

$id   = req('id');
$stmt = $_db->prepare(
    "SELECT * FROM users 
    WHERE id = ?
    AND role != 'superadmin'
    AND is_deleted IN (0, 1)"
);
$stmt->execute([$id]);
$user = $stmt->fetch();

if (empty($user)) {
    temp('danger', 'User not found');
    return redirect('/admin/user');
}

global $_USER_DATA;

if ($_USER_DATA->id === $user->id) {
    temp('danger', 'You cannot update your own profile');
    return redirect('/admin/user');
}

extract((array) $user);

$forward = req('forward');
if (empty($forward)) {
    $forward = "/admin/user/view?id=$id";
}

$name_regex         = '/^[a-zA-Z ]+$/';
$phone_number_regex = "/^01[0-9]{1}-[0-9]{7,8}$/";

if (is_post()) {
    $id           = req('id');
    $first_name   = req('first_name');
    $last_name    = req('last_name');
    $email        = req('email');
    $phone_number = req('phone_number');
    $role         = req('role');
    $user_image   = req_file('user_image');

    if ($_USER_DATA->role == $user->role && !in_array($_USER_DATA->role, roles_can_access())) {
        temp('danger', 'Not Enough Permissions / Invalid Role');
        return redirect("/admin/user/update?id=$id");
    }

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

    if (empty($role)) {
        $_err["role"] = "Required";
    } else if ($_USER_DATA->role == $user->role && !in_array($_USER_DATA->role, roles_can_access())) {
        $_err['role'] = 'Not Enough Permissions / Invalid Role';
    } else if (!in_array($role, roles_can_access())) {
        $_err['role'] = 'Not Enough Permissions / Invalid Role';
    }

    if ($user_image) {
        $image_result = check_image($user_image);
        if (!empty($image_result)) {
            $_err['user_image'] = $image_result;
        }
    }

    if (!$_err) {
        if ($_USER_DATA->id == $id) {
            temp('danger', 'You cannot update your own account');
            return redirect("/admin/user/view?id=$id");
        }
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
            "role"         => $role,
            "image_url"    => $image_url
        ], "id", $id);
        temp('success', 'User updated successfully');
        return redirect("/admin/user/view?id=$id");
    }
}
?>

<h1><?= $_title ?> : <?= html_print($email) ?></h1>

<form method="post" class="form" enctype="multipart/form-data">
    <label for="user_image">Profile Image:</label>
    <label class="upload" tabindex="0">
        <?= html_file('user_image', 'image/*', 'hidden') ?>
        <img src="<?= $image_url ?: '/images/profile-icon.png' ?>" alt="<?= $email ?>" class="img-25">
        <span></span>
    </label>
    <?= err('user_image') ?>

    <label for="id">Id</label>
    <b><?= $id ?></b>
    <?= err('id') ?>

    <label for="first_name">First Name: </label>
    <?= html_text("first_name", "maxlength=50"); ?>
    <?= err('first_name') ?>

    <label for="last_name">Last Name: </label>
    <?= html_text("last_name", "maxlength=50"); ?>
    <?= err('last_name') ?>

    <label for="email">Email: </label>
    <?= html_text("email", "maxlength=50"); ?>
    <?= err('email') ?>

    <label for="phone_number">Phone Number</label>
    <?= html_text("phone_number", "maxlength=20 placeholder='e.g. xxx-xxxxxxx'"); ?>
    <?= err('phone_number') ?>

    <label for="is_blocked">Block Status</label>
    <span><?= $is_blocked ? 'Blocked' : 'Non-Blocked' ?></span>
    <span></span>

    <label for="is_deleted">Delete Status</label>
    <span><?= $is_deleted ? 'Deleted' : 'Active' ?></span>
    <span></span>

    <label for="role">Role</label>
    <?= html_select("role", $_ROLES, $role); ?>
    <?= err('role') ?>

    <section>
        <button type="submit" class="primary" data-confirm>Update</button>
        <?php if ($is_deleted) : ?>
            <button data-post="/admin/user/recover?id=<?= $id ?>" data-confirm="Are you sure to recover <?= $email ?>?"
                class="warning">Recover</button>
            <button data-post="/admin/user/permanent_delete?id=<?= $id ?>"
                data-confirm="Are you sure to permanently delete <?= html_print($email) ?>?" class="danger">Delete</button>
        <?php endif; ?>
        <?php if (!$is_deleted) : ?>
            <?php if ($is_blocked) : ?>
                <button data-post="/admin/user/unblock?id=<?= $id ?>" data-confirm="Are you sure to unblock <?= $email ?>?"
                    class="warning">Unblock</button>
            <?php else : ?>
                <button data-post="/admin/user/block?id=<?= $id ?>" data-confirm="Are you sure to block <?= $email ?>?"
                    class="warning">Block</button>
            <?php endif; ?>
            <button data-post="/admin/user/delete?id=<?= $id ?>" data-confirm="Are you sure to delete <?= $email ?>?"
                class="danger">Delete</button>
        <?php endif; ?>
        <button type="reset">Reset</button>
        <button type="button" data-get="<?= $forward ?>" class="success">Go Back</button>
    </section>
</form>