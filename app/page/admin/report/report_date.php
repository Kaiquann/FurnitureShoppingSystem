<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filter = req('filter');
    
    if ($filter == 'day') {
        $date = req('date');
    } elseif ($filter == 'month') {
        $date = req('month') . '-01'; 
    } elseif ($filter == 'year') {
        $date = req('year') . '-01-01'; 
    }
    
    $reportDataPoints = generate_sales_report($filter, $date);
}

function generate_sales_report($filter, $date) {
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

    $stm = $_db->prepare("
        SELECT p.name AS product_name, SUM(i.quantity) AS total_quantity
        FROM orders o
        JOIN item i ON o.order_id = i.order_id
        JOIN product p ON i.product_id = p.id
        WHERE o.status = 'delivered'
        AND DATE(o.created_at) BETWEEN :start_date AND :end_date
        GROUP BY p.name
        ORDER BY total_quantity DESC;
    ");
    $stm->execute(['start_date' => $start_date, 'end_date' => $end_date]);

    $dataPoints = [];
    foreach ($stm->fetchAll() as $row) {
        $dataPoints[] = ["label" => $row->product_name, "y" => $row->total_quantity];
    }

    return $dataPoints;
}
?>

<body>
<div id="chartContainer" style="height: 370px; width: 100%;"></div>

<form method="POST" id="reportForm">
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

    <button type="submit">Generate Report</button>
    <a href="/admin/">Go Back</a>
</form>

<div id="tableContent" style="display: <?= isset($reportDataPoints) ? 'block' : 'none' ?>;">
    <h1>Sales Report for <?php 
        if ($filter == 'day') {
            echo htmlspecialchars($date);
        } elseif ($filter == 'month') {
            echo htmlspecialchars(date('F Y', strtotime($date))); 
        } elseif ($filter == 'year') {
            echo htmlspecialchars(date('Y', strtotime($date))); 
        }
        ?>
        </h1>
    <table id="salesTable" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="border: 1px solid #000;">Product Name</th>
                <th style="border: 1px solid #000;">Total Quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($reportDataPoints)): ?>
                <?php foreach ($reportDataPoints as $data): ?>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px;"><?= htmlspecialchars($data['label']) ?></td>
                        <td style="border: 1px solid #000; padding: 5px;"><?= htmlspecialchars($data['y']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<button id="downloadPDF">Download PDF</button>
</body>

<script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
window.onload = function () {
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
            dataPoints: <?php echo isset($reportDataPoints) ? json_encode($reportDataPoints, JSON_NUMERIC_CHECK) : '[]'; ?>
        }]
    });
    chart.render();
}


document.getElementById('downloadPDF').addEventListener('click', function() {
    const element = document.getElementById('tableContent'); 
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

document.getElementById('filter').addEventListener('change', function() {
    const filterValue = this.value;
    document.getElementById('datePicker').style.display = (filterValue === 'day') ? 'block' : 'none';
    document.getElementById('monthPicker').style.display = (filterValue === 'month') ? 'block' : 'none';
    document.getElementById('yearPicker').style.display = (filterValue === 'year') ? 'block' : 'none';

    document.getElementById('date').disabled = (filterValue !== 'day');
    document.getElementById('month').disabled = (filterValue !== 'month');
    document.getElementById('year').disabled = (filterValue !== 'year');
});
</script>
