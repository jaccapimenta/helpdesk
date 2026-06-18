<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tickets_id = $_POST['tickets_id']; // De qual chamado é essa resposta
    $description = $_POST['description']; // O texto que o técnico digitador
    $tech_id = $_SESSION['usuario_id']; // id do técnico logado
    
    // Pega o status atual que veio do formulário (ou busca do banco)
    $status_id = $_POST['status_id']; 
    $date_now = date('Y-m-d H:i:s');

    try {
        // Insere a linha na tabela de histórico
        $sql = "INSERT INTO feedback_history (status_id, description, tech_support_id, Date) 
                VALUES (:status_id, :description, :tech_id, :data_envio)";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':status_id' => $status_id,
            ':description' => $description,
            ':tech_id' => $tech_id,
            ':data_envio' => $date_now
        ]);

        // Redireciona de volta para a página dos detalhes do chamado
        header("Location: detalhes-chamado.php?id=" . $tickets_id);
        exit();
    } catch (PDOException $e) {
        echo "Erro ao salvar histórico: " . $e->getMessage();
    }
}