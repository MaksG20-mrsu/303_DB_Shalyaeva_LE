<?php
// task7_cli.php
// Консольный скрипт для вывода студентов с фильтром по группам
date_default_timezone_set('Europe/Moscow'); 

try {
    $pdo = new PDO('sqlite:students.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $currentYear = date('Y');
    $stmt = $pdo->prepare("SELECT id, group_number FROM groups WHERE graduation_year <= :year ORDER BY group_number");
    $stmt->execute([':year' => $currentYear]);
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$groups) {
        echo "Нет действующих групп.\n";
        exit;
    }

    echo "Доступные группы:\n";
    foreach ($groups as $g) {
        echo $g['group_number'] . "\n";
    }

    echo "Введите номер группы (или Enter для всех): ";
    $handle = fopen("php://stdin", "r");
    $input = trim(fgets($handle));
    fclose($handle);

    $groupIds = array_column($groups, 'id', 'group_number');
    if ($input !== '' && !array_key_exists($input, $groupIds)) {
        echo "Ошибка: такой группы нет в списке.\n";
        exit;
    }

    $sql = "
        SELECT g.group_number, g.direction, s.last_name, s.first_name, s.middle_name, s.gender, s.birth_date, s.student_card
        FROM students s
        JOIN groups g ON s.group_id = g.id
        WHERE g.graduation_year <= :year
    ";
    $params = [':year' => $currentYear];

    if ($input !== '') {
        $sql .= " AND g.id = :gid";
        $params[':gid'] = $groupIds[$input];
    }

    $sql .= " ORDER BY g.group_number, s.last_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$students) {
        echo "Студентов не найдено.\n";
        exit;
    }

    $headers = ['Группа', 'Направление', 'ФИО', 'Пол', 'Дата рождения', 'Студ. билет'];

    $widths = [];
    foreach ($headers as $h) $widths[$h] = mb_strlen($h);
    foreach ($students as $s) {
        $fio = $s['last_name'].' '.$s['first_name'].' '.$s['middle_name'];
        $widths['Группа'] = max($widths['Группа'], mb_strlen($s['group_number']));
        $widths['Направление'] = max($widths['Направление'], mb_strlen($s['direction']));
        $widths['ФИО'] = max($widths['ФИО'], mb_strlen($fio));
        $widths['Пол'] = max($widths['Пол'], mb_strlen($s['gender']));
        $widths['Дата рождения'] = max($widths['Дата рождения'], mb_strlen($s['birth_date']));
        $widths['Студ. билет'] = max($widths['Студ. билет'], mb_strlen($s['student_card']));
    }

    function printLine($widths) {
        echo '+';
        foreach ($widths as $w) echo str_repeat('-', $w + 2) . '+';
        echo "\n";
    }

    printLine($widths);
    echo '|';
    foreach ($headers as $h) echo ' ' . str_pad($h, $widths[$h]) . ' |';
    echo "\n";
    printLine($widths);

    foreach ($students as $s) {
        $fio = $s['last_name'].' '.$s['first_name'].' '.$s['middle_name'];
        echo '| ' . str_pad($s['group_number'], $widths['Группа']) . ' | ' .
             str_pad($s['direction'], $widths['Направление']) . ' | ' .
             str_pad($fio, $widths['ФИО']) . ' | ' .
             str_pad($s['gender'], $widths['Пол']) . ' | ' .
             str_pad($s['birth_date'], $widths['Дата рождения']) . ' | ' .
             str_pad($s['student_card'], $widths['Студ. билет']) . " |\n";
    }

    printLine($widths);

} catch (PDOException $e) {
    echo "Ошибка базы данных: " . $e->getMessage() . "\n";
}
?>
