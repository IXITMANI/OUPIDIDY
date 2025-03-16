<button?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: Main.php");
    exit();
}


?>
<!DOCTYPE html>
<html>
<meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Admin Page</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='../css/admin.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='../css/style.css'>
</head>
    <body>
    <header>
        <h1 style="padding-left: 10%">Admin Page</h1>
    </header>
    <div>
        <div class="container">
            <h2>Добавить новую профессию</h2>
            <form method="post" action="admin.php">
                <label for="profession_name">Название профессии:</label></br>
                <input type="text" id="profession_name" name="profession_name" required></br>
                <label for="profession_description">Описание:</label></br>
                <input type="text" id="profession_description" name="profession_description" required>
                </br>
                <button type="submit" name="add_profession">Добавить</button>
            </form>
        </div>
    <div>
        <a href="admin.php"><button>Назад</button></a>
    </div>
    </body>
</html>