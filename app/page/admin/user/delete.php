<?php
$_title = "Delete User";

if (is_get()) {
    temp("danger", "Invalid action");
}

if (is_post()) {
    $id = req('id', []);

    if (!is_array($id)) $id = [$id];

    global $_USER_DATA;

    try {
        $_db->beginTransaction();
        foreach ($id as $user_id) {
            if (empty($user_id)) {
                throw new Exception('User ID cannot be empty');
            }

            if ($_USER_DATA->id == $user_id) {
                throw new Exception('You cannot delete your own account');
            }

            $stmt = $_db->prepare("SELECT * FROM users WHERE id = ?");
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

            $stmt = $_db->prepare("UPDATE users SET is_blocked = 0, is_deleted = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $stmt->closeCursor();

            $subject = 'Account Deleted';
            $body    = generateBodyWithTitleMessage(
                $subject,
                'Your account has been deleted by admin on date ' . getDateTime()
            );
            sendEmail($user->email, $subject, $body);
        }
        $_db->commit();
        temp('success', 'The selected user(s) have been deleted');
    }
    catch (Exception $e) {
        $_db->rollBack();
        temp('danger', $e->getMessage());
    }
}

return redirect('/admin/user');
