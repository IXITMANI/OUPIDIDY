<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../html/login.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MyUsers";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $test_name = "Тест на цвет";
    $mean_reaction_time = $_POST['mean_reaction_time'];
    $std_dev = $_POST['std_dev'];
    $accuracy = $_POST['accuracy'];
    $incorrect_responses = $_POST['incorrect_responses'];
    $misses = $_POST['misses'];

    $sql = "INSERT INTO test_results (user_id, test_name, mean_reaction_time, std_dev, accuracy, incorrect_responses, misses)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdiii", $user_id, $test_name, $mean_reaction_time, $std_dev, $accuracy, $incorrect_responses, $misses);
    $stmt->execute();
    $stmt->close();

    echo "Результаты успешно сохранены!";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест на реакцию на цвет</title>
    <link rel="stylesheet" type="text/css" href="../css/reaction_test.css">
    <link rel="stylesheet" type="text/css" href="../css/nav.css">

    <style>
        #keyBindings {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 18px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header class="heading" style="background-color: #13141d86;">
        <nav class="links_header">
            <ul class="nav_links">
                <li><a href="./user.php">Назад</a></li>
            </ul>
        </nav>
        <div class="heading_text">тест на цвет</div>
    </header>
    <div id="description">
        <p>На экране будет появляться круг красного, синего или зелёного цвета.</p>
        <p>Ваша задача - нажимать соответствующую клавишу в ответ на появление круга.</p>
        <p>Система будет считывать среднее время вашей реакции, точность ответов, количество ошибок и пропусков.</p>
        <p>Нажмите "Готов", чтобы начать тест.</p>
    </div>
    <div id="keyBindings">
        <p>1 – красный</p>
        <p>2 – синий</p>
        <p>3 – зелёный</p>
    </div>
    <div id="circle"></div>
    <button id="startButton">Готов</button>
    <div id="results"></div>
    <script>
        let reactionTimes = [];
        let correctResponses = 0;
        let incorrectResponses = 0;
        let misses = 0;
        let startTime;
        let circle = document.getElementById('circle');
        let startButton = document.getElementById('startButton');
        let results = document.getElementById('results');
        let description = document.getElementById('description');
        let keyBindings = document.getElementById('keyBindings');
        let totalSignals = 30; // Установим количество кругов на 30 (примерно 1 сигнал в секунду)
        let signalsShown = 0;
        let timeout;
        let colors = ['red', 'blue', 'green'];
        let colorKeys = {
            'red': 'Digit1',
            'blue': 'Digit2',
            'green': 'Digit3'
        };
        let currentColor;

        function showCircle() {
            if (signalsShown >= totalSignals) {
                calculateResults();
                return;
            }
            signalsShown++;
            currentColor = colors[Math.floor(Math.random() * colors.length)];
            circle.style.backgroundColor = currentColor;
            circle.style.display = 'block';
            startTime = new Date().getTime();
            timeout = setTimeout(hideCircle, Math.random() * 1000 + 500); // Показать круг на случайное время от 500 до 1500 мс
        }

        function hideCircle() {
            if (circle.style.display === 'block') {
                circle.style.display = 'none';
                misses++;
                setTimeout(showCircle, Math.random() * 1000 + 500); // Показать следующий круг через случайное время
            }
        }

        function calculateResults() {
            let sum = reactionTimes.reduce((a, b) => a + b, 0);
            let mean = sum / reactionTimes.length;
            let variance = reactionTimes.reduce((a, b) => a + Math.pow(b - mean, 2), 0) / reactionTimes.length;
            let stdDev = Math.sqrt(variance);
            let accuracy = (correctResponses / (correctResponses + incorrectResponses)) * 100;

            results.innerHTML = `
                <p>Среднее время реакции: ${mean.toFixed(2)} мс</p>
                <p>Стандартное отклонение: ${stdDev.toFixed(2)} мс</p>
                <p>Точность ответов: ${accuracy.toFixed(2)}%</p>
                <p>Количество ошибок: ${incorrectResponses}</p>
                <p>Количество пропусков: ${misses}</p>
            `;

            saveResults(mean.toFixed(2), stdDev.toFixed(2), accuracy.toFixed(2), incorrectResponses, misses);
            startButton.style.display = 'block'; // Показать кнопку "Готов"
            description.style.display = 'block'; // Показать описание
            keyBindings.style.display = 'none'; // Скрыть назначения клавиш
        }

        function saveResults(mean, stdDev, accuracy, incorrectResponses, misses) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "color_test.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                }
            };
            xhr.send(`mean_reaction_time=${mean}&std_dev=${stdDev}&accuracy=${accuracy}&incorrect_responses=${incorrectResponses}&misses=${misses}`);
        }

        document.body.onkeydown = function(e) {
            if (circle.style.display === 'block') {
                let reactionTime = new Date().getTime() - startTime;
                if (e.code === colorKeys[currentColor]) {
                    reactionTimes.push(reactionTime);
                    correctResponses++;
                } else {
                    incorrectResponses++;
                }
                circle.style.display = 'none';
                clearTimeout(timeout); // Остановить таймер после нажатия
                setTimeout(showCircle, Math.random() * 1000 + 500); // Показать следующий круг через случайное время
            }
        };

        startButton.onclick = function() {
            startButton.style.display = 'none';
            description.style.display = 'none'; // Показать описание
            keyBindings.style.display = 'block'; // Показать назначения клавиш
            reactionTimes = [];
            correctResponses = 0;
            incorrectResponses = 0;
            misses = 0;
            signalsShown = 0;
            results.innerHTML = ''; // Очистить результаты
            setTimeout(showCircle, Math.random() * 1000 + 500); // Начать показ кругов через случайное время
        };
    </script>
</body>
</html>