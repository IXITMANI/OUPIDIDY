<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MyUsers";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = $_POST['username'];
$pass = $_POST['password'];

$sql = "SELECT password, role FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($hashed_password, $role);
    $stmt->fetch();
    if (password_verify($pass, $hashed_password)) {
        session_start();
        $_SESSION['username'] = $user;
        $_SESSION['role'] = $role;
        if ($role === 'admin') {
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