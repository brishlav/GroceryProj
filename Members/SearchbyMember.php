<!DOCTYPE html>
<html lang="en">
<head>
    <title>Search Sales by Member ID</title>
</head>
<body>
    <h1>Search Sales by Member ID</h1>
    <form method="get" action="">
        <label for="member_id">Member ID:</label>
        <input type="text" name="member_id" id="member_id" value="<?php echo isset($_GET['member_id']) ? htmlspecialchars($_GET['member_id']) : ''; ?>">
        <input type="submit" value="Search">
    </form>
    <hr>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['member_id']) && !empty($_GET['member_id'])) {
        try {
            $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
            $member_id = $_GET['member_id'];
            $like_member_id = '%' . $member_id . '%'; 

            $salesQuery = "SELECT * FROM dbo.sales WHERE member_id LIKE :member_id ORDER BY sale_id";
            $stmtSales = $conn->prepare($salesQuery);
            $stmtSales->bindParam(':member_id', $like_member_id, PDO::PARAM_STR);
            $stmtSales->execute();

            while ($sale = $stmtSales->fetch(PDO::FETCH_ASSOC)) {
                echo "Sale ID: " . htmlspecialchars($sale['sale_id']) . "<br>";
                echo "Member ID: " . htmlspecialchars($sale['member_id']) . "<br>";
                echo "Sale Date: " . htmlspecialchars($sale['sale_date']) . "<br>";
                echo "Total Cost: " . htmlspecialchars($sale['total_cost']) . "<br>";

                $saleItemsQuery = "SELECT * FROM dbo.Sale_Items WHERE sale_id = :sale_id";
                $stmtItems = $conn->prepare($saleItemsQuery);
                $stmtItems->bindParam(':sale_id', $sale['sale_id'], PDO::PARAM_STR);
                $stmtItems->execute();

                echo "Items: <br>";
                while ($item = $stmtItems->fetch(PDO::FETCH_ASSOC)) {
                    echo "Stock ID: " . htmlspecialchars($item['stock_id']) . "<br>";
                    echo "Quantity: " . htmlspecialchars($item['quantity']) . "<br>";
                    echo "Item Cost: " . htmlspecialchars($item['item_cost']) . "<br><br>";
                }
                echo "<hr>";
            }
        } catch (PDOException $e) {
            echo "Error: " . htmlspecialchars($e->getMessage());
        }
    }
    ?>
</body>
</html>
