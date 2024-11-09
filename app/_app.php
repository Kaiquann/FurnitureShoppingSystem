<script>
    // Sort Transaction Method Script
    $('#transaction_method').val("<?= $transaction_method ?? '' ?>");
    $('#transaction_method').on('change', function () {
        window.location.href = "?transaction_method=" + $(this).val() + "&search=" + "<?= $search ?? '' ?>" + "&sort=" + "<?= $sort ?? '' ?>" + "&dir=" + "<?= $dir ?? '' ?>&category_sort=" + "<?= $category_sort ?? '' ?>&table_rows=" + "<?= $table_rows ?? '' ?>&transaction_status=" + "<?= $transaction_status ?? '' ?>";
    });
    // Sort Transaction Status Script
    $('#transaction_status').val("<?= $transaction_status ?? '' ?>");
    $('#transaction_status').on('change', function () {
        window.location.href = "?transaction_status=" + $(this).val() + "&search=" + "<?= $search ?? '' ?>" + "&sort=" + "<?= $sort ?? '' ?>" + "&dir=" + "<?= $dir ?? '' ?>&category_sort=" + "<?= $category_sort ?? '' ?>&table_rows=" + "<?= $table_rows ?? '' ?>&transaction_method=" + "<?= $transaction_method ?? '' ?>";
    });
    // Sort Table Rows Script
    $('#table_rows').val("<?= $table_rows ?? '' ?>");
    $('#table_rows').on('change', function () {
        window.location.href = "?table_rows=" + $(this).val() + "&search=" + "<?= $search ?? '' ?>" + "&sort=" + "<?= $sort ?? '' ?>" + "&dir=" + "<?= $dir ?? '' ?>&price_range=" + "<?= $price_range ?? '' ?>&category_sort=" + "<?= $category_sort ?? '' ?>&role=" + "<?= $role ?? '' ?>&transaction_method=" + "<?= $transaction_method ?? '' ?>&transaction_status=" + "<?= $transaction_status ?? '' ?>&stock_sort=" + "<?= $stock_sort ?? '' ?>";
    });
</script>