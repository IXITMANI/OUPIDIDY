<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест на реакцию на числа</title>
    <link rel="stylesheet" type="text/css" href="../css/reaction_test.css">
    <link rel="stylesheet" type="text/css" href="../css/nav.css">
    <style>
        #keyBindings {
            position: absolute;
            bottom: 10px; /* Переместим элемент вниз */
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
        <div class="heading_text">тест на числа</div>
    </header>
    <div id="description">
        <p>На протяжении 2 минут будут воспроизводиться два числа в виде звуков.</p>
        <p>Ваша задача - нажимать соответствующую клавишу в ответ на сумму чисел.</p>
        <p>Q – четная сумма, W – нечетная сумма.</p>
        <p>Система будет считывать среднее время вашей реакции, точность ответов, количество ошибок и пропусков.</p>
        <p>Нажмите "Готов", чтобы начать тест.</p>
    </div>
    <div id="keyBindings">
        <p>Q – четная сумма</p>
        <p>W – нечетная сумма</p>
    </div>
    <button id="startButton">Готов</button>
    <div id="results"></div>
    <audio id="sound1" src="../sounds/sound1.mp3"></audio>
    <audio id="sound2" src="../sounds/sound2.mp3"></audio>
    <audio id="sound3" src="../sounds/sound3.mp3"></audio>
    <audio id="sound4" src="../sounds/sound4.mp3"></audio>
    <audio id="sound5" src="../sounds/sound5.mp3"></audio>
    <audio id="sound6" src="../sounds/sound6.mp3"></audio>
    <audio id="sound7" src="../sounds/sound7.mp3"></audio>
    <audio id="sound8" src="../sounds/sound8.mp3"></audio>
    <audio id="sound9" src="../sounds/sound9.mp3"></audio>
    <script>
        let reactionTimes = [];
        let correctResponses = 0;
        let incorrectResponses = 0;
        let misses = 0;
        let startTime;
        let startButton = document.getElementById('startButton');
        let results = document.getElementById('results');
        let description = document.getElementById('description');
        let keyBindings = document.getElementById('keyBindings');
        keyBindings.style.display = 'none';
        let totalSignals = 4; // Установим количество сигналов на 120 (примерно 1 сигнал в секунду)
        let signalsShown = 0;
        let timeout;
        let sounds = [
            document.getElementById('sound1'),
            document.getElementById('sound2'),
            document.getElementById('sound3'),
            document.getElementById('sound4'),
            document.getElementById('sound5'),
            document.getElementById('sound6'),
            document.getElementById('sound7'),
            document.getElementById('sound8'),
            document.getElementById('sound9')
        ];
        let currentSum;

        function playSounds() {
            if (signalsShown >= totalSignals) {
                calculateResults();
                return;
            }
            signalsShown++;
            let num1 = Math.floor(Math.random() * 9) + 1;
            let num2 = Math.floor(Math.random() * 9) + 1;
            currentSum = num1 + num2;
            sounds[num1 - 1].play();
            sounds[num1 - 1].onended = function() {
                setTimeout(() => {
                    sounds[num2 - 1].play();
                    startTime = new Date().getTime();
                }, 300); // Воспроизвести второй звук через 300 мс после окончания первого звука
            };
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
            keyBindings.style.display = 'none'; // Показать назначения клавиш
        }

        document.body.onkeydown = function(e) {
            if (sounds.every(sound => sound.paused)) {
                let reactionTime = new Date().getTime() - startTime;
                if ((e.code === 'KeyQ' && currentSum % 2 === 0) || (e.code === 'KeyW' && currentSum % 2 !== 0)) {
                    reactionTimes.push(reactionTime);
                    correctResponses++;
                } else {
                    incorrectResponses++;
                }
                clearTimeout(timeout); // Остановить таймер после нажатия
                setTimeout(playSounds, Math.random() * 1000 + 500); // Проиграть следующий звук через случайное время от 500 до 1500 мс
            }
        };

        startButton.onclick = function() {
            startButton.style.display = 'none';
            description.style.display = 'none'; // Скрыть описание
            keyBindings.style.display = 'block'; // Показать назначения клавиш
            reactionTimes = [];
            correctResponses = 0;
            incorrectResponses = 0;
            misses = 0;
            signalsShown = 0;
            results.innerHTML = ''; // Очистить результаты
            playSounds(); // Начать проигрывание звуков
        };
    </script>
</body>
</html>