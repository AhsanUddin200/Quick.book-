<?php
// dashboard.php
require_once 'db.php';
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info including profile image
$stmt = $pdo->prepare("SELECT username, profile_image FROM quickbook_users WHERE id=?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

$user_image = $user_info['profile_image'] ?? null;

if(empty($user_image)){
    $user_image = 'default_profile.png';
}

// Calculate total income (from paid invoices)
$stmt = $pdo->prepare("SELECT SUM(amount) as total_income FROM quickbook_invoices WHERE user_id=? AND status='Paid'");
$stmt->execute([$user_id]);
$income = $stmt->fetch(PDO::FETCH_ASSOC)['total_income'] ?? 0;

// Calculate total expense
$stmt = $pdo->prepare("SELECT SUM(amount) as total_expense FROM quickbook_expenses WHERE user_id=?");
$stmt->execute([$user_id]);
$expense = $stmt->fetch(PDO::FETCH_ASSOC)['total_expense'] ?? 0;

// Recent invoices
$stmt = $pdo->prepare("SELECT * FROM quickbook_invoices WHERE user_id=? ORDER BY id DESC LIMIT 5");
$stmt->execute([$user_id]);
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent expenses
$stmt = $pdo->prepare("SELECT * FROM quickbook_expenses WHERE user_id=? ORDER BY id DESC LIMIT 5");
$stmt->execute([$user_id]);
$expenses_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// MONTHLY DATA FOR CHART
$stmt = $pdo->prepare("
SELECT DATE_FORMAT(due_date, '%Y-%m') as month, SUM(amount) as total_income 
FROM quickbook_invoices 
WHERE user_id=? AND status='Paid'
GROUP BY DATE_FORMAT(due_date,'%Y-%m')
ORDER BY month DESC LIMIT 6
");
$stmt->execute([$user_id]);
$income_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
SELECT DATE_FORMAT(date, '%Y-%m') as month, SUM(amount) as total_expense
FROM quickbook_expenses
WHERE user_id=?
GROUP BY DATE_FORMAT(date,'%Y-%m')
ORDER BY month DESC LIMIT 6
");
$stmt->execute([$user_id]);
$expense_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$income_assoc = [];
foreach($income_rows as $inc) {
    $income_assoc[$inc['month']] = $inc['total_income'];
}
$expense_assoc = [];
foreach($expense_rows as $exp) {
    $expense_assoc[$exp['month']] = $exp['total_expense'];
}

$all_months = array_unique(array_merge(array_keys($income_assoc), array_keys($expense_assoc)));
rsort($all_months); // descending order

$month_data = [];
$income_data = [];
$expense_data = [];

foreach($all_months as $m) {
    $month_data[] = $m;
    $income_data[] = isset($income_assoc[$m]) ? (float)$income_assoc[$m] : 0;
    $expense_data[] = isset($expense_assoc[$m]) ? (float)$expense_assoc[$m] : 0;
}

// Difference array (Income - Expense for each month)
$difference_data = [];
for ($i=0; $i<count($month_data); $i++){
    $difference_data[] = $income_data[$i] - $expense_data[$i];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickBook Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-green: #2c9f1b;
        }
        body {
            background-color: #f4f4f8;
            font-family: 'Arial', sans-serif;
        }
        .dashboard-card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        .profile-image {
            width:40px; 
            height:40px; 
            border-radius:50%;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
    <header style="background-color: var(--primary-green); color: white; padding: 16px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mr-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.25 4.5l-7.5 7.5 7.5 7.5m1.5-15l7.5 7.5-7.5 7.5"/>
                </svg>
                <h1 class="text-2xl font-bold">QuickBook Dashboard</h1>
            </div>
            <div class="flex items-center">
                <nav>
                    <a href="invoice.php" class="mx-2 hover:text-[#2c9f1b] transition">Invoices</a>
                    <a href="expense.php" class="mx-2 hover:text-[#2c9f1b] transition">Expenses</a>
                    <a href="report.php" class="mx-2 hover:text-[#2c9f1b] transition">Reports</a>
                    <a href="logout.php" class="mx-2 hover:text-[#2c9f1b] transition">Logout</a>
                </nav>
                <img src="<?php echo htmlspecialchars($user_image); ?>" alt="User Image" class="profile-image ml-4">
            </div>
        </header>

        <div class="grid md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg p-6 text-center dashboard-card">
                <h2 style="color: var(--primary-green); font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Total Income</h2>
                <p class="text-3xl font-bold text-green-600"><?php echo number_format($income,2); ?></p>
            </div>
            <div class="bg-white rounded-lg p-6 text-center dashboard-card">
                <h2 style="color: var(--primary-green); font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Total Expense</h2>
                <p class="text-3xl font-bold text-red-600"><?php echo number_format($expense,2); ?></p>
            </div>
            <div class="bg-white rounded-lg p-6 text-center dashboard-card">
                <h2 style="color: var(--primary-green); font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Balance</h2>
                <p class="text-3xl font-bold <?php echo $income - $expense >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                    <?php echo number_format($income - $expense,2); ?>
                </p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg p-6 dashboard-card">
                <h2 style="color: var(--primary-green); font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Recent Invoices</h2>
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 text-left">Client</th>
                            <th class="p-2 text-left">Amount</th>
                            <th class="p-2 text-left">Due Date</th>
                            <th class="p-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($invoices as $inv): ?>
                        <tr class="border-b">
                            <td class="p-2"><?php echo htmlspecialchars($inv['client_name']); ?></td>
                            <td class="p-2"><?php echo number_format($inv['amount'],2); ?></td>
                            <td class="p-2"><?php echo $inv['due_date']; ?></td>
                            <td class="p-2"><?php echo $inv['status']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-lg p-6 dashboard-card">
                <h2 style="color: var(--primary-green); font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Recent Expenses</h2>
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 text-left">Category</th>
                            <th class="p-2 text-left">Amount</th>
                            <th class="p-2 text-left">Date</th>
                            <th class="p-2 text-left">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($expenses_list as $exp): ?>
                        <tr class="border-b">
                            <td class="p-2"><?php echo htmlspecialchars($exp['category']); ?></td>
                            <td class="p-2"><?php echo number_format($exp['amount'],2); ?></td>
                            <td class="p-2"><?php echo $exp['date']; ?></td>
                            <td class="p-2"><?php echo htmlspecialchars($exp['notes']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6 mt-6">
            <div class="bg-white rounded-lg p-6 dashboard-card">
                <h2 style="color: var(--primary-green); font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Monthly Income vs Expense</h2>
                <canvas id="barChart" width="400" height="200"></canvas>
            </div>
            <div class="bg-white rounded-lg p-6 dashboard-card">
                <h2 style="color: var(--primary-green); font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Monthly Balance</h2>
                <canvas id="lineChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const labels = <?php echo json_encode(array_reverse($month_data)); ?>;
        const incomeData = <?php echo json_encode(array_reverse($income_data)); ?>;
        const expenseData = <?php echo json_encode(array_reverse($expense_data)); ?>;

        const barData = {
            labels: labels,
            datasets: [
                {
                    label: 'Income',
                    backgroundColor: 'var(--primary-green)',
                    borderColor: 'var(--primary-green)',
                    data: incomeData
                },
                {
                    label: 'Expense',
                    backgroundColor: 'rgba(255,99,132,0.6)',
                    borderColor: 'rgba(255,99,132,1)',
                    data: expenseData
                }
            ]
        };

        const barConfig = {
            type: 'bar',
            data: barData,
            options: {
                responsive:true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        };

        const differenceData = <?php echo json_encode(array_reverse($difference_data)); ?>;
        const lineData = {
            labels: labels,
            datasets: [
                {
                    label: 'Monthly Balance (Income - Expense)',
                    fill: false,
                    borderColor: 'var(--primary-green)',
                    backgroundColor: 'rgba(54,162,235,0.2)',
                    data: differenceData,
                    tension: 0.1
                }
            ]
        };
        const lineConfig = {
            type: 'line',
            data: lineData,
            options: {
                responsive:true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        };

        const barChart = new Chart(document.getElementById('barChart'), barConfig);
        const lineChart = new Chart(document.getElementById('lineChart'), lineConfig);
    </script>
</body>
</html>
