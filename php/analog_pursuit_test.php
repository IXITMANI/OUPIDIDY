<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MyUsers";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mean_reaction_time'])) {
    $user_id = $_SESSION['user_id'] ?? 0;
    $test_name = "Аналоговое преследование";
    $mean_reaction_time = $_POST['mean_reaction_time'] ?? 0;
    $std_dev = $_POST['std_dev'] ?? 0;
    $accuracy = $_POST['accuracy'] ?? 0;

    $sql = "INSERT INTO test_results (user_id, test_name, mean_reaction_time, std_dev, accuracy)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isddd", $user_id, $test_name, $mean_reaction_time, $std_dev, $accuracy);
    $stmt->execute();
    $stmt->close();

    echo "Результаты сохранены!";
    exit();
}

$testOptions = $_SESSION['analog_pursuit_options'] ?? [
    'duration' => 120,
    'showTimer' => true,
    'showResults' => true,
    'showProgress' => true,
    'speedIncrease' => 5,
    'speedInterval' => 10
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аналоговое преследование</title>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <style>
        .track-container {
            width: 600px;
            height: 100px;
            background-color: #333;
            margin: 50px auto;
            position: relative;
            border: 2px solid white;
        }
        .target {
            width: 50px;
            height: 50px;
            background-color: white;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }
        .crosshair {
            width: 20px;
            height: 20px;
            background-color: green;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>
<body>
    <a href="analog_pursuit_settings.php" class="back-button">Назад</a>
    <h1 class="heading_text">Аналоговое преследование</h1>
    <div id="description">
        <p>Используйте стрелки влево и вправо, чтобы удерживать зеленый прицел на белом кружке.</p>
        <button id="startButton">Начать тест</button>
>Get ready to dive into the code! I’ve whipped up two tests based on your requirements: **Analog Tracking** and **Analog Pursuit**. Each has a settings page and a test page, with PHP for backend logic, CSS for styling, and JavaScript for interactivity. The setup is similar to your previous example, storing results in a MySQL database and using your provided CSS for consistency. Below, I’ll outline the key components and include the full code for both tests within `<xaiArtifact>` tags.

---

### **Analog Tracking Test**

This test involves a white circle moving left or right on a horizontal track. The user must use the left and right arrow keys to keep the circle centered between two black lines. The reaction time is measured from when the circle starts moving until the user begins counteracting it.

#### **Settings Page (analog_tracking_settings.php)**
- Allows users to configure:
  - Test duration (120–2700 seconds).
  - Show/hide timer, results, and progress.
  - Speed increase (%) and interval (seconds).
- Saves settings in the session and redirects to the test page.

#### **Test Page (analog_tracking_test.php)**
- Displays a 600x100px track with a 50x50px white circle.
- Two black vertical lines mark the center.
- The circle moves randomly (10% chance per frame) with a speed between -2 and 2 pixels per frame.
- Arrow keys adjust the circle’s speed to keep it centered.
- Tracks reaction times (from movement start to user input) and calculates mean and standard deviation.
- Saves results to the database (`test_results` table).
- Supports timer, progress, and result display based on settings.
- Speed increases by the configured percentage every specified interval.

---

### **Analog Pursuit Test**

This test features a white circle moving along a horizontal track, with the user controlling a green crosshair using arrow keys to keep it aligned with the circle. It measures reaction time and pursuit accuracy (time the crosshair overlaps the target).

#### **Settings Page (analog_pursuit_settings.php)**
- Identical to the tracking test’s settings page, but saves to a different session variable.

#### **Test Page (analog_pursuit_test.php)**
- Same 600x100px track as the tracking test.
- A 50x50px white circle (target) moves with random speed changes.
- A 20x20px green crosshair is controlled via arrow keys.
- Measures reaction time (from target movement to user input) and accuracy (percentage of time the crosshair overlaps the target).
- Saves mean reaction time, standard deviation, and accuracy to the database.
- Supports timer, progress, and result display, with configurable speed increases.

---

### **CSS (main.css)**
I reused your provided `main.css` for styling, ensuring a consistent look with your previous test. The test-specific styles are embedded in the PHP files for simplicity, defining the track, circle, and crosshair appearances.

---

### **Database**
Assumes a MySQL database (`MyUsers`) with a `test_results` table. The schema for the tracking test includes `user_id`, `test_name`, `mean_reaction_time`, and `std_dev`. The pursuit test adds an `accuracy` column.

---

### **Code**

Below are the complete files for both tests. I’ve kept the code modular and commented for clarity. The artifact IDs are unique UUIDs, and I’ve ensured the content is wrapped correctly in `<xaiArtifact>` tags.

#### **Analog Tracking: Settings Page**
<xaiArtifact artifact_id="240b288d-3001-4d8c-afd0-6d3a6ef9d193" artifact_version_id="361a6a3e-8d3f-4093-bcbb-8e4dbe52112d" title="analog_tracking_settings.php" contentType="text/php">
<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['analog_tracking_options'] = [
        'duration' => (int)$_POST['duration'],
        'showTimer' => isset($_POST['showTimer']),
        'showResults' => isset($_POST['showResults']),
        'showProgress' => isset($_POST['showProgress']),
        'speedIncrease' => (float)$_POST['speedIncrease'],
        'speedInterval' => (int)$_POST['speedInterval']
    ];
    header('Location: analog_tracking_test.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройки аналогового слежения</title>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
</head>
<body>
    <a href="../index.php" id="backButton">Назад</a>
    <h1 class="heading_text">Настройки аналогового слежения</h1>
    <form method="POST" id="settingsForm">
        <div id="description">
            <p>Выберите параметры теста:</p>
            <label>Время выполнения (секунды, 120–2700):</label>
            <input type="number" name="duration" min="120" max="2700" value="120" required><br><br>
            <label><input type="checkbox" name="showTimer" checked> Показывать таймер</label><br>
            <label><input type="checkbox" name="showResults" checked> Показывать результаты</label><br>
            <label><input type="checkbox" name="showProgress" checked> Показывать прогресс</label><br>
            <label>Ускорение движения (на сколько, %):</label>
            <input type="number" name="speedIncrease" step="0.1" min="0" value="5" required><br><br>
            <label>Интервал ускорения (секунды):</label>
            <input type="number" name="speedInterval" min="1" value="10" required><br><br>
            <button type="submit" id="startButton">Сохранить и начать</button>
        </div>
    </form>
</body>
</html>