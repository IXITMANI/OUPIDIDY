<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MyUsers";

// Создаем соединение
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверяем соединение
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

// Проверяем, что данные из формы существуют
if (!isset($_POST['username'], $_POST['password'], $_POST['password_confirm'], $_POST['phone'], $_POST['email'], $_POST['age'])) {
    $error_message = "Все поля обязательны для заполнения!";
} else {
    // Получаем данные из формы
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $pass_confirm = $_POST['password_confirm'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $age = $_POST['age'];

    // Проверка паролей
    if ($pass !== $pass_confirm) {
        $error_message = "Пароли не совпадают!";
    } else {
        // Хешируем пароль
        $pass = password_hash($pass, PASSWORD_DEFAULT);

        // Проверка на пустые поля
        if (empty($user) || empty($pass) || empty($phone) || empty($email) || empty($age)) {
            $error_message = "Все поля обязательны для заполнения!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Проверка корректности email
            $error_message = "Некорректный email адрес!";
        } elseif (!is_numeric($age) || $age < 16 || $age > 121) {
            // Проверка возраста
            $error_message = "Возраст должен быть числом от 16 до 121";
        } elseif (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
            // Проверка корректности номера телефона
            $error_message = "Некорректный номер телефона!";
        } else {
            // Проверка на существование пользователя с таким же именем
            $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $user, $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error_message = "Пользователь с таким именем или email уже существует";
            } else {
                $stmt = $conn->prepare("INSERT INTO users (username, password, email, phone, age) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssi", $user, $pass, $email, $phone, $age);

                if ($stmt->execute() === TRUE) {
                    // Сохраняем данные пользователя в сессии
                    session_start();
                    $_SESSION['username'] = $user;
                    header("Location: user.php");
                    exit();
                } else {
                    $error_message = "Ошибка: " . $stmt->error;
                }
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Register</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='../css/nav.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='../css/style.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='../css/register.css'>
</head>
<body>

    <header class="heading" style="background-color: #13141d86;">
        <nav class="links_header">
            <ul class="nav_links">
                <li><a href="../Main.php">домой</a></li>
            </ul>
        </nav>
        <div class="heading_text">Регистрация</div>
    </header>

    <?php if (!empty($error_message)): ?>
        <h3 class="error-message" style="color: white; margin-left: 10vh;">
            <?php echo $error_message; ?>
        </h3>
        <br>
    <?php endif; ?>
</body>
</html>