<?php
// logout.php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out | QuickBook</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c9f1b; /* Updated to green */
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
            background: linear-gradient(135deg, var(--background-color) 0%, #e9ecef 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            line-height: 1.6;
        }

        .logout-container {
            background-color: var(--card-background);
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: fadeIn 0.5s ease-out;
        }

        .logout-icon {
            font-size: 72px;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            animation: bounce 0.7s ease;
        }

        .logout-title {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 24px;
            font-weight: 600;
        }

        .logout-message {
            color: var(--text-color);
            margin-bottom: 25px;
            font-size: 16px;
        }

        .login-btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .login-btn:hover {
            background-color: #238515; /* Darker green for hover */
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }

        .login-link a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #238515;
        }

        .logo {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .logo img {
            height: 70px;
        }
    </style>
</head>
<body>
    <div class="logout-container">
    <div class="logo">
            <a href="index.php">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTZUtjZpjxP9v1wXDLP7Ob3-j85wrGP9yGyhg&s" alt="QuickBook Logo">
            </a>
        </div>
        <div class="logout-icon">
            ✌️
        </div>
        <h2 class="logout-title">Logged Out Successfully</h2>
        <p class="logout-message">
            You have been securely logged out of your QuickBook account. 
            We hope to see you again soon!
        </p>
        <a href="login.php" class="login-btn">Back to Login</a>
    </div>

    <script>
        // Automatic redirect after 5 seconds
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 5000);
    </script>
</body>
</html>
