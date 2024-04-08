<!DOCTYPE html>
<html lang="en">
<head>
    <title>Member Dashboard</title>
    <link rel="stylesheet" href="Home.css">
  </head>
<body>
    <header>
        <h1>Member Dashboard</h1>
        <nav>
        <ul>
        <li id="Logout"><a href='../login Page/login.php'>Logout</a></li>
</ul>
</nav>
    </header>
   
<?php
// Include any necessary configuration files or session management for user authentication.

try {
    $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Replace this with the authenticated member's ID
    $memberId = 1;

    // Fetch member's information including loyalty points
    $stmt = $conn->prepare("SELECT member_id, member_name, phone_number, loyalty_points FROM Members WHERE member_id = :memberId");
    $stmt->bindParam(":memberId", $memberId, PDO::PARAM_INT);
    $stmt->execute();
    $memberData = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h2>Welcome, " . $memberData['member_name'] . "</h2>";
    echo "<p><strong>ID:</strong> " . $memberData['member_id'] . "</p>";
    echo "<p><strong>Phone Number:</strong> " . $memberData['phone_number'] . "</p>";
    echo "<p><strong>Loyalty Points:</strong> " . $memberData['loyalty_points'] . "</p>";

    // Fetch the member's orders
    $stmt = $conn->prepare("SELECT sale_id, sale_date, total_cost FROM sales WHERE member_id = :memberId");
    $stmt->bindParam(":memberId", $memberId, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>Orders:</h2>";
    if (!empty($orders)) {
        echo "<ul>";
        foreach ($orders as $order) {
            echo "<li>";
            echo "Sale ID: " . $order['sale_id'] . "<br>";
            echo "Sale Date: " . $order['sale_date'] . "<br>";
            echo "Total Cost: " . $order['total_cost'] . "<br>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No orders found for this member.</p>";
    }

} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
