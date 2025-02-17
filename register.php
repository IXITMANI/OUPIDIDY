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

// Получаем данные из формы
$user = $_POST['username'];
$pass = $_POST['password']; // Временное хранение пароля до проверки
$pass_confirm = $_POST['password_confirm']; // Получаем подтверждение пароля
$phone = $_POST['phone'];
$email = $_POST['email'];
$age = $_POST['age'];

//Проверка подтверждения пароля
if ($pass !== $pass_confirm) {
    die("Пароли не совпадают!");
}

$pass = password_hash($pass, PASSWORD_DEFAULT); // Хешируем пароль после проверки



//Простая валидация (необходимо расширить для реального приложения)
if (empty($user) || empty($pass) || empty($phone) || empty($email) || empty($age)) {
    die("Все поля обязательны для заполнения!");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Некорректный email адрес!");
}

if (!is_numeric($age) || $age < 16 || $age > 121) {
    die("Возраст должен быть числом от 16 до 121");
}


// Проверяем, существует ли пользователь с таким же именем
$sql = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "Пользователь с таким именем уже существует";
} else {
    // Подготовленный SQL-запрос для вставки данных -  добавлены новые поля
    $stmt = $conn->prepare(query: "INSERT INTO users (username, password, phone, email, age) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $user, $pass, $phone, $email, $age);

    if ($stmt->execute() === TRUE) {
        echo "Регистрация прошла успешно!";
    } else {
        echo "Ошибка: " . $stmt->error;
    }
}

$stmt->close();
$conn->close();
?>
<br>
<h2><a href="Main.html"><button>Назад</button></a></h2>