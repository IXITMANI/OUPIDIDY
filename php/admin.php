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
// Обработка добавления новой профессии
if (isset($_POST['add_profession'])) {
    $profession_name = $_POST['profession_name'];
    $profession_description = $_POST['profession_description'];

    // Проверка, существует ли уже такая профессия
    $sql = "SELECT COUNT(*) FROM professions WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $profession_name);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count == 0) {
        // Вставка новой профессии
        $sql = "INSERT INTO professions (name, description, color_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $color_id = rand(1, 5); // Предполагаем, что у вас есть 5 цветов в таблице colors
        $stmt->bind_param("ssi", $profession_name, $profession_description, $color_id);
        $stmt->execute();
        $stmt->close();
        $message = "Профессия успешно добавлена.";
    } else {
        $message = "Эта профессия уже существует.";
    }
}
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

// Получение списка профессий
$sql = "SELECT id, name FROM professions";
$professions_result = $conn->query($sql);
$professions = [];
while ($row = $professions_result->fetch_assoc()) {
    $professions[] = $row;
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Admin Page</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='../css/admin.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='../css/style.css'>

</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1>Admin Page</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="logout.php" class="btn">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <div class="container">
        <h1>Привет, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <h2>Manage Users</h2>
        <?php if ($error_message): ?>
            <div class="error-message">
                <p><?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>
        <table>
            <tr class="table_text">
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
                <?php if ($row['username'] !== $_SESSION['username']): ?>
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
                                    <option value="critic" <?php if ($row['role'] == 'critic') echo 'selected'; ?>>Critic</option>
                                    <option value="adviser" <?php if ($row['role'] == 'adviser') echo 'selected'; ?>>Adviser</option>
                                    <option value="expert" <?php if ($row['role'] == 'expert') echo 'selected'; ?>>Expert</option>
                                    <option value="admin" <?php if ($row['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                                </select>
                                <input type="submit" name="update_role" value="Update" class="update-btn">
                            </form>
                        </td>
                        <td>
                            <a href="admin.php?delete=<?php echo $row['id']; ?>" class="btn">Delete</a>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endwhile; ?>
        </table>
    </div>
    <div class="button_group">
        <a href="prof_add.php"><button>Добавить профессию</button></a>
    </div>
</body>
</html>

<?php
$conn->close();
?>