<?php
function generate_low_stock_product_list($product)
{
    $product_list = "";
    foreach ($product as $p) {
        $product_list .=
            "<tr>
                <td>
                    <b>$p->name</b>
                </td>
                <td>
                    <b>Qty $p->stock</b>
                </td>
            </tr>
            ";
    }
    return $product_list;
}

$current_date    = date('Y-m-d');
$low_stock_alert = db_select_single('low_stock_alert', 'current_date', $current_date);

if (!$low_stock_alert) {
    $product = $_db->query('SELECT * FROM product WHERE stock <= 10')->fetchAll();
    $email   = $_db->query('SELECT email FROM users WHERE role IN ("superadmin")')->fetchColumn();
    if ($product && $email) {
        $created_at   = getDateTime();
        $product_list = generate_low_stock_product_list($product);
        $body         = str_replace(['{{ created_at }}', '{{ product_list }}'], [$created_at, $product_list], LOW_STOCK_ALERT_BODY);
        sendEmail($email, LOW_STOCK_ALERT_SUBJECT, $body);
        $low_stock_alert_payload = [
            'today_date' => $current_date,
            'created_at' => $created_at
        ];
        db_insert('low_stock_alert', $low_stock_alert_payload);
    }
}
?>
<script>
    $(() => {
        // Sort Product Script
        $('#category_sort').val("<?= $category_sort ?? '' ?>");
        $('#category_sort').on('change', function () {
            window.location.href = "?category_sort=" + $(this).val() + "&search=" + "<?= $search ?? '' ?>" + "&sort=" + "<?= $sort ?? '' ?>" + "&dir=" + "<?= $dir ?? '' ?>" + "&price_range=" + "<?= $price_range ?? '' ?>&stock_sort=" + "<?= $stock_sort ?? '' ?>&table_rows=" + "<?= $table_rows ?? '' ?>";
        });
        $('#price_range').val("<?= $price_range ?? '' ?>");
        $('#price_range').on('change', function () {
            window.location.href = "?category_sort=" + "<?= $category_sort ?? '' ?>" + "&search=" + "<?= $search ?? '' ?>" + "&sort=" + "<?= $sort ?? '' ?>" + "&dir=" + "<?= $dir ?? '' ?>" + "&price_range=" + $(this).val() + "&stock_sort=" + "<?= $stock_sort ?? '' ?>&table_rows=" + "<?= $table_rows ?? '' ?>";
        });
        $('#stock_sort').val("<?= $stock_sort ?? '' ?>");
        $('#stock_sort').on('change', function () {
            window.location.href = "?category_sort=" + "<?= $category_sort ?? '' ?>" + "&search=" + "<?= $search ?? '' ?>" + "&sort=" + "<?= $sort ?? '' ?>" + "&dir=" + "<?= $dir ?? '' ?>" + "&price_range=" + "<?= $price_range ?? '' ?>&stock_sort=" + $(this).val() + "&table_rows=" + "<?= $table_rows ?? '' ?>";
        });
        // Sort User Script
        $('#role_sort').val("<?= $role ?? '' ?>");
        $('#role_sort').on('change', function () {
            window.location.href = "?role=" + $(this).val() + "&search=" + "<?= $search ?? '' ?>" + "&sort=" + "<?= $sort ?? '' ?>" + "&dir=" + "<?= $dir ?? '' ?>&table_rows=" + "<?= $table_rows ?? '' ?>";
        });
    });
</script>