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
    $test_name = "Тест на числа (экран)";
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
    <title>Тест на числа (экран)</title>
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
        #numbers {
            font-size: 48px;
            text-align: center;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
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
        <div class="heading_text">Тест на числа (экран)</div>
    </header>
    <div id="description">
        <p>10 раз будут воспроизводиться два числа в виде цифр на экране.</p>
        <p>Ваша задача - нажимать соответствующую клавишу в ответ на сумму чисел.</p>
        <p>Q – четная сумма, W – нечетная сумма.</p>
        <p>Система будет считывать среднее время вашей реакции, точность ответов, количество ошибок и пропусков.</p>
        <p>Нажмите "Готов", чтобы начать тест.</p>
    </div>
    <div id="keyBindings">
        <p>Q – четная сумма</p>
        <p>W – нечетная сумма</p>
    </div>
    <button id="startButton">Готов</button>
    <div id="results"></div>
    <div id="numbers"></div>
    <script>
        let reactionTimes = [];
        let correctResponses = 0;
        let incorrectResponses = 0;
        let misses = 0;
        let startTime;
        let startButton = document.getElementById('startButton');
        let results = document.getElementById('results');
        let description = document.getElementById('description');
        let keyBindings = document.getElementById('keyBindings');
        keyBindings.style.display = 'none';
        let numbers = document.getElementById('numbers');
        let totalSignals = 10; // Установим количество сигналов
        let signalsShown = 0;
        let timeout;
        let currentSum;

        function displayNumbers() {
            if (signalsShown >= totalSignals) {
                calculateResults();
                return;
            }
            signalsShown++;
            let num1 = Math.floor(Math.random() * 10) + 1;
            let num2 = Math.floor(Math.random() * 10) + 1;
            currentSum = num1 + num2;
            numbers.innerHTML = `${num1} + ${num2}`;
            startTime = new Date().getTime();
            timeout = setTimeout(() => {
                numbers.innerHTML = '';
                misses++;
                displayNumbers();
            }, 2000); // Время на ответ - 2 секунды
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
            xhr.open("POST", "number_display_test.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                }
            };
            xhr.send(`mean_reaction_time=${mean}&std_dev=${stdDev}&accuracy=${accuracy}&incorrect_responses=${incorrectResponses}&misses=${misses}`);
        }

        document.body.onkeydown = function(e) {
            if (numbers.innerHTML !== '') {
                let reactionTime = new Date().getTime() - startTime;
                if ((e.code === 'KeyQ' && currentSum % 2 === 0) || (e.code === 'KeyW' && currentSum % 2 !== 0)) {
                    reactionTimes.push(reactionTime);
                    correctResponses++;
                } else {
                    incorrectResponses++;
                }
                numbers.innerHTML = '';
                clearTimeout(timeout); // Остановить таймер после нажатия
                setTimeout(displayNumbers, Math.random() * 1000 + 500); // Показать следующие числа через случайное время
            }
        };

        startButton.onclick = function() {
            startButton.style.display = 'none';
            description.style.display = 'none'; // Скрыть описание
            keyBindings.style.display = 'block'; // Показать назначения клавиш
            reactionTimes = [];
            correctResponses = 0;
            incorrectResponses = 0;
            misses = 0;
            signalsShown = 0;
            results.innerHTML = ''; // Очистить результаты
            setTimeout(displayNumbers, Math.random() * 1000 + 500); // Начать показ чисел через случайное время
        };
    </script>
</body>
</html>