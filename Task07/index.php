<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Moscow');

try {
    // Абсолютный путь к базе данных
    $pdo = new PDO('sqlite:C:/Users/shalyaevalyubov/Desktop/Databases/303_DB_Shalyaeva_LE/Task07/students.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $currentYear = date('Y');

    // Получаем список действующих групп
    $stmt = $pdo->prepare("SELECT id, group_number FROM groups WHERE graduation_year <= :year ORDER BY group_number");
    $stmt->execute([':year' => $currentYear]);
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Определяем выбранную группу
    $selectedGroup = isset($_GET['group']) ? $_GET['group'] : '';

    // Формируем SQL для студентов
    $sql = "
        SELECT g.group_number, g.direction, s.last_name, s.first_name, s.middle_name, s.gender, s.birth_date, s.student_card
        FROM students s
        JOIN groups g ON s.group_id = g.id
        WHERE g.graduation_year <= :year
    ";
    $params = [':year' => $currentYear];

    if ($selectedGroup !== '' && array_search($selectedGroup, array_column($groups, 'group_number')) !== false) {
        $sql .= " AND g.group_number = :gnum";
        $params[':gnum'] = $selectedGroup;
    }

    $sql .= " ORDER BY g.group_number, s.last_name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список студентов</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 5px; text-align: left; }
        th { background-color: #eee; }
        select { padding: 3px; }
        input[type="submit"] { padding: 3px 6px; }
    </style>
</head>
<body>

<h2>Список студентов</h2>

<form method="get">
    <label>Фильтр по группе:
        <select name="group">
            <option value="">Все</option>
            <?php foreach ($groups as $g): ?>
                <option value="<?= $g['group_number'] ?>" <?= ($selectedGroup === $g['group_number']) ? 'selected' : '' ?>>
                    <?= $g['group_number'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <input type="submit" value="Фильтровать">
</form>

<?php if ($students): ?>
    <table>
        <tr>
            <th>Группа</th>
            <th>Направление</th>
            <th>ФИО</th>
            <th>Пол</th>
            <th>Дата рождения</th>
            <th>Студ. билет</th>
        </tr>
        <?php foreach ($students as $s): ?>
            <tr>
                <td><?= $s['group_number'] ?></td>
                <td><?= $s['direction'] ?></td>
                <td><?= $s['last_name'] . ' ' . $s['first_name'] . ' ' . $s['middle_name'] ?></td>
                <td><?= $s['gender'] ?></td>
                <td><?= $s['birth_date'] ?></td>
                <td><?= $s['student_card'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>Студентов не найдено.</p>
<?php endif; ?>

</body>
</html>
