<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
$pdo = new PDO("sqlite:" . __DIR__ . "/students.db");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = $_GET['id'] ?? 0;
if($id){
    // сначала удалить экзамены студента
    $pdo->prepare("DELETE FROM exams WHERE student_id=:id")->execute([':id'=>$id]);
    // потом студента
    $pdo->prepare("DELETE FROM students WHERE id=:id")->execute([':id'=>$id]);
}

header("Location:index.php");
exit;
