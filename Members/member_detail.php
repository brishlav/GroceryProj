<?php
// Connect to the database
try {
    $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to SQL Server: " . $e->getMessage());
}

// Fetch the member details
$member_id = $_GET['member_id'];
$stmt = $conn->prepare("SELECT * FROM Members WHERE member_id = :member_id");
$stmt->bindParam(':member_id', $member_id);
$stmt->execute();
$member = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $stmt = $conn->prepare("DELETE FROM Members WHERE member_id = :member_id");
    $stmt->bindParam(':member_id', $member_id);
    $stmt->execute();
    
    echo "Member deleted successfully.";
    // Redirect to the members list page after deleting.
    header("Location: member_display.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member Details</title>
     
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
    <h1>Member Details</h1>
   <!-- import global navigation -->
   <?php include "../global/adminNavigation.php" ?>
    <?php
    // Check if a member is found
    if ($member) {
        // Display member details and provide options to edit or delete
        echo 'Member Name: ' . htmlspecialchars($member['member_name']) . '<br>';
        echo 'Card Number: ' . htmlspecialchars($member['card_number']) . '<br>';
        echo 'Phone Number: ' . htmlspecialchars($member['phone_number']) . '<br>';
        echo 'Loyalty Points: ' . htmlspecialchars($member['loyalty_points']) . '<br>';
        
        // Link to edit page
        echo '<a href="member_edit.php?member_id=' . htmlspecialchars($member['member_id']) . '"><button type="button">Edit</button></a><br>';
        
        // Delete form
        echo '<form method="POST" action="">';
        echo '<input type="submit" name="delete" value="Delete" onclick="return confirm(\'Are you sure you want to delete this member?\');">';
        echo '</form>';
    } else {
        echo "Member not found.";
    }
    ?>
</div>

</body>
</html>

