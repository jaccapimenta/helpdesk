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

$sql = "SELECT t.*, u.name AS user_name, s.description AS status_name,
               assigned.name AS assigned_tech_name
        FROM tickets t
        LEFT JOIN users u ON t.users_id = u.id
        LEFT JOIN status s ON t.status_id = s.id
        LEFT JOIN tech_support assigned ON t.tech_support_id = assigned.id
        WHERE t.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $tickets_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    die("Chamado nao encontrado.");
}

$status = $pdo->query("SELECT * FROM status WHERE id IN (2, 4) ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT fh.description, fh.Date, fh.users_id,
               ts.name AS tech_name, u.name AS user_name,
               s.description AS status_name
        FROM feedback_histories fh
        LEFT JOIN tech_support ts ON fh.tech_support_id = ts.id
        LEFT JOIN users u ON fh.users_id = u.id
        LEFT JOIN status s ON fh.status_id = s.id
        WHERE fh.tickets_id = :tickets_id
        ORDER BY fh.Date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':tickets_id' => $tickets_id]);
$historicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$technicians = $pdo->prepare('SELECT id, name FROM tech_support WHERE id <> :current_id ORDER BY name');
$technicians->execute([':current_id' => (int) $_SESSION['usuario_id']]);
$technicians = $technicians->fetchAll(PDO::FETCH_ASSOC);
$can_register_history = in_array((int) $ticket['status_id'], [2, 5], true)
    && (int) $ticket['tech_support_id'] === (int) $_SESSION['usuario_id'];
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
    <main class="main-content detail-content">
        <nav class="detail-nav-actions" aria-label="Acoes da pagina">
            <a href="painel-tecnico.php" class="back-dashboard">Voltar ao Dashboard</a>
            <a href="logout.php" class="detail-logout">Sair</a>
        </nav>
        <header class="ticket-page-header">
            <h1>Chamado #<?php echo (int) $ticket['id']; ?></h1>
            <div class="ticket-status-highlight">
                <span>Status do chamado</span>
                <strong class="detail-status detail-status-<?php echo (int) $ticket['status_id']; ?>">
                    <?php echo htmlspecialchars($ticket['status_name']); ?>
                </strong>
            </div>
        </header>

        <div class="ticket-detail-layout">
        <section class="tickets ticket-overview">
            <div class="section-title overview-title">
                <div>
                    <span>Resumo do atendimento</span>
                    <h2>Detalhes do ticket</h2>
                </div>
            </div>
            <dl class="ticket-facts">
                <div>
                    <dt>Solicitante</dt>
                    <dd><?php echo htmlspecialchars($ticket['user_name'] ?? 'Aluno'); ?></dd>
                </div>
                <div>
                    <dt>Categoria</dt>
                    <dd><?php echo htmlspecialchars($ticket['tickets_type']); ?></dd>
                </div>
                <div>
                    <dt>Responsavel</dt>
                    <dd><?php echo htmlspecialchars($ticket['assigned_tech_name'] ?? 'Nao atribuido'); ?></dd>
                </div>
                <div>
                    <dt>Data de abertura</dt>
                    <dd><?php echo date('d/m/Y H:i', strtotime($ticket['openningdate'])); ?></dd>
                </div>
            </dl>
            <?php if (!empty($ticket['satisfaction_level'])): ?>
                <p class="overview-satisfaction"><span>Satisfacao do usuario</span><strong><?php echo (int) $ticket['satisfaction_level']; ?>/5</strong></p>
            <?php endif; ?>
            <div class="ticket-description-block">
                <h2>Descricao do chamado</h2>
                <p><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
            </div>

            <?php if (!empty($ticket['image']) && file_exists($ticket['image'])): ?>
                <p style="margin-top:15px;">
                    <a href="<?php echo htmlspecialchars($ticket['image']); ?>" target="_blank">Ver imagem enviada</a>
                </p>
            <?php endif; ?>
        </section>

        <section class="tickets ticket-history">
            <div class="section-title">
                <h2>Historico da interacao</h2>
            </div>

            <?php if (empty($historicos)): ?>
                <p class="empty-history">Nenhuma atualizacao registrada neste chamado.</p>
            <?php else: ?>
                <?php foreach ($historicos as $historico): ?>
                    <?php $mensagem_usuario = !empty($historico['users_id']); ?>
                    <article class="history-item">
                        <div class="history-meta">
                            <div class="history-author">
                                <span class="author-badge <?php echo $mensagem_usuario ? 'author-user' : ''; ?>">
                                    <?php echo $mensagem_usuario ? 'Usuario' : 'Tecnico'; ?>
                                </span>
                                <strong><?php echo htmlspecialchars($mensagem_usuario ? ($historico['user_name'] ?? 'Usuario') : ($historico['tech_name'] ?? 'Suporte tecnico')); ?></strong>
                            </div>
                            <span><?php echo date('d/m/Y H:i', strtotime($historico['Date'])); ?></span>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($historico['description'])); ?></p>
                        <small><?php echo htmlspecialchars($historico['status_name'] ?? ''); ?></small>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
        </div>

        <?php if ($can_register_history): ?>
        <section class="tickets history-form">
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
                                <?php echo (int) $item['id'] === 4 ? 'Chamado Encerrado' : htmlspecialchars($item['description']); ?>
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
        <?php endif; ?>

        <?php if ($can_register_history && !empty($technicians)): ?>
        <section class="tickets transfer-section" id="transferir">
            <div class="section-title">
                <h2>Transferir chamado</h2>
            </div>
            <form action="transferir_chamado.php" method="POST" class="transfer-form">
                <input type="hidden" name="tickets_id" value="<?php echo (int) $ticket['id']; ?>">
                <label for="target_tech_id">Tecnico de destino</label>
                <select id="target_tech_id" name="target_tech_id" required>
                    <option value="">Selecione um tecnico</option>
                    <?php foreach ($technicians as $technician): ?>
                        <option value="<?php echo (int) $technician['id']; ?>">
                            <?php echo htmlspecialchars($technician['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Transferir</button>
            </form>
        </section>
        <?php endif; ?>
    </main>
</body>
</html>
