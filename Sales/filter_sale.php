<!DOCTYPE html>
<html lang="en">
<head>
    <title>Filter Sales</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 0;
        }
        h1 {
            color: #333366;
            text-align: center;
        }
        form {
            max-width: 600px;
            margin: 20px auto;
        }
        label {
            margin-right: 10px;
            font-weight: bold;
        }
        input[type=checkbox] {
            margin-right: 5px;
        }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #333366;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Sort and Filter Sales</h1>
    <?php include "../global/adminNavigation.php" ?>
    <nav>
        <ul>
    <li><a href="edit_sale.php">Edit</a></li>
    <li><a href="filter_sale.php">Filter</a></li>
    </ul>
    </nav>
    <!-- Form for Sorting and Filtering -->
    <form method="get" action="">
       
        <label>Display Columns:</label>
        <input type="checkbox" name="column[]" value="s.sale_id">Sale ID
        <input type="checkbox" name="column[]" value="member_id">Member ID
        <input type="checkbox" name="column[]" value="stock_id">Stock ID
        <input type="checkbox" name="column[]" value="sale_date">Sale Date
        <input type="checkbox" name="column[]" value="total_cost">Total Cost
        <input type="checkbox" name="column[]" value="quantity">Quantity
        <input type="checkbox" name="column[]" value="item_cost">Item Cost
        
        <input type="submit" value="Display">
    </form>
    <hr>

    <?php
   try {
    // Establishing a connection to the database
    $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Whitelisting allowed columns for display
    $allowed_columns = ['s.sale_id', 'member_id', 'stock_id', 'sale_date', 'total_cost', 'quantity', 'item_cost'];
    
    // Retrieving and validating user inputs for column display
    $columns = isset($_GET['column']) && is_array($_GET['column']) ? $_GET['column'] : ['s.sale_id']; 
    $columns = array_intersect($columns, $allowed_columns);  // Ensure only allowed columns are used
    $columns_sql = implode(", ", $columns);
    
    // Constructing the SQL query
    $query = "SELECT $columns_sql FROM dbo.sales s LEFT JOIN dbo.Sale_Items si ON s.sale_id = si.sale_id";
              
    // Preparing and executing the SQL query
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    // Displaying the results
    echo "<h2>Displaying Results for Columns: " . htmlspecialchars(implode(", ", $columns)) . "</h2>";
    echo "<table>";
    echo "<tr>";
    foreach ($columns as $column) {
        // Display the column name without the table alias for header
        echo "<th>" . htmlspecialchars(preg_replace('/^.*\./', '', $column)) . "</th>";
    }
    echo "</tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($columns as $column) {
            // Use the column name without the table alias for displaying results
            echo "<td>" . htmlspecialchars($row[preg_replace('/^.*\./', '', $column)]) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    // Handling and displaying errors
    echo "Error: " . htmlspecialchars($e->getMessage());
}

    ?>
</body>
</html>
