<?php
session_start();

// Подключение к базе данных
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MyUsers";
$mean_reaction_time = 0; // Среднее время реакции (пока не рассчитывается)
$std_dev = 0; // Стандартное отклонение (пока не рассчитывается)
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['correct_responses'])) {
    $user_id = $_SESSION['user_id'] ?? 0; // Убедитесь, что пользователь авторизован
    $test_name = "Тест на внимание";
    $correct_responses = $_POST['correct_responses'] ?? 0;
    $incorrect_responses = $_POST['incorrect_responses'] ?? 0;
    $misses = $_POST['misses'] ?? 0;

    // Рассчитываем точность (accuracy)
    $total_attempts = $correct_responses + $incorrect_responses + $misses;
    $accuracy = $total_attempts > 0 ? ($correct_responses / $total_attempts) * 100 : 0;

    // SQL-запрос для вставки данных
$sql = "INSERT INTO test_results (user_id, test_name, mean_reaction_time, std_dev, accuracy, incorrect_responses, misses)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Ошибка подготовки запроса: " . $conn->error);
    }
    $stmt->bind_param("issdiii", $user_id, $test_name, $mean_reaction_time, $std_dev, $accuracy, $incorrect_responses, $misses);

    if (!$stmt->execute()) {
        die("Ошибка выполнения запроса: " . $stmt->error);
    }
    $stmt->close();
    echo "Результаты успешно сохранены!";
    exit();
}

// Получение настроек из сессии
$testOptions = $_SESSION['attention_test_options'] ?? [
    'difficulty' => 'easy', // Значение по умолчанию
    'duration' => 3, // Время на ответ в секундах
    'colors' => ['red', 'blue', 'green'], // Цвета для легкого уровня
    'numberRange' => [0, 5] // Диапазон чисел для легкого уровня
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест на внимание</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }
        .circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 20px auto;
        }
        #number {
            font-size: 48px;
            margin: 20px;
        }
        #remainingPairs {
            font-size: 18px;
            margin: 10px;
        }
        #hint {
            margin-top: 20px;
            font-size: 18px;
            color: #555;
        }
    </style>
</head>
<body>
    <button id="backButton" onclick="goBack()">Назад</button>
    <h1>Тест на внимание</h1>
    <div id="description">
        <p>Условия теста:</p>
        <p>- Нажимайте "A" (да), если цвет круга red и число меньше или равно 5.</p>
        <p>- Нажимайте "D" (нет), если цвет круга не red и число больше 5.</p>
        <p>- Не нажимайте ничего, если оба условия выполнены или оба не выполнены.</p>
        <button id="startButton">Начать тест</button>
    </div>

    <div id="testContainer" style="display: none;">
        <div id="circle" class="circle"></div>
        <div id="number"></div>
        <div id="remainingPairs"></div>
        <div id="hint"></div> <!-- Подсказки -->
    </div>

    <script>
        const testOptions = <?php echo json_encode($testOptions); ?>;

        const colors = testOptions.colors;
        const numberRange = testOptions.numberRange;
        const duration = Math.max((testOptions.duration || 3) * 1000, 1000); // Минимум 1 секунда
        const totalPairs = 10; // Количество пар
        let correctResponses = 0;
        let incorrectResponses = 0;
        let misses = 0;
        let currentPair = 0;
        let interval;

        const startButton = document.getElementById('startButton');
        const description = document.getElementById('description');
        const testContainer = document.getElementById('testContainer');
        const circle = document.getElementById('circle');
        const numberElement = document.getElementById('number');
        const remainingPairs = document.getElementById('remainingPairs');
        const hint = document.getElementById('hint'); // Элемент для подсказок
        let showHints = true; // Флаг для отображения подсказок (можно отключить после первого вопроса или по другим условиям)

        function showNextPair() {
            if (currentPair >= totalPairs) {
                clearInterval(interval);
                finishTest();
                return;
            }

            currentPair++;
            const color = colors[Math.floor(Math.random() * colors.length)];
            const number = Math.floor(Math.random() * 11);

            circle.style.backgroundColor = color;
            numberElement.textContent = number;
            remainingPairs.textContent = `Осталось пар: ${totalPairs - currentPair}`;

            // Условие для подсказок
            if (showHints) {
                showHints = false; // Отключаем подсказки после первого вопроса
                const colorCondition = color === 'red'; // Пример условия для цвета
                const numberCondition = number > 5; // Пример условия для числа

                if (colorCondition && !numberCondition) {
                    hint.textContent = 'Нажмите "A" (да), так как цвет соответствует условию.';
                } else if (!colorCondition && numberCondition) {
                    hint.textContent = 'Нажмите "D" (нет), так как число соответствует условию.';
                } else if (colorCondition && numberCondition) {
                    hint.textContent = 'Не нажимайте ничего, так как оба условия выполнены.';
                } else {
                    hint.textContent = 'Не нажимайте ничего, так как оба условия не выполнены.';
                }
            } else {
                hint.textContent = ''; // Если подсказки отключены, очищаем текст
            }
        }

        function finishTest() {
    testContainer.style.display = 'none';
    description.style.display = 'block';
    description.innerHTML = `
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

    // Отправляем данные
    xhr.send(`correct_responses=${correctResponses}&incorrect_responses=${incorrectResponses}&misses=${misses}`);
}

        document.addEventListener('keydown', (event) => {
            const colorCondition = circle.style.backgroundColor === 'red'; // Пример условия для цвета
            const numberCondition = parseInt(numberElement.textContent) > 5; // Пример условия для числа

            if (event.key === 'a' || event.key === 'A') {
                // Нажата клавиша "A" (да)
                if (colorCondition && !numberCondition) {
                    correctResponses++;
                } else {
                    incorrectResponses++;
                }
            } else if (event.key === 'd' || event.key === 'D') {
                // Нажата клавиша "D" (нет)
                if (!colorCondition && numberCondition) {
                    correctResponses++;
                } else {
                    incorrectResponses++;
                }
            } else {
                // Если нажата другая клавиша, ничего не делаем
                misses++;
            }

            // Переход к следующей паре
            if (currentPair < totalPairs) {
                showNextPair();
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
            window.location.href = 'attention_test_settings.php'; // Переход на страницу настроек
        }
    </script>
</body>
</html>