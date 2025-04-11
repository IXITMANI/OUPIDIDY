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

// Получение списка профессий
$sql = "SELECT id, name FROM professions";
$professions_result = $conn->query($sql);
$professions = [];
while ($row = $professions_result->fetch_assoc()) {
    $professions[] = $row;
}

// Получение качеств для выбранной профессии
$profession_qualities = [];
$previous_ratings = [];
if (isset($_GET['selected_profession'])) {
    $selected_profession = $_GET['selected_profession'];
    $expert_id = $_SESSION['user_id']; // ID эксперта из сессии

    // Получение качеств профессии
    $sql = "SELECT pq.id, q.name AS quality_name
            FROM profession_qualities pq
            JOIN professions p ON pq.profession_id = p.id
            JOIN qualities q ON pq.quality_id = q.id
            WHERE p.name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selected_profession);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $profession_qualities[] = $row;
    }
    $stmt->close();

    // Получение прошлых оценок эксперта
    $sql = "SELECT profession_quality_id, rating FROM expert_ratings WHERE expert_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $expert_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $previous_ratings[$row['profession_quality_id']] = $row['rating'];
    }
    $stmt->close();
}

// Обработка сохранения рейтингов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ratings'])) {
    $ratings = $_POST['ratings'];
    $expert_id = $_SESSION['user_id']; // ID эксперта из сессии
    $max_rating = count($profession_qualities); // Максимальный рейтинг равен количеству качеств

    // Проверка на уникальность и диапазон значений
    $unique_ratings = array_unique($ratings);
    if (count($ratings) !== count($unique_ratings)) {
        $error_message = "Значения рейтинга не должны повторяться.";
    } elseif (max($ratings) > $max_rating || min($ratings) < 1) {
        $error_message = "Рейтинг должен быть в диапазоне от 1 до $max_rating.";
    } else {
        // Сохранение рейтингов в базе данных
        $conn->begin_transaction();
        try {
            foreach ($ratings as $profession_quality_id => $rating) {
                $sql = "INSERT INTO expert_ratings (expert_id, profession_quality_id, rating)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE rating = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iidi", $expert_id, $profession_quality_id, $rating, $rating);
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();
            $message = "Рейтинги качеств успешно сохранены!";
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Ошибка при сохранении рейтингов: " . $e->getMessage();
        }
    }
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
            <p style="color: green;"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
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
            <form method="post" action="adviser.php?selected_profession=<?php echo urlencode($_GET['selected_profession']); ?>">
                <table>
                    <thead>
                        <tr>
                            <th>Качество</th>
                            <th>Рейтинг (1 - <?php echo count($profession_qualities); ?>)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($profession_qualities as $quality): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($quality['quality_name']); ?></td>
                                <td>
                                    <input type="number" name="ratings[<?php echo $quality['id']; ?>]" min="1" max="<?php echo count($profession_qualities); ?>" value="<?php echo isset($previous_ratings[$quality['id']]) ? $previous_ratings[$quality['id']] : ''; ?>" required>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit">Сохранить рейтинги</button>
            </form>
        <?php endif; ?>
        <div class="button_group">
            <a href="quality_add.php"><button>Добавить качество</button></a>
        </div>
    </main>
</body>
</html>