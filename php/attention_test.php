<?php
session_start();

// Подключение к базе данных
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MyUsers";
$mean_reaction_time = 0;
$std_dev = 0;
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['correct_responses'])) {
    $user_id = $_SESSION['user_id'] ?? 0;
    $test_name = "Тест на внимание";
    $difficulty = $_SESSION['attention_test_options']['difficulty'] ?? 'easy';
    $correct_responses = $_POST['correct_responses'] ?? 0;
    $incorrect_responses = $_POST['incorrect_responses'] ?? 0;
    $misses = $_POST['misses'] ?? 0;

    $total_attempts = $correct_responses + $incorrect_responses + $misses;
    $accuracy = $total_attempts > 0 ? ($correct_responses / $total_attempts) * 100 : 0;

    $sql = "INSERT INTO test_results (user_id, test_name, mean_reaction_time, std_dev, accuracy, incorrect_responses, misses, difficulty)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Ошибка подготовки запроса: " . $conn->error);
    }
    $stmt->bind_param("issdiiis", $user_id, $test_name, $mean_reaction_time, $std_dev, $accuracy, $incorrect_responses, $misses, $difficulty);

    if (!$stmt->execute()) {
        die("Ошибка выполнения запроса: " . $stmt->error);
    }
    $stmt->close();
    echo "Результаты успешно сохранены!";
    exit();
}

// Получаем настройки из сессии
$testOptions = $_SESSION['attention_test_options'] ?? [
    'difficulty' => 'easy',
    'time_per_pair' => 5,
    'colors' => ['red', 'blue', 'green'],
    'number_range' => ['min' => 0, 'max' => 5]
];
$difficulty = $testOptions['difficulty'];
$timePerPair = $testOptions['time_per_pair'] ?? 5;
$colors = $testOptions['colors'];
$numberRange = $testOptions['number_range'];
$pairs = $testOptions['pairs'] ?? 10;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест на внимание</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin: 20px; }
        .circle { width: 100px; height: 100px; border-radius: 50%; margin: 20px auto; }
        #number { font-size: 48px; margin: 20px; }
        #remainingPairs { font-size: 18px; margin: 10px; }
        #hint { margin-top: 20px; font-size: 18px; color: #555; }
        #condition { margin: 20px; font-size: 20px; color: #007bff; }
    </style>
</head>
<body>
    <button id="backButton" onclick="goBack()">Назад</button>
    <h1>Тест на внимание</h1>
    <div id="description">
        <div id="condition"></div>
        <p>Условия теста:</p>
        <p>- Нажимайте "A" (да), если условие выполняется.</p>
        <p>- Нажимайте "D" (нет), если условие не выполняется.</p>
        <p>- Не нажимайте ничего, если только одна часть условия выполнена.</p>
        <div id="currentInfo" style="margin: 10px 0; font-size: 18px; color: #333;"></div>
        <button id="startButton">Начать тест</button>
    </div>

    <div id="testContainer" style="display: none;">
        <div id="circle" class="circle"></div>
        <div id="number"></div>
        <div id="remainingPairs"></div>
        <div id="hint"></div>
        <div id="currentInfo" style="margin: 10px 0; font-size: 18px; color: #333;"></div>
    </div>

    <script>
        const colors = <?php echo json_encode($colors); ?>;
        const numberRange = <?php echo json_encode($numberRange); ?>;
        const duration = <?php echo (int)$timePerPair * 1000; ?>;
        const totalPairs = <?php echo (int)$pairs; ?>;

        let correctResponses = 0;
        let incorrectResponses = 0;
        let misses = 0;
        let currentPair = 0;
        let interval;

        let conditionColor = '';
        let conditionNumber = 0;

        const startButton = document.getElementById('startButton');
        const description = document.getElementById('description');
        const testContainer = document.getElementById('testContainer');
        const circle = document.getElementById('circle');
        const numberElement = document.getElementById('number');
        const remainingPairs = document.getElementById('remainingPairs');
        const hint = document.getElementById('hint');
        const conditionDiv = document.getElementById('condition');

        // Генерируем условие один раз при загрузке страницы
        function generateCondition() {
            conditionColor = colors[Math.floor(Math.random() * colors.length)];
            conditionNumber = Math.floor(Math.random() * (numberRange.max - numberRange.min + 1)) + numberRange.min;
            conditionDiv.textContent = `Условие: цвет круга "${conditionColor}" и число ≤ ${conditionNumber}`;
        }
        generateCondition();

        function showNextPair() {
            if (currentPair >= totalPairs) {
                clearInterval(interval);
                finishTest();
                return;
            }

            currentPair++;
            const color = colors[Math.floor(Math.random() * colors.length)];
            const number = Math.floor(Math.random() * (numberRange.max - numberRange.min + 1)) + numberRange.min;

            circle.style.backgroundColor = color;
            numberElement.textContent = number;
            remainingPairs.textContent = `Осталось пар: ${totalPairs - currentPair}`;
            document.querySelectorAll('#currentInfo').forEach(el => {
                el.textContent = `Цвет круга: ${color}, число: ${number}`;
            });

            // Без подсказок
            hint.textContent = '';
        }

        function finishTest() {
            testContainer.style.display = 'none';
            description.style.display = 'block';
            description.innerHTML = `
                <div id="condition">${conditionDiv.textContent}</div>
                <p>Тест завершён!</p>
                <p>Правильные ответы: ${correctResponses}</p>
                <p>Неправильные ответы: ${incorrectResponses}</p>
                <p>Пропуски: ${misses}</p>
            `;

            // Отправка результатов на сервер
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "attention_test.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                } else {
                    console.error("Ошибка отправки данных: " + xhr.status);
                }
            };
            xhr.send(`correct_responses=${correctResponses}&incorrect_responses=${incorrectResponses}&misses=${misses}`);
        }

        document.addEventListener('keydown', (event) => {
            if (testContainer.style.display === 'none') return;

            const color = circle.style.backgroundColor;
            const number = parseInt(numberElement.textContent);
            const colorCondition = color === conditionColor;
            const numberCondition = number <= conditionNumber;

            // "A" или "Ф" (да)
            if (event.key === 'a' || event.key === 'A' || event.key === 'ф' || event.key === 'Ф') {
                if (colorCondition && numberCondition) {
                    correctResponses++;
                } else {
                    incorrectResponses++;
                }
                if (currentPair < totalPairs) showNextPair();
            }
            // "D" или "В" (нет)
            else if (event.key === 'd' || event.key === 'D' || event.key === 'в' || event.key === 'В') {
                if (!colorCondition && !numberCondition) {
                    correctResponses++;
                } else {
                    incorrectResponses++;
                }
                if (currentPair < totalPairs) showNextPair();
            }
            // Пропуск — если только одна часть условия выполнена
            else {
                if ((colorCondition && !numberCondition) || (!colorCondition && numberCondition)) {
                    correctResponses++;
                } else {
                    misses++;
                }
                if (currentPair < totalPairs) showNextPair();
            }
        });

        startButton.onclick = function () {
            description.style.display = 'none';
            testContainer.style.display = 'block';
            currentPair = 0;
            correctResponses = 0;
            incorrectResponses = 0;
            misses = 0;
            interval = setInterval(showNextPair, duration);
            showNextPair();
        };

        function goBack() {
            window.location.href = 'attention_test_settings.php';
        }
    </script>
</body>
</html>