<?php
/**
 * @modified: Chong Jun Xiang
 */
$_title = "Delete Voucher";

if (is_get()) {
    temp("danger", "Invalid action");
}

if (is_post()) {
    $id = req('id');

    try {
        $_db->beginTransaction();
        if (empty($id)) {
            throw new Exception("Voucher ID is required");
        }

        $stmt = $_db->prepare('SELECT * FROM voucher WHERE id = ?');
        $stmt->execute([$id]);
        $voucher = $stmt->fetch();

        if (!$voucher) {
            throw new Exception("Voucher ID: $id does not exist");
        }

        $deleted_voucher_code = "PD: {$voucher->code} (Deleted on " . getDateTime() . ")";

        $stmt = $_db->prepare('UPDATE voucher SET code = ?, is_active = 0, is_deleted = 1 WHERE id = ?');
        $stmt->execute([$deleted_voucher_code, $id]);
        $_db->commit();
        temp('success', "The voucher has been deleted");
    }
    catch (Exception $ex) {
        $_db->rollBack();
        temp('danger', $ex->getMessage());
    }
}

return redirect('/admin/voucher');
