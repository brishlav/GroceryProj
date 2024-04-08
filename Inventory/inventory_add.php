<?php
try {
    $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to SQL Server: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = trim(stripslashes(htmlspecialchars($_POST['item_name'])));
    $description = trim(stripslashes(htmlspecialchars($_POST['description'])));
    $SOH = trim(stripslashes(htmlspecialchars($_POST['SOH'])));
    $low_threshold = trim(stripslashes(htmlspecialchars($_POST['low_threshold'])));
    $cost = trim(stripslashes(htmlspecialchars($_POST['cost'])));
    $category = trim(stripslashes(htmlspecialchars($_POST['category'])));

    if(empty($item_name) || empty($description) || empty($SOH) || empty($low_threshold) || empty($cost) || empty($category)) {
        $error_message = "All fields are required.";
    } elseif(!filter_var($SOH, FILTER_VALIDATE_INT, array("options" => array("min_range"=>0)))) {
        $error_message = "Invalid SOH. Please enter a positive integer.";
    } elseif(!filter_var($low_threshold, FILTER_VALIDATE_INT, array("options" => array("min_range"=>0)))) {
        $error_message = "Invalid low threshold. Please enter a positive integer.";
    } elseif(!filter_var($cost, FILTER_VALIDATE_FLOAT)) {
        $error_message = "Invalid cost. Please enter a valid number.";
    }

    if (!isset($error_message)) {
        try {
            $stmt = $conn->prepare("INSERT INTO dbo.Inventory (item_name, description, SOH, low_threshold, cost, category) VALUES (:item_name, :description, :SOH, :low_threshold, :cost, :category)");
            $stmt->bindParam(':item_name', $item_name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':SOH', $SOH);
            $stmt->bindParam(':low_threshold', $low_threshold);
            $stmt->bindParam(':cost', $cost);
            $stmt->bindParam(':category', $category);

            $stmt->execute();
            $success_message = "Stock item added successfully!";
        } catch(PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
		header("Location: inventory_display.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Stock Item</title>
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

        .error {
            color: red;
        }

        .success {
            color: green;
        }
    </style>
</head>
<body>

<h2>Add Stock Item</h2>
<?php include "../global/adminNavigation.php"; ?>
<?php
if (isset($error_message)) {
    echo '<p class="error">' . $error_message . '</p>';
}

if (isset($success_message)) {
    echo '<p class="success">' . $success_message . '</p>';
}
?>

<form action="" method="POST">
    <label for="item_name">Item Name:</label>
    <input type="text" id="item_name" name="item_name">

    <label for="description">Description:</label>
    <input type="text" id="description" name="description">

    <label for="SOH">Stock On Hand (SOH):</label>
    <input type="text" id="SOH" name="SOH">

    <label for="low_threshold">Low Threshold:</label>
    <input type="text" id="low_threshold" name="low_threshold">

    <label for="cost">Cost:</label>
    <input type="text" id="cost" name="cost">

    <label for="category">Category:</label>
    <input type="text" id="category" name="category"><br><br>

    <input type="submit" value="Add Item">
</form>

</body>
</html>
