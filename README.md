# OUPIDIDY
Itmo Web-programming

Этот проект представляет собой простой веб-сайт, созданный для демонстрации различных страниц и функциональности, включая регистрацию пользователей и сохранение данных в базе данных MySQL.

## Установка

### Шаг 1: Установка XAMPP

1. Скачайте XAMPP с [официального сайта](https://www.apachefriends.org/index.html).
2. Установите XAMPP, следуя инструкциям мастера установки.
3. Запустите XAMPP Control Panel и нажмите "Start" рядом с Apache и MySQL.

### Шаг 2: Настройка базы данных

1. Откройте phpMyAdmin, перейдя по адресу [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
2. Создайте новую базу данных, например `MyUsers`.
3. Выполните следующий SQL-запрос для создания таблицы `users`:

    ```sql
    CREATE TABLE users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL,
        password VARCHAR(255) NOT NULL,
        reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        age INT(5) NOT NULL,
        role VARCHAR(255) DEFAULT 'user'
    );
    ```
    ```sql
    CREATE TABLE professions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    color_id INT,
    FOREIGN KEY (color_id) REFERENCES colors(id) ON DELETE SET NULL
    );
    ```
    ```sql
    CREATE TABLE qualities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
    );
    ```
    ```sql
    CREATE TABLE colors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    color VARCHAR(50) NOT NULL,
    hex VARCHAR(7) NOT NULL
    );
    ```
    ```sql
    CREATE TABLE profession_qualities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profession_id INT NOT NULL,
    quality_id INT NOT NULL,
    FOREIGN KEY (profession_id) REFERENCES professions(id) ON DELETE CASCADE,
    FOREIGN KEY (quality_id) REFERENCES qualities(id) ON DELETE CASCADE
    );
    ```
    ```sql
    CREATE TABLE expert_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT(6) UNSIGNED NOT NULL,
    profession_quality_id INT NOT NULL,
    rating DECIMAL(3,2) NOT NULL,
    UNIQUE KEY unique_rating (expert_id, profession_quality_id),
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (profession_quality_id) REFERENCES profession_qualities(id) ON DELETE CASCADE
    );
    ```

### Шаг 3: Размещение файлов проекта
 Скопируйте файлы проекта в папку `C:\xampp\htdocs\OUPIDIDY\`.
    1. Перейдите в папку `C:\xampp\htdocs` в терминале (консоли).
    2. Напишите команду `git clone https://github.com/IXITMANI/OUPIDIDY`.

## Использование

1. Откройте браузер и перейдите по адресу `http://localhost/OUPIDIDY/Main.php`.
2. На главной странице нажмите ссылку "Регистрация", чтобы перейти на страницу регистрации.
3. Заполните форму регистрации и нажмите кнопку "Зарегистрироваться".
4. Если регистрация прошла успешно, вы будете перенаправлены на страницу пользователя.

## Структура проекта

```plaintext
C:/xampp/htdocs/OUPIDIDY/
├── Main.php                 # Главная страница
├── README.md                # Файл с описанием проекта
├── css/                     # Папка со стилями
│   ├── admin.css
│   ├── adviser.css
│   ├── login.css
│   ├── main.css
│   ├── nav.css
│   ├── navmain.css
│   ├── rating.css
│   ├── register.css
│   └── style.css
├── html/                    # Папка с HTML-страницами
│   ├── BlackDevOps.html
│   ├── Cybersecurity.html
│   ├── DataScientist.html
│   ├── login.html
│   ├── register.html
│   ├── SoftwareDeveloper.html
│   ├── UIDesigner.html
│   └── WebDeveloper.html
├── php/                     # Папка с PHP-скриптами
│   ├── admin.php
│   ├── adviser.php
│   ├── login.php
│   ├── logout.php
│   ├── prof_add.php
│   ├── profession.php
│   ├── ratings.php
│   ├── register.php
│   └── user.php
```