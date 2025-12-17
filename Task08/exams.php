<?php
$pdo = new PDO("sqlite:" . __DIR__ . "/students.db");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$student_id = $_GET['student_id'] ?? 0;
if(!$student_id) die("Не указан студент");

// Получаем студента
$stmt = $pdo->prepare("SELECT * FROM students WHERE id=:id");
$stmt->execute([':id'=>$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$student) die("Студент не найден");

// Получаем экзамены студента
$stmt = $pdo->prepare("
SELECT exams.id as exam_id, subjects.name, exams.exam_date, exams.grade
FROM exams
JOIN subjects ON exams.subject_id = subjects.id
WHERE exams.student_id=:student_id
ORDER BY exams.exam_date
");
$stmt->execute([':student_id'=>$student_id]);
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем предметы студента для добавления нового экзамена
$direction = $pdo->query("SELECT direction FROM groups WHERE id=".$student['group_id'])->fetchColumn();
$subjects = $pdo->prepare("SELECT * FROM subjects WHERE direction=:dir ORDER BY study_year");
$subjects->execute([':dir'=>$direction]);
$subjects = $subjects->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Экзамены</title></head>
<body>
<h1>Экзамены студента <?= htmlspecialchars($student['last_name'].' '.$student['first_name']) ?></h1>

<table border="1" cellpadding="5">
<tr>
<th>Дисциплина</th><th>Дата</th><th>Оценка</th><th>Действия</th>
</tr>
<?php foreach($exams as $e): ?>
<tr>
<td><?= htmlspecialchars($e['name']) ?></td>
<td><?= htmlspecialchars($e['exam_date']) ?></td>
<td><?= htmlspecialchars($e['grade']) ?></td>
<td>
<a href="edit_exam.php?id=<?= $e['exam_id'] ?>">Редактировать</a> |
<a href="delete_exam.php?id=<?= $e['exam_id'] ?>&student_id=<?= $student_id ?>" onclick="return confirm('Удалить экзамен?')">Удалить</a>
</td>
</tr>
<?php endforeach; ?>
</table>

<h2>Добавить новый экзамен</h2>
<form method="post" action="add_exam.php?student_id=<?= $student_id ?>">
<label>Дисциплина:
<select name="subject_id">
<option value="">— выберите —</option>
<?php foreach($subjects as $s): ?>
<option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
<?php endforeach; ?>
</select>
</label><br><br>
<label>Дата экзамена: <input type="date" name="exam_date"></label><br><br>
<label>Оценка: <input type="text" name="grade"></label><br><br>
<button type="submit">Добавить экзамен</button>
</form>

<p><a href="index.php">Назад к студентам</a></p>
</body>
</html>
