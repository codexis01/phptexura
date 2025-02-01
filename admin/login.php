<?php
session_start();

// Handle Login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $host = "localhost"; 
    $user = "root"; 
    $pass = "";  
    $dbname = "texura_db"; 

    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get user input
    $login_value = $_POST['login_value'];
    $password = $_POST['password'];

    // Prevent SQL Injection
    $login_value = $conn->real_escape_string($login_value);

    // Check if username or email exists
    $sql = "SELECT id, username, password FROM users_table WHERE username='$login_value' OR email='$login_value' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            echo "<script>alert('Login successful!'); window.location='index.php';</script>";
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that username or email.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f4f4f4;
        }
        .login-container {
            max-width: 400px;
            margin: auto;
            margin-top: 100px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .form-control {
            border-radius: 20px;
        }
        .btn-custom {
            background-color: #007bff;
            color: white;
            border-radius: 20px;
            width: 100%;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="login-container">
        <h2 class="text-center mb-4">User Login</h2>

        <?php if (isset($error)) { echo "<p class='error-message'>$error</p>"; } ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="login_value" class="form-label">Username or Email:</label>
                <input type="text" name="login_value" id="login_value" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-custom">Login</button>
        </form>

        <p class="text-center mt-3">Don't have an account? <a href="registration.php">Register here</a></p>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
