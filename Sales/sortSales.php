<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sort Sales</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #f9f9f9;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #b33230;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        h1 {
            font-size: 2em;
            color: #333;
        }
        h2 {
            font-size: 1.5em;
            color: #9e1705;
        }
        form {
            margin-bottom: 20px;
        }
        label, select, input {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <h1>Sort Sales</h1>
    <form method="get" action="">
        <label for="sort_by">Sort By:</label>
        <select name="sort_by" id="sort_by">
            <option value="sale_id">Sale ID</option>
            <option value="member_id">Member ID</option>
            <option value="stock_id">Stock ID</option>
        </select>
        
        <label for="order">Order:</label>
        <select name="order" id="order">
            <option value="ASC">Ascending</option>
            <option value="DESC">Descending</option>
        </select>
        
        <input type="submit" value="Sort">
    </form>
    <hr>

    <?php
    try {
        $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $sort_by = $_GET['sort_by'] ?? 'sale_id'; 
        $order = $_GET['order'] ?? 'ASC'; 
        
        $query = "SELECT s.sale_id, s.member_id, s.sale_date, s.total_cost, si.stock_id, si.quantity, si.item_cost
                  FROM dbo.sales s
                  LEFT JOIN dbo.Sale_Items si ON s.sale_id = si.sale_id
                  ORDER BY $sort_by $order";
                  
        $stmt = $conn->prepare($query);
        $stmt->execute();
        
        echo "<h2>Displaying Results Sorted by $sort_by in $order Order</h2>";
        echo "<table>";
        echo "<tr><th>Sale ID</th><th>Member ID</th><th>Sale Date</th><th>Total Cost</th><th>Stock ID</th><th>Quantity</th><th>Item Cost</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['sale_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['member_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['sale_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['total_cost']) . "</td>";
            echo "<td>" . htmlspecialchars($row['stock_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
            echo "<td>" . htmlspecialchars($row['item_cost']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (PDOException $e) {
        echo "Error: " . htmlspecialchars($e->getMessage());
    }
    ?>
</body>
</html>
