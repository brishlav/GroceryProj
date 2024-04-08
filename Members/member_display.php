<!--Aniket Debnath
Edited-25/09/2023-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Members</title>
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
        button{
            padding: 10px 20px;
            border: none;
            color: #fff;
            background-color: #4CAF50;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Members List</h1>
    <!-- import global navigation -->
    <?php include "../global/adminNavigation.php" ?>
    <button onclick="location.href='member_form.php'">Add Member</button>
    <div class="search-box">
        <form action="" method="GET">
            <input type="text" name="search" placeholder="Search by member name...">
            <select name="filter">
                <option value="">Filter by Points</option>
                <option value="0">1-100</option>
                <option value="100">101-200</option>
            </select>
            <input type="submit" value="Apply">
        </form>
    </div>

    <?php
    try {
        $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Error connecting to SQL Server: " . $e->getMessage());
    }

try {
    $searchQuery = "";
    $filterQuery = "";
    $orderQuery = "";

    // Check if search parameter is set and not empty
    if (isset($_GET['search']) && $_GET['search'] !== "") {
        $search = $_GET['search'];
        $searchQuery = " WHERE member_name LIKE :search"; // search in member_name column
    }

    if (isset($_GET['filter']) && $_GET['filter'] !== "") {
        $filterValue = (int)$_GET['filter'];
        $filterUpperLimit = $filterValue + 100;
        // Check if $searchQuery is not empty, then use AND otherwise use WHERE
        $filterQuery = ($searchQuery !== "" ? " AND" : " WHERE") . " loyalty_points > :filterValue AND loyalty_points <= :filterUpperLimit";
    }

    if (isset($_GET['sort'])) {
        $sort = $_GET['sort'];
        $order = isset($_GET['order']) && strtolower($_GET['order']) == 'desc' ? 'DESC' : 'ASC';
        $orderQuery = " ORDER BY $sort $order";
        $nextOrder = $order == 'ASC' ? 'desc' : 'asc';
    }

    $stmt = $conn->prepare("SELECT * FROM Members" . $searchQuery . $filterQuery . $orderQuery);

    // Bind the search parameter if $searchQuery is not empty
    if (!empty($searchQuery)) {
        $search = "%" . $search . "%"; // add % for LIKE clause
        $stmt->bindParam(':search', $search, PDO::PARAM_STR);
    }

    if (!empty($filterQuery)) {
        $stmt->bindParam(':filterValue', $filterValue, PDO::PARAM_INT);
        $stmt->bindParam(':filterUpperLimit', $filterUpperLimit, PDO::PARAM_INT);
    }

    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

    if (isset($error_message)) {
        echo '<p class="error-message">' . $error_message . '</p>';
    }
    ?>

    <table>
        <thead>
            <tr>
                <th>Card Number <a href="?sort=card_number&order=<?= $nextOrder ?? 'asc' ?>&filter=<?= $_GET['filter'] ?? '' ?>" class="sort-link">⬆️⬇️</a></th>
                <th>Member Name <a href="?sort=member_name&order=<?= $nextOrder ?? 'asc' ?>&filter=<?= $_GET['filter'] ?? '' ?>" class="sort-link">⬆️⬇️</a></th>
                <th>Phone Number <a href="?sort=phone_number&order=<?= $nextOrder ?? 'asc' ?>&filter=<?= $_GET['filter'] ?? '' ?>" class="sort-link">⬆️⬇️</a></th>
                <th>Loyalty Points <a href="?sort=loyalty_points&order=<?= $nextOrder ?? 'asc' ?>&filter=<?= $_GET['filter'] ?? '' ?>" class="sort-link">⬆️⬇️</a></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($members as $member) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($member['card_number']) . '</td>';
                echo '<td><a href="member_detail.php?member_id=' . htmlspecialchars($member['member_id']) . '">' . htmlspecialchars($member['member_name']) . '</a></td>';
                echo '<td>' . htmlspecialchars($member['phone_number']) . '</td>';
                echo '<td>' . htmlspecialchars($member['loyalty_points']) . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>





