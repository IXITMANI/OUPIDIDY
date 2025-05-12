<?php
session_start();

// Настройки игры
$testOptions = [
    'difficulty' => $_GET['difficulty'] ?? 'easy', // Уровень сложности
    'matrixSize' => ['easy' => 3, 'medium' => 4, 'hard' => 5], // Размер матрицы
    'viewTime' => ['easy' => 5000, 'medium' => 3000, 'hard' => 2000], // Время просмотра матрицы (мс)
];
$difficulty = $testOptions['difficulty'];
$matrixSize = $testOptions['matrixSize'][$difficulty];
$viewTime = $testOptions['viewTime'][$difficulty];

// Подключение к базе данных
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MyUsers";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Сохранение результата в базу данных
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accuracy'])) {
    $user_id = $_SESSION['user_id'] ?? 0;
    $accuracy = $_POST['accuracy'];

    $sql = "INSERT INTO memory_game_results (user_id, difficulty, accuracy, completed_at)
            VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isd", $user_id, $difficulty, $accuracy);
    $stmt->execute();
    $stmt->close();

    echo "Результат успешно сохранен!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра "Плиточки"</title>
    <link rel="stylesheet" type="text/css" href="../css/memory_test.css">
    <script>
        const matrixSize = <?php echo $matrixSize; ?>;
        const viewTime = <?php echo $viewTime; ?>;
    </script>
</head>
<body>
    <header>
        <nav class="links_header">
            <ul class="nav_links">
                <li><a href="./user.php">Назад</a></li>
            </ul>
        </nav>
        <h1>Игра "Плиточки"</h1>
    </header>   
    <div id="resultContainer" style="display: none;">
    <h2 id="resultText"></h2>
</div>
    <div id="gameContainer">
        <!-- Этап 1: Просмотр матрицы -->
        <div id="stage1" class="stage">
            <h2>Запомните расположение цветов</h2>
            <div id="matrix"></div>
        </div>

        <!-- Этап 2: Повторение рисунка -->
        <div id="stage2" class="stage" style="display: none;">
            <h2>Повторите рисунок</h2>
            <div id="userMatrix"></div>
            <button id="submitGame">Проверить</button>
        </div>
    </div>

    <script>
        let originalMatrix = []; // Оригинальная матрица цветов
        let userMatrix = []; // Матрица пользователя

        // Массив цветов и индекс для зацикливания
        const colors = ['red', 'blue', 'green', 'yellow', 'purple'];
        let colorIndex = 0;
        // Получение следующего цвета с зацикливанием
        function getNextColor() {
            return colors[Math.floor(Math.random() * colors.length)];
        }
        // Этап 1: Генерация матрицы
        function startStage1() {
            const matrix = document.getElementById('matrix');
            matrix.innerHTML = '';
            originalMatrix = [];

            for (let i = 0; i < matrixSize; i++) {
                const row = [];
                const rowElement = document.createElement('div');
                rowElement.classList.add('row');

                for (let j = 0; j < matrixSize; j++) {
                    const color = getNextColor(); // Используем зацикленный цвет
                    row.push(color);

                    const cell = document.createElement('div');
                    cell.classList.add('cell');
                    cell.style.backgroundColor = color;
                    rowElement.appendChild(cell);
                }

                originalMatrix.push(row);
                matrix.appendChild(rowElement);
            }

            // Показать матрицу на заданное время, затем перейти к этапу 2
            setTimeout(() => {
                document.getElementById('stage1').style.display = 'none';
                startStage2();
            }, viewTime);
        }
        function getColor() {
        return colors[Math.floor(Math.random() * colors.length)];
        }
        // Этап 2: Повторение рисунка
        function startStage2() {
            const userMatrixContainer = document.getElementById('userMatrix');
            userMatrixContainer.innerHTML = '';
            userMatrix = [];

            for (let i = 0; i < matrixSize; i++) {
                const row = [];
                const rowElement = document.createElement('div');
                rowElement.classList.add('row');

                for (let j = 0; j < matrixSize; j++) {
                    row.push(null);

                    const cell = document.createElement('div');
                    cell.classList.add('cell');
                    cell.addEventListener('click', () => {
                        const color = getColor();
                        cell.style.backgroundColor = color;
                        row[j] = color;
                    });

                    rowElement.appendChild(cell);
                }

                userMatrix.push(row);
                userMatrixContainer.appendChild(rowElement);
            }

            document.getElementById('stage2').style.display = 'block';
        }
        // Сохранение результата в базу данных
function saveResultToDatabase(accuracy) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'memory_test.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            console.log(xhr.responseText);
        }
    };
    xhr.send(`accuracy=${accuracy}`);
}
 // Проверка результата
function checkResult() {
    let correctCount = 0;
    let totalCount = matrixSize * matrixSize;

    // Подсчет правильных совпадений
    for (let i = 0; i < matrixSize; i++) {
        for (let j = 0; j < matrixSize; j++) {
            if (originalMatrix[i][j] === userMatrix[i][j]) {
                correctCount++;
            }
        }
    }

    const accuracy = (correctCount / totalCount) * 100;

    // Показать правильный вариант матрицы
    showCorrectMatrix();

        const resultContainer = document.getElementById('resultContainer');
    const resultText = document.getElementById('resultText');
    resultText.textContent = `Вы правильно повторили ${accuracy.toFixed(2)}% рисунка!`;
    resultContainer.style.display = 'block';

    // Удалить кнопку "Проверить"
    document.getElementById('submitGame').style.display = 'none';

    // Отключить возможность изменения цветов
    const userCells = document.querySelectorAll('#userMatrix .cell');
    userCells.forEach(cell => {
        cell.style.pointerEvents = 'none'; // Отключить клики
    });
    saveResultToDatabase(accuracy);
}

// Отображение правильной матрицы
function showCorrectMatrix() {
    const correctMatrixContainer = document.createElement('div');
    correctMatrixContainer.id = 'correctMatrix';
    correctMatrixContainer.innerHTML = '<h2>Правильный вариант:</h2>';

    for (let i = 0; i < matrixSize; i++) {
        const rowElement = document.createElement('div');
        rowElement.classList.add('row');

        for (let j = 0; j < matrixSize; j++) {
            const cell = document.createElement('div');
            cell.classList.add('cell');
            cell.style.backgroundColor = originalMatrix[i][j];
            rowElement.appendChild(cell);
        }

        correctMatrixContainer.appendChild(rowElement);
    }

    // Добавить правильную матрицу в контейнер игры
    const gameContainer = document.getElementById('gameContainer');
    gameContainer.appendChild(correctMatrixContainer);
}

        // Обработчик кнопки проверки
        document.getElementById('submitGame').addEventListener('click', checkResult);

        // Запуск первого этапа
        startStage1();
    </script>
</body>
</html>