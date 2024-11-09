<?php
$_title = "View User";

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
    temp('danger', 'You cannot view your own profile');
    return redirect('/admin/user');
}

extract((array) $user);

$forward = req('forward');
if (empty($forward)) {
    $forward = '/admin/user';
}
?>

<div class="admin-container">
    <h1><?= $_title ?> : <?= html_print($email) ?></h1>
    <p class="admin-image">
        <?= html_image($id, $image_url, "alt='$email' class='img-20'") ?>
    </p>

    <section class="admin-content">
        <table class="admin-table">
            <tbody>
                <tr>
                    <td><strong>Id</strong></td>
                    <td><?= $id ?></td>
                </tr>
                <tr>
                    <td><strong>First Name</strong></td>
                    <td><?= html_print($first_name) ?></td>
                </tr>
                <tr>
                    <td><strong>Last Name</strong></td>
                    <td><?= html_print($last_name) ?></td>
                </tr>
                <tr>
                    <td><strong>Email</strong></td>
                    <td><?= html_print($email) ?></td>
                </tr>
                <tr>
                    <td><strong>Phone Number</strong></td>
                    <td><?= html_print($phone_number) ?></td>
                </tr>
                <tr>
                    <td><strong>Block Status</strong></td>
                    <td><?= $is_blocked ? 'Blocked' : 'Non-Blocked' ?></td>
                </tr>
                <tr>
                    <td><strong>Delete Status</strong></td>
                    <td><?= $is_deleted ? 'Deleted' : 'Active' ?></td>
                </tr>
                <tr>
                    <td><strong>Role</strong></td>
                    <td><?= html_print($role) ?></td>
                </tr>
                <tr>
                    <td><strong>Created At</strong></td>
                    <td><?= $created_at ?></td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="admin-action">
        <button data-get="/admin/user/update?id=<?= $id ?>" class="primary">Update</button>
        <?php if ($is_deleted) : ?>
            <button data-post="/admin/user/recover?id=<?= $id ?>"
                data-confirm="Are you sure to recover <?= html_print($email) ?>?" class="warning">Recover</button>
            <button data-post="/admin/user/permanent_delete?id=<?= $id ?>"
                data-confirm="Are you sure to permanently delete <?= html_print($email) ?>?" class="danger">Delete</button>
        <?php endif; ?>
        <?php if (!$is_deleted) : ?>
            <?php if ($is_blocked) : ?>
                <button data-post="/admin/user/unblock?id=<?= $id ?>"
                    data-confirm="Are you sure to unblock <?= html_print($email) ?>?" class="warning">Unblock</button>
            <?php else : ?>
                <button data-post="/admin/user/block?id=<?= $id ?>"
                    data-confirm="Are you sure to block <?= html_print($email) ?>?" class="warning">Block</button>
            <?php endif; ?>
            <button data-post="/admin/user/delete?id=<?= $id ?>"
                data-confirm="Are you sure to delete <?= html_print($email) ?>?" class="danger">Delete</button>
        <?php endif; ?>
        <?php if (!$is_deleted && !$is_blocked) : ?>
            <button data-post="reset_password?id=<?= $id ?>"
                data-confirm="Are you sure to reset password for <?= html_print($email) ?>?" class="danger">Reset
                Password</button>
        <?php endif; ?>
        <button data-get="<?= $forward ?>" class="success">Go Back</button>
    </section>
</div>