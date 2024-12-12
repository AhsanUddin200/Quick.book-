<?php
// expense.php
require_once 'db.php';

// Remove the session_start() if it's already been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

$error = "";
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $category = trim($_POST['category']);
    $amount = trim($_POST['amount']);
    $date = trim($_POST['date']);
    $notes = trim($_POST['notes']);

    if(!empty($category) && !empty($amount) && !empty($date)){
        $stmt = $pdo->prepare("INSERT INTO quickbook_expenses (user_id, category, amount, date, notes) VALUES (?,?,?,?,?)");
        if($stmt->execute([$user_id, $category, $amount, $date, $notes])){
            // success
        } else {
            $error = "Failed to add expense.";
        }
    } else {
        $error = "Category, Amount, and Date are required.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM quickbook_expenses WHERE user_id=? ORDER BY id DESC");
$stmt->execute([$user_id]);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Expenses</title>
    <style>
        :root {
            --primary-color: #2c9f1b; /* Updated to green */
            --secondary-color: #f4f4f4;
            --accent-color: #2c9f1b; /* Updated to green */
            --background-light: #ecf0f1;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
            background-color: var(--background-light);
            line-height: 1.6;
            color: var(--primary-color);
        }

        header {
            background-color: var(--accent-color);
            color: var(--white);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        header nav a {
            color: var(--white);
            text-decoration: none;
            margin-left: 15px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        header nav a:hover {
            color: #f1f1f1;
            opacity: 0.8;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 25px 20px;
        }

        .expense-form {
            background-color: var(--white);
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }

        .expense-form h2 {
            color: var(--accent-color);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 10px;
        }

        .error {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        input[type="text"], 
        input[type="number"], 
        input[type="date"], 
        textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus, 
        input[type="number"]:focus, 
        input[type="date"]:focus, 
        textarea:focus {
            outline: none;
            border-color: var(--accent-color);
        }

        .submit-btn {
            background-color: var(--accent-color);
            color: var(--white);
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #238515; /* Darker green */
        }

        .expenses-table {
            width: 100%;
            background-color: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .expenses-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .expenses-table th {
            background-color: var(--accent-color);
            color: var(--white);
            padding: 15px;
            text-align: left;
        }

        .expenses-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .expenses-table tr:last-child td {
            border-bottom: none;
        }

        .expenses-table tr:nth-child(even) {
            background-color: #f1f4f7;
        }

        @media screen and (max-width: 600px) {
            header {
                flex-direction: column;
                text-align: center;
            }

            header nav {
                margin-top: 10px;
            }

            header nav a {
                margin: 0 5px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Expenses</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="invoice.php">Invoices</a>
            <a href="report.php">Reports</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    
    <div class="container">
        <div class="expense-form">
            <form method="post">
                <h2>Add New Expense</h2>
                <?php if(!empty($error)): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <input type="text" name="category" placeholder="Category (e.g. Rent, Utilities)" required>
                <input type="number" step="0.01" name="amount" placeholder="Amount" required>
                <input type="date" name="date" required>
                <textarea name="notes" placeholder="Notes (optional)"></textarea>
                
                <button type="submit" class="submit-btn">Add Expense</button>
            </form>
        </div>

        <div class="expenses-table">
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody> 
                    <?php foreach($expenses as $exp): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($exp['category']); ?></td>
                        <td><?php echo number_format($exp['amount'], 2); ?></td>
                        <td><?php echo $exp['date']; ?></td>
                        <td><?php echo htmlspecialchars($exp['notes']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
