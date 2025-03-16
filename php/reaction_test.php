<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест на реакцию</title>
    <link rel="stylesheet" type="text/css" href="../css/reaction_test.css">
</head>
<body>
    <a id="backButton" href="../Main.php">Назад</a>
    <div id="description">
        <p>На экране будет появляться красный круг с рандомной периодичностью 5 раз.</p>
        <p>Ваша задача - нажимать пробел в ответ на появление круга.</p>
        <p>Система будет считывать среднее время вашей реакции и количество пропусков.</p>
        <p>Нажмите "Готов", чтобы начать тест.</p>
    </div>
    <div id="circle"></div>
    <button id="startButton">Готов</button>
    <div id="results"></div>
    <script>
        let reactionTimes = [];
        let misses = 0;
        let startTime;
        let circle = document.getElementById('circle');
        let startButton = document.getElementById('startButton');
        let results = document.getElementById('results');
        let description = document.getElementById('description');
        let totalSignals = 5; // Установим количество кругов на 5
        let signalsShown = 0;
        let timeout;

        function showCircle() {
            if (signalsShown >= totalSignals) {
                calculateResults();
                return;
            }
            signalsShown++;
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

            results.innerHTML = `
                <p>Среднее время реакции: ${mean.toFixed(2)} мс</p>
                <p>Стандартное отклонение: ${stdDev.toFixed(2)} мс</p>
                <p>Количество пропусков: ${misses}</p>
            `;
            startButton.style.display = 'block'; // Показать кнопку "Готов"
            description.style.display = 'block'; // Показать описание
        }

        document.body.onkeydown = function(e) {
            if (e.code === 'Space' && circle.style.display === 'block') {
                let reactionTime = new Date().getTime() - startTime;
                reactionTimes.push(reactionTime);
                circle.style.display = 'none';
                clearTimeout(timeout); // Остановить таймер после нажатия
                setTimeout(showCircle, Math.random() * 1000 + 500); // Показать следующий круг через случайное время
            }
        };

        startButton.onclick = function() {
            startButton.style.display = 'none';
            description.style.display = 'none'; // Скрыть описание
            reactionTimes = [];
            misses = 0;
            signalsShown = 0;
            results.innerHTML = ''; // Очистить результаты
            setTimeout(showCircle, Math.random() * 1000 + 500); // Начать показ кругов через случайное время
        };
    </script>
</body>
</html>