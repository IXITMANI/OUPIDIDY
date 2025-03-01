<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'adviser') {
    header("Location: login.html");
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

// Обработка добавления новой профессии
if (isset($_POST['add_profession'])) {
    $profession_name = $_POST['profession_name'];

    // Проверка, существует ли уже такая профессия
    $sql = "SELECT COUNT(*) FROM professions WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $profession_name);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count == 0) {
        $sql = "INSERT INTO professions (name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $profession_name);
        $stmt->execute();
        $stmt->close();
        $message = "Профессия успешно добавлена.";
    } else {
        $message = "Эта профессия уже существует.";
    }
}

// Обработка добавления нового качества
if (isset($_POST['add_quality'])) {
    $quality_name = $_POST['quality_name'];

    // Проверка, существует ли уже такое качество
    $sql = "SELECT COUNT(*) FROM qualities WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $quality_name);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count == 0) {
        $sql = "INSERT INTO qualities (name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $quality_name);
        $stmt->execute();
        $stmt->close();
        $message = "Качество успешно добавлено.";
    } else {
        $message = "Это качество уже существует.";
    }
}

// Обработка назначения качеств профессиям
if (isset($_POST['assign_qualities'])) {
    $profession_id = $_POST['profession_id'];
    $quality_ids = $_POST['quality_ids'];

    foreach ($quality_ids as $quality_id) {
        // Проверка, существует ли уже такое качество для данной профессии
        $sql = "SELECT COUNT(*) FROM profession_qualities WHERE profession_id = ? AND quality_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $profession_id, $quality_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count == 0) {
            $sql = "INSERT INTO profession_qualities (profession_id, quality_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $profession_id, $quality_id);
            $stmt->execute();
            $stmt->close();
            $message = "Качества успешно назначены профессии.";
        } else {
            $message = "Некоторые качества уже назначены данной профессии.";
        }
    }
}

// Получение списка профессий и качеств
$sql = "SELECT pq.id, p.name AS profession_name, q.name AS quality_name
        FROM profession_qualities pq
        JOIN professions p ON pq.profession_id = p.id
        JOIN qualities q ON pq.quality_id = q.id";
$result = $conn->query($sql);

$profession_qualities = [];
while ($row = $result->fetch_assoc()) {
    $profession_qualities[] = $row;
}

// Получение списка профессий
$sql = "SELECT id, name FROM professions";
$professions_result = $conn->query($sql);

$professions = [];
while ($row = $professions_result->fetch_assoc()) {
    $professions[] = $row;
}

// Получение списка качеств
$sql = "SELECT id, name FROM qualities";
$qualities_result = $conn->query($sql);

$qualities = [];
while ($row = $qualities_result->fetch_assoc()) {
    $qualities[] = $row;
}

// Получение списка оценок, которые пользователь уже сделал
$expert_id = $_SESSION['user_id'];
$sql = "SELECT profession_quality_id FROM expert_ratings WHERE expert_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $expert_id);
$stmt->execute();
$stmt->bind_result($rated_profession_quality_id);

$rated_profession_qualities = [];
while ($stmt->fetch()) {
    $rated_profession_qualities[] = $rated_profession_quality_id;
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ratings'])) {
    foreach ($_POST['ratings'] as $profession_quality_id => $rating) {
        $sql = "INSERT INTO expert_ratings (expert_id, profession_quality_id, rating)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE rating = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iidd", $expert_id, $profession_quality_id, $rating, $rating);
        $stmt->execute();
        $stmt->close();
    }
    echo "Рейтинги успешно сохранены!";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adviser Page</title>
</head>
<body>
    <header>
        <h1>Привет, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <nav>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <?php if (isset($message)): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
        <h2>Оцените важность качеств для каждой профессии</h2>
        <form method="post" action="adviser.php">
            <table>
                <thead>
                    <tr>
                        <th>Профессия</th>
                        <th>Качество</th>
                        <th>Рейтинг (0-10)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($profession_qualities as $pq): ?>
                        <?php if (!in_array($pq['id'], $rated_profession_qualities)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pq['profession_name']); ?></td>
                                <td><?php echo htmlspecialchars($pq['quality_name']); ?></td>
                                <td>
                                    <input type="number" name="ratings[<?php echo $pq['id']; ?>]" min="0" max="10" step="0.1" required>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit">Сохранить рейтинги</button>
        </form>

        <h2>Назначить качества профессии</h2>
        <form method="post" action="adviser.php">
            <label for="profession_id">Профессия:</label>
            <select id="profession_id" name="profession_id" required>
                <?php foreach ($professions as $profession): ?>
                    <option value="<?php echo $profession['id']; ?>"><?php echo htmlspecialchars($profession['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <label for="quality_ids">Качества:</label>
            <div id="quality_ids">
                <?php foreach ($qualities as $quality): ?>
                    <input type="checkbox" name="quality_ids[]" value="<?php echo $quality['id']; ?>">
                    <label><?php echo htmlspecialchars($quality['name']); ?></label><br>
                <?php endforeach; ?>
            </div>
            <button type="submit" name="assign_qualities">Назначить качества</button>
        </form>

        <h2>Добавить новую профессию</h2>
        <form method="post" action="adviser.php">
            <label for="profession_name">Название профессии:</label>
            <input type="text" id="profession_name" name="profession_name" required>
            <button type="submit" name="add_profession">Добавить профессию</button>
        </form>

        <h2>Добавить новое качество</h2>
        <form method="post" action="adviser.php">
            <label for="quality_name">Название качества:</label>
            <input type="text" id="quality_name" name="quality_name" required>
            <button type="submit" name="add_quality">Добавить качество</button>
        </form>
    </main>
</body>
</html>