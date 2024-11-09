<?php
$fields = [
    'code'                   => 'Voucher Code',
    'discount_type'          => 'Discount Type',
    'percentage'             => 'Percentage(%)',
    'amount'                 => 'Amount(RM)',
    'description'            => 'Description',
    'min_spend'              => 'Minimum Spend',
    'max_spend'              => 'Maximum Spend',
    'usage_limit_per_coupon' => 'Qty Voucher',
    'usage_limit_per_user'   => 'Limit Per User',
    'expired_at'             => 'Expired',
    'created_at'             => 'Created At',
    'status'                 => 'Status'
];

$search = req('search');

$sort = req('sort');
key_exists($sort, $fields) || $sort = 'status';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

$page = req('page', 1);

$statuses = ["Unavailable", "Available"];

// Update the SQL for the pager to use IN clause
$voucher = new SimplePager(
    "SELECT * FROM voucher
        WHERE status IN (?, ?)
        ORDER BY $sort $dir",
    $statuses,
    10,
    $page
);


$arr = $voucher->result; // Get the results

$_title = "View Voucher";
?>

<h1><?= $_title ?></h1>

<table class="table detail">
    <thead>
        <tr>
            <?= table_headers($fields, $sort, $dir, "search=$search&page=$page") ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($arr)) : ?>
            <tr>
                <td colspan="<?= count($fields) ?>">No record found</td>
            </tr>
        <?php else : ?>
            <?php foreach ($arr as $index => $ov) : ?>
                <tr>
                    <td><?= htmlspecialchars($ov->code ?? '') ?></td>
                    <td><?= htmlspecialchars($ov->discount_type ?? '') ?></td>
                    <td><?= htmlspecialchars($ov->percentage ?? '-') ?></td>
                    <td><?= htmlspecialchars($ov->amount ?? '-') ?></td>
                    <td><?= htmlspecialchars($ov->description ?? '') ?></td>
                    <td><?= htmlspecialchars($ov->min_spend ?? '') ?></td>
                    <td><?= htmlspecialchars($ov->max_spend ?? '') ?></td>
                    <td><?= htmlspecialchars($ov->usage_limit_per_coupon ?? '') ?></td>
                    <td><?= htmlspecialchars($ov->usage_limit_per_user ?? '') ?></td>
                    <td><?= htmlspecialchars($ov->expired_at ?? '') ?></td>
                    <td><?= htmlspecialchars($ov->created_at ?? '') ?></td>
                    <td><?= htmlspecialchars($ov->status ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<button data-get="/admin/voucher" class="success">Back</button>