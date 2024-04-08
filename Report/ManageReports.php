<!-- Connection string to Database -->
<?php
        // sensitive informaiton, need to move to config file before "release". Not enough time atm)
        // connection string from azure 
        $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
    $query_id = $_POST["query_id"];

    if (isset($_POST['delete'])) {
        // Delete record
            $sqlcon = $conn->prepare("DELETE FROM [dbo].[SavedQueries] WHERE [query_id] = :query_id");
            $sqlcon->bindParam(':query_id', $query_id);
            $sqlcon->execute();
            $message = "Record deleted successfully!";

    } else {
    $query_name = $_POST["query_name"];
    $query_text = $_POST["query_text"];

        $sqlcon = $conn->prepare("UPDATE [dbo].[SavedQueries] SET [query_name] = :query_name, [query_text] = :query_text WHERE [query_id] = :query_id");
        $sqlcon->bindParam(':query_id', $query_id);
        $sqlcon->bindParam(':query_name', $query_name);
        $sqlcon->bindParam(':query_text', $query_text);
        $sqlcon->execute();
        $message = "Record updated successfully!";

}
}
// Fetch data from database
$sqlcon = $conn->prepare("SELECT TOP (1000) [query_id], [query_name], [query_text] FROM [dbo].[SavedQueries]");
$sqlcon->execute();
$result = $sqlcon->fetchAll(PDO::FETCH_ASSOC);
// checks which button was clicked to complete form, then creates 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create'])) {
    $new_query_name = $_POST["new_query_name"];
    $new_query_text = $_POST["new_query_text"];
        $sqlcon = $conn->prepare("INSERT INTO [dbo].[SavedQueries] ([query_name], [query_text]) VALUES (:query_name, :query_text)");
        $sqlcon->bindParam(':query_name', $new_query_name);
        $sqlcon->bindParam(':query_text', $new_query_text);
        $sqlcon->execute();
        $message = "New record created successfully!";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Reports</title>
    <link rel="stylesheet" href="Report.css">
</head>
<?php include "../global/adminNavigation.php" ?>
<body>

<form method="post" action="">
        <input type="hidden" name="create" value="1">
        <label for="new_query_name">New Query Name:</label>
        <input type="text" id="new_query_name" name="new_query_name" value=""><br>
        <label for="new_query_text">New Query Text:</label>
        <textarea id="new_query_text" name="new_query_text"></textarea><br>
        <input type="submit" value="Create">
    </form>
    <div class="separator"></div>
    <?php foreach ($result as $row): ?>
        <form method="post" action="">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="query_id" value="<?= $row['query_id']; ?>">
        Query Name: <input type="text" name="query_name" value="<?= $row['query_name']; ?>"><br>
        Query Text: <textarea name="query_text"><?= $row['query_text']; ?></textarea><br>
        <input type="submit" value="Update">
        <input type="submit" name="delete" value="Delete">
    </form>
        <hr>
    <?php endforeach; ?>
</body>
</html>