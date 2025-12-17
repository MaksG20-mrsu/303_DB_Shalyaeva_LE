<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$dbFile = __DIR__ . '/students.db';

// Удаляем старую, если есть
if (file_exists($dbFile)) {
    unlink($dbFile);
}

$pdo = new PDO("sqlite:$dbFile");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Создаём таблицы
$pdo->exec("
CREATE TABLE groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    group_number TEXT NOT NULL,
    direction TEXT NOT NULL,
    year_start INTEGER,
    year_end INTEGER
);

CREATE TABLE students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    last_name TEXT NOT NULL,
    first_name TEXT NOT NULL,
    middle_name TEXT,
    gender TEXT CHECK(gender IN ('M','F')) NOT NULL,
    birth_date TEXT NOT NULL,
    student_card TEXT NOT NULL,
    group_id INTEGER NOT NULL,
    FOREIGN KEY(group_id) REFERENCES groups(id)
);

CREATE TABLE subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    direction TEXT NOT NULL,
    study_year INTEGER NOT NULL
);

CREATE TABLE exams (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    exam_date TEXT NOT NULL,
    grade TEXT NOT NULL,
    FOREIGN KEY(student_id) REFERENCES students(id),
    FOREIGN KEY(subject_id) REFERENCES subjects(id)
);
");

// Заполняем справочник групп
$groups = [
    ['101', 'Информатика', 2021, 2025],
    ['102', 'Информатика', 2021, 2025],
    ['201', 'Экономика', 2020, 2024],
];
$stmt = $pdo->prepare("INSERT INTO groups (group_number, direction, year_start, year_end) VALUES (?, ?, ?, ?)");
foreach ($groups as $g) {
    $stmt->execute($g);
}

// Заполняем справочник предметов
$subjects = [
    ['Программирование', 'Информатика', 1],
    ['Алгоритмы и структуры данных', 'Информатика', 2],
    ['Базы данных', 'Информатика', 2],
    ['Веб-разработка', 'Информатика', 3],
    ['Микроэкономика', 'Экономика', 1],
    ['Макроэкономика', 'Экономика', 2],
    ['Финансы', 'Экономика', 3],
];
$stmt = $pdo->prepare("INSERT INTO subjects (name, direction, study_year) VALUES (?, ?, ?)");
foreach ($subjects as $s) {
    $stmt->execute($s);
}

// Заполняем студентов
$students = [
    ['Иванов', 'Иван', 'Иванович', 'M', '2003-03-10', 'S101', 1],
    ['Петрова', 'Мария', 'Сергеевна', 'F', '2004-05-22', 'S102', 1],
    ['Сидоров', 'Пётр', 'Алексеевич', 'M', '2003-11-15', 'S103', 2],
    ['Кузнецова', 'Анна', 'Игоревна', 'F', '2004-01-30', 'S104', 2],
    ['Смирнов', 'Алексей', 'Николаевич', 'M', '2002-07-05', 'S201', 3],
];
$stmt = $pdo->prepare("
INSERT INTO students 
(last_name, first_name, middle_name, gender, birth_date, student_card, group_id)
VALUES (?, ?, ?, ?, ?, ?, ?)
");
foreach ($students as $s) {
    $stmt->execute($s);
}

// Заполняем экзамены
$exams = [
    [1, 1, '2022-06-10', '5'],
    [1, 2, '2023-01-15', '4'],
    [2, 1, '2022-06-10', '5'],
    [2, 3, '2023-01-20', '4'],
    [3, 2, '2023-02-01', '3'],
    [3, 4, '2024-05-15', '4'],
    [4, 2, '2023-02-01', '4'],
    [4, 4, '2024-05-15', '5'],
    [5, 5, '2021-06-10', '5'],
    [5, 6, '2022-05-12', '4'],
];
$stmt = $pdo->prepare("INSERT INTO exams (student_id, subject_id, exam_date, grade) VALUES (?, ?, ?, ?)");
foreach ($exams as $e) {
    $stmt->execute($e);
}

echo "База данных создана и заполнена!";
