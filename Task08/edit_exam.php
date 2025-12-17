<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

$pdo = new PDO("sqlite:" . __DIR__ . "/students.db");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = $_GET['id'] ?? 0;
if(!$id) die("Не указан ID экзамена");

// Получаем экзамен
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id=:id");
$stmt->execute([':id'=>$id]);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$exam) die("Экзамен не найден");

// Получаем студента
$stmt = $pdo->prepare("SELECT * FROM students WHERE id=:id");
$stmt->execute([':id'=>$exam['student_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Получаем предметы студента
$direction = $pdo->query("SELECT direction FROM groups WHERE id=".$student['group_id'])->fetchColumn();
$subjects = $pdo->prepare("SELECT * FROM subjects WHERE direction=:dir ORDER BY study_year");
$subjects->execute([':dir'=>$direction]);
$subjects = $subjects->fetchAll(PDO::FETCH_ASSOC);

$errors = [];

if($_SERVER['REQUEST_METHOD']==='POST'){
    $subject_id = $_POST['subject_id'] ?? 0;
    $exam_date = $_POST['exam_date'] ?? '';
    $grade = $_POST['grade'] ?? '';

    if(!$subject_id) $errors[]="Выберите дисциплину";
    if(!$exam_date) $errors[]="Введите дату экзамена";
    if(!$grade) $errors[]="Введите оценку";

    if(empty($errors)){
        $stmt = $pdo->prepare("
            UPDATE exams SET
            subject_id=:subject_id,
            exam_date=:exam_date,
            grade=:grade
            WHERE id=:id
        ");
        $stmt->execute([
            ':subject_id'=>$subject_id,
            ':exam_date'=>$exam_date,
            ':grade'=>$grade,
            ':id'=>$id
        ]);
        header("Location:exams.php?student_id=".$student['id']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Редактировать экзамен</title></head>
<body>
<h1>Редактировать экзамен студента: <?= htmlspecialchars($student['last_name'].' '.$student['first_name']) ?></h1>
<?php if($errors): ?>
<ul style="color:red;">
<?php foreach($errors as $e) echo "<li>$e</li>"; ?>
</ul>
<?php endif; ?>

<form method="post">
<label>Дисциплина:
<select name="subject_id">
<option value="">— выберите —</option>
<?php foreach($subjects as $s): ?>
<option value="<?= $s['id'] ?>" <?= ($s['id']==$exam['subject_id'])?'selected':'' ?>><?= htmlspecialchars($s['name']) ?></option>
<?php endforeach; ?>
</select>
</label><br><br>

<label>Дата экзамена: <input type="date" name="exam_date" value="<?= htmlspecialchars($exam['exam_date']) ?>"></label><br><br>
<label>Оценка: <input type="text" name="grade" value="<?= htmlspecialchars($exam['grade']) ?>"></label><br><br>

<button type="submit">Сохранить</button>
</form>

<p><a href="exams.php?student_id=<?= $student['id'] ?>">Назад к экзаменам</a></p>
</body>
</html>
