<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: Main.php");
    exit();
}

// Список доступных качеств (можно расширить)
$qualities = [
    'attention' => 'Внимательность',
    'reaction' => 'Скорость реакции',
    'thinking' => 'Мышление'
];

// Получаем список тестов из базы данных
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MyUsers";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$tests = [];
$sql = "SELECT name FROM tests";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tests[] = $row['name'];
    }
}
$conn->close();
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
            <h3>Требуемые качества и пороги:</h3>
            <?php foreach ($qualities as $key => $label): ?>
                <label>
                    <input type="checkbox" name="qualities[<?= $key ?>][use]" value="1">
                    <?= htmlspecialchars($label) ?>
                </label>
                <input type="number" step="0.01" min="0" max="1" name="qualities[<?= $key ?>][min]" placeholder="Минимум (0-1)">
                <input type="number" step="0.01" min="0" max="1" name="qualities[<?= $key ?>][max]" placeholder="Максимум (0-1)">
                <br>
            <?php endforeach; ?>
            <h3>Необходимые тесты:</h3>
            <?php foreach ($tests as $test): ?>
                <label>
                    <input type="checkbox" name="required_tests[]" value="<?= htmlspecialchars($test) ?>">
                    <?= htmlspecialchars($test) ?>
                </label><br>
            <?php endforeach; ?>
            <button type="submit" name="add_profession">Добавить</button>
        </form>
    </div>
    <div>
        <a href="admin.php"><button>Назад</button></a>
    </div>
</body>
</html>