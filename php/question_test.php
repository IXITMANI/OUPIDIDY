<?php
session_start();

// Подключение к базе данных
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MyUsers";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Получаем сложность из сессии (или по умолчанию)
$difficulty = $_SESSION['thinking_test_difficulty'] ?? 'easy';
$timeLimits = ['easy' => 20, 'medium' => 12, 'hard' => 7];
$timePerQuestion = $timeLimits[$difficulty];

// Получаем по 4 вопроса из каждой секции
$questions = [];
$sections = ['Сравнение', 'Анализ', 'Классификация'];
foreach ($sections as $section) {
    $sql = "SELECT * FROM thinking_questions WHERE section=? ORDER BY RAND() LIMIT 4";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $section);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $questions[] = [
            'section' => $row['section'],
            'question' => $row['question'],
            'options' => [$row['option1'], $row['option2'], $row['option3'], $row['option4']],
            'answer' => (int)$row['correct_option']
        ];
    }
    $stmt->close();
}
shuffle($questions);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['results'])) {
    // Сохраняем результат в БД
    $user_id = $_SESSION['user_id'] ?? 0;
    $test_name = "Тест на мышление";
    $score = intval($_POST['score'] ?? 0);
    $total = intval($_POST['total'] ?? 0);

    $sql = "INSERT INTO test_results (user_id, test_name, mean_reaction_time, std_dev, accuracy, incorrect_responses, misses)
            VALUES (?, ?, 0, 0, ?, ?, ?, NOW())";
    $accuracy = $total > 0 ? ($score / $total) * 100 : 0;
    $incorrect = $total - $score;
    $misses = 0;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isddii", $user_id, $test_name, $accuracy, $incorrect, $misses);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    echo "Результаты сохранены!";
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тест на мышление</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin: 20px; }
        .question { font-size: 20px; margin: 20px 0; }
        .options { margin: 15px 0; }
        .option-btn { padding: 10px 20px; margin: 5px; font-size: 16px; cursor: pointer; }
        #timer { font-size: 18px; color: #007bff; margin-bottom: 10px; }
        #section { font-size: 18px; color: #555; margin-bottom: 10px; }
        #result { font-size: 22px; margin-top: 30px; }
    </style>
</head>
<body>
    <h1>Тест на мышление</h1>
    <div id="section"></div>
    <div id="timer"></div>
    <div id="questionBlock">
        <div class="question" id="question"></div>
        <div class="options" id="options"></div>
    </div>
    <div id="result" style="display:none"></div>
    <button id="startBtn" onclick="startTest()">Начать тест</button>
    <button id="backBtn" onclick="window.location.href='user.php'">Назад</button>

    <script>
        const questions = <?php echo json_encode($questions, JSON_UNESCAPED_UNICODE); ?>;
        const timePerQuestion = <?php echo $timePerQuestion; ?>;
        let current = 0, score = 0, timer = null, timeLeft = timePerQuestion;

        const sectionDiv = document.getElementById('section');
        const timerDiv = document.getElementById('timer');
        const questionDiv = document.getElementById('question');
        const optionsDiv = document.getElementById('options');
        const resultDiv = document.getElementById('result');
        const questionBlock = document.getElementById('questionBlock');
        const startBtn = document.getElementById('startBtn');

        function startTest() {
            startBtn.style.display = 'none';
            resultDiv.style.display = 'none';
            questionBlock.style.display = 'block';
            current = 0;
            score = 0;
            showQuestion();
        }

        function showQuestion() {
            if (current >= questions.length) {
                finishTest();
                return;
            }
            const q = questions[current];
            sectionDiv.textContent = "Секция: " + q.section;
            questionDiv.innerHTML = q.question;
            optionsDiv.innerHTML = '';
            q.options.forEach((opt, idx) => {
                const btn = document.createElement('button');
                btn.className = 'option-btn';
                btn.textContent = opt;
                btn.onclick = () => answer(idx);
                optionsDiv.appendChild(btn);
            });
            timeLeft = timePerQuestion;
            timerDiv.textContent = `Время: ${timeLeft} сек.`;
            clearInterval(timer);
            timer = setInterval(() => {
                timeLeft--;
                timerDiv.textContent = `Время: ${timeLeft} сек.`;
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    answer(-1); // пропуск
                }
            }, 1000);
        }

        function answer(idx) {
            clearInterval(timer);
            const q = questions[current];
            if (idx === q.answer) score++;
            current++;
            showQuestion();
        }

        function finishTest() {
            questionBlock.style.display = 'none';
            timerDiv.textContent = '';
            sectionDiv.textContent = '';
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = `Тест завершён!<br>Правильных ответов: ${score} из ${questions.length}`;
            // Отправка результата на сервер
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "question_test.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send(`results=1&score=${score}&total=${questions.length}`);
        }
    </script>
</body>
</html>