<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MyUsers";

// Создаем соединение
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверяем соединение
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

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

// Получение списка качеств для выбранной профессии
$selected_profession_id = isset($_GET['profession_id']) ? intval($_GET['profession_id']) : 0;
$assigned_qualities = [];
if ($selected_profession_id) {
    $sql = "SELECT q.name FROM profession_qualities pq JOIN qualities q ON pq.quality_id = q.id WHERE pq.profession_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selected_profession_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $assigned_qualities[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Назначить качество профессии</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='../css/admin.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='../css/style.css'>
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1>Назначить качество профессии</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="logout.php" class="btn">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <div class="container">
        <h2>Выберите профессию</h2>
        <form method="get" action="quality_add.php">
            <label for="profession_id">Профессия:</label>
            <select id="profession_id" name="profession_id" required>
                <?php foreach ($professions as $profession): ?>
                    <option value="<?php echo $profession['id']; ?>" <?php echo $selected_profession_id == $profession['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($profession['name']); ?></option>
                <?php endforeach; ?>
            </select>
                </br>
            <button type="submit">Выбрать</button>
        </form>

        <?php if ($selected_profession_id): ?>
            <h2>Назначить качество профессии</h2>
            <?php if ($message): ?>
                <p><?php echo $message; ?></p>
            <?php endif; ?>
            <form method="post" action="quality_add.php">
                <input type="hidden" name="profession_id" value="<?php echo $selected_profession_id; ?>">
                <label for="quality_id">Качество:</label>
                <select id="quality_id" name="quality_id" required>
                    <?php foreach ($qualities as $quality): ?>
                        <option value="<?php echo $quality['id']; ?>"><?php echo htmlspecialchars($quality['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                </br>
                <button type="submit" name="assign_quality">Назначить</button>
            </form>

            <h2>Качества, назначенные профессии</h2>
            <?php if (!empty($assigned_qualities)): ?>
                <ul>
                    <?php foreach ($assigned_qualities as $quality): ?>
                        <li><?php echo htmlspecialchars($quality['name']); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Для этой профессии еще не назначены качества.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <div>
        <a href="admin.php"><button>Назад</button></a>
    </div>
</body>
</html>