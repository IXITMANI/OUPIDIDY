<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'expert') {
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

// Получение списка пользователей
$sql = "SELECT id, username FROM users WHERE role = 'user'";
$users_result = $conn->query($sql);

// Список доступных тестов
$tests = [
    ['id' => 1, 'name' => 'Тест на реакцию', 'link' => './php/reaction_test.php'],
    ['id' => 2, 'name' => 'Тест на звук', 'link' => './php/sound_test.php'],
    ['id' => 3, 'name' => 'Тест на цвет', 'link' => './php/color_test.php'],
    ['id' => 4, 'name' => 'Тест на числа (звук)', 'link' => './php/number_sound_test.php'],
    ['id' => 5, 'name' => 'Тест на числа (экран)', 'link' => './php/number_display_test.php'],
];

// Обработка назначения тестов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['test_ids'])) {
    $user_id = $_POST['user_id'];
    $test_ids = $_POST['test_ids'];

    foreach ($test_ids as $test_id) {
        $sql = "INSERT INTO user_tests (user_id, test_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $test_id);
        $stmt->execute();
        $stmt->close();
    }

    $message = "Тесты успешно назначены пользователю!";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Назначение тестов</title>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/navmain.css">
</head>
<body>
    <header>
        <nav class="links_header">
            <ul class="nav_links">
                <li><a href="../Main.php">Домой</a></li>
                <li><a href="../php/logout.php"><button>Выйти</button></a></li>
            </ul>
        </nav>
    </header>

    <div class="main-shit">
        <h2 class="heading">Назначение тестов пользователям</h2>

        <?php if (isset($message)): ?>
            <p style="color: green;"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="post" action="expert.php">
            <label for="user_id">Выберите пользователя:</label>
            <select id="user_id" name="user_id" required>
                <?php if ($users_result->num_rows > 0): ?>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option disabled>Нет доступных пользователей</option>
                <?php endif; ?>
            </select>

            <h3>Выберите тесты:</h3>
            <?php foreach ($tests as $test): ?>
                <div>
                    <input type="checkbox" id="test_<?php echo $test['id']; ?>" name="test_ids[]" value="<?php echo $test['id']; ?>">
                    <label for="test_<?php echo $test['id']; ?>"><?php echo htmlspecialchars($test['name']); ?></label>
                </div>
            <?php endforeach; ?>

            <button type="submit">Назначить тесты</button>
        </form>
    </div>
</body>
</html>