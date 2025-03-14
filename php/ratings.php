<?php
session_start();
// Подключение к базе данных
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MyUsers";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Получение списка профессий и их качеств с рейтингами
$sql = "SELECT p.name AS profession_name, q.name AS quality_name, AVG(er.rating) AS average_rating
        FROM expert_ratings er
        JOIN profession_qualities pq ON er.profession_quality_id = pq.id
        JOIN professions p ON pq.profession_id = p.id
        JOIN qualities q ON pq.quality_id = q.id
        GROUP BY p.name, q.name
        ORDER BY p.name, average_rating DESC";
$result = $conn->query($sql);

$ratings = [];
while ($row = $result->fetch_assoc()) {
    $ratings[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Рейтинг качеств к профессиям</title>
    <link rel="stylesheet" href="../css/rating.css">
    <link rel="stylesheet" href="../css/navmain.css">
</head>
<body>
    <header class="heading" style="background-color: #c66281;">
        <nav class="links_header">
            <ul class="nav_links">
                <li><a href="../Main.php">домой</a></li>
                <li><a href="#">###</a></li>
                <li><a href="#">###</a></li>
            </ul>
        </nav>
        <div class="empty_space"> </br> </div>
        <div class="heading_text">Рейтинг оценки страниц</div>
    </header>
    <main>
        <table>
            <thead>
                <tr>
                    <th>профессия</th>
                    <th>качество</th>
                    <th>средний рейтинг</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ratings as $rating): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($rating['profession_name']); ?></td>
                        <td><?php echo htmlspecialchars($rating['quality_name']); ?></td>
                        <td><?php echo number_format($rating['average_rating'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>