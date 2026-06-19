<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'tecnico') {
    header("Location: login.html");
    exit();
}

$tickets_id = $_GET['id'] ?? null;

if (!$tickets_id) {
    die("Chamado nao informado.");
}

$sql = "SELECT t.*, u.name AS user_name, s.description AS status_name
        FROM tickets t
        LEFT JOIN users u ON t.users_id = u.id
        LEFT JOIN status s ON t.status_id = s.id
        WHERE t.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $tickets_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    die("Chamado nao encontrado.");
}

$status = $pdo->query("SELECT * FROM status ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Chamado</title>
    <link rel="stylesheet" href="tech.css">
</head>
<body>
    <aside class="sidebar">
        <h2>Help Desk</h2>
        <nav>
            <ul>
                <li><a href="painel-tecnico.php" style="color: white; text-decoration: none;">Dashboard</a></li>
                <li><a href="login.html" style="color: white; text-decoration: none;">Sair</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header>
            <h1>Chamado #<?php echo (int) $ticket['id']; ?></h1>
            <p><?php echo htmlspecialchars($ticket['status_name']); ?></p>
        </header>

        <section class="tickets">
            <p><strong>Aluno:</strong> <?php echo htmlspecialchars($ticket['user_name'] ?? 'Aluno'); ?></p>
            <p><strong>Tipo:</strong> <?php echo htmlspecialchars($ticket['tickets_type']); ?></p>
            <p><strong>Aberto em:</strong> <?php echo date('d/m/Y H:i', strtotime($ticket['openningdate'])); ?></p>
            <p style="margin-top:15px;"><strong>Descricao:</strong></p>
            <p><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>

            <?php if (!empty($ticket['image']) && file_exists($ticket['image'])): ?>
                <p style="margin-top:15px;">
                    <a href="<?php echo htmlspecialchars($ticket['image']); ?>" target="_blank">Ver imagem enviada</a>
                </p>
            <?php endif; ?>
        </section>

        <section class="tickets" style="margin-top:20px;">
            <div class="section-title">
                <h2>Registrar historico</h2>
            </div>

            <form action="salvar_historico.php" method="POST">
                <input type="hidden" name="tickets_id" value="<?php echo (int) $ticket['id']; ?>">

                <p>
                    <label for="status_id">Status</label><br>
                    <select id="status_id" name="status_id" required style="width:100%; padding:10px; margin-top:6px;">
                        <?php foreach ($status as $item): ?>
                            <option value="<?php echo (int) $item['id']; ?>" <?php echo ((int) $item['id'] === (int) $ticket['status_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($item['description']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p style="margin-top:15px;">
                    <label for="description">Descricao</label><br>
                    <textarea id="description" name="description" rows="5" required style="width:100%; padding:10px; margin-top:6px;"></textarea>
                </p>

                <button type="submit" style="margin-top:15px;">Salvar historico</button>
            </form>
        </section>
    </main>
</body>
</html>
