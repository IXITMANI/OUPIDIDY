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
            top: 10px; /* Поднимем элемент выше */
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
                <li><a href="../Main.php">домой</a></li>
            </ul>
        </nav>
        <div class="heading_text">тест на цвет</div>
    </header>
    <div id="description">
        <p>На экране будет появляться круг красного, синего или зелёного цвета.</p>
        <p>Ваша задача - нажимать соответствующую клавишу в ответ на появление круга.</p>
        <p>Система будет считывать среднее время вашей реакции, точность ответов, количество ошибок и пропусков.</p>
        <p>Нажмите "Готов", чтобы начать тест.</p>
        <div>
            <p>1 – красный</p>
            <p>2 – синий</p>
            <p>3 – зелёный</p>
        </div>
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
            startButton.style.display = 'block'; // Показать кнопку "Готов"
            description.style.display = 'block'; // Показать описание
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
            description.style.display = 'none'; // Скрыть описание
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