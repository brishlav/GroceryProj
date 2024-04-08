<?php
function logQuery($query){
    include "db_conn.php";
    $ip = $_SERVER['REMOTE_ADDR']; 
    $user = "";
    $dateTime = date("Y-m-d H:i:s"); 

    // Start the session if it hasn't been started yet
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user ID exists in session
    if (isset($_SESSION['id'])) {
        $user = $_SESSION['id'];
    }
   // Prepare the SQL statement for inserting log data
   $sql = "INSERT INTO logs (userID, dateTime, IP, query) VALUES (:user, :dateTime, :ip, :query)";
   $sqlcon = $conn->prepare($sql);

   // Bind the parameters
   $sqlcon->bindParam(':user', $user);
   $sqlcon->bindParam(':dateTime', $dateTime);
   $sqlcon->bindParam(':ip', $ip);
   $sqlcon->bindParam(':query', $query);

   // Execute the statement
   $sqlcon->execute();
}
?>


