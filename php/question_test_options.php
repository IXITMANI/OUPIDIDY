<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $difficulty = $_POST['difficulty'] ?? 'easy';
    $_SESSION['thinking_test_difficulty'] = $difficulty;
    header('Location: question_test.php');
    exit();
}

$currentDifficulty = $_SESSION['thinking_test_difficulty'] ?? 'easy';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Настройки теста на мышление</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin: 20px; }
        form { display: inline-block; text-align: left; margin-top: 30px; }
        label { display: block; margin: 10px 0 5px; }
        select { width: 100%; padding: 5px; margin-bottom: 10px; }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <h1>Настройки теста на мышление</h1>
    <form method="POST">
        <label for="difficulty">Уровень сложности:</label>
        <select id="difficulty" name="difficulty">
            <option value="easy" <?php if($currentDifficulty === 'easy') echo 'selected'; ?>>Легкий</option>
            <option value="medium" <?php if($currentDifficulty === 'medium') echo 'selected'; ?>>Средний</option>
            <option value="hard" <?php if($currentDifficulty === 'hard') echo 'selected'; ?>>Сложный</option>
        </select>
        <button type="submit">Сохранить настройки</button>
    </form>
    <br>
    <a href="user.php"><button>Назад</button></a>
</body>
</html>