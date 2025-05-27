<?php

session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MyUsers";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// 1. Важные качества и их параметры
$qualities = [
    'attention' => [
        'name' => 'Внимательность',
        'tests' => ['Тест на цвет'],
        'params' => [
            'accuracy' => ['weight' => 0.7, 'min' => 0.6, 'reverse' => false],
            'misses' => ['weight' => 0.3, 'min' => null, 'reverse' => true], // меньше — лучше
        ],
        'weight' => 0.4
    ],
    'reaction' => [
        'name' => 'Скорость реакции',
        'tests' => ['Аналоговое преследование'],
        'params' => [
            'mean_reaction_time' => ['weight' => 1.0, 'min' => null, 'reverse' => true], // меньше — лучше
        ],
        'weight' => 0.3
    ],
    'thinking' => [
        'name' => 'Мышление',
        'tests' => ['Тест на мышление'],
        'params' => [
            'accuracy' => ['weight' => 1.0, 'min' => 0.6, 'reverse' => false],
        ],
        'weight' => 0.3
    ]
];

// 2. Получаем лучшие результаты тестов пользователя (по каждому параметру среди всех тестов)
$user_id = $_SESSION['user_id'] ?? 0;
$test_results = [];
foreach ($qualities as $key => $quality) {
    $test_results[$key] = [];
    foreach ($quality['params'] as $param => $settings) {
        $best_value = null;
        foreach ($quality['tests'] as $test_name) {
            $order = $settings['reverse'] ? "ASC" : "DESC";
            $sql = "SELECT $param FROM test_results WHERE user_id=? AND test_name=? AND $param IS NOT NULL ORDER BY $param $order LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $user_id, $test_name);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                if ($best_value === null) {
                    $best_value = $row[$param];
                } else {
                    if ($settings['reverse']) {
                        if ($row[$param] < $best_value) $best_value = $row[$param];
                    } else {
                        if ($row[$param] > $best_value) $best_value = $row[$param];
                    }
                }
            }
            $stmt->close();
        }
        if ($best_value !== null) {
            $test_results[$key][$param] = $best_value;
        }
    }
}

// 3. Оценка по каждому качеству
$quality_scores = [];
$quality_passed = [];
foreach ($qualities as $key => $quality) {
    $score = 0;
    $passed = true;
    $row = $test_results[$key] ?? [];
    foreach ($quality['params'] as $param => $settings) {
        $value = $row[$param] ?? null;
        if ($value === null) continue;
        // Обратный параметр (меньше — лучше)
        if ($settings['reverse']) {
            // Для времени реакции переводим из мс в секунды!
            $norm = ($param === 'mean_reaction_time') ? min($value / 1000, 1) : $value;
            $param_score = 1 - $norm;
        } else {
            $param_score = $value;
            if ($param_score > 1) $param_score = $param_score / 100; // если проценты
        }
        $score += $param_score * $settings['weight'];
        // Минимальный порог
        if ($settings['min'] !== null) {
            $check = $settings['reverse'] ? ($value <= $settings['min']) : ($param_score >= $settings['min']);
            if (!$check) $passed = false;
        }
    }
    $quality_scores[$key] = round($score, 2);
    $quality_passed[$key] = $passed;
}

// 4. Итоговая оценка
$total_score = 0;
$total_weight = 0;
foreach ($qualities as $key => $quality) {
    $total_score += $quality_scores[$key] * $quality['weight'];
    $total_weight += $quality['weight'];
}
$total_score = $total_weight > 0 ? round($total_score / $total_weight, 2) : 0;
$all_passed = !in_array(false, $quality_passed, true);

// 5. Сохраняем итоговую оценку
$sql = "INSERT INTO skill_assessment (user_id, attention_score, reaction_score, thinking_score, total_score, passed)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "iddddi",
    $user_id,
    $quality_scores['attention'],
    $quality_scores['reaction'],
    $quality_scores['thinking'],
    $total_score,
    $all_passed
);
$stmt->execute();
$stmt->close();

// 6. Определяем подходящую профессию
$professions = [
    [
        'name' => 'Тестировщик',
        'requirements' => [
            'attention' => 0.7
        ]
    ],
    [
        'name' => 'Frontend-разработчик',
        'requirements' => [
            'thinking' => 0.7,
            'reaction' => 0.5
        ]
    ],
    [
        'name' => 'Backend-разработчик',
        'requirements' => [
            'thinking' => 0.8
        ]
    ],
    [
        'name' => 'Гейм-дизайнер',
        'requirements' => [
            'attention' => 0.6,
            'thinking' => 0.6,
            'reaction' => 0.6
        ]
    ]
];

$matched_profession = 'Нет подходящей профессии';
foreach ($professions as $prof) {
    $ok = true;
    foreach ($prof['requirements'] as $q => $min) {
        if (($quality_scores[$q] ?? 0) < $min) {
            $ok = false;
            break;
        }
    }
    if ($ok) {
        $matched_profession = $prof['name'];
        break;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оценка навыков программиста</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin: 30px; }
        table { margin: 0 auto; border-collapse: collapse; }
        td, th { border: 1px solid #ccc; padding: 8px 16px; }
        th { background: #f0f0f0; }
        .fail { color: red; }
        .ok { color: green; }
    </style>
</head>
<body>
    <h1>Оценка навыков программиста</h1>
    <table>
        <tr>
            <th>Качество</th>
            <th>Оценка</th>
            <th>Порог пройден?</th>
        </tr>
        <?php foreach ($qualities as $key => $quality): ?>
        <tr>
            <td><?= htmlspecialchars($quality['name']) ?></td>
            <td><?= $quality_scores[$key] ?? '-' ?></td>
            <td class="<?= $quality_passed[$key] ? 'ok' : 'fail' ?>">
                <?= $quality_passed[$key] ? 'Да' : 'Нет' ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <th>Итоговая оценка</th>
            <th colspan="2"><?= $total_score ?> <?= $all_passed ? '(пройдено)' : '(не пройдено)' ?></th>
        </tr>
    </table>
    <br>
    <p><b>Вам подходит профессия:</b> <?= htmlspecialchars($matched_profession) ?></p>
    <a href="user.php"><button>Назад</button></a>
</body>
</html>