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
    $test_name = "Аналоговое преследование";
    $mean_reaction_time = $_POST['mean_reaction_time'] ?? 0;
    $std_dev = $_POST['std_dev'] ?? 0;
    $accuracy = $_POST['accuracy'] ?? 0;

    $sql = "INSERT INTO test_results (user_id, test_name, mean_reaction_time, std_dev, accuracy)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isddd", $user_id, $test_name, $mean_reaction_time, $std_dev, $accuracy);
    $stmt->execute();
    $stmt->close();

    echo "Результаты сохранены!";
    exit();
}

$testOptions = $_SESSION['analog_pursuit_options'] ?? [
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
    <title>Аналоговое преследование</title>
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
        .target {
            width: 50px;
            height: 50px;
            background-color: white;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }
        .crosshair {
            width: 20px;
            height: 20px;
            background-color: green;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>
<body>
    <a href="analog_pursuit_settings.php" class="back-button">Назад</a>
    <h1 class="heading_text">Аналоговое преследование</h1>
    <div id="description">
        <p>Используйте стрелки влево и вправо, чтобы удерживать зеленый прицел на белом кружке.</p>
        <button id="startButton">Начать тест</button>
    </div>
    <div id="testContainer" style="display:none;">
        <div class="track-container" id="track">
            <div class="target" id="target"></div>
            <div class="crosshair" id="crosshair"></div>
        </div>
        <div id="timer"></div>
        <div id="progress"></div>
    </div>
    <div id="results"></div>
    <script>
        const duration = <?php echo (int)$testOptions['duration']; ?>;
        const showTimer = <?php echo $testOptions['showTimer'] ? 'true' : 'false'; ?>;
        const showProgress = <?php echo $testOptions['showProgress'] ? 'true' : 'false'; ?>;
        const speedIncrease = <?php echo (float)$testOptions['speedIncrease']; ?>;
        const speedInterval = <?php echo (int)$testOptions['speedInterval']; ?>;

        const track = document.getElementById('track');
        const target = document.getElementById('target');
        const crosshair = document.getElementById('crosshair');
        const timerDiv = document.getElementById('timer');
        const progressDiv = document.getElementById('progress');
        const resultsDiv = document.getElementById('results');
        const startButton = document.getElementById('startButton');
        const description = document.getElementById('description');
        const testContainer = document.getElementById('testContainer');

        let targetPos = 300;
        let crosshairPos = 0;
        let targetSpeed = 2;
        let crosshairSpeed = 0;
        let startTime, interval, speedIntervalId, randomDirInterval, overlapTime = 0, lastOverlap = false, reactionTimes = [], reacted = false, lastMoveTime = 0, frame = 0;

        function randomizeTargetSpeed() {
            let maxSpeed = 4;
            let minSpeed = 2;
            let direction = Math.random() < 0.5 ? -1 : 1;
            targetSpeed = direction * (minSpeed + Math.random() * (maxSpeed - minSpeed));
        }

        function startTest() {
            description.style.display = 'none';
            testContainer.style.display = 'block';
            resultsDiv.innerHTML = '';
            targetPos = 300;
            crosshairPos = 0;
            randomizeTargetSpeed();
            crosshairSpeed = 0;
            overlapTime = 0;
            reactionTimes = [];
            reacted = false;
            lastMoveTime = Date.now();
            frame = 0;
            startTime = Date.now();
            interval = setInterval(update, 16);
            speedIntervalId = setInterval(() => {
                // targetSpeed += targetSpeed * (speedIncrease / 100);
            }, speedInterval * 1000);
            randomDirInterval = setInterval(randomizeTargetSpeed, 1000);
        }

        function update() {
            frame++;
            targetPos += targetSpeed;
            if (targetPos < 0) {
                targetPos = 0;
                targetSpeed *= -1;
            }
            if (targetPos > 550) {
                targetPos = 550;
                targetSpeed *= -1;
            }
            target.style.left = targetPos + 'px';

            crosshairPos += crosshairSpeed;
            if (crosshairPos < 0) crosshairPos = 0;
            if (crosshairPos > 580) crosshairPos = 580;
            crosshair.style.left = crosshairPos + 'px';

            let overlap = Math.abs((crosshairPos + 10) - (targetPos + 25)) < 25;
            if (overlap) {
                overlapTime += 16;
            }
            if (!reacted && (crosshairSpeed !== 0)) {
                reactionTimes.push(Date.now() - lastMoveTime);
                reacted = true;
            }
            if (!overlap && lastOverlap) {
                lastMoveTime = Date.now();
                reacted = false;
            }
            lastOverlap = overlap;

            if (showTimer) {
                let elapsed = Math.floor((Date.now() - startTime) / 1000);
                timerDiv.textContent = `Оставшееся время: ${duration - elapsed} сек.`;
            }
            if (showProgress) {
                let elapsed = Math.floor((Date.now() - startTime) / 1000);
                let progress = Math.min((elapsed / duration) * 100, 100);
                progressDiv.textContent = `Прогресс: ${progress.toFixed(2)}%`;
            }
            if ((Date.now() - startTime) / 1000 >= duration) {
                clearInterval(interval);
                clearInterval(speedIntervalId);
                clearInterval(randomDirInterval);
                finishTest();
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.code === 'ArrowLeft') crosshairSpeed = -4;
            if (e.code === 'ArrowRight') crosshairSpeed = 4;
        });
        document.addEventListener('keyup', function(e) {
            if (e.code === 'ArrowLeft' || e.code === 'ArrowRight') crosshairSpeed = 0;
        });

        function finishTest() {
            testContainer.style.display = 'none';
            let mean = reactionTimes.length ? reactionTimes.reduce((a, b) => a + b, 0) / reactionTimes.length : 0;
            let stdDev = reactionTimes.length ? Math.sqrt(reactionTimes.reduce((a, b) => a + Math.pow(b - mean, 2), 0) / reactionTimes.length) : 0;
            let accuracy = (overlapTime / (duration * 1000)) * 100;

            // Показываем результаты всегда
            resultsDiv.innerHTML = `Среднее время реакции: ${mean.toFixed(2)} мс<br>Стандартное отклонение: ${stdDev.toFixed(2)}<br>Точность слежения: ${accuracy.toFixed(2)}%`;
            resultsDiv.style.display = 'block';

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "analog_pursuit_test.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send(`mean_reaction_time=${mean}&std_dev=${stdDev}&accuracy=${accuracy}`);
        }

        startButton.onclick = startTest;
    </script>
</body>
</html>