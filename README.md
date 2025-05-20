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
    ('Тест с кругом', 'Проверьте свою реакцию на движущуюся точку.', './test_settings.php'),
    ('Тест с тремя кругами', 'Проверьте свою реакцию на три движущиеся точки.', './three_circle_settings.php'),
    ('Тест на внимание', 'Проверьте свою концентрацию, переключаемость и распределение внимания.', './attention_test_settings.php'),
    ('Тест на память', 'Запомни и повтори рисунок', './memory_test_settings.php'),
    ('Тест на оценку мышления', 'ответь на вопросы', './question_test_options.php'),
    ('Тест аналоговое слежение ', 'держи кружок в центре', './analog_tracking_settings.php'),
    ('Тест аналоговое преследование', 'держи прицел на кружке', './analog_pursuit_settings.php');

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
    CREATE TABLE IF NOT EXISTS memory_test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6) UNSIGNED NOT NULL,
    test_name VARCHAR(255) NOT NULL,
    stage INT NOT NULL,
    score INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    CREATE TABLE thinking_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(32) NOT NULL,
    question TEXT NOT NULL,
    option1 VARCHAR(255) NOT NULL,
    option2 VARCHAR(255) NOT NULL,
    option3 VARCHAR(255) NOT NULL,
    option4 VARCHAR(255) NOT NULL,
    correct_option INT NOT NULL -- 0,1,2,3
    );
    -- Секция: Сравнение (10 вопросов)
    INSERT INTO thinking_questions (section, question, option1, option2, option3, option4, correct_option) VALUES
    ('Сравнение', 'Предмет 1: Красная машина с двумя дверями. Предмет 2: Красная машина с четырьмя дверями. Чем они отличаются?', 'Цвет', 'Назначение', 'Количество дверей', 'Материал', 2),
    ('Сравнение', 'Предмет 1: Синяя ручка. Предмет 2: Красная ручка. Чем они отличаются?', 'Цвет', 'Форма', 'Длина', 'Материал', 0),
    ('Сравнение', 'Предмет 1: Деревянный стол. Предмет 2: Стеклянный стол. Чем они отличаются?', 'Материал', 'Цвет', 'Размер', 'Назначение', 0),
    ('Сравнение', 'Предмет 1: Желтый мяч. Предмет 2: Желтый кубик. Чем они отличаются?', 'Цвет', 'Форма', 'Размер', 'Материал', 1),
    ('Сравнение', 'Предмет 1: Большой дом. Предмет 2: Маленький дом. Чем они отличаются?', 'Размер', 'Цвет', 'Материал', 'Назначение', 0),
    ('Сравнение', 'Предмет 1: Круглая тарелка. Предмет 2: Квадратная тарелка. Чем они отличаются?', 'Форма', 'Цвет', 'Размер', 'Материал', 0),
    ('Сравнение', 'Предмет 1: Зеленое яблоко. Предмет 2: Красное яблоко. Чем они отличаются?', 'Цвет', 'Вкус', 'Размер', 'Форма', 0),
    ('Сравнение', 'Предмет 1: Пластиковая бутылка. Предмет 2: Стеклянная бутылка. Чем они отличаются?', 'Материал', 'Цвет', 'Размер', 'Назначение', 0),
    ('Сравнение', 'Предмет 1: Легковой автомобиль. Предмет 2: Грузовой автомобиль. Чем они отличаются?', 'Назначение', 'Цвет', 'Размер', 'Форма', 0),
    ('Сравнение', 'Предмет 1: Короткая верёвка. Предмет 2: Длинная верёвка. Чем они отличаются?', 'Длина', 'Цвет', 'Материал', 'Форма', 0);

    -- Секция: Анализ (10 вопросов)
    INSERT INTO thinking_questions (section, question, option1, option2, option3, option4, correct_option) VALUES
    ('Анализ', 'На уроке биологии ученики рассматривали листья под микроскопом. Учитель объяснил, как устроены клетки растений. Что делали ученики?', 'Слушали объяснение', 'Изучали растения', 'Писали конспект', 'Рисовали таблицу', 1),
    ('Анализ', 'В магазине мама выбирала овощи, а сын рассматривал витрину с фруктами. Что делал сын?', 'Покупал овощи', 'Считал деньги', 'Рассматривал витрину', 'Готовил еду', 2),
    ('Анализ', 'Петя читал книгу, а его сестра рисовала картину. Что делала сестра?', 'Читала', 'Рисовала', 'Писала', 'Слушала музыку', 1),
    ('Анализ', 'На уроке труда дети делали поделки из бумаги. Что делали дети?', 'Читали', 'Делали поделки', 'Смотрели фильм', 'Писали диктант', 1),
    ('Анализ', 'В парке дети катались на велосипедах, а взрослые гуляли по дорожкам. Что делали взрослые?', 'Катались на велосипедах', 'Гуляли', 'Кормили птиц', 'Сидели на скамейке', 1),
    ('Анализ', 'На кухне мама готовила обед, а папа читал газету. Что делал папа?', 'Готовил обед', 'Читал газету', 'Мыл посуду', 'Смотрел телевизор', 1),
    ('Анализ', 'В классе дети решали задачи, а учитель объяснял новую тему. Что делал учитель?', 'Решал задачи', 'Объяснял тему', 'Проверял тетради', 'Писал на доске', 1),
    ('Анализ', 'На стадионе спортсмены бегали по дорожке, а тренер следил за ними. Что делал тренер?', 'Бегал', 'Следил', 'Играл в футбол', 'Прыгал', 1),
    ('Анализ', 'В библиотеке дети читали книги, а библиотекарь расставлял книги по полкам. Что делал библиотекарь?', 'Читал книги', 'Расставлял книги', 'Писал сочинение', 'Рисовал', 1),
    ('Анализ', 'На уроке рисования дети рисовали пейзаж, а учитель помогал им. Что делал учитель?', 'Рисовал пейзаж', 'Помогал детям', 'Пел песню', 'Читал рассказ', 1);

    -- Секция: Классификация (10 вопросов)
    INSERT INTO thinking_questions (section, question, option1, option2, option3, option4, correct_option) VALUES
    ('Классификация', 'Слова: собака, кошка, попугай, машина. Что лишнее?', 'собака', 'кошка', 'попугай', 'машина', 3),
    ('Классификация', 'Слова: яблоко, груша, апельсин, стол. Что лишнее?', 'яблоко', 'груша', 'апельсин', 'стол', 3),
    ('Классификация', 'Слова: автобус, велосипед, поезд, яблоко. Что лишнее?', 'автобус', 'велосипед', 'поезд', 'яблоко', 3),
    ('Классификация', 'Слова: зима, лето, весна, стол. Что лишнее?', 'зима', 'лето', 'весна', 'стол', 3),
    ('Классификация', 'Слова: карандаш, ручка, тетрадь, мяч. Что лишнее?', 'карандаш', 'ручка', 'тетрадь', 'мяч', 3),
    ('Классификация', 'Слова: лиса, волк, медведь, трактор. Что лишнее?', 'лиса', 'волк', 'медведь', 'трактор', 3),
    ('Классификация', 'Слова: стол, стул, диван, апельсин. Что лишнее?', 'стол', 'стул', 'диван', 'апельсин', 3),
    ('Классификация', 'Слова: огурец, помидор, капуста, автобус. Что лишнее?', 'огурец', 'помидор', 'капуста', 'автобус', 3),
    ('Классификация', 'Слова: ворона, голубь, воробей, компьютер. Что лишнее?', 'ворона', 'голубь', 'воробей', 'компьютер', 3),
    ('Классификация', 'Слова: молоко, кефир, сыр, велосипед. Что лишнее?', 'молоко', 'кефир', 'сыр', 'велосипед', 3);
    
    CREATE TABLE skill_assessment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    attention_score FLOAT,
    reaction_score FLOAT,
    thinking_score FLOAT,
    total_score FLOAT,
    passed BOOL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
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