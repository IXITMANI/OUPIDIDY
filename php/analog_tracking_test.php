<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MyUsers";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mean_reaction_time'])) {
    $user_id = $_SESSION['user_id'] ?? 0;
    $test_name = "Аналоговое слежение";
    $mean_reaction_time = $_POST['mean_reaction_time'] ?? 0;
    $std_dev = $_POST['std_dev'] ?? 0;

    $sql = "INSERT INTO test_results (user_id, test_name, mean_reaction_time, std_dev)
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdd", $user_id, $test_name, $mean_reaction_time, $std_dev);
    $stmt->execute();
    $stmt->close();

    echo "Результаты сохранены!";
    exit();
}

$testOptions = $_SESSION['analog_tracking_options'] ?? [
    'duration' => 120,
    'showTimer' => true,
    'showResults' => true,
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
    <title>Аналоговое слежение</title>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <style>
        .track-container {
            width: 600px;
            height: 100px;
            background-color: #333;
            margin: 50px auto;
            position: relative;
            border: 2px solid white;
        }
        .circle {
            width: 50px;
            height: 50px;
            background-color: white;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }
        .center-line {
            position: absolute;
            top: 0;
            left: 50%;
            width: 10px;
            height: 100%;
            background-color: black;
            transform: translateX(-50%);
        }
    </style>
</head>
<body>
    <a href="analog_tracking_settings.php" class="back-button">Назад</a>
    <h1 class="heading_text">Аналоговое слежение</h1>
    <div id="description">
        <p>Используйте стрелки влево и вправо, чтобы вернуть кружок в центр (между черными полосками).</p>
        <button id="startButton">Начать тест</button>
    </div>
    <div id="testContainer" style="display: none;">
        <div class="track-container">
            <div class="center-line"></div>
            <div class="circle" id="circle"></div>
        </div>
        <p id="timer"></p>
        <p id="progress"></p>
        <button id="finishButton">Завершить тест</button>
        <div id="results" style="display: none;"></div>
    </div>

    <script>
        const testOptions = <?php echo json_encode($testOptions); ?>;
        const duration = testOptions.duration;
        const showTimer = testOptions.showTimer;
        const showResults = testOptions.showResults;
        const showProgress = testOptions.showProgress;
        const speedIncrease = testOptions.speedIncrease;
        const speedInterval = testOptions.speedInterval;

        let startTime;
        let circle = document.getElementById('circle');
        let position = 300;
        let speed = 0;
        let reactionTimes = [];
        let interval;
        let isTestFinished = false;

        const timerElement = document.getElementById('timer');
        const progressElement = document.getElementById('progress');
        const resultsElement = document.getElementById('results');
        const startButton = document.getElementById('startButton');
        const finishButton = document.getElementById('finishButton');
        const description = document.getElementById('description');
        const testContainer = document.getElementById('testContainer');

        function updateCircle() {
            position += speed;
            if (position < 0) position = 0;
            if (position > 550) position = 550;
            circle.style.left = `${position}px`;
        }

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

        function updateProgress() {
            const elapsed = Math.floor((Date.now() - startTime) / 1000);
            const progress = Math.min((elapsed / duration) * 100, 100);
            if (showProgress) {
                progressElement.textContent = `Прогресс: ${progress.toFixed(2)}%`;
            }
        }

        function randomMovement() {
            if (Math.random() < 0.1) {
                speed = (Math.random() - 0.5) * 4;
                reactionTimes.push({ start: Date.now(), reacted: false });
            }
        }

        document.addEventListener('keydown', (event) => {
            if (isTestFinished) return;
            if (event.code === 'ArrowLeft') {
                speed -= 2;
            } else if (event.code === 'ArrowRight') {
                speed += 2;
            }
            reactionTimes.forEach(rt => {
                if (!rt.reacted && speed !== 0) {
                    rt.reacted = true;
                    rt.time = Date.now() - rt.start;
                }
            });
        });

        function finishTest() {
        clearInterval(interval);
        isTestFinished = true;

        const meanReactionTime = reactionTimes.length
            ? reactionTimes.filter(rt => rt.reacted).reduce((a, b) => a + b.time, 0) / reactionTimes.filter(rt => rt.reacted).length
            : 0;
        const stdDev = reactionTimes.length
            ? Math.sqrt(reactionTimes.filter(rt => rt.reacted).reduce((a, b) => a + Math.pow(b.time - meanReactionTime, 2), 0) / reactionTimes.filter(rt => rt.reacted).length)
            : 0;

        // Показываем результаты всегда
        resultsElement.style.display = 'block';
        resultsElement.innerHTML = `
            <p>Среднее время реакции (мс): ${meanReactionTime.toFixed(2)}</p>
            <p>Стандартное отклонение: ${stdDev.toFixed(2)}</p>
        `;

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "analog_tracking_test.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send(`mean_reaction_time=${meanReactionTime.toFixed(2)}&std_dev=${stdDev.toFixed(2)}`);
    }

        setInterval(() => {
            speed *= (1 + speedIncrease / 100);
        }, speedInterval * 1000);

        function startTest() {
            description.style.display = 'none';
            testContainer.style.display = 'block';
            startTime = Date.now();
            interval = setInterval(() => {
                randomMovement();
                updateCircle();
                updateTimer();
                updateProgress();
            }, 16);
        }

        startButton.onclick = startTest;
        finishButton.onclick = finishTest;
    </script>
</body>
</html>