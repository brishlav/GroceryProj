<!DOCTYPE html>
<html lang="en">

<head>
    <title>Sales Processing</title>
    <link rel="stylesheet" href="Sales.css">
    <script>
        function addItem() {
            const itemsDiv = document.getElementById('items');
            const div = document.createElement('div');
            div.innerHTML = `
            <label for="stock_id[]">Stock ID:</label>
            <input type="text" name="stock_id[]" required><br>

            <label for="quantity[]">Quantity:</label>
            <input type="number" name="quantity[]" required><br>
            `;
            itemsDiv.appendChild(div);
        }
    </script>
</head>

<body>
    <h1>Sales Processing</h1>
    <?php include "../global/adminNavigation.php" ?>
    <nav>
        <ul>
    <li><a href="edit_sale.php">Edit</a></li>
    <li><a href="filter_sale.php">Filter</a></li>
    </ul>
    </nav>
    <?php
    try {
        $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Error connecting to SQL Server: " . $e->getMessage());
    }

    $error = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        function sanitise_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        // Validate Sale ID
        $sale_id = sanitise_input($_POST['sale_id']);
        if (empty($sale_id) || !preg_match('/^\d+$/', $sale_id)) {
            $error .= "Invalid Sale ID.<br>";
        }

        // Validate Member ID
        $member_id = sanitise_input($_POST['member_id']);
        if (empty($member_id) || !preg_match('/^\d+$/', $member_id)) {
            $error .= "Invalid Member ID.<br>";
        }

        // Validate Sale Date
        $sale_date = sanitise_input($_POST['sale_date']);

        // Validate Total Cost
        // $total_cost = sanitise_input($_POST['total_cost']);
        // if (!is_numeric($total_cost) || $total_cost < 0) {
        //     $error .= "Total Cost should be a valid positive number.<br>";
        // }  
        $total_cost = 0;
        // Validate each item in the arrays
        $stock_ids = $_POST['stock_id'];
        $quantities = $_POST['quantity'];
        // $item_costs = $_POST['item_cost'];
    
        foreach ($stock_ids as $index => $stock_id) {
            // Validate Stock ID
            $sanitized_stock_id = sanitise_input($stock_id);
            if (empty($sanitized_stock_id) || !preg_match('/^\d+$/', $sanitized_stock_id)) {
                $error .= "Invalid Stock ID<br>";
                continue; 
            }
            
            // Validate Quantity
            $sanitized_quantity = sanitise_input($quantities[$index]);
            if (!is_numeric($sanitized_quantity) || $sanitized_quantity < 1) {
                $error .= "Quantity should be a valid number greater than 0 at position $index.<br>";
                continue; 
            }
            
            // Validate Item Cost
            // $sanitized_item_cost = sanitise_input($item_costs[$index]);
            // if (!is_numeric($sanitized_item_cost) || $sanitized_item_cost < 0) {
            //     $error .= "Item Cost should be a valid positive number at position $index.<br>";
            // }
        
            
            $item_costs = $conn->prepare("SELECT cost FROM Inventory Where stock_id = :stock_id");
            $item_costs->bindParam(':stock_id', $sanitized_stock_id, PDO::PARAM_INT);
            $item_costs->execute();
            $result = $item_costs->fetch(PDO::FETCH_ASSOC);
            $sanitized_item_cost = $result['cost'];

            $total_item_cost = $sanitized_item_cost * $sanitized_quantity;
            $total_cost = $total_item_cost + $total_cost;
        }

        if (empty($error)) {
            try {
                $conn->beginTransaction();
        
                $stmt = $conn->prepare("INSERT INTO dbo.sales (sale_id, member_id, sale_date, total_cost) VALUES (:sale_id, :member_id, :sale_date, :total_cost)");
                $stmt->bindParam(':sale_id', $sale_id);
                $stmt->bindParam(':member_id', $member_id);
                $stmt->bindParam(':sale_date', $sale_date);
                $stmt->bindParam(':total_cost', $total_cost);
                $stmt->execute();
        
                $stmt2 = $conn->prepare("INSERT INTO dbo.Sale_Items (sale_id, stock_id, quantity, item_cost) VALUES (:sale_id, :stock_id, :quantity, :item_cost)");
        
                $stmt3 = $conn->prepare("UPDATE Inventory SET SOH = SOH - :quantity WHERE stock_id = :stock_id ");
        
                foreach ($stock_ids as $index => $stock_id) {
                    $sanitized_stock_id = (int)sanitise_input($stock_ids[$index]);
                    $sanitized_quantity = (int)sanitise_input($quantities[$index]);
                    // $sanitized_item_cost = sanitise_input($item_costs[$index]);
        
                    // Checking if there is enough SOH
                    $stmt4 = $conn->prepare("SELECT SOH FROM Inventory WHERE stock_id = :stock_id AND SOH >= :quantity");
                    $stmt4->bindParam(':stock_id', $sanitized_stock_id, PDO::PARAM_INT);
                    $stmt4->bindParam(':quantity', $sanitized_quantity, PDO::PARAM_INT);
                    $stmt4->execute();
                    if($stmt4->rowCount() == 0) {
                        $conn->rollBack();
                        echo "<div class='error'>Error: Insufficient stock for item $sanitized_stock_id.</div>";
                        return;
                    }
        
                    // Inserting sale item
                    $stmt2->bindParam(':sale_id', $sale_id);
                    $stmt2->bindParam(':stock_id', $sanitized_stock_id, PDO::PARAM_INT);
                    $stmt2->bindParam(':quantity', $sanitized_quantity, PDO::PARAM_INT);
                    $stmt2->bindParam(':item_cost', $sanitized_item_cost);
                    $stmt2->execute();
        
                    // Updating the Inventory
                    $stmt3->bindParam(':stock_id', $sanitized_stock_id, PDO::PARAM_INT);
                    $stmt3->bindParam(':quantity', $sanitized_quantity, PDO::PARAM_INT);
                    $stmt3->execute();
        
                    // Check if inventory is updated
                    if ($stmt3->rowCount() == 0) {
                        $conn->rollBack();
                        echo "<div class='error'>Error: Inventory update failed for item $sanitized_stock_id.</div>";
                        return;
                    }

                    $points_to_add = intval($total_cost);
                    $stmt5 = $conn->prepare("UPDATE dbo.Members SET loyalty_points = loyalty_points + :points WHERE member_id = :member_id");
                    $stmt5->bindParam(':points', $points_to_add);
                    $stmt5->bindParam(':member_id', $member_id);
                    $stmt5->execute();
                }
        
                $conn->commit();
                echo "Sales successfully added!";
            } catch (PDOException $e) {
                $conn->rollBack();
                echo "<div class='error'>Database error: " . $e->getMessage() . "</div>";
            }
        } else {
            echo "<div class='error'>$error</div>";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] != 'POST' || !empty($error)):
    ?>
