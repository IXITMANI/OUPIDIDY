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

### Шаг 3: Размещение файлов проекта
 Скопируйте файлы проекта в папку `C:\xampp\htdocs\OUPIDIDY\`.
    1. Перейдите в папку `C:\xampp\htdocs` в терминале (консоли).
    2. Напишите команду `git clone https://github.com/IXITMANI/OUPIDIDY`.

## Использование

1. Откройте браузер и перейдите по адресу `http://localhost/OUPIDIDY/Main.html`.
2. На главной странице нажмите ссылку "Регистрация", чтобы перейти на страницу регистрации.
3. Заполните форму регистрации и нажмите кнопку "Зарегистрироваться".
4. Если регистрация прошла успешно, вы будете перенаправлены на страницу пользователя.

## Структура проекта

```plaintext
C:/xampp/htdocs/OUPIDIDY/
├── Main.html                 # Главная страница
├── register.html             # Страница регистрации
├── register.php              # Обработчик регистрации на PHP
├── login.html                # Страница входа
├── login.php                 # Обработчик входа на PHP
├── user.php                  # Страница пользователя
├── admin.php                 # Страница администратора
├── logout.php                # Обработчик выхода
├── SoftwareDeveloper.html    # Страница профессии "Software Developer"
├── DataScientist.html        # Страница профессии "Data Scientist"
├── WebDeveloper.html         # Страница профессии "Web Developer"
├── Cybersecurity.html        # Страница профессии "Cybersecurity Specialist"
├── BlackDevOps.html          # Страница профессии "DevOps Specialist"
└── styles.css                # Стили (опционально)
```