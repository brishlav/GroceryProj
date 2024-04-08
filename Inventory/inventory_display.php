<?php
// Database connection
// (as before, ideally this would be separated into a config or utility file)
try {
    $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to SQL Server: " . $e->getMessage());
}

$query = "SELECT stock_id, item_name, description, SOH, low_threshold, cost, category FROM dbo.Inventory";
$items = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Stock Items</title>
    <style>
        header {
    background-color: #333;
    color: #fff;
    padding: 10px 0;
    text-align: center;
}

nav {
    background-color: #444;
}

nav ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
    display: flex;
    justify-content: center;
}

nav li {
    margin: 0 15px;
}

nav a {
    text-decoration: none;
    color: #fff;
    font-weight: bold;
}

        
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        h2 {
            text-align: center;
        }
		
		h3 {
			text-align: center;
		}

        table {
            border-collapse: collapse;
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        a {
            text-decoration: none;
            color: #333;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
    
</head>

<body>

<h2>Stock Items</h2>
        <!-- import global navigation -->
        <?php include "../global/adminNavigation.php" ?>
<h3><a href="inventory_add.php" style="display: inline-block; background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; margin-bottom: 20px; border-radius: 4px;">Add New Stock</a></h3>


<table>
    <tr>
        <th>Stock ID</th>
        <th>Item Name</th>
        <th>Description</th>
        <th>SOH</th>
        <th>Low Threshold</th>
        <th>Cost</th>
        <th>Category</th>
    </tr>
    <?php
    while ($item = $items->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td><a href='inventory_edit.php?stock_id=" . $item['stock_id'] . "'>" . $item['stock_id'] . "</a></td>";
        echo "<td>" . $item['item_name'] . "</td>";
        echo "<td>" . $item['description'] . "</td>";
        echo "<td>" . $item['SOH'] . "</td>";
        echo "<td>" . $item['low_threshold'] . "</td>";
        echo "<td>" . $item['cost'] . "</td>";
        echo "<td>" . $item['category'] . "</td>";
        echo "</tr>";
    }
    ?>
</table>

</body>
</html>
