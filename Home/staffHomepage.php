<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="Home.css">
  </head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
    </header>

    <?php include "../global/adminNavigation.php"; ?>
    <?php
    try {
        $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Total Sales for Today
        $stmt = $conn->query("SELECT SUM(total_cost) as total_sales_today FROM sales WHERE sale_date = CAST(GETDATE() AS DATE)");
        $total_sales_today = $stmt->fetchColumn();

        // Average Sales for the Last 7 Days
        $stmt = $conn->query("SELECT AVG(total_cost) as avg_sales FROM sales WHERE sale_date BETWEEN CAST(GETDATE() - 7 AS DATE) AND CAST(GETDATE() AS DATE)");
        $avg_sales_last_7_days = $stmt->fetchColumn();

        // Total Number of Members
        $stmt = $conn->query("SELECT COUNT(*) FROM Members");
        $total_members = $stmt->fetchColumn();


        echo "<h2>Total Sales Today: $ $total_sales_today</h2>";
        echo "<h2>Average Sales for the Last 7 Days: $ $avg_sales_last_7_days</h2>";
        echo "<h2>Total Number of Members: $total_members</h2>";
        echo "<h2>Items Below Threshold</h2>";
        

        
        // Items Below Threshold
        $stmt = $conn->query("SELECT stock_id, item_name, SOH, low_threshold FROM Inventory WHERE SOH < low_threshold");
        $items_below_threshold = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($items_below_threshold) > 0) {
            echo "<ul>";
            foreach ($items_below_threshold as $item) {
                $item_name = $item['item_name'];
                $soh = $item['SOH'];
                $low_threshold = $item['low_threshold'];

                echo "<li>Item Name: $item_name, Stock On Hand: $soh, Low Threshold: $low_threshold</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>All items are above their respective low thresholds.</p>";
        }

      
// Prepare and execute SQL query to predict stock depletion
$stmt = $conn->query(
  // Select item name, calculate average daily sales, and get the current stock (SOH)
  "SELECT Inventory.item_name, AVG(Sale_Items.quantity) AS avg_daily_sales, Inventory.SOH " .

  // Join Sale_Items table with sales table where sale_id matches
  "FROM Sale_Items " .
  "JOIN sales ON Sale_Items.sale_id = sales.sale_id " .

  // Further join with Inventory table where stock IDs match
  "JOIN Inventory ON Sale_Items.stock_id = Inventory.stock_id " .

  // Filter records to only include sales in the last 7 days
  "WHERE sale_date BETWEEN CAST(GETDATE() - 7 AS DATE) AND CAST(GETDATE() AS DATE) " .

  // Group results by item_name and SOH for a summarized view
  "GROUP BY Inventory.item_name, Inventory.SOH"
);


$predictions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Predictions for Stock Running Out</h2>";

foreach($predictions as $result) {
    $item_name = $result['item_name'];
    $avg_daily_sales = $result['avg_daily_sales'];
    $current_stock = $result['SOH'];

    $days_left = 0;
    if ($avg_daily_sales > 0) {
        $days_left = floor($current_stock / $avg_daily_sales);
    }

    echo "<p>Item Name: $item_name, Days left before running out: $days_left</p>";
}

    } catch (PDOException $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
    ?>


</body>
</html>