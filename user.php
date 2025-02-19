<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>User Page</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='style.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='SoftwareDeveloper.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='register.css'>
    
</head>
<body>
    <h1>Дарова, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p>Ты юзер, ты ниче не можешь... пока что</p>
    <a href="logout.php"><button class="back-button">Выйти из аккаунта</button></a>
</body>
</html>