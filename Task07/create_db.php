<?php
try {
    $pdo = new PDO('sqlite:students.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("DROP TABLE IF EXISTS students");
    $pdo->exec("DROP TABLE IF EXISTS groups");

    $pdo->exec("
        CREATE TABLE groups (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            group_number TEXT NOT NULL,
            direction TEXT NOT NULL,
            graduation_year INTEGER NOT NULL
        )
    ");

    $pdo->exec("
        CREATE TABLE students (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            last_name TEXT NOT NULL,
            first_name TEXT NOT NULL,
            middle_name TEXT,
            birth_date TEXT NOT NULL,
            gender TEXT NOT NULL CHECK (gender IN ('M','F')),
            student_card TEXT NOT NULL,
            group_id INTEGER NOT NULL,
            FOREIGN KEY(group_id) REFERENCES groups(id)
        )
    ");

    $groups = [
        ['group_number' => '101', 'direction' => 'Информатика', 'graduation_year' => 2026],
        ['group_number' => '102', 'direction' => 'Математика', 'graduation_year' => 2025],
        ['group_number' => '103', 'direction' => 'Физика', 'graduation_year' => 2024]
    ];

    $stmt = $pdo->prepare("INSERT INTO groups (group_number, direction, graduation_year) VALUES (:group_number, :direction, :graduation_year)");
    foreach ($groups as $g) {
        $stmt->execute($g);
    }

    $students = [
        ['last_name'=>'Иванов','first_name'=>'Иван','middle_name'=>'Иванович','birth_date'=>'2004-05-12','gender'=>'M','student_card'=>'S101','group_id'=>1],
        ['last_name'=>'Петрова','first_name'=>'Мария','middle_name'=>'Сергеевна','birth_date'=>'2004-08-23','gender'=>'F','student_card'=>'S102','group_id'=>1],
        ['last_name'=>'Сидоров','first_name'=>'Пётр','middle_name'=>'Александрович','birth_date'=>'2003-11-02','gender'=>'M','student_card'=>'S201','group_id'=>2],
        ['last_name'=>'Кузнецова','first_name'=>'Анна','middle_name'=>'Игоревна','birth_date'=>'2003-02-15','gender'=>'F','student_card'=>'S202','group_id'=>2],
        ['last_name'=>'Морозов','first_name'=>'Дмитрий','middle_name'=>'Сергеевич','birth_date'=>'2002-07-30','gender'=>'M','student_card'=>'S301','group_id'=>3]
    ];

    $stmt = $pdo->prepare("
        INSERT INTO students (last_name, first_name, middle_name, birth_date, gender, student_card, group_id)
        VALUES (:last_name, :first_name, :middle_name, :birth_date, :gender, :student_card, :group_id)
    ");
    foreach ($students as $s) {
        $stmt->execute($s);
    }

    echo "База данных students.db успешно создана с тестовыми данными.\n";

} catch (PDOException $e) {
    echo "Ошибка при создании базы: " . $e->getMessage() . "\n";
}
?>
