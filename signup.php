<?php
// signup.php
require_once 'db.php';
session_start();

$error = "";
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $hashed   = password_hash($password, PASSWORD_DEFAULT);

    // Image handling
    $profile_image_path = null;
    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK){
        $allowed_types = ['image/jpeg','image/png','image/gif'];
        if(in_array($_FILES['profile_image']['type'], $allowed_types)){
            // Create uploads directory if not exists
            if(!is_dir('uploads')){
                mkdir('uploads', 0777, true);
            }

            $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'user_'.time().'_'.rand(1000,9999).'.'.$ext; 
            $destination = 'uploads/'.$new_filename;

            if(move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)){
                $profile_image_path = $destination;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Only JPEG, PNG, or GIF images are allowed.";
        }
    }

    if(empty($error)){
        if(!empty($username) && !empty($email) && !empty($password)){
            // Check if user already exists
            $stmt = $pdo->prepare("SELECT id FROM quickbook_users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if($stmt->rowCount() > 0){
                $error = "Username or Email already exists.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO quickbook_users (username,email,password,profile_image) VALUES (?,?,?,?)");
                if($stmt->execute([$username,$email,$hashed,$profile_image_path])){
                    // Redirect to login page
                    header("Location: login.php");
                    exit;
                } else {
                    $error = "Failed to register.";
                }
            }
        } else {
            $error = "All fields are required.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickBook - Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c9f1b;
            --secondary-color: #2ecc71;
            --background-color: #f4f6f7;
            --text-color: #2c3e50;
            --card-background: #ffffff;
            --error-color: #e74c3c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            line-height: 1.6;
            padding: 20px;
            margin-top: 100px;
            overflow: hidden; /* Hide scroll until splash is gone */
        }

        /* Splash Screen Styles */
        .splash {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .splash img {
            max-width: 150px;
            height: auto;
        }

        .signup-container {
            background-color: var(--card-background);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 35px;
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s ease;
            opacity: 0; /* Initially hidden */
        }

        .signup-container:hover {
            transform: translateY(-5px);
        }

        .signup-title {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 25px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }

        .file-input {
            display: flex;
            align-items: center;
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 10px;
        }

        .file-input input[type="file"] {
            margin-left: 10px;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #2c9f1b;
        }

        .error-message {
            color: var(--error-color);
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .login-link {
            text-align: center;
            margin-top: 15px;
            color: var(--text-color);
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #2c9f1b;
        }

        .logo {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <!-- Splash Screen -->
    <div class="splash">
        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTZUtjZpjxP9v1wXDLP7Ob3-j85wrGP9yGyhg&s" alt="Loading...">
    </div>

    <div class="signup-container">
        <div class="logo">
            <a href="index.php">
                <div style="display: flex; justify-content: center; ">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTZUtjZpjxP9v1wXDLP7Ob3-j85wrGP9yGyhg&s" alt="QuickBook Logo" style="height: 60px;">
                </div>
            </a>
        </div>
        <h2 class="signup-title">Create Your Account</h2>
        
        <?php if(!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="post" onsubmit="return validateForm();" enctype="multipart/form-data">
            <div class="form-group">
                <input type="text" name="username" class="form-input" placeholder="Username" id="username" required>
            </div>
            
            <div class="form-group">
                <input type="email" name="email" class="form-input" placeholder="Email Address" id="email" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="password" class="form-input" placeholder="Password" id="password" required>
            </div>
            
            <div class="form-group">
                <div class="file-input">
                    <label>Profile Picture (Optional)</label>
                    <input type="file" name="profile_image" accept="image/*">
                </div>
            </div>
            
            <button type="submit" class="submit-btn">Sign Up</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Log In</a>
        </div>
    </div>

    <script>
    window.addEventListener('load', () => {
        setTimeout(() => {
            document.querySelector('.splash').style.display = 'none';
            document.querySelector('.signup-container').style.opacity = '1';
            document.body.style.overflow = 'auto'; // allow scrolling now if needed
        }, 2000); // 2 seconds
    });

    function validateForm(){
        let user = document.getElementById('username').value.trim();
        let email = document.getElementById('email').value.trim();
        let pass = document.getElementById('password').value.trim();
        
        if(!user || !email || !pass) {
            alert("All fields are required.");
            return false;
        }
        return true;
    }
    </script>
</body>
</html>
