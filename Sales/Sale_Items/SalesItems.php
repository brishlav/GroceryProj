<!DOCTYPE html>
<html lang="en">

<head>
    <title>Sales</title>
    <link rel="stylesheet" href="SalesItems.css">
</head>



<body>
<header>
<h1>Sale Items</h1>
</header>
<nav>
        <ul>
            <li><a href="../../Home/StaffHomePage.php">Home</a></li>
            <li><a href="../../Members/Member.php">Member</a></li>
            <li><a href="../../Inventory/Inventory_display.php">Inventory</a></li>
            <li><a href="../../Sales/Sales.php">Sale</a></li>
            <li><a href="../../Report/Report.php">Report</a></li>
            <li id="Logout"><a href='../../login Page/login.php'>Logout</a></li>
        </ul>
</nav>
<h1>Sale Items</h1>

<?php
try {
    $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $key = $_GET['sale_id'];
    $stmt = $conn->prepare("SELECT * FROM dbo.Sale_Items where sale_id = $key");
    $stmt->execute();

    echo "<table>";
    echo "<tr><th>Sale ID</th><th>Stock ID</th><th>Quantity</th><th>Item Cost</th></tr>";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
                <td>{$row['sale_id']}</td>
                <td>{$row['stock_id']}</td>
                <td>{$row['quantity']}</td>
                <td>{$row['item_cost']}</td>
            </tr>";
    }

    echo "</table>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

</body>

</html>