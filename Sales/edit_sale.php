<!DOCTYPE html>
<html lang="en">

<head>
    <title>Edit Sale</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 0;
        }
        h1, h2 {
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
        input[type=text], input[type=number], input[type=date] {
            width: 100%;
            padding: 8px;
            margin: 5px 0 22px 0;
            display: inline-block;
            border: none;
            background: #f1f1f1;
        }
        input[type=submit], button {
            background-color: #333366;
            color: white;
            padding: 12px 20px;
            border: none;
            cursor: pointer;
        }
        input[type=submit]:hover, button:hover {
            opacity: 0.8;
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
        .formD{
            max-width: 600px;
            margin: 20px auto;

        }
        .errMsg{

        color: red; 
         font-size: 2em; 
         text-align: center
        }
    </style>
    <script>
        function addItem() {
            const itemsDiv = document.getElementById('items');
            const div = document.createElement('div');
            div.innerHTML = `
            <label for="edit_stock_id[]">Stock ID:</label>
            <input type="text" name="edit_stock_id[]" required><br>

            <label for="edit_quantity[]">Quantity:</label>
            <input type="number" name="edit_quantity[]" required><br>

            <label for="edit_item_cost[]">Item Cost:</label>
            <input type="number" name="edit_item_cost[]" required><br>`;
            itemsDiv.appendChild(div);
        }
    </script>
</head>

<body>
    <h1>Edit Sale</h1>
    <?php include "../global/adminNavigation.php" ?>
    <nav>
        <ul>
    <li><a href="edit_sale.php">Edit</a></li>
    <li><a href="filter_sale.php">Filter</a></li>
    </ul>
    </nav>
    <?php
    // Database connection
    try {
        $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Error connecting to SQL Server: " . $e->getMessage());
    }
    $error = "";

    
        // Prepare the SQL statement with a JOIN to get data from both tables
        function displayTable($conn, $header) {
            echo "<h2>$header</h2>";
            echo "<table border='1'>";
            echo "<tr><th>Sale ID</th><th>Member ID</th><th>Sale Date</th><th>Total Cost</th><th>Stock ID</th><th>Quantity</th></tr>";
        
            // Prepare the SQL statement with a JOIN to get data from both tables
            $stmt = $conn->prepare("
                SELECT 
                    s.sale_id, 
                    s.member_id, 
                    s.sale_date, 
                    s.total_cost, 
                    si.stock_id, 
                    si.quantity 
                FROM 
                    dbo.sales s
                JOIN 
                    dbo.sale_items si 
                ON 
                    s.sale_id = si.sale_id 
            ");
            
            try {
                // Execute the statement
                $stmt->execute();
                
                // Fetch all the rows from the executed query
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Loop through each row and output the table data
                foreach ($rows as $row) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['sale_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['member_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['sale_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['total_cost']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['stock_id']) . "</td>"; // Stock ID from sale_items
                    echo "<td>" . htmlspecialchars($row['quantity']) . "</td>"; // Quantity from sale_items
                    echo "</tr>";
                }
                echo "</table>";
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    
    
    // Display initial sales table
    displayTable($conn, "SELECT * FROM dbo.sales", "Sales Before Update");

 


// Handling POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sale_id = $_POST['sale_id'];
    $member_id = $_POST['member_id'];
    $sale_date = $_POST['sale_date'];
    $total_cost = $_POST['total_cost'];
    
    $stock_ids = $_POST['edit_stock_id'];
    $quantities = $_POST['edit_quantity'];
    $item_costs = $_POST['edit_item_cost'];
    $old_sohs = [];
    $new_sohs = [];

    function sanitise_input($data){

        $data=trim($data);
        $data=stripslashes($data);
        $data=htmlspecialchars($data);
        return $data;
      }


    $error = ""; // Initialize error as an empty string

    $sale_id = sanitise_input($_POST['sale_id']);
    $member_id = sanitise_input($_POST['member_id']);
     $total_cost = sanitise_input($_POST['total_cost']);
     $sale_date =sanitise_input($_POST['sale_date']);
    $only_numbers_regex = "/^\d+$/";

 

    if (!preg_match($only_numbers_regex, $sale_id) || !preg_match($only_numbers_regex, $member_id) || !preg_match($only_numbers_regex, $total_cost)) {
        $error .= "Sale ID, Member ID, and Total Cost must be numbers.<br>";
    }
    
    // Validate date format
        $date_format = "/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/" ; // ddmmyyyy
    if (!preg_match($date_format, $sale_date)) {
        $error .= "Sale Date must be in the format DD-MM-YYYY.<br>";
        echo"$sale_date";
    }
    
    // Validate stock IDs and quantities
    foreach ($stock_ids as $index => $stock_id) {
        $stock_id = sanitise_input($stock_id);
        $quantity = sanitise_input($quantities[$index]);
        
        if (!preg_match($only_numbers_regex, $stock_id) || !preg_match($only_numbers_regex, $quantity)) {
            $error .= "Stock ID and Quantity must be numbers.<br>";
        }
    }

    if (empty($error)) {
     
        try {
            // Check if the Sale ID exists
            $stmt = $conn->prepare("SELECT * FROM dbo.sales WHERE sale_id = :sale_id");
            $stmt->bindParam(':sale_id', $sale_id);
            $stmt->execute();
            $sale = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$sale) {
                $error .= "Sale ID {$sale_id} does not exist.<br>";
            }
    
            // Check if the Member ID exists
            $stmt = $conn->prepare("SELECT * FROM dbo.members WHERE member_id = :member_id");
            $stmt->bindParam(':member_id', $member_id);
            $stmt->execute();
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$member) {
                $error .= "Member ID {$member_id} does not exist.<br>";
            }
    
            // Check if each Stock ID exists
            foreach ($stock_ids as $stock_id) {
                $stmt = $conn->prepare("SELECT * FROM dbo.Inventory WHERE stock_id = :stock_id");
                $stmt->bindParam(':stock_id', $stock_id);
                $stmt->execute();
                $stock = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if (!$stock) {
                    $error .= "Stock ID {$stock_id} does not exist.<br>";
                }
            }
    
        } catch (Exception $e) {
            
            $error .= "An error occurred: " . $e->getMessage() . "<br>";
        }
       
    }
  
    

        // If there are no errors, proceed with the queries and table display
        if (empty($error)) {
           
            try {
                $conn->beginTransaction();
            
                // Fetch old SOH for all items in the sale
        foreach ($stock_ids as $stock_id) {
            $stmt = $conn->prepare("SELECT SOH FROM dbo.Inventory WHERE stock_id = :stock_id");
            $stmt->bindParam(':stock_id', $stock_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $old_sohs[$stock_id] = $result['SOH'];
        }
                // Update sale details
                $stmt = $conn->prepare("UPDATE dbo.sales SET member_id = :member_id, sale_date = :sale_date, total_cost = :total_cost WHERE sale_id = :sale_id");
                $stmt->bindParam(':sale_id', $sale_id);
                $stmt->bindParam(':member_id', $member_id);
                $stmt->bindParam(':sale_date', $sale_date);
                $stmt->bindParam(':total_cost', $total_cost);
                $stmt->execute();
            
                // Initialize temporary quantity
                $tempQuantity = 0;
            
                // Update sale items and SOH levels
                foreach ($stock_ids as $index => $stock_id) {
                    $quantity = $quantities[$index];
                    $item_cost = $item_costs[$index];
            
                    // Check if this item already exists in the sale_items table
                    $stmt = $conn->prepare("SELECT quantity FROM dbo.sale_items WHERE sale_id = :sale_id AND stock_id = :stock_id");
                    $stmt->bindParam(':sale_id', $sale_id);
                    $stmt->bindParam(':stock_id', $stock_id);
                    $stmt->execute();
                    $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
            
                    if ($existing_item) {
                        // This item exists, so we update it
                        $original_quantity = $existing_item['quantity'];
            
                        // Update sale_items
                        $stmt = $conn->prepare("UPDATE dbo.sale_items SET quantity = :quantity, item_cost = :item_cost WHERE sale_id = :sale_id AND stock_id = :stock_id");
                        $stmt->bindParam(':sale_id', $sale_id);
                        $stmt->bindParam(':stock_id', $stock_id);
                        $stmt->bindParam(':quantity', $quantity);
                        $stmt->bindParam(':item_cost', $item_cost);
                        $stmt->execute();
            
                        // Calculate the difference between the new and original quantity
                        $quantity_difference = $quantity - $original_quantity;
                    } else {
                        // This item doesn't exist, so we insert a new record
                        $stmt = $conn->prepare("INSERT INTO dbo.sale_items (sale_id, stock_id, quantity, item_cost) VALUES (:sale_id, :stock_id, :quantity, :item_cost)");
                        $stmt->bindParam(':sale_id', $sale_id);
                        $stmt->bindParam(':stock_id', $stock_id);
                        $stmt->bindParam(':quantity', $quantity);
                        $stmt->bindParam(':item_cost', $item_cost);
                        $stmt->execute();
            
                        // The quantity difference is the quantity itself since it's a new item
                        $quantity_difference = $quantity;
                    }
            
                    // Update SOH levels in stock table based on the quantity difference
                    if ($quantity_difference > 0) {
                        // If the new quantity is greater, subtract the difference from SOH
                        $stmt = $conn->prepare("UPDATE dbo.Inventory SET SOH = SOH - :quantity_difference WHERE stock_id = :stock_id");
                        $stmt->bindParam(':quantity_difference', $quantity_difference);
                        $stmt->bindParam(':stock_id', $stock_id);
                        $stmt->execute();
                    } elseif ($quantity_difference < 0) {
                        // If the new quantity is less, add the difference to SOH (making the negative difference positive)
                        $quantity_difference = abs($quantity_difference);
                        $stmt = $conn->prepare("UPDATE dbo.Inventory SET SOH = SOH + :quantity_difference WHERE stock_id = :stock_id");
                        $stmt->bindParam(':quantity_difference', $quantity_difference);
                        $stmt->bindParam(':stock_id', $stock_id);
                        $stmt->execute();
                    }
                    // If the quantity_difference is 0, no need to update SOH
                }
    
                foreach ($stock_ids as $stock_id) {
                    $stmt = $conn->prepare("SELECT SOH FROM dbo.Inventory WHERE stock_id = :stock_id");
                    $stmt->bindParam(':stock_id', $stock_id);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $new_sohs[$stock_id] = $result['SOH'];
                }
            
                $conn->commit();
                echo "Sale and items updated successfully!";
            } catch (PDOException $e) {
                $conn->rollBack();
                echo "Error: " . $e->getMessage();
            }
            displayTable($conn, "SELECT * FROM dbo.sales", "Sales After Update");

            // Query to fetch current SOH details from Inventory table
            $stmt = $conn->prepare("SELECT stock_id, SOH FROM dbo.Inventory");
            $stmt->execute();

            // Fetch the results
            $inventoryDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

            
            echo "<table>";
            echo "<tr><th>Stock ID</th><th>Old SOH</th><th>New SOH</th></tr>";
            foreach ($stock_ids as $stock_id) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($stock_id) . "</td>";
                echo "<td>" . htmlspecialchars($old_sohs[$stock_id]) . "</td>";
                echo "<td>" . htmlspecialchars($new_sohs[$stock_id]) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<div class='errMsg'>The following errors occurred:<br>
            $error</div>"; }

    
}
      
    ?>
 <div class="formD">
    <form action="" method="post">
        <label for="sale_id">Sale ID:</label>
        <input type="text" name="sale_id" required><br>

        <label for="member_id">Member ID:</label>
        <input type="text" name="member_id" required><br>

        <label for="sale_date">Sale Date:</label>
        <input type="date" name="sale_date" placeholder="dd-mm-yyyy" required><br>

        <label for="total_cost">Total Cost:</label>
        <input type="number" name="total_cost" required><br>

        <h2>Edit Sale Items</h2>
        <div id="items">
            
            <div class="item">
                <label for="edit_stock_id[]">Stock ID:</label>
                <input type="text" name="edit_stock_id[]" required><br>

                <label for="edit_quantity[]">Quantity:</label>
                <input type="number" name="edit_quantity[]" required><br>

                <label for="edit_item_cost[]">Item Cost:</label>
                <input type="number" name="edit_item_cost[]" required><br>
            </div>
        </div>
        <button type="button" onclick="addItem()">Add Item</button><br>
        <input type="submit" value="Update Sale">
    </form>
</div>
</body>

</html>