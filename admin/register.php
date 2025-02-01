<?php
// Handle Registration
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
    $username = $_POST['username'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];
    
    // Password validation regex
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
        $error = "Password must be at least 8 characters long, and include at least one uppercase letter, one lowercase letter, one number, and one special character.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT); // Hash the password

        // Prevent SQL Injection
        $username = $conn->real_escape_string($username);
        $name = $conn->real_escape_string($name);
        $email = $conn->real_escape_string($email);
        $mobile = $conn->real_escape_string($mobile);

        // Check if username or email already exists
        $sql = "SELECT id FROM users_table WHERE username='$username' OR email='$email' LIMIT 1";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $error = "Username or Email already exists.";
        } else {
            // Insert new user into the database
            $sql = "INSERT INTO users_table (username, password, name, email, mobile) VALUES ('$username', '$password_hash', '$name', '$email', '$mobile')";

            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('Registration successful! You can now log in.'); window.location='login.php';</script>";
            } else {
                $error = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2 class="text-center my-4">User Registration</h2>

        <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" name="username" id="username" required>
            </div>

            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" class="form-control" name="name" id="name" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" name="email" id="email" required>
            </div>

            <div class="form-group">
                <label for="mobile">Mobile Number:</label>
                <input type="text" class="form-control" name="mobile" id="mobile" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>

        <p class="text-center mt-3">Already have an account? <a href="login.php">Login here</a></p>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
