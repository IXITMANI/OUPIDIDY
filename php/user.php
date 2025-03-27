<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../html/login.html");
    exit();
}

// Подключение к базе данных
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MyUsers";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id']; // ID текущего пользователя

// Получение назначенных тестов
$sql = "SELECT t.id, t.name, t.description, t.link 
        FROM user_tests ut
        JOIN tests t ON ut.test_id = t.id
        WHERE ut.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$assigned_tests = [];
while ($row = $result->fetch_assoc()) {
    $assigned_tests[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ваши тесты</title>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/navmain.css">
</head>
<body>
    <header>
        <nav class="links_header">
            <ul class="nav_links">
                <li><a href="./result.php">Моя статистика</a></li>
                <li><a href="../php/logout.php"><button>Назад</button></a></li>
            </ul>
        </nav>
    </header>

    <div class="main-shit">
        <h2 class="heading">Ваши тесты</h2>
        <section id="tests">
            <?php if (!empty($assigned_tests)): ?>
                <?php foreach ($assigned_tests as $test): ?>
                    <a href="<?php echo $test['link']; ?>">
                        <article class="profession" style="box-shadow: -7px 7px #D15955;">
                            <h3><?php echo htmlspecialchars($test['name']); ?></h3>
                            <p><?php echo htmlspecialchars($test['description']); ?></p>
                        </article>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Вам пока не назначены тесты.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>