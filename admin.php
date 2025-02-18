<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

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

// Удаление пользователя
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Проверка роли пользователя перед удалением
    $sql = "SELECT role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($role);
    $stmt->fetch();
    $stmt->close();

    if ($role !== 'admin') {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin.php");
        exit();
    } else {
        $error_message = "Невозможно удалить другого администратора.";
    }
}

// Обновление роли пользователя
if (isset($_POST['update_role'])) {
    $id = $_POST['user_id'];
    $new_role = $_POST['role'];

    $sql = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_role, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Получение списка пользователей
$sql = "SELECT id, username, email, phone, age, reg_date, role FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Admin Page</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='admin.css'>
</head>
<body>
    <h1>Welcome, Admin!</h1>
    <p>This is the admin page.</p>
    <h2>Manage Users</h2>
    <?php if ($error_message): ?>
        <div class="error-message">
            <p><?php echo $error_message; ?></p>
        </div>
    <?php endif; ?>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Age</th>
            <th>Registration Date</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['username']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['phone']; ?></td>
            <td><?php echo $row['age']; ?></td>
            <td><?php echo $row['reg_date']; ?></td>
            <td>
                <form method="post" action="admin.php">
                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                    <select name="role">
                        <option value="user" <?php if ($row['role'] == 'user') echo 'selected'; ?>>User</option>
                        <option value="admin" <?php if ($row['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                    </select>
                    <input type="submit" name="update_role" value="Update">
                </form>
            </td>
            <td>
                <a href="admin.php?delete=<?php echo $row['id']; ?>">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <a href="logout.php"><button>Logout</button></a>
</body>
</html>

<?php
$conn->close();
?>