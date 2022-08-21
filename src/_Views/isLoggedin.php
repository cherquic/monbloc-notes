<?php

function isLoggedIn()
{
    $pdo = require_once $_SERVER['DOCUMENT_ROOT'] . '\src\DataBase\database.php';
    $sessionId = $_COOKIE['session'] ?? '';
    if ($sessionId) {
        $sessionUserStatement = $pdo->prepare('SELECT * FROM session JOIN user on user.id=session.userid WHERE session.id=?');
        $sessionUserStatement->execute([$sessionId]);
        $user = $sessionUserStatement->fetch();
    }

    return $user ?? false;
}
