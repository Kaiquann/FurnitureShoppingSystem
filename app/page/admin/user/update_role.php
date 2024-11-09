<?php
$_title = "Update Role";

$forward = req('forward');
if (empty($forward)) {
    $forward = '/admin/user';
}

if (is_get()) {
    temp("danger", "Invalid action");
}

if (is_post()) {
    $id                 = req('id', []);
    $batch_action_roles = req('batch_action_roles');

    if (!is_array($id)) $id = [$id];

    global $_USER_DATA;

    try {
        $_db->beginTransaction();
        $roles = array_column(db_select_all('roles'), 'name', 'name');
        if (!$batch_action_roles || !in_array($batch_action_roles, $roles)) throw new Exception('Invalid Role');
        foreach ($id as $user_id) {
            if (empty($user_id)) {
                throw new Exception('User ID cannot be empty');
            }

            if ($_USER_DATA->id == $user_id) {
                throw new Exception('You cannot update your own role or permissions');
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

            if (!in_array($batch_action_roles, roles_can_access())) {
                throw new Exception('Not Enough Permissions / Invalid Role');
            }

            $stmt = $_db->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$batch_action_roles, $user_id]);
            $stmt->closeCursor();
        }
        $_db->commit();
        temp('success', 'The selected user(s) role has been updated');
    }
    catch (Exception $e) {
        $_db->rollBack();
        temp('danger', $e->getMessage());
    }
}

return redirect($forward);
