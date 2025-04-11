<!-- filepath: c:\xampp\htdocs\OUPIDIDY\php\test.php -->
<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();

}

// Получаем настройки теста
$test_duration = $_POST['test_duration'];
$show_timer = $_POST['show_timer'];
$show_results = $_POST['show_results'];
$show_progress = $_POST['show_progress'];
$speed_increase = $_POST['speed_increase'];
$speed_interval = $_POST['speed_interval'];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест</title>
    <style>
        body {
            text-align: center;
            font-family: Arial, sans-serif;
        }
        .circle {
            width: 300px;
            height: 300px;
            border: 5px solid black;
            border-radius: 50%;
            margin: 50px auto;
            position: relative;
        }
        .dot {
            width: 20px;
            height: 20px;
            background-color: red;
            border-radius: 50%;
            position: absolute;
            top: 0;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
</head>
<body>
    <h1>Тест</h1>
    <div class="circle">
        <div class="dot" id="dot"></div>
    </div>
    <p id="timer"></p>
    <p id="progress"></p>

    <script>
        const duration = <?php echo $test_duration * 60; ?>; // Время теста в секундах
        const showTimer = <?php echo $show_timer; ?>;
        const showProgress = <?php echo $show_progress; ?>;
        const speedIncrease = <?php echo $speed_increase; ?>;
        const speedInterval = <?php echo $speed_interval; ?>;

        let angle = 0;
        let speed = 1; // Скорость движения точки
        const dot = document.getElementById('dot');
        const timerElement = document.getElementById('timer');
        const progressElement = document.getElementById('progress');
        let startTime = Date.now();

        // Обновление позиции точки
        function updateDot() {
            angle += speed;
            if (angle >= 360) angle = 0;

            const radians = (angle * Math.PI) / 180;
            const x = 150 + 140 * Math.cos(radians);
            const y = 150 + 140 * Math.sin(radians);

            dot.style.left = `${x}px`;
            dot.style.top = `${y}px`;
        }

        // Таймер
        function updateTimer() {
            if (showTimer) {
                const elapsed = Math.floor((Date.now() - startTime) / 1000);
                const remaining = duration - elapsed;
                timerElement.textContent = `Оставшееся время: ${remaining} секунд`;
                if (remaining <= 0) {
                    clearInterval(interval);
                    alert('Тест завершён!');
                }
            }
        }

        // Прогресс
        function updateProgress() {
            if (showProgress) {
                const elapsed = Math.floor((Date.now() - startTime) / 1000);
                const progress = Math.min((elapsed / duration) * 100, 100);
                progressElement.textContent = `Прогресс: ${progress.toFixed(2)}%`;
            }
        }

        // Ускорение
        setInterval(() => {
            speed += speedIncrease / 100;
        }, speedInterval * 1000);

        // Запуск теста
        const interval = setInterval(() => {
            updateDot();
            updateTimer();
            updateProgress();
        }, 16); // 60 FPS
    </script>
</body>
</html>