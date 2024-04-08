<?php
// Start the session if it hasn't been started yet
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if a session is active and destroy it
if (isset($_SESSION['id'])) {
    session_unset();
    session_destroy();
}

// Include the script to connect to the database
include "../global/db_conn.php";

// Check if the login form is submitted
if (isset($_POST["login"])) {
    // Retrieve and sanitize user input for ID and Password
    $id = $_POST["id"];
    $password = $_POST["password"];

    // SQL query to check user credentials
    include "../Global/query.php";
    $query = "SELECT * FROM accounts WHERE account_id = ? AND password = ?";
    $logQuery = "SELECT * FROM accounts WHERE account_id = $id AND password = $password";
    logQuery($logQuery);
    $stmt = $conn->prepare($query);
    $stmt->execute([$id, $password]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check for empty fields or incorrect credentials
    if ($id == NULL || $password == NULL) {
        echo '<script type="text/javascript">';
        echo ' alert("Please fill in all fields.")';
        echo '</script>';
    } elseif (!$row) {
        echo '<script type="text/javascript">';
        echo ' alert("Incorrect ID or Password")';
        echo '</script>';
    } else {
        // Start the session and set session variables
        session_start();
        $_SESSION["id"] = $id;
        $_SESSION["password"] = $password;

        // Redirect based on user role
        if ($row['position'] == 'Admin') {
            header("Location: ../home/staffHomepage.php");
        } else {
            header("Location: ../home/MemberHomePage.php");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Login to Your Account</title>
    <link rel="stylesheet" href="Login.css">
</head>
<body>
    <!-- Header for the webpage -->
    <div class="header">
        <h1>Login to Your Account</h1>
    </div>

    <div class="content">
        <!-- Login form -->
        <div class="login-form">
            <form action="login.php" method="post">
                <!-- Input fields for ID and Password -->
                <label for="id">ID:</label><br>
                <input type="text" name="id" required><br><br>
                <label for="password">Password:</label><br>
                <input type="password" name="password" required><br><br>
                <!-- Submit button for logging in -->
                <input type="submit" name="login" value="LOGIN">
            </form>
            
            <br>
           
            <a href="registrationForm.html">Register</a>
               

            <!-- Password recovery information -->
            <p>If you forgot your password, please contact your supervisor.</p>
        </div>
    </div>
    
</body>
</html>
