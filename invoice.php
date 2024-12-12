<?php
require_once 'db.php';
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

$error = "";

// Add Invoice Logic
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_invoice'){
    $client_name = trim($_POST['client_name']);
    $amount = trim($_POST['amount']);
    $due_date = trim($_POST['due_date']);

    if(!empty($client_name) && !empty($amount) && !empty($due_date)){
        $stmt = $pdo->prepare("INSERT INTO quickbook_invoices (user_id, client_name, amount, due_date, status) VALUES (?,?,?,?,?)");
        if($stmt->execute([$user_id, $client_name, $amount, $due_date, 'Pending'])){
            header("Location: ".$_SERVER['PHP_SELF']."?success=1");
            exit;
        } else {
            $error = "Failed to add invoice.";
        }
    } else {
        $error = "All fields are required.";
    }
}

// Mark Paid Logic
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_paid'){
    $invoice_id = intval($_POST['invoice_id']);
    $stmt = $pdo->prepare("SELECT id FROM quickbook_invoices WHERE id=? AND user_id=?");
    $stmt->execute([$invoice_id, $user_id]);
    if($stmt->rowCount() > 0){
        $upd = $pdo->prepare("UPDATE quickbook_invoices SET status='Paid' WHERE id=?");
        $upd->execute([$invoice_id]);
        header("Location: ".$_SERVER['PHP_SELF']."?success=2");
        exit;
    } else {
        $error = "Invalid invoice or no permission.";
    }
}

// Fetch Invoices
$stmt = $pdo->prepare("SELECT * FROM quickbook_invoices WHERE user_id=? ORDER BY id DESC");
$stmt->execute([$user_id]);
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c9f1b; /* Updated to green */
            --secondary-color: #f4f4f4;
            --text-color: #333;
        }
        body {
            background-color: var(--secondary-color);
            font-family: 'Inter', sans-serif;
            color: var(--text-color);
        }
        .invoice-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-control, .btn {
            border-radius: 8px;
        }
        .table {
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: 600;
        }
        .status-paid {
            background-color: rgba(44, 159, 27, 0.1); /* Green badge background */
            color: var(--primary-color); /* Green text */
        }
        .status-pending {
            background-color: rgba(255,193,7,0.1);
            color: #ffc107;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #238515; /* Darker green on hover */
        }
        .btn-success {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }
        .btn-success:hover {
            background-color: #238515 !important;
            border-color: #238515 !important;
        }
        .alert-custom {
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Invoice Manager</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link text-white" href="dashboard.php">Dashboard</a>
                <a class="nav-link text-white" href="expense.php">Expenses</a>
                <a class="nav-link text-white" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="invoice-container">
                    <h3 class="mb-4" style="color: var(--primary-color);">Create Invoice</h3>
                    
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger alert-custom"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if(isset($_GET['success']) && $_GET['success'] == 1): ?>
                        <div class="alert alert-success alert-custom">Invoice added successfully!</div>
                    <?php endif; ?>

                    <form method="post">
                        <input type="hidden" name="action" value="add_invoice">
                        <div class="mb-3">
                            <label class="form-label">Client Name</label>
                            <input type="text" name="client_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Create Invoice</button>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="invoice-container">
                    <h3 class="mb-4" style="color: var(--primary-color);">Invoice List</h3>
                    
                    <?php if(isset($_GET['success']) && $_GET['success'] == 2): ?>
                        <div class="alert alert-success alert-custom">Invoice marked as paid!</div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Amount</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($invoices as $inv): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($inv['client_name']); ?></td>
                                    <td>$<?php echo number_format($inv['amount'], 2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($inv['due_date'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $inv['status'] === 'Paid' ? 'status-paid' : 'status-pending'; ?>">
                                            <?php echo $inv['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($inv['status'] === 'Pending'): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="mark_paid">
                                            <input type="hidden" name="invoice_id" value="<?php echo $inv['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success">Mark Paid</button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
