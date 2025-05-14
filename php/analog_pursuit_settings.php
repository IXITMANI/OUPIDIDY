<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['analog_pursuit_options'] = [
        'duration' => (int)$_POST['duration'],
        'showTimer' => isset($_POST['showTimer']),
        'showResults' => isset($_POST['showResults']),
        'showProgress' => isset($_POST['showProgress']),
        'speedIncrease' => (float)$_POST['speedIncrease'],
        'speedInterval' => (int)$_POST['speedInterval']
    ];
    header('Location: analog_pursuit_test.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройки аналогового преследования</title>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
</head>
<body>
    <a href="../index.php" id="backButton">Назад</a>
    <h1 class="heading_text">Настройки аналогового преследования</h1>
    <form method="POST" id="settingsForm">
        <div id="description">
            <p>Выберите параметры теста:</p>
            <label>Время выполнения (секунды, 120–2700):</label>
            <input type="number" name="duration" min="120" max="2700" value="120" required><br><br>
            <label><input type="checkbox" name="showTimer" checked> Показывать таймер</label><br>
            <label><input type="checkbox" name="showResults" checked> Показывать результаты</label><br>
            <label><input type="checkbox" name="showProgress" checked> Показывать прогресс</label><br>
            <label>Ускорение движения (на сколько, %):</label>
            <input type="number" name="speedIncrease" step="0.1" min="0" value="5" required><br><br>
            <label>Интервал ускорения (секунды):</label>
            <input type="number" name="speedInterval" min="1" value="10" required><br><br>
            <button type="submit" id="startButton">Сохранить и начать</button>
        </div>
    </form>
</body>
</html>