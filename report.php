<?php
// report.php
require_once 'db.php';
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// Monthly Income & Expense Summary
$stmt = $pdo->prepare("
SELECT DATE_FORMAT(due_date,'%Y-%m') as month, SUM(amount) as total_income 
FROM quickbook_invoices 
WHERE user_id=? AND status='Paid' 
GROUP BY DATE_FORMAT(due_date,'%Y-%m')
ORDER BY month DESC
");
$stmt->execute([$user_id]);
$income_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
SELECT DATE_FORMAT(date,'%Y-%m') as month, SUM(amount) as total_expense 
FROM quickbook_expenses
WHERE user_id=? 
GROUP BY DATE_FORMAT(date,'%Y-%m')
ORDER BY month DESC
");
$stmt->execute([$user_id]);
$expense_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$report = [];
foreach($income_rows as $inc){
    $report[$inc['month']]['income'] = $inc['total_income'];
}
foreach($expense_rows as $exp){
    $report[$exp['month']]['expense'] = $exp['total_expense'];
}

// Sort by month descending
krsort($report);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Financial Reports</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c9f1b; /* Updated to green */
            --secondary-color: #238515; /* Darker green for accents */
            --background-color: #f4f6f7;
            --text-color: #2c3e50;
            --card-background: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .navbar {
            background-color: var(--primary-color);
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .navbar-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .navbar-links {
            display: flex;
            gap: 15px;
        }

        .navbar-links a {
            color: white;
            text-decoration: none;
            font-weight: 300;
            transition: color 0.3s ease;
        }

        .navbar-links a:hover {
            color: rgba(255,255,255,0.8);
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .report-card {
            background-color: var(--card-background);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 25px;
        }

        .report-title {
            color: var(--primary-color);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        .report-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
        }

        .report-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }

        .report-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .report-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .report-table tr:hover {
            background-color: #f1f3f5;
        }

        .positive-balance {
            color: var(--secondary-color);
            font-weight: 600;
        }

        .negative-balance {
            color: #e74c3c;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-title">Financial Reports</div>
            <div class="navbar-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="invoice.php">Invoices</a>
                <a href="expense.php">Expenses</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="report-card">
            <h2 class="report-title">Monthly Income & Expense Summary</h2>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Income</th>
                        <th>Expense</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($report as $month => $data): 
                        $inc = $data['income'] ?? 0;
                        $exp = $data['expense'] ?? 0;
                        $balance = $inc - $exp;
                        $balanceClass = $balance >= 0 ? 'positive-balance' : 'negative-balance';
                    ?>
                    <tr>
                        <td><?php echo $month; ?></td>
                        <td><?php echo number_format($inc, 2); ?></td>
                        <td><?php echo number_format($exp, 2); ?></td>
                        <td class="<?php echo $balanceClass; ?>"><?php echo number_format($balance, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
