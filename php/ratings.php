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
</head>
<body>
    <header>
        <h1>Рейтинг качеств к профессиям</h1>
        <nav>
            <a href="../Main.html"><button>Назад</button></a>
        </nav>
    </header>
    <main>
        <table>
            <thead>
                <tr>
                    <th>Профессия</th>
                    <th>Качество</th>
                    <th>Средний рейтинг</th>
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