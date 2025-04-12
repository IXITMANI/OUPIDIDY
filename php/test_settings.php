<?php
session_start();

// Сохранение настроек в сессии при отправке формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['test_options'] = [
        'duration' => (int)$_POST['test_duration'], // Время теста в минутах
        'showTimer' => isset($_POST['show_timer']) ? true : false,
        'showResultsPerMinute' => isset($_POST['show_results_per_minute']) ? true : false,
        'showProgress' => isset($_POST['show_progress']) ? true : false,
        'speedIncrease' => (int)$_POST['speed_increase'], // Ускорение в процентах
        'speedInterval' => (int)$_POST['speed_interval'] // Интервал ускорения в секундах
    ];

    // Перенаправление на страницу теста
    header('Location: circle_test.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройки теста</title>
    <style>
        .back-button {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<a href="user.php" class="back-button">Назад</a>
    <h1>Настройки теста</h1>
    <form method="post" action="test_settings.php">
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
        <input type="checkbox" id="show_timer" name="show_timer">
        <br><br>

        <!-- Отображение результата за минуту и в целом -->
        <label for="show_results_per_minute">Отображение результата за минуту и в целом:</label>
        <input type="checkbox" id="show_results_per_minute" name="show_results_per_minute">
        <br><br>

        <!-- Отображение прогресса выполнения теста -->
        <label for="show_progress">Отображение прогресса выполнения теста:</label>
        <input type="checkbox" id="show_progress" name="show_progress">
        <br><br>

        <!-- Ускорение движения объектов -->
        <label for="speed_increase">Ускорение движения объектов (в процентах):</label>
        <input type="number" id="speed_increase" name="speed_increase" min="0" max="100" step="1" required>
        <br><br>

        <label for="speed_interval">Интервал ускорения (секунды):</label>
        <input type="number" id="speed_interval" name="speed_interval" min="1" max="60" step="1" required>
        <br><br>

        <button type="submit">Сохранить настройки и начать тест</button>
    </form>
</body>
</html>