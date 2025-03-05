<button?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: Main.html");
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
</head>
    <body>
    <header>
        <div class="container">
            <div id="branding">
                <h1>Admin Page</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="logout.php" class="btn">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <div>
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