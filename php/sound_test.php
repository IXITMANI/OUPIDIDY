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
                <li><a href="../Main.php">домой</a></li>
            </ul>
        </nav>
        <div class="heading_text">тест на звук</div>
    </header>
    <div id="description">
        <p>На протяжении времени будет проигрываться четкий звук с рандомной периодичностью.</p>
        <p>Ваша задача - нажимать пробел в ответ когда услышите звук.</p>
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
            timeout = setTimeout(playSound, Math.random() * 4500 + 500); // Проиграть звук через случайное время от 500 до 5000 мс
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