<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

$pdo = new PDO("sqlite:" . __DIR__ . "/students.db");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = $_GET['id'] ?? 0;
if(!$id){
    die("Не указан ID студента");
}

// Получаем данные студента
$stmt = $pdo->prepare("SELECT * FROM students WHERE id=:id");
$stmt->execute([':id'=>$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$student) die("Студент не найден");

// Получаем список групп
$groups = $pdo->query("SELECT id, group_number FROM groups ORDER BY group_number")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];

if($_SERVER['REQUEST_METHOD']==='POST'){
    $last_name = trim($_POST['last_name'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $student_card = trim($_POST['student_card'] ?? '');
    $group_id = $_POST['group_id'] ?? '';

    // Валидация
    if($last_name==='') $errors[]="Введите фамилию";
    if($first_name==='') $errors[]="Введите имя";
    if(!in_array($gender,['M','F'])) $errors[]="Выберите пол";
    if($birth_date==='') $errors[]="Введите дату рождения";
    if($student_card==='') $errors[]="Введите номер студенческого";
    if(!array_column($groups,'id','id')[$group_id]??false) $errors[]="Выберите группу";

    if(empty($errors)){
        $stmt = $pdo->prepare("
            UPDATE students SET
            last_name=:last_name,
            first_name=:first_name,
            middle_name=:middle_name,
            gender=:gender,
            birth_date=:birth_date,
            student_card=:student_card,
            group_id=:group_id
            WHERE id=:id
        ");
        $stmt->execute([
            ':last_name'=>$last_name,
            ':first_name'=>$first_name,
            ':middle_name'=>$middle_name,
            ':gender'=>$gender,
            ':birth_date'=>$birth_date,
            ':student_card'=>$student_card,
            ':group_id'=>$group_id,
            ':id'=>$id
        ]);
        header("Location:index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Редактировать студента</title></head>
<body>
<h1>Редактировать студента</h1>
<?php if($errors): ?>
<ul style="color:red;">
<?php foreach($errors as $e) echo "<li>$e</li>"; ?>
</ul>
<?php endif; ?>

<form method="post">
<label>Фамилия: <input type="text" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? $student['last_name']) ?>"></label><br><br>
<label>Имя: <input type="text" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? $student['first_name']) ?>"></label><br><br>
<label>Отчество: <input type="text" name="middle_name" value="<?= htmlspecialchars($_POST['middle_name'] ?? $student['middle_name']) ?>"></label><br><br>

<label>Пол:
<label><input type="radio" name="gender" value="M" <?= (($_POST['gender'] ?? $student['gender'])==='M')?'checked':'' ?>> М</label>
<label><input type="radio" name="gender" value="F" <?= (($_POST['gender'] ?? $student['gender'])==='F')?'checked':'' ?>> Ж</label>
</label><br><br>

<label>Дата рождения: <input type="date" name="birth_date" value="<?= htmlspecialchars($_POST['birth_date'] ?? $student['birth_date']) ?>"></label><br><br>
<label>№ студенческого: <input type="text" name="student_card" value="<?= htmlspecialchars($_POST['student_card'] ?? $student['student_card']) ?>"></label><br><br>

<label>Группа:
<select name="group_id">
<option value="">— выберите группу —</option>
<?php foreach($groups as $g): ?>
<option value="<?= $g['id'] ?>" <?= ($g['id']==($_POST['group_id'] ?? $student['group_id']))?'selected':'' ?>><?= htmlspecialchars($g['group_number']) ?></option>
<?php endforeach; ?>
</select>
</label><br><br>

<button type="submit">Сохранить</button>
</form>

<p><a href="index.php">Назад к списку студентов</a></p>
</body>
</html>
