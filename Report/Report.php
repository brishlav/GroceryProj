<!DOCTYPE html>
<html lang="en">
<head>
    <title>Report</title>
    <!-- improt style sheet -->
    <link rel="stylesheet" href="Report.css">
    <!-- import export to csv function -->
    <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
</head>
<body>
    <header>
        <h1>Report</h1>
    </header>
    <!-- import global navigation -->
    <?php include "../global/adminNavigation.php" ?>
    <!-- button to manage reports page -->
    <a href="../Report/ManageReports.php">Manage Reports</a>


    <form method="post">
        <!-- connection string -->
        <?php
        // import query logger
        include "../Global/query.php";
        // connect to database
        $conn = new PDO("sqlsrv:server = tcp:swe20001.database.windows.net,1433; Database = GoToGrocery", "CloudSA170f230c", "#SWE200001");
        $error_msg = "";
        //wraps all buttons into div
        echo '<div class="query-button-container">';
        $sql = "SELECT query_id, query_name FROM SavedQueries";
       // run logger function
        logQuery($sql);
        // loops through each returned row and creates a button with functions
        foreach ($conn->query($sql) as $row) {
            echo '<button class="query-button" type="submit" name="run_query" value="' . $row['query_id'] . '">' . $row['query_name'] . '</button>';
        }
        echo '</div>';
        ?>

        <?php
        // checks if query has been run when report button is selected
        if (isset($_POST['run_query'])) {
                $query_id = $_POST['run_query'];
                // gets report id from button
                $sql = "SELECT query_text, query_name FROM SavedQueries WHERE query_id = ?";
                // log query
                logQuery($sql);
                // execute query
                $sqlcon = $conn->prepare($sql);
                $sqlcon->execute([$query_id]);
                $row = $sqlcon->fetch();
                if ($row) {
                    $query_text = $row['query_text'];
                    $query_name = $row['query_name'];
                    $result = $conn->query($query_text);
                    // sets heading to query name
                    if ($query_name !== null) {
                        echo "<h2 id=\"selectedQueryName\">" . htmlspecialchars($query_name) . "</h2>";
                    }
                    // sets table to id export for export feature
                    echo "<table border='1' id='export'>";
                    //identifies first row
                    $firstRow = true;
                    //loops through creating table from result
                    foreach ($result as $row) {
                        if ($firstRow) {
                            echo "<tr>";
                            foreach ($row as $key => $value) {
                                if (is_string($key)) {
                                    echo "<th>" . $key . "</th>";
                                }
                            }
                            echo "</tr>";
                            $firstRow = false;
                        }
                        // populates each column for result
                        echo "<tr>";
                        foreach ($row as $key => $value) {
                            if (is_string($key)) {
                                echo "<td>" . $value . "</td>";
                            }
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                    // creates export button only when report is displaying
                    echo '<button onclick="ExportToExcel(\'xlsx\')">Export table to excel</button>';
                
            } 
        }
        ?>
    </form>

    <script>
        function FileName() {
            const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' };
            const dateTimeString = new Date().toLocaleString(undefined, options);
            let tableName = document.getElementById('selectedQueryName');
            let fileName = tableName.textContent + "_" + dateTimeString;
            return fileName;
        }
        
        function ExportToExcel(type, fn, dl) {
            var elt = document.getElementById('export');
            var wb = XLSX.utils.table_to_book(elt, { sheet: "sheet1" });
            return dl ? XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }) : XLSX.writeFile(wb, fn || (FileName() + '.' + (type || 'xlsx')));
        }
    </script>
</body>
</html>