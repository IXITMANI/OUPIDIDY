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
    CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL,
    password VARCHAR(255) NOT NULL,
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    age INT(5) NOT NULL,
    role VARCHAR(255) DEFAULT 'user'
    );

    CREATE TABLE IF NOT EXISTS colors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    color VARCHAR(50) NOT NULL,
    hex VARCHAR(7) NOT NULL
    );

    CREATE TABLE IF NOT EXISTS professions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    color_id INT,
    FOREIGN KEY (color_id) REFERENCES colors(id) ON DELETE SET NULL
    );

    CREATE TABLE IF NOT EXISTS qualities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
    );

    CREATE TABLE IF NOT EXISTS profession_qualities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profession_id INT NOT NULL,
    quality_id INT NOT NULL,
    FOREIGN KEY (profession_id) REFERENCES professions(id) ON DELETE CASCADE,
    FOREIGN KEY (quality_id) REFERENCES qualities(id) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS expert_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT(6) UNSIGNED NOT NULL,
    profession_quality_id INT NOT NULL,
    rating DECIMAL(3,2) NOT NULL,
    UNIQUE KEY unique_rating (expert_id, profession_quality_id),
    FOREIGN KEY (expert_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (profession_quality_id) REFERENCES profession_qualities(id) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    link VARCHAR(255) NOT NULL
    );

    INSERT INTO tests (name, description, link) VALUES
    ('Тест на реакцию', 'Проверьте свою скорость реакции на появление круга.', './reaction_test.php'),
    ('Тест на звук', 'Проверьте свою реакцию на звуковые сигналы.', './sound_test.php'),
    ('Тест на цвет', 'Проверьте свою реакцию на цвета.', './color_test.php'),
    ('Тест на числа (звук)', 'Проверьте свою реакцию на сумму чисел, воспроизводимых звуками.', './number_sound_test.php'),
    ('Тест на числа (экран)', 'Проверьте свою реакцию на сумму чисел, отображаемых на экране.', './number_display_test.php'),
    ('Тест с кругом', 'Проверьте свою реакцию на движущуюся точку.', './test_settings.php');

    CREATE TABLE IF NOT EXISTS user_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6) UNSIGNED NOT NULL,
    test_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
    );
    CREATE TABLE IF NOT EXISTS test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6) UNSIGNED NOT NULL,
    test_name VARCHAR(255) NOT NULL,
    mean_reaction_time DECIMAL(10, 2) NOT NULL,
    std_dev DECIMAL(10, 2) NOT NULL,
    accuracy DECIMAL(5, 2) NOT NULL,
    incorrect_responses INT NOT NULL,
    misses INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
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
├── [Main.php](http://_vscodecontentref_/1)                 # Главная страница
├── [README.md](http://_vscodecontentref_/2)                # Файл с описанием проекта
├── css/                     # Папка со стилями
│   ├── admin.css
│   ├── adviser.css
│   ├── login.css
│   ├── main.css
│   ├── nav.css
│   ├── navmain.css
│   ├── rating.css
│   ├── register.css
│   ├── style.css
│   └── reaction_test.css    # Стили для теста на реакцию
├── html/                    # Папка с HTML-страницами
│   ├── BlackDevOps.html
│   ├── Cybersecurity.html
│   ├── DataScientist.html
│   ├── login.html
│   ├── register.html
│   ├── SoftwareDeveloper.html
│   ├── UIDesigner.html
│   ├── WebDeveloper.html
│   └── reaction_test.html   # Страница теста на реакцию
├── php/                     # Папка с PHP-скриптами
│   ├── admin.php
│   ├── adviser.php
│   ├── login.php
│   ├── logout.php
│   ├── prof_add.php
│   ├── profession.php
│   ├── ratings.php
│   ├── register.php
│   ├── user.php
│   └── reaction_test.php    # Скрипт для теста на реакцию
```