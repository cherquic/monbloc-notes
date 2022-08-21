<?php
session_start();
$pdo = require_once $_SERVER['DOCUMENT_ROOT'] . '\src\DataBase\database.php';

$sessionId = $_SESSION['username'] ?? '';

if ($sessionId) {
    $statement = $pdo->prepare('DELETE FROM session where id=?');
    $statement->execute([$sessionId]);
    setcookie('session', '', time() - 1);
    $_SESSION['username'] = "";
}
header('Location: /index.php');
