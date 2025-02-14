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
$pass = $_POST['password'];

// SQL-запрос для получения данных пользователя
$sql = "SELECT password FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    if (password_verify($pass, $hashed_password)) {
        session_start();
        $_SESSION['username'] = $user;
        if ($user === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: user.php");
        }
        exit();
    } else {
        echo "Неверный логин или пароль";
    }
} else {
    echo "Пользователя с таким именем не существует";
}

$stmt->close();
$conn->close();
?>
<br>
<h2><a href="Main.html"><button>Back</button></a></h2>