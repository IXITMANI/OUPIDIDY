<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>IT Профессии</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='css/main.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='css/navmain.css'>
</head>
<body>
    <header>
        <nav class="links_header">
            <div class="empty_space"> </br> </div>
            <ul class="nav_links">
                <li><nav class="links">
                    <a href="php/ratings.php"><button>рейтинги</button></a>
                    <a href="#">###</a>
                    <a href="#">###</a></nav>
                </li>
                <li><nav class="auth">
                    <a href='html/login.html'><button>вход</button></a>
                    <a href='html/register.html'><button>регистрация</button></a>
                    </nav>
                </li>
            </ul>
        </nav>
    </header>

    <div class="main-shit">
    <h2 class="heading">IT-профессии</h2>

    <section id="professions">
        <?php
        // Подключение к базе данных
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "MyUsers";

        // Создание соединения
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Проверка соединения
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // SQL-запрос для получения профессий и их цветов
        $sql = "SELECT p.id, p.name, p.description, c.hex AS color 
                FROM professions p 
                JOIN colors c ON p.color_id = c.id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Вывод данных каждой строки
            while($row = $result->fetch_assoc()) {
                echo "<a href='php/profession.php?id=" . $row["id"] . "'>";
                echo "<article class='profession';'>";
                echo "<h3>" . $row["name"] . "</h3>";
                echo "<p>". $row["description"]."</p>"; // Здесь можно добавить описание из другой таблицы, если оно есть
                echo "</article>";
                echo "</a>";
            }
        } else {
            echo "0 results";
        }

        $conn->close();
        ?>
    </section>
  </div>  
</body>
    <a href="https://псж.онлайн"><footer> <p>&copy; 2025  Путеводитель по IT-профессиям</p> </footer></a>
</html>