<div class="formD">
    <form action="" method="post">
        <label for="sale_id">Sale ID:</label>
        <input type="text" name="sale_id" required><br>

        <label for="member_id">Member ID:</label>
        <input type="text" name="member_id" required><br>

        <label for="sale_date">Sale Date:</label>
        <input type="date" name="sale_date" required><br>

        <!-- <label for="total_cost">Total Cost:</label>
        <input type="number" type="hidden" name="total_cost" required><br> -->

        <div id="items"></div>
        <button type="button" onclick="addItem()">Add Items and details about stock</button><br>

        <input type="submit" value="Submit">
    </form>
    </div>
    <?php endif; ?>
    <h1>Sales</h1>
    <form method="get" action="">
        <label for="sort_by">Sort By:</label>
        <select name="sort_by" id="sort_by">
            <option value="sale_id">Sale ID</option>
            <option value="member_id">Member ID</option>
            <option value="sale_date">Sale Date</option>
            <option value="total_cost" >Total Cost</option>
        </select>
        
        <label for="order">Order:</label>
        <select name="order" id="order">
            <option value="ASC">Ascending</option>
            <option value="DESC">Descending</option>
        </select>
        
        <input type="submit" value="Sort">
    </form>
    <!-- <?php
    try {
        $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM dbo.sales");
        $stmt->execute();

        echo "<table>";
        echo "<tr><th>Sale ID</th><th>Member ID</th><th>Sale Date</th><th>Total Cost</th></tr>";

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr data-id='{$row['sale_id']}'>
                    <td>{$row['sale_id']}</td>
                    <td>{$row['member_id']}</td>
                    <td>{$row['sale_date']}</td>
                    <td>{$row['total_cost']}</td>
                  </tr>";
        }
        
        echo "</table>";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    ?> -->

<?php
    try {
        $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $sort_by = $_GET['sort_by'] ?? 'sale_id'; 
        $order = $_GET['order'] ?? 'ASC'; 
        
        $query = "SELECT s.sale_id, s.member_id, s.sale_date, s.total_cost
                  FROM dbo.sales s
                  ORDER BY $sort_by $order";
                  
        $stmt = $conn->prepare($query);
        $stmt->execute();
        
        echo "<h2>Displaying Results Sorted by $sort_by in $order Order</h2>";
        echo "<table>";
        echo "<tr><th>Sale ID</th><th>Member ID</th><th>Sale Date</th><th>Total Cost</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $saleId = htmlspecialchars($row['sale_id']);
            echo "<tr data-id='$saleId'>";
            echo "<td>" . $saleId . "</td>";
            echo "<td>" . htmlspecialchars($row['member_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['sale_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['total_cost']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (PDOException $e) {
        echo "Error: " . htmlspecialchars($e->getMessage());
    }
    ?>

<script> 
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('tr[data-id]');
        rows.forEach(row => {
            row.addEventListener('dblclick', function() {
                const saleId = this.getAttribute('data-id');
                window.location.href = `./Sale_Items/SalesItems.php?sale_id=${saleId}`;
            });
        });
    });
</script>
</body>

</html>
