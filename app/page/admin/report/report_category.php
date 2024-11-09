<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filter = req('filter');
    $category_id = req('category_id'); 

    if ($filter == 'day') {
        $date = req('date');
    } elseif ($filter == 'month') {
        $date = req('month') . '-01'; 
    } elseif ($filter == 'year') {
        $date = req('year') . '-01-01'; 
    }
    
    $reportDataPoints = generate_sales_report($filter, $date, $category_id);
} else {
    $reportDataPoints = [];
}

$categories = db_select_all("category");

function generate_sales_report($filter, $date, $category_id)
{
    global $_db;

    if ($filter == 'day') {
        $start_date = $date;
        $end_date = $date;
    } elseif ($filter == 'month') {
        $start_date = date('Y-m-01', strtotime($date));
        $end_date = date('Y-m-t', strtotime($date));
    } elseif ($filter == 'year') {
        $start_date = date('Y-01-01', strtotime($date));
        $end_date = date('Y-12-31', strtotime($date));
    } else {
        return []; 
    }

    if ($category_id == 'all') {
        $stm = $_db->prepare("
            SELECT c.name AS category_name, SUM(i.quantity) AS total_quantity
            FROM orders o
            JOIN item i ON o.order_id = i.order_id
            JOIN product p ON i.product_id = p.id
            JOIN category c ON p.category_id = c.id
            WHERE o.status = 'delivered'
            AND DATE(o.created_at) BETWEEN :start_date AND :end_date
            GROUP BY c.name
            ORDER BY total_quantity DESC;
        ");
        $stm->execute(['start_date' => $start_date, 'end_date' => $end_date]); 
    } else {
        $stm = $_db->prepare("
            SELECT p.name AS product_name, SUM(i.quantity) AS total_quantity
            FROM orders o
            JOIN item i ON o.order_id = i.order_id
            JOIN product p ON i.product_id = p.id
            WHERE o.status = 'delivered'
            AND DATE(o.created_at) BETWEEN :start_date AND :end_date
            AND p.category_id = :category_id
            GROUP BY p.name
            ORDER BY total_quantity DESC;
        ");
        $stm->execute(['start_date' => $start_date, 'end_date' => $end_date, 'category_id' => $category_id]); 
    }

    $dataPoints = [];
    foreach ($stm->fetchAll() as $row) {
        $dataPoints[] = ["label" => $category_id == 'all' ? $row->category_name : $row->product_name, "y" => $row->total_quantity];
    }

    return $dataPoints;
}
?>

<body>
    <div id="chartContainer" style="height: 370px; width: 100%;"></div>

    <!-- Form -->
    <form method="POST">
    <label for="filter">Select Filter:</label>
    <select name="filter" id="filter">
        <option value="day">Day</option>
        <option value="month">Month</option>
        <option value="year">Year</option>
    </select>
    
    <div id="datePicker">
        <label for="date">Select Date:</label>
        <input type="date" name="date" id="date" required>
    </div>

    <div id="monthPicker" style="display:none;">
        <label for="month">Select Month:</label>
        <input type="month" name="month" id="month" disabled>
    </div>

    <div id="yearPicker" style="display:none;">
        <label for="year">Select Year:</label>
        <input type="number" name="year" id="year" min="2000" max="<?= date('Y') ?>" step="1" placeholder="YYYY" disabled>
    </div>

        <label for="category_id">Select Category:</label>
        <select name="category_id" id="category_id" required>
            <option value="all">All Categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category->id; ?>"><?php echo $category->name; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Generate Report</button>
        <a href="/admin/report">Go Back</a>
    </form>

    <!-- Sales Report Table -->
    <div id="reportContent">
        <div>
            <h1>Sales Report</h1>
            <table id="salesTable" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #000;">Product/Category Name</th>
                        <th style="border: 1px solid #000;">Total Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reportDataPoints)): ?>
                        <?php foreach ($reportDataPoints as $data): ?>
                            <tr>
                                <td style="border: 1px solid #000; padding: 5px;"><?php echo htmlspecialchars($data['label']); ?></td>
                                <td style="border: 1px solid #000; padding: 5px;"><?php echo htmlspecialchars($data['y']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center; padding: 5px;">No data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <button id="downloadPDF">Download PDF</button>

    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
    document.getElementById('filter').addEventListener('change', function() {
    const filterValue = this.value;
    document.getElementById('datePicker').style.display = (filterValue === 'day') ? 'block' : 'none';
    document.getElementById('monthPicker').style.display = (filterValue === 'month') ? 'block' : 'none';
    document.getElementById('yearPicker').style.display = (filterValue === 'year') ? 'block' : 'none';

    document.getElementById('date').disabled = (filterValue !== 'day');
    document.getElementById('month').disabled = (filterValue !== 'month');
    document.getElementById('year').disabled = (filterValue !== 'year');
});

window.onload = function () {
    var dataPoints = <?php echo json_encode($reportDataPoints, JSON_NUMERIC_CHECK); ?>;
    if (dataPoints.length > 0) {
        var chart = new CanvasJS.Chart("chartContainer", {
            animationEnabled: true,
            theme: "light2",
            title: {
                text: "Sales Report"
            },
            axisY: {
                title: "Total Quantity Sold",
                scaleBreaks: {
                    autoCalculate: true
                }
            },
            data: [{
                type: "column",
                yValueFormatString: "#,##0",
                indexLabel: "{y}",
                indexLabelPlacement: "inside",
                indexLabelFontColor: "white",
                dataPoints: dataPoints
            }]
        });
        chart.render();
    } else {
        document.getElementById("chartContainer").innerHTML = "<h3>No data available for the chart</h3>";
    }
}

    document.getElementById('downloadPDF').addEventListener('click', function() {
        const element = document.getElementById('reportContent');
        html2pdf()
            .from(element)
            .set({
                margin: 1,
                filename: 'sales_report.pdf',
                html2canvas: { scale: 2 },
                jsPDF: { format: 'a4', orientation: 'landscape' }
            })
            .save();
    });
    </script>
</body>
