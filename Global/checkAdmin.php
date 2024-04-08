
<?php
error_log("checkAdmin.php is included");

// Start the session if it hasn't been started yet
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    error_log("Session started");
}

// Include the database connection script
include "db_conn.php";
error_log("Included database connection");

// Check if the session ID exists
if (isset($_SESSION['id'])) {
    error_log("Session ID exists");
    $id = $_SESSION['id'];

    // SQL query to look up the user ID in the accounts table
    $query = "SELECT * FROM accounts WHERE account_id = ?";
    $sqlconn = $conn->prepare($query);
    $sqlconn->execute([$id]);
    $row = $sqlconn->fetch(PDO::FETCH_ASSOC);

    // Check if the account is an admin
    if ($row && $row['position'] == 'Admin') {
        error_log("User is an admin");
        // Admin check passed so no further action needed
    } else {
        error_log("User is not an admin");
        // Not an admin so clear session and redirect
        session_unset();
        session_destroy();
        header("Location: ../login page/login.php");
        exit();
    }
} else {
    error_log("No session ID");
    // No session ID so clear session and redirect
    session_unset();
    session_destroy();
    header("Location: ../login page/login.php");
    exit();
}
?>
