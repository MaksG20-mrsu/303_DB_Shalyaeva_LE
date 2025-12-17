<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
$pdo = new PDO("sqlite:" . __DIR__ . "/students.db");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// группы
$groupsStmt = $pdo->query("SELECT id, group_number FROM groups ORDER BY group_number");
$groups = $groupsStmt->fetchAll(PDO::FETCH_ASSOC);

$selectedGroup = $_GET['group_id'] ?? '';

// студенты
$sql = "
SELECT s.id, g.group_number, s.last_name, s.first_name, s.middle_name,
       s.gender, s.birth_date, s.student_card
FROM students s
JOIN groups g ON s.group_id = g.id
";
$params = [];
if ($selectedGroup !== '') {
    $sql .= " WHERE g.id = :group_id";
    $params[':group_id'] = $selectedGroup;
}
$sql .= " ORDER BY g.group_number, s.last_name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Список студентов</title>
<style>
table { border-collapse: collapse; }
th, td { border:1px solid #444; padding:6px 10px; }
th { background:#eee; }
</style>
</head>
<body>
<h1>Список студентов</h1>

<form method="get">
<label>Группа:
<select name="group_id">
<option value="">— все группы —</option>
<?php foreach($groups as $g): ?>
<option value="<?= $g['id'] ?>" <?= ($g['id']==$selectedGroup)?'selected':'' ?>>
<?= htmlspecialchars($g['group_number']) ?></option>
<?php endforeach; ?>
</select>
</label>
<button type="submit">Показать</button>
</form>

<br>
<table>
<tr>
<th>Группа</th>
<th>ФИО</th>
<th>Пол</th>
<th>Дата рождения</th>
<th>№ студенческого</th>
<th>Действия</th>
</tr>
<?php foreach($students as $s): ?>
<tr>
<td><?= htmlspecialchars($s['group_number']) ?></td>
<td><?= htmlspecialchars($s['last_name'].' '.$s['first_name'].' '.$s['middle_name']) ?></td>
<td><?= $s['gender']==='M'?'М':'Ж' ?></td>
<td><?= htmlspecialchars($s['birth_date']) ?></td>
<td><?= htmlspecialchars($s['student_card']) ?></td>
<td>
<a href="edit_student.php?id=<?= $s['id'] ?>">Редактировать</a> |
<a href="delete_student.php?id=<?= $s['id'] ?>">Удалить</a> |
<a href="exams.php?student_id=<?= $s['id'] ?>">Результаты экзаменов</a>
</td>
</tr>
<?php endforeach; ?>
</table>
<br>
<a href="add_student.php"><button>Добавить студента</button></a>

</body>
</html>
