<?php
$_title = "Reset Password";

if (is_get()) {
    temp("danger", "Invalid action");
}

if (is_post()) {
    $id = req('id', []);

    if (!is_array($id)) $id = [$id];

    global $_USER_DATA;

    if (!$_err) {
        try {
            $_db->beginTransaction();
            foreach ($id as $user_id) {
                if (empty($user_id)) {
                    throw new Exception('User ID cannot be empty');
                }

                if ($_USER_DATA->id == $user_id) {
                    throw new Exception('You cannot send reset password link to your own account');
                }

                $stmt = $_db->prepare('SELECT * FROM users WHERE id = ? AND is_blocked = 0 AND is_deleted = 0');
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                $stmt->closeCursor();

                if (!$user) {
                    throw new Exception("User not found or blocked or deleted");
                }

                if (!isSuperAdmin()) {
                    if ($_USER_DATA->role == $user->role && !in_array($_USER_DATA->role, roles_can_access())) {
                        throw new Exception('Not Enough Permissions / Invalid Role');
                    }
                }

                $token = generate_token();
                $email = $user->email;
                $url   = base("session/reset_password?token=$token");
                $body  = str_replace("{{ url }}", $url, RESET_PASSWORD_BODY);

                $stmt = $_db->prepare('
                    DELETE FROM reset_password WHERE email = ?;

                    INSERT INTO reset_password (email, token, expired_at) 
                    VALUES (?, ?, ADDTIME(NOW(), "00:10:00"));
                ');
                $stmt->execute([$email, $email, $token]);
                $stmt->closeCursor();

                sendEmail($email, RESET_PASSWORD_SUBJECT, $body);
            }
            $_db->commit();
            temp("success", "The selected user(s) have been sent reset password link");
        }
        catch (Exception $e) {
            $_db->rollBack();
            temp('danger', $e->getMessage());
        }
    }
}

return redirect("/admin/user");
