<?php
session_start();

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $difficulty = $_POST['difficulty'];

    // Настройки для каждого уровня сложности
    $settings = [
        'easy' => [
            'time_per_pair' => 5,
            'colors' => ['red', 'blue', 'green'],
            'number_range' => ['min' => 0, 'max' => 5]
        ],
        'medium' => [
            'time_per_pair' => 3,
            'colors' => ['red', 'blue', 'green', 'yellow'],
            'number_range' => ['min' => 0, 'max' => 7]
        ],
        'hard' => [
            'time_per_pair' => 2,
            'colors' => ['red', 'blue', 'green', 'yellow', 'purple'],
            'number_range' => ['min' => 0, 'max' => 10]
        ]
    ];

    // Сохраняем настройки в сессии
    $_SESSION['attention_test_options'] = array_merge(['difficulty' => $difficulty], $settings[$difficulty]);
    header('Location: attention_test.php');
    exit();
}

// Получение текущих настроек
$currentOptions = $_SESSION['attention_test_options'] ?? ['difficulty' => 'easy'];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройки теста на внимание</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }
        form {
            display: inline-block;
            text-align: left;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        select {
            width: 100%;
            padding: 5px;
            margin-bottom: 10px;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Настройки теста на внимание</h1>
    <form method="POST">
        <label for="difficulty">Уровень сложности:</label>
        <select id="difficulty" name="difficulty">
            <option value="easy" <?php echo $currentOptions['difficulty'] === 'easy' ? 'selected' : ''; ?>>Легкий</option>
            <option value="medium" <?php echo $currentOptions['difficulty'] === 'medium' ? 'selected' : ''; ?>>Средний</option>
            <option value="hard" <?php echo $currentOptions['difficulty'] === 'hard' ? 'selected' : ''; ?>>Сложный</option>
        </select>

        <button type="submit">Сохранить настройки</button>
    </form>
    <br>
    <a href="user.php"><button>Назад</button></a>
</body>
</html>