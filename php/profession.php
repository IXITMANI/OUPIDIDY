<?php
session_start();
if (!isset($_SESSION['username'])) {
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

// Получение списка профессий с их описаниями и цветами
$sql = "SELECT p.id, p.name, p.description, c.color, c.hex
        FROM professions p
        LEFT JOIN colors c ON p.color_id = c.id";
$result = $conn->query($sql);

$professions = [];
while ($row = $result->fetch_assoc()) {
    $professions[] = $row;
}

// Получение качеств для каждой профессии
$profession_qualities = [];
foreach ($professions as $profession) {
    $sql = "SELECT q.name
            FROM profession_qualities pq
            JOIN qualities q ON pq.quality_id = q.id
            WHERE pq.profession_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $profession['id']);
    $stmt->execute();
    $stmt->bind_result($quality_name);

    $qualities = [];
    while ($stmt->fetch()) {
        $qualities[] = $quality_name;
    }
    $stmt->close();

    $profession_qualities[$profession['id']] = $qualities;
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
    <link rel='stylesheet' type='text/css' media='screen' href='../css/profession.css'>
    <title>Список профессий</title>
</head>
<body>
    <header class="heading" style="background-color: #7464af;">
        <nav class="links_header">
            <ul class="nav_links">
                <li><a href="../Main.html">домой</a></li>
            </ul>
        </nav>
        <div class="heading_text">Список профессий</div>
    </header>
    <main>
        <h2>Список профессий</h2>
        <table>
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Цвет</th>
                    <th>Качества</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($professions as $profession): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($profession['name']); ?></td>
                        <td><?php echo htmlspecialchars($profession['description']); ?></td>
                        <td style="background-color: <?php echo htmlspecialchars($profession['hex']); ?>;">
                            <?php echo htmlspecialchars($profession['color']); ?>
                        </td>
                        <td>
                            <ul>
                                <?php foreach ($profession_qualities[$profession['id']] as $quality): ?>
                                    <li><?php echo htmlspecialchars($quality); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>