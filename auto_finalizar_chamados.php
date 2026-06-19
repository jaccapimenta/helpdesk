<?php
// Encerra automaticamente chamados que aguardam confirmacao do usuario ha 2 dias.
$sql_auto = "UPDATE tickets
             SET status_id = 3,
                 conclusiondate = NOW()
             WHERE status_id = 4
               AND resolution_notice_date IS NOT NULL
               AND resolution_notice_date <= DATE_SUB(NOW(), INTERVAL 2 DAY)";
$pdo->exec($sql_auto);
?>
