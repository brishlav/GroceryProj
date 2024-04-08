<?php
// Connection to your database
try {
    $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to SQL Server: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch the form data
    $member_id = $_POST['member_id'];
    $card_number = $_POST['card_number'];
    $member_name = $_POST['member_name'];
    $phone_number = $_POST['phone_number'];
    $loyalty_points = $_POST['loyalty_points'];

    try {
        // Update the member in the database
        $stmt = $conn->prepare("UPDATE Members SET card_number = :card_number, member_name = :member_name, phone_number = :phone_number, loyalty_points = :loyalty_points WHERE member_id = :member_id");
        $stmt->bindParam(':member_id', $member_id);
        $stmt->bindParam(':card_number', $card_number);
        $stmt->bindParam(':member_name', $member_name);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':loyalty_points', $loyalty_points);
        $stmt->execute();

        echo "Member updated successfully.";
		// Redirect to index.php after successful update
        header('Location: member_display.php');
        exit();
    } catch (PDOException $e) {
        echo "Error updating member: " . $e->getMessage();
    }
} else {
    // Fetch the existing member details
    $member_id = $_GET['member_id'];

    $stmt = $conn->prepare("SELECT * FROM Members WHERE member_id = :member_id");
    $stmt->bindParam(':member_id', $member_id);
    $stmt->execute();
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member</title>
	<style>
	        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 18px;
            text-align: left;
            box-shadow: 0px 0px 30px 0px #aaa;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .error-message, .search-box {
            text-align: center;
            margin: 20px;
        }
        input[type="text"] {
            padding: 10px;
            border: none;
            border-radius: 5px;
        }
        input[type="submit"] {
            padding: 10px 20px;
            border: none;
            color: #fff;
            background-color: #4CAF50;
            cursor: pointer;
            border-radius: 5px;
        }
        h1 {
            font-size: 2em;
            color: #333;
            text-align: center;
        }
        .sort-link {
            color: #000;
            text-decoration: none;
            padding-left: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Edit Member</h1>
    <form action="" method="POST">
        <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($member['member_id']); ?>">
        <label for="card_number">Card Number:</label>
        <input type="text" name="card_number" value="<?php echo htmlspecialchars($member['card_number']); ?>" required><br>
        <label for="member_name">Member Name:</label>
        <input type="text" name="member_name" value="<?php echo htmlspecialchars($member['member_name']); ?>" required><br>
        <label for="phone_number">Phone Number:</label>
        <input type="text" name="phone_number" value="<?php echo htmlspecialchars($member['phone_number']); ?>" required><br>
        <label for="loyalty_points">Loyalty Points:</label>
        <input type="number" name="loyalty_points" value="<?php echo htmlspecialchars($member['loyalty_points']); ?>" required><br>
        <input type="submit" value="Update Member">
    </form>
</div>
</body>
</html>
