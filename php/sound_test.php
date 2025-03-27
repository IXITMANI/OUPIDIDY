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
    $test_name = "Тест на звук";
    $mean_reaction_time = $_POST['mean_reaction_time'];
    $std_dev = $_POST['std_dev'];
    $misses = $_POST['misses'];

    $sql = "INSERT INTO test_results (user_id, test_name, mean_reaction_time, std_dev, misses)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdi", $user_id, $test_name, $mean_reaction_time, $std_dev, $misses);
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
    <title>Тест на реакцию на звук</title>
    <link rel="stylesheet" type="text/css" href="../css/reaction_test.css">
    <link rel="stylesheet" type="text/css" href="../css/nav.css">
</head>
<body>
    <header class="heading" style="background-color: #13141d86;">
        <nav class="links_header">
            <ul class="nav_links">
                <li><a href="./user.php">Назад</a></li>
            </ul>
        </nav>
        <div class="heading_text">тест на звук</div>
    </header>
    <div id="description">
        <p>На протяжении времени будет проигрываться четкий звук с рандомной периодичностью.</p>
        <p>Ваша задача - нажимать пробел в ответ, когда услышите звук.</p>
        <p>Система будет считывать среднее время вашей реакции и количество пропусков.</p>
        <p>Нажмите "Готов", чтобы начать тест.</p>
    </div>
    <button id="startButton">Готов</button>
    <div id="results"></div>
    <audio id="sound" src="../sounds/beep.mp3"></audio>
    <script>
        let reactionTimes = [];
        let misses = 0;
        let startTime;
        let startButton = document.getElementById('startButton');
        let results = document.getElementById('results');
        let description = document.getElementById('description');
        let sound = document.getElementById('sound');
        let totalSignals = 5; // Установим количество звуков на 5
        let signalsShown = 0;
        let timeout;

        function playSound() {
            if (signalsShown >= totalSignals) {
                calculateResults();
                return;
            }
            signalsShown++;
            sound.play();
            startTime = new Date().getTime();
            timeout = setTimeout(() => {
                misses++;
                playSound();
            }, 2000); // Время на ответ - 2 секунды
        }

        function calculateResults() {
            let sum = reactionTimes.reduce((a, b) => a + b, 0);
            let mean = sum / reactionTimes.length;
            let variance = reactionTimes.reduce((a, b) => a + Math.pow(b - mean, 2), 0) / reactionTimes.length;
            let stdDev = Math.sqrt(variance);

            results.innerHTML = `
                <p>Среднее время реакции: ${mean.toFixed(2)} мс</p>
                <p>Стандартное отклонение: ${stdDev.toFixed(2)} мс</p>
                <p>Количество пропусков: ${misses}</p>
            `;

            saveResults(mean.toFixed(2), stdDev.toFixed(2), misses);
            startButton.style.display = 'block'; // Показать кнопку "Готов"
            description.style.display = 'block'; // Показать описание
        }

        function saveResults(mean, stdDev, misses) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "sound_test.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                }
            };
            xhr.send(`mean_reaction_time=${mean}&std_dev=${stdDev}&misses=${misses}`);
        }

        document.body.onkeydown = function(e) {
            if (e.code === 'Space' && !sound.paused) {
                let reactionTime = new Date().getTime() - startTime;
                reactionTimes.push(reactionTime);
                sound.pause();
                sound.currentTime = 0;
                clearTimeout(timeout); // Остановить таймер после нажатия
                setTimeout(playSound, Math.random() * 4500 + 500); // Проиграть следующий звук через случайное время от 500 до 5000 мс
            }
        };

        startButton.onclick = function() {
            startButton.style.display = 'none';
            description.style.display = 'none'; // Скрыть описание
            reactionTimes = [];
            misses = 0;
            signalsShown = 0;
            results.innerHTML = ''; // Очистить результаты
            setTimeout(playSound, Math.random() * 4500 + 500); // Начать проигрывание звуков через случайное время от 500 до 5000 мс
        };
    </script>
</body>
</html>