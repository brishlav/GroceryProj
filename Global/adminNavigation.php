<?php
include "checkAdmin.php";
?>
<style>
    body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f2f2f2;
}

header {
    background-color: #333;
    color: #fff;
    padding: 10px 0;
    text-align: center;
}

nav {
    background-color: #444;
}

nav ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
    display: flex;
    justify-content: center;
}

nav li {
    margin: 0 15px;
}

nav a {
    text-decoration: none;
    color: #fff;
    font-weight: bold;
}

/* Main content section */
.content {
    max-width: 960px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 5px;
}


#Logout {
    background-color: #7ec0da;
    color: #fff;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
}

#Logout:hover {
    background-color: #89d0ec;
}

body {
    font-family: 'Arial', sans-serif;
    background-color: #e6f7ff; /* Light Blue Background */
    text-align: center;
}

h1 {
    color: #333366; /* Dark Blue */
}

table {
    width: 50%;
    border-collapse: collapse;
    margin: 25px 0;
    font-size: 18px;
    text-align: left;
    box-shadow: 0px 0px 30px 0px #0000000f;
    overflow: hidden;
    border-radius: 10px;
    margin-left: auto;
    margin-right: auto;
}

th, td {
    padding: 15px;
    border-bottom: 1px solid #ccc; /* Light Grey Border */
}

th {
    background-color: #3399ff; /* Sky Blue for Table Header */
    color: white;
}

tr:hover {
    background-color: #b3d9ff; /* Hover color: Light Sky Blue */
}

td {
    text-align: center;
    color: #333366; /* Text color: Dark Blue */
}
</style>
<nav>
        <ul>
            <li><a href="../Home/StaffHomePage.php">Home</a></li>
            <li><a href="../Members/Member_display.php">Member</a></li>
            <li><a href="../Inventory/Inventory_display.php">Inventory</a></li>
            <li><a href="../Sales/Sales.php">Sale</a></li>
            <li><a href="../Report/Report.php">Report</a></li>
            <li id="Logout"><a href='../login Page/login.php'>Logout</a></li>
        </ul>
</nav>