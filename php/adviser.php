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
if (isset($_POST['assign_quality'])) {
    $profession_id = $_POST['profession_id'];
    $quality_id = $_POST['quality_id'];

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
        $message = "Качество успешно назначено профессии.";
    } else {
        $message = "Это качество уже назначено данной профессии.";
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
    $profession_qualities[$row['profession_name']][] = $row;
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
$sql = "SELECT pq.id, p.name AS profession_name, q.name AS quality_name, er.rating
        FROM expert_ratings er
        JOIN profession_qualities pq ON er.profession_quality_id = pq.id
        JOIN professions p ON pq.profession_id = p.id
        JOIN qualities q ON pq.quality_id = q.id
        WHERE er.expert_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $expert_id);
$stmt->execute();
$result = $stmt->get_result();

$rated_profession_qualities = [];
while ($row = $result->fetch_assoc()) {
    $rated_profession_qualities[$row['profession_name']][$row['quality_name']] = $row['rating'];
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    $profession_quality_id = $_POST['profession_quality_id'];
    $rating = $_POST['rating'];

    $sql = "INSERT INTO expert_ratings (expert_id, profession_quality_id, rating)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iidd", $expert_id, $profession_quality_id, $rating, $rating);
    $stmt->execute();
    $stmt->close();

    header("Location: adviser.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel='stylesheet' type='text/css' media='screen' href='../css/nav.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='../css/style.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='../css/adviser.css'>
    <title>Adviser Page</title>
</head>
<body>
    <header class="heading" style="background-color: #7464af;">
        <nav class="links_header">
            <ul class="nav_links">
                <li><a href="../Main.php">домой</a></li>
            </ul>
        </nav>
        <div class="heading_text">Оценка</div>
    </header>
    <main>
        <?php if (isset($message)): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
        <h2>Выберите профессию для оценки</h2>
        <form method="get" action="adviser.php">
            <label for="selected_profession">Профессия:</label>
            <select id="selected_profession" name="selected_profession" required>
                <?php foreach ($professions as $profession): ?>
                    <option value="<?php echo $profession['name']; ?>" <?php echo isset($_GET['selected_profession']) && $_GET['selected_profession'] == $profession['name'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($profession['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Выбрать</button>
        </form>

        <?php if (isset($_GET['selected_profession'])): ?>
            <h2>Оцените важность качеств для профессии: <?php echo htmlspecialchars($_GET['selected_profession']); ?></h2>
            <div class="profession-tables">
                <?php $selected_profession = $_GET['selected_profession']; ?>
                <?php if (isset($profession_qualities[$selected_profession])): ?>
                    <div class="profession-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Качество</th>
                                    <th>Рейтинг (0-10)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($profession_qualities[$selected_profession] as $quality): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($quality['quality_name']); ?></td>
                                        <td>
                                            <form method="post" action="adviser.php">
                                                <input type="hidden" name="profession_quality_id" value="<?php echo $quality['id']; ?>">
                                                <input type="number" name="rating" min="0" max="10" step="0.1" value="<?php echo isset($rated_profession_qualities[$selected_profession][$quality['quality_name']]) ? htmlspecialchars($rated_profession_qualities[$selected_profession][$quality['quality_name']]) : ''; ?>" required>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                
                            </tbody>
        
                        </table>
                        <button type="submit">Сохранить</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p>Для выбранной профессии нет назначенных качеств.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>