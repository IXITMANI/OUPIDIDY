<!-- filepath: c:\xampp\htdocs\OUPIDIDY\php\test_setup.php -->
<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройки теста</title>
</head>
<body>
    <h1>Настройки теста</h1>
    <form method="post" action="circle_test.php">
        <!-- Выбор времени прохождения теста -->
        <label for="test_duration">Выберите время прохождения теста (минуты):</label>
        <select id="test_duration" name="test_duration" required>
            <?php for ($i = 2; $i <= 45; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?> минут</option>
            <?php endfor; ?>
        </select>
        <br><br>

        <!-- Включение/выключение отображения времени выполнения -->
        <label for="show_timer">Отображение времени выполнения теста:</label>
        <select id="show_timer" name="show_timer" required>
            <option value="1">Включить</option>
            <option value="0">Выключить</option>
        </select>
        <br><br>

        <!-- Отображение результата -->
        <label for="show_results">Отображение результата:</label>
        <select id="show_results" name="show_results" required>
            <option value="1">Показывать</option>
            <option value="0">Не показывать</option>
        </select>
        <br><br>

        <!-- Отображение прогресса -->
        <label for="show_progress">Отображение прогресса выполнения:</label>
        <select id="show_progress" name="show_progress" required>
            <option value="1">Показывать</option>
            <option value="0">Не показывать</option>
        </select>
        <br><br>

        <!-- Ускорение движения объектов -->
        <label for="speed_increase">Ускорение движения объектов (в процентах):</label>
        <input type="number" id="speed_increase" name="speed_increase" min="0" max="100" step="1" required>
        <br><br>

        <label for="speed_interval">Интервал ускорения (секунды):</label>
        <input type="number" id="speed_interval" name="speed_interval" min="1" max="60" step="1" required>
        <br><br>

        <button type="submit">Начать тест</button>
    </form>
</body>
</html>