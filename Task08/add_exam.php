<?php
$pdo = new PDO("sqlite:" . __DIR__ . "/students.db");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$student_id = $_GET['student_id'] ?? 0;
if(!$student_id) die("Не указан студент");

if($_SERVER['REQUEST_METHOD']==='POST'){
    $subject_id = $_POST['subject_id'] ?? 0;
    $exam_date = $_POST['exam_date'] ?? '';
    $grade = $_POST['grade'] ?? '';

    if($subject_id && $exam_date && $grade){
        $stmt = $pdo->prepare("INSERT INTO exams (student_id, subject_id, exam_date, grade) VALUES (:sid, :subid, :date, :grade)");
        $stmt->execute([
            ':sid'=>$student_id,
            ':subid'=>$subject_id,
            ':date'=>$exam_date,
            ':grade'=>$grade
        ]);
    }
}

header("Location:exams.php?student_id=$student_id");
exit;
