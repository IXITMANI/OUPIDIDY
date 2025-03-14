<?php
// Подключение к базе данных
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MyUsers";

// Создание соединения
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Получение ID профессии из параметра запроса
$profession_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// SQL-запрос для получения информации о профессии и ее цвета
$sql = "SELECT p.name, p.description, c.hex AS color 
        FROM professions p 
        JOIN colors c ON p.color_id = c.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profession_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Получение данных профессии
    $row = $result->fetch_assoc();
    $profession_name = $row['name'];
    $profession_description = $row['description'];
    $profession_color = $row['color'];
} else {
    echo "Профессия не найдена.";
    exit;
}

$stmt->close();

// SQL-запрос для получения топ 5 качеств профессии с их рейтингами
$sql = "SELECT q.name, AVG(er.rating) AS average_rating 
        FROM profession_qualities pq 
        JOIN qualities q ON pq.quality_id = q.id 
        LEFT JOIN expert_ratings er ON pq.id = er.profession_quality_id 
        WHERE pq.profession_id = ? 
        GROUP BY q.name 
        ORDER BY average_rating DESC 
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profession_id);
$stmt->execute();
$result = $stmt->get_result();

$qualities = [];
while ($row = $result->fetch_assoc()) {
    $qualities[] = [
        'name' => $row['name'],
        'average_rating' => $row['average_rating']
    ];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title><?php echo htmlspecialchars($profession_name); ?></title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='../css/nav.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='../css/style.css'>
</head>
<body>
    <header class="heading" style="background-color: <?php echo htmlspecialchars($profession_color); ?>;">
        <nav class="links_header">
            <ul class="nav_links">
                <li><a href="../Main.php">домой</a></li>
                <li><a href="#">пустышка</a></li>
                <li><a href="#">пустышка</a></li>
            </ul>
        </nav>
        <div class="empty_space"> </br> </div>
        <div class="heading_text"><?php echo htmlspecialchars($profession_name); ?></div>
    </header>

    <div class="main-shit">
        <h2 class="heading"><?php echo htmlspecialchars($profession_name); ?></h2>
        <section id="profession-details">
            <p><?php echo htmlspecialchars($profession_description); ?></p>
        </section>

        <section id="top-qualities">
            <h3>Топ 5 качеств</h3>
            <?php if (!empty($qualities)): ?>
                <ul>
                    <?php foreach ($qualities as $quality): ?>
                        <li><?php echo htmlspecialchars($quality['name']); ?> - Рейтинг: <?php echo number_format($quality['average_rating'], 2); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Качества для этой профессии отсутствуют.</p>
            <?php endif; ?>
        </section>
    </div>  
</body>
</html> 