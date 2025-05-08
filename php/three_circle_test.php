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

// Обработка сохранения результатов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mean_reaction_time'])) {
    $user_id = $_SESSION['user_id'] ?? 0; // Получите ID пользователя из сессии
    $test_name = "Тест с тремя кругами";
    $mean_reaction_time = $_POST['mean_reaction_time'] ?? 0;
    $std_dev = $_POST['std_dev'] ?? 0;
    $accuracy = $_POST['accuracy'] ?? 0;
    $incorrect_responses = $_POST['incorrect_responses'] ?? 0;
    $misses = $_POST['misses'] ?? 0;

    $sql = "INSERT INTO test_results (user_id, test_name, mean_reaction_time, std_dev, accuracy, incorrect_responses, misses)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdiii", $user_id, $test_name, $mean_reaction_time, $std_dev, $accuracy, $incorrect_responses, $misses);
    $stmt->execute();
    $stmt->close();

    echo "Результаты успешно сохранены!";
    exit();
}

// Получение настроек из сессии
$testOptions = $_SESSION['three_circle_test_options'] ?? [
    'duration' => 2, // Значения по умолчанию
    'showTimer' => true,
    'showProgress' => true,
    'speedIncrease' => 5,
    'speedInterval' => 10
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест с тремя кругами</title>
    <style>
        body {
            color: white;
            text-align: center;
            font-family: Arial, sans-serif;
        }
        .circle-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 50px;
        }
        .circle {
            width: 200px;
            height: 200px;
            border: 5px solid white;
            border-radius: 50%;
            position: relative;
        }
        .indicator {
            position: absolute;
            top: -5px; /* Расположение полоски сверху круга */
            left: 50%;
            width: 10px; /* Ширина полоски */
            height: 20px; /* Высота полоски */
            background-color: red; /* Цвет полоски */
            transform: translateX(-50%);
            z-index: 1; /* Убедитесь, что полоска находится выше круга */
            border-radius: 5px; /* Закругленные края полоски */
        }
        .dot {
            background-color: #FFFFFF;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            position: absolute;
            top: 0;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        #testContainer {
            display: none;
        }
        .back-button {
            color: black;
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 10px 15px;
            background-color: rgb(56, 210, 81);
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/settings.css">
</head>
<body>
<a href="three_circle_settings.php" class="back-button">Назад</a>
    <h1>Тест с тремя кругами</h1>
    <div id="description">
        <p>На экране будут отображаться три круга, по которым движутся точки. Ваша задача — нажимать клавиши "1", "2" или "3" в зависимости от круга, когда точка находится в самой верхней точке круга.</p>
        <button id="startButton">Начать тест</button>
    </div>

    <div id="testContainer">
        <div class="circle-container">
        <div class="circle" id="circle1">
            <div class="indicator"></div> <!-- Полоска сверху круга -->
            <div class="dot" id="dot1"></div>
        </div>
        <div class="circle" id="circle2">
            <div class="indicator"></div> <!-- Полоска сверху круга -->
            <div class="dot" id="dot2"></div>
        </div>
        <div class="circle" id="circle3">
            <div class="indicator"></div> <!-- Полоска сверху круга -->
            <div class="dot" id="dot3"></div>
        </div></div>
        <p id="timer"></p>
        <p id="progress"></p>
        <button id="finishButton">Завершить тест</button>
        <div id="results" style="display: none;"></div>
    </div>

    <script>
        // Получаем настройки из PHP-сессии
        const testOptions = <?php echo json_encode($testOptions); ?>;

        // Инициализация настроек
        const duration = testOptions.duration * 60; // Перевод минут в секунды
        const showTimer = testOptions.showTimer;
        const showProgress = testOptions.showProgress;
        const speedIncrease = testOptions.speedIncrease;
        const speedInterval = testOptions.speedInterval;

        let startTime;
        let speeds = [1, 1.5, 2]; // Скорости вращения для каждого круга
        let angles = [0, 0, 0];
        let interval;
        let reactionTimes = [];
        let misses = 0;
        let incorrectResponses = 0; // Ошибки
        let isTestFinished = false; // Флаг для отслеживания завершения теста

        const dots = [
            document.getElementById('dot1'),
            document.getElementById('dot2'),
            document.getElementById('dot3')
        ];
        const timerElement = document.getElementById('timer');
        const progressElement = document.getElementById('progress');
        const finishButton = document.getElementById('finishButton');
        const resultsElement = document.getElementById('results');
        const startButton = document.getElementById('startButton');
        const description = document.getElementById('description');
        const testContainer = document.getElementById('testContainer');

        // Обновление позиции точек
        function updateDots() {
            dots.forEach((dot, index) => {
                angles[index] += speeds[index];
                if (angles[index] >= 360) angles[index] = 0;

                const radians = (angles[index] * Math.PI) / 180;
                const x = 100 + 90 * Math.cos(radians);
                const y = 100 + 90 * Math.sin(radians);

                dot.style.left = `${x}px`;
                dot.style.top = `${y}px`;
            });
        }

        // Таймер
        function updateTimer() {
            const elapsed = Math.floor((Date.now() - startTime) / 1000);
            const remaining = duration - elapsed;
            if (showTimer) {
                timerElement.textContent = `Оставшееся время: ${remaining} секунд`;
            }
            if (remaining <= 0) {
                finishTest();
            }
        }

        // Прогресс
        function updateProgress() {
            const elapsed = Math.floor((Date.now() - startTime) / 1000);
            const progress = Math.min((elapsed / duration) * 100, 100);
            if (showProgress) {
                progressElement.textContent = `Прогресс: ${progress.toFixed(2)}%`;
            }
        }

        // Обработка нажатия клавиш
        document.addEventListener("keydown", (event) => {
            if (isTestFinished) return; // Если тест завершён, игнорируем нажатие

            const keyMap = { Digit1: 0, Digit2: 1, Digit3: 2 };
            if (keyMap[event.code] !== undefined) {
                const circleIndex = keyMap[event.code];
                const currentAngle = angles[circleIndex] % 360; // Угол точки
                let timeDifference;

                // Расчет времени до верхней точки
                if (currentAngle >= 355 || currentAngle <= 5) {
                    timeDifference = currentAngle <= 5 ? currentAngle : -(360 - currentAngle);
                } else {
                    timeDifference = currentAngle;
                    incorrectResponses++; // Увеличиваем количество ошибок
                }

                // Запись данных
                reactionTimes.push(timeDifference); // Время со знаком
            }
        });

        // Завершение теста
        function finishTest() {
            clearInterval(interval);
            isTestFinished = true; // Устанавливаем флаг завершения теста

            // Рассчитать результаты
            const meanReactionTime = reactionTimes.length
                ? reactionTimes.reduce((a, b) => a + b, 0) / reactionTimes.length
                : 0;

            const stdDev = reactionTimes.length
                ? Math.sqrt(
                      reactionTimes.reduce((a, b) => a + Math.pow(b - meanReactionTime, 2), 0) /
                          reactionTimes.length
                  )
                : 0;

            const accuracy = reactionTimes.length
                ? ((reactionTimes.length - incorrectResponses) / reactionTimes.length) * 100
                : 0;

            // Показать результаты
            resultsElement.style.display = 'block';
            resultsElement.innerHTML = `
                <p>Среднее время реакции (мс): ${meanReactionTime.toFixed(2)}</p>
                <p>Стандартное отклонение: ${stdDev.toFixed(2)}</p>
                <p>Точность (%): ${accuracy.toFixed(2)}</p>
                <p>Ошибки: ${incorrectResponses}</p>
                <p>Пропуски: ${misses}</p>
            `;

            // Отправка результатов на сервер
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "three_circle_test.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                }
            };
            xhr.send(
                `mean_reaction_time=${meanReactionTime.toFixed(2)}&std_dev=${stdDev.toFixed(2)}&accuracy=${accuracy.toFixed(2)}&incorrect_responses=${incorrectResponses}&misses=${misses}`
            );
        }

        // Ускорение
        setInterval(() => {
            speeds = speeds.map(speed => speed + speedIncrease / 100);
        }, speedInterval * 1000);

        // Запуск теста
        function startTest() {
            description.style.display = 'none';
            testContainer.style.display = 'block';
            startTime = Date.now();
            interval = setInterval(() => {
                updateDots();
                updateTimer();
                updateProgress();
            }, 16); // 60 FPS
        }

        // Привязка кнопки "Начать тест"
        startButton.onclick = startTest;

        // Привязка кнопки "Завершить тест"
        finishButton.onclick = finishTest;
    </script>
</body>
</html>