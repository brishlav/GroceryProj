<?php
try {
    $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to SQL Server: " . $e->getMessage());
}

$stock_id = $_GET['stock_id'] ?? null;
$item = null;

if (!$stock_id) {
    die("Invalid stock ID provided.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        // Handle delete action
        $stmt = $conn->prepare("DELETE FROM dbo.Inventory WHERE stock_id = :stock_id");
        $stmt->bindParam(':stock_id', $stock_id);
        $stmt->execute();

        header("Location: inventory_display.php");
        exit;
    } elseif (isset($_POST['update'])) {
        // Handle update action
        $stmt = $conn->prepare("UPDATE dbo.Inventory SET item_name = :item_name, description = :description, SOH = :SOH, low_threshold = :low_threshold, cost = :cost, category = :category WHERE stock_id = :stock_id");

        $stmt->bindParam(':stock_id', $stock_id);
        $stmt->bindParam(':item_name', $_POST['item_name']);
        $stmt->bindParam(':description', $_POST['description']);
        $stmt->bindParam(':SOH', $_POST['SOH']);
        $stmt->bindParam(':low_threshold', $_POST['low_threshold']);
        $stmt->bindParam(':cost', $_POST['cost']);
        $stmt->bindParam(':category', $_POST['category']);

        $stmt->execute();

        header("Location: inventory_display.php");
        exit;
    }
}

// Fetch the stock details
$stmt = $conn->prepare("SELECT * FROM dbo.Inventory WHERE stock_id = :stock_id");
$stmt->bindParam(':stock_id', $stock_id);
$stmt->execute();
$item = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!-- The HTML and form remain largely unchanged from the previous version -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- import global navigation -->
    <?php include "../global/adminNavigation.php" ?>
    <title>Edit Stock</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        h2 {
            text-align: center;
        }

        form {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
            width: 80%;
            margin: 20px auto;
        }

        label, input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<h2>Edit Stock Item</h2>

<?php if ($item): ?>
<form method="POST" action="">

    <!-- Display the stock details -->
    <label for="stock_id">Stock ID:</label>
    <input type="text" id="stock_id" value="<?php echo $item['stock_id']; ?>" disabled><br>

    <label for="item_name">Item Name:</label>
    <input type="text" id="item_name" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>"><br>

    <label for="description">Description:</label>
    <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($item['description']); ?>"><br>

    <label for="SOH">SOH:</label>
    <input type="text" id="SOH" name="SOH" value="<?php echo $item['SOH']; ?>"><br>

    <label for="low_threshold">Low Threshold:</label>
    <input type="text" id="low_threshold" name="low_threshold" value="<?php echo $item['low_threshold']; ?>"><br>

    <label for="cost">Cost:</label>
    <input type="text" id="cost" name="cost" value="<?php echo $item['cost']; ?>"><br>

    <label for="category">Category:</label>
    <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($item['category']); ?>"><br>

    <input type="submit" name="update" value="Update">
    <input type="submit" name="delete" value="Delete" onclick="return confirm('Are you sure you want to delete this item?')">

</form>
<?php else: ?>
<p>Item not found.</p>
<?php endif; ?>

</body>
</html>