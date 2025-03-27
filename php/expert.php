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

// Получение списка тестов из базы данных
$tests = [];
$sql = "SELECT id, name, description FROM tests";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tests[] = $row;
    }
}

// Обработка назначения тестов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['test_ids'])) {
    $user_id = $_POST['user_id'];
    $test_ids = $_POST['test_ids'];

    // Удаляем все текущие назначения для пользователя
    $sql = "DELETE FROM user_tests WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Добавляем новые назначения
    foreach ($test_ids as $test_id) {
        $sql = "INSERT INTO user_tests (user_id, test_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $test_id);
        $stmt->execute();
        $stmt->close();
    }

    $message = "Тесты успешно обновлены для пользователя!";
}

// Получение назначенных тестов для выбранного пользователя
$assigned_tests = [];
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $sql = "SELECT test_id FROM user_tests WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $assigned_tests[] = $row['test_id'];
    }

    $stmt->close();
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
                <li></li>
                <li><a href="../php/logout.php"><button>Выйти</button></a></li>
            </ul>
        </nav>
    </header>

    <div class="main-shit">
        <h2 class="heading">Назначение тестов пользователям</h2>

        <?php if (isset($message)): ?>
            <p style="color: green;"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="get" action="expert.php">
            <label for="user_id">Выберите пользователя:</label>
            <select id="user_id" name="user_id" required onchange="this.form.submit()">
                <option value="" disabled selected>-- Выберите пользователя --</option>
                <?php if ($users_result->num_rows > 0): ?>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo (isset($_GET['user_id']) && $_GET['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option disabled>Нет доступных пользователей</option>
                <?php endif; ?>
            </select>
        </form>

        <?php if (isset($_GET['user_id'])): ?>
            <h3>Назначить тесты</h3>
            <form method="post" action="expert.php">
                <input type="hidden" name="user_id" value="<?php echo $_GET['user_id']; ?>">
                <?php foreach ($tests as $test): ?>
                    <div>
                        <input type="checkbox" id="test_<?php echo $test['id']; ?>" name="test_ids[]" value="<?php echo $test['id']; ?>" 
                            <?php echo in_array($test['id'], $assigned_tests) ? 'checked' : ''; ?>>
                        <label for="test_<?php echo $test['id']; ?>"><?php echo htmlspecialchars($test['name']); ?> - <?php echo htmlspecialchars($test['description']); ?></label>
                    </div>
                <?php endforeach; ?>
                <button type="submit">Обновить тесты</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>