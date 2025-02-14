<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Admin Page</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='admin.css'>
    <script src='admin.js'></script>
</head>
<body>
    <h1>Welcome, Admin!</h1>
    <p>This is the admin page.</p>
    <a href="logout.php"><button>Logout</button></a>
</body>
</html>