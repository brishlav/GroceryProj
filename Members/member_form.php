<!-- Aniket Debnath
Edited-01/10/2023 -->
<?php
try {
    $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to SQL Server: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $card_number = trim(stripslashes(htmlspecialchars($_POST['card_number'])));
    $member_name = trim(stripslashes(htmlspecialchars($_POST['member_name'])));
    $phone_number = trim(stripslashes(htmlspecialchars($_POST['phone_number'])));
    $loyalty_points = trim(stripslashes(htmlspecialchars($_POST['loyalty_points'])));

    // Basic validation - ensure fields are not empty
    if(empty($card_number) || empty($member_name) || empty($phone_number) || empty($loyalty_points)) {
        $error_message = "All fields are required.";
    }

    // If validation is successful, proceed to store the data in the database
    if (!isset($error_message)) {
        try {
            $stmt = $conn->prepare("INSERT INTO Members (card_number, member_name, phone_number, loyalty_points) VALUES (:card_number, :member_name, :phone_number, :loyalty_points)");
            $stmt->bindParam(':card_number', $card_number);
            $stmt->bindParam(':member_name', $member_name);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->bindParam(':loyalty_points', $loyalty_points);

            $stmt->execute();
            $success_message = "Application submitted successfully!";
            header('Location: member_display.php');
            exit();
        } catch(PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!-- HTML form for input -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Application Form</title>
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
            padding: 20px;
            background-color: #fff;
            box-shadow: 0px 0px 30px 0px #aaa;
        }
        input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            box-sizing: border-box; /* Added this line */
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        label {
            font-size: 18px;
        }
        .error-message, .success-message {
            text-align: center;
            margin: 20px 0;
            font-size: 18px;
        }
        .error-message {
            color: red;
        }
        .success-message {
            color: green;
        }
    </style>
</head>
<body>
    <?php include "../global/adminNavigation.php" ?>
<div class="container">
    <?php
    if (isset($error_message)) {
        echo '<p class="error-message">' . $error_message . '</p>';
    }

    if (isset($success_message)) {
        echo '<p class="success-message">' . $success_message . '</p>';
    }
    ?>

    <form action="" method="POST">
        <label for="card_number">Card Number:</label>
        <input type="text" id="card_number" name="card_number">

        <label for="member_name">Member Name:</label>
        <input type="text" id="member_name" name="member_name">

        <label for="phone_number">Phone Number:</label>
        <input type="text" id="phone_number" name="phone_number">

        <label for="loyalty_points">Loyalty Points:</label>
        <input type="text" id="loyalty_points" name="loyalty_points"><br>

        <input type="submit" value="Submit">
    </form>
</div>

</body>
</html>
