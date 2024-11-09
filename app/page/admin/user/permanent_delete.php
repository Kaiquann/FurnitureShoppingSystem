<?php
$_title = "Permenant Delete User";

if (is_get()) {
    temp("danger", "Invalid action");
}

if (is_post()) {
    $id = req('id', []);

    if (!is_array($id)) $id = [$id];

    try {
        $_db->beginTransaction();
        foreach ($id as $user_id) {
            if (empty($user_id)) {
                throw new Exception("User ID is required");
            }

            if ($_USER_DATA->id == $user_id) {
                throw new Exception('You cannot unblock your own account');
            }

            $stmt = $_db->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            $stmt->closeCursor();

            if (!$user) {
                throw new Exception("User ID: $user_id does not exist");
            }

            if (!isSuperAdmin()) {
                if ($_USER_DATA->role == $user->role && !in_array($_USER_DATA->role, roles_can_access())) {
                    throw new Exception('Not Enough Permissions / Invalid Role');
                }
            }

            $deleted_user_email = "PD: {$user->email} (Deleted on " . getDateTime() . ")";

            $stmt = $_db->prepare('UPDATE users SET email = ?, is_deleted = 2 WHERE id = ?');
            $stmt->execute([$deleted_user_email, $user_id]);
            $stmt->closeCursor();

            $subject = 'Account Permanent Deleted';
            $body    = generateBodyWithTitleMessage(
                $subject,
                'Your account has been permanently deleted by admin on date ' . getDateTime()
            );
            sendEmail($user->email, $subject, $body);
        }
        $_db->commit();
        temp('success', "The selected user(s) has been permanently deleted");
    }
    catch (Exception $ex) {
        $_db->rollBack();
        temp('danger', $ex->getMessage());
    }
}

return redirect('/admin/user/deleted');
