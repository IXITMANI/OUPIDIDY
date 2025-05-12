<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../html/login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройки теста "Оценка памяти"</title>
    <link rel="stylesheet" type="text/css" href="../css/settings.css">
</head>
<body>
    <header>
        <nav class="links_header">
            <ul class="nav_links">
                <li><a href="./user.php">Назад</a></li>
            </ul>
        </nav>
        <h1>Настройки теста "Оценка памяти"</h1>
    </header>

    <div class="settings-container">
        <form action="memory_test.php" method="GET">
            <label for="difficulty">Выберите уровень сложности:</label>
            <select name="difficulty" id="difficulty">
                <option value="easy">Легкий</option>
                <option value="medium">Средний</option>
                <option value="hard">Сложный</option>
            </select>
            <button type="submit">Начать тест</button>
        </form>
    </div>
</body>
</html>