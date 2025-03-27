<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../html/login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тесты пользователя</title>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/navmain.css">
</head>
<body>
    <header>
        <nav class="links_header">
            <div class="empty_space"> </br> </div>
            <ul class="nav_links">
                <li><nav class="auth">
                    <a href="../php/logout.php"><button>Выйти</button></a>
                </nav></li>
            </ul>
        </nav>
    </header>

    <div class="main-shit">
        <h2 class="heading">Ваши тесты</h2>
        <section id="tests">
            <a href="../php/reaction_test.php">
                <article class="profession" style="box-shadow: -7px 7px #D15955;">
                    <h3>Тест на реакцию</h3>
                    <p>Проверьте свою скорость реакции на появление круга.</p>
                </article>
            </a>
            <a href="../php/sound_test.php">
                <article class="profession" style="box-shadow: -7px 7px #5a5a79b8;">
                    <h3>Тест на звук</h3>
                    <p>Проверьте свою реакцию на звуковые сигналы.</p>
                </article>
            </a>
            <a href="../php/color_test.php">
                <article class="profession" style="box-shadow: -7px 7px #8164af;">
                    <h3>Тест на цвет</h3>
                    <p>Проверьте свою реакцию на цвета.</p>
                </article>
            </a>
            <a href="../php/number_sound_test.php">
                <article class="profession" style="box-shadow: -7px 7px #368529;">
                    <h3>Тест на числа (звук)</h3>
                    <p>Проверьте свою реакцию на сумму чисел, воспроизводимых звуками.</p>
                </article>
            </a>
            <a href="../php/number_display_test.php">
                <article class="profession" style="box-shadow: -7px 7px #ab5a74;">
                    <h3>Тест на числа (экран)</h3>
                    <p>Проверьте свою реакцию на сумму чисел, отображаемых на экране.</p>
                </article>
            </a>
        </section>
    </div>
</body>
</html>