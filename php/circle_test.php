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
    $test_name = "Тест с кругом";
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
$testOptions = $_SESSION['test_options'] ?? [
    'duration' => 2, // Значения по умолчанию
    'showTimer' => true,
    'showResultsPerMinute' => false,
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
    <title>Тест</title>
    <style>
        body {
            color: white;
            text-align: center;
            font-family: Arial, sans-serif;
        }
        .circle {
            
            width: 300px;
            height: 300px;
            border: 5px solid white;
            border-radius: 50%;
            margin: 50px auto;
            position: relative;
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
<a href="test_settings.php" class="back-button">Назад</a>
    <h1>Тест с кругом</h1>
    <div id="description">
        <p>На экране будет отображаться круг, по которому движется точка. Ваша задача — нажимать клавишу "Пробел" в момент, когда точка находится в самой верхней точке круга.</p>
        <p>Если вы нажмете раньше, результат будет положительным, если позже — отрицательным.</p>
        <button id="startButton">Начать тест</button>
    </div>

    <div id="testContainer">
        <div class="circle">
            <div class="dot" id="dot"></div>
        </div>
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
        let speed = 1; // Скорость движения точки
        let angle = 0;
        let interval;
        let reactionTimes = [];
        let misses = 0;
        let incorrectResponses = 0; // Ошибки
        let isTestFinished = false; // Флаг для отслеживания завершения теста

        const dot = document.getElementById('dot');
        const timerElement = document.getElementById('timer');
        const progressElement = document.getElementById('progress');
        const finishButton = document.getElementById('finishButton');
        const resultsElement = document.getElementById('results');
        const startButton = document.getElementById('startButton');
        const description = document.getElementById('description');
        const testContainer = document.getElementById('testContainer');

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

        // Обработка нажатия клавиши пробел
        document.addEventListener("keydown", (event) => {
            if (isTestFinished) return; // Если тест завершён, игнорируем нажатие

            if (event.code === "Space") {
                const currentAngle = angle % 360; // Угол точки
                let timeDifference;

                // Расчет времени до верхней точки
                if (currentAngle >= 355 || currentAngle <= 5) {
                    timeDifference = currentAngle <= 5 ? currentAngle : -(360 - currentAngle);
                } else if (currentAngle < 355) {
                    timeDifference = currentAngle;
                    incorrectResponses++; // Увеличиваем количество ошибок
                } else {
                    timeDifference = -(360 - currentAngle);
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
    xhr.open("POST", "circle_test.php", true);
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
            speed += speedIncrease / 100;
        }, speedInterval * 1000);

        // Запуск теста
        function startTest() {
            description.style.display = 'none';
            testContainer.style.display = 'block';
            startTime = Date.now();
            interval = setInterval(() => {
                updateDot();
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