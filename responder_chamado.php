<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['users_id'])) {
    header('Location: login.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: meus-chamados.php');
    exit();
}

$tickets_id = filter_input(INPUT_POST, 'tickets_id', FILTER_VALIDATE_INT);
$description = trim($_POST['description'] ?? '');
$users_id = (int) $_SESSION['users_id'];

if (!$tickets_id || $description === '') {
    die('Dados invalidos.');
}

$sql = "INSERT INTO feedback_histories (tickets_id, status_id, description, users_id, Date)
        SELECT t.id, t.status_id, :description, :users_id, NOW()
        FROM tickets t
        WHERE t.id = :tickets_id
          AND t.users_id = :users_id
          AND t.status_id IN (2, 5)
          AND EXISTS (
              SELECT 1 FROM feedback_histories fh
              WHERE fh.tickets_id = t.id
                AND fh.tech_support_id IS NOT NULL
          )";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':description' => $description,
    ':users_id' => $users_id,
    ':tickets_id' => $tickets_id
]);

header('Location: meus-chamados.php?respondido=1');
exit();
