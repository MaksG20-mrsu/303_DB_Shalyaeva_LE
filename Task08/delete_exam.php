<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
$pdo = new PDO("sqlite:" . __DIR__ . "/students.db");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = $_GET['id'] ?? 0;
if($id){
    $student_id = $pdo->query("SELECT student_id FROM exams WHERE id=$id")->fetchColumn();
    $pdo->prepare("DELETE FROM exams WHERE id=:id")->execute([':id'=>$id]);
    header("Location:exams.php?student_id=".$student_id);
    exit;
}
