<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../html/login.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MyUsers";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Получение всех уникальных названий тестов
$sql = "SELECT DISTINCT test_name FROM test_results WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$test_names = [];
while ($row = $result->fetch_assoc()) {
    $test_names[] = $row['test_name'];
}
$stmt->close();

// Упорядочиваем тесты: "Тест на реакцию" -> "Тест на звук" -> остальные
$ordered_test_names = [];
if (in_array("Тест на реакцию", $test_names)) {
    $ordered_test_names[] = "Тест на реакцию";
}
if (in_array("Тест на звук", $test_names)) {
    $ordered_test_names[] = "Тест на звук";
}
foreach ($test_names as $test_name) {
    if (!in_array($test_name, $ordered_test_names)) {
        $ordered_test_names[] = $test_name;
}
}

// Получение результатов для каждого теста
$test_results = [];
foreach ($ordered_test_names as $test_name) {
    $sql = "SELECT test_name, mean_reaction_time, std_dev, accuracy, incorrect_responses, misses, completed_at
            FROM test_results
            WHERE user_id = ? AND test_name = ?
            ORDER BY completed_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $test_name);
    $stmt->execute();
    $result = $stmt->get_result();

    $test_results[$test_name] = [];
    while ($row = $result->fetch_assoc()) {
        $test_results[$test_name][] = $row;
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
    <title>Результаты тестов</title>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/navmain.css">
</head>
<body>
    <header>
        <nav class="links_header">
            <ul class="nav_links">
                <li><a href="../php/user.php">Назад</a></li>
                <li><a href="../php/logout.php"><button>На главную</button></a></li>
            </ul>
        </nav>
    </header>

    <div class="main-shit">
        <h2 class="heading">Ваши результаты</h2>
        <section id="results">
            <?php if (!empty($test_results)): ?>
                <?php foreach ($test_results as $test_name => $results): ?>
                    <h3><?php echo htmlspecialchars($test_name); ?></h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Среднее время реакции (мс)</th>
                                <th>Стандартное отклонение</th>
                                <?php if ($test_name !== "Тест на звук" && $test_name !== "Тест на реакцию"): ?>
                                    <th>Точность (%)</th>
                                <th>Ошибки</th>
                                <?php endif; ?>
                                <th>Пропуски</th>
                                <th>Дата выполнения</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $result): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($result['mean_reaction_time']); ?></td>
                                    <td><?php echo htmlspecialchars($result['std_dev']); ?></td>
                                    <?php if ($test_name !== "Тест на звук" && $test_name !== "Тест на реакцию"): ?>
                                        <td><?php echo htmlspecialchars($result['accuracy']); ?></td>
                                        <td><?php echo htmlspecialchars($result['incorrect_responses']); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo htmlspecialchars($result['misses']); ?></td>
                                    <td><?php echo htmlspecialchars($result['completed_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Вы еще не проходили тесты</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>