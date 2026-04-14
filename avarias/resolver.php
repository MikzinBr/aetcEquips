<?php
require_once '../config.php';
require_once '../inc/helpers.php';
require_once DBAPI;
session_start();

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

if (strcasecmp((string)($_SESSION['usuario_tipo'] ?? ''), 'Professor') === 0) {
  header("Location: index.php?erro=Você não tem permissão para resolver avarias");
  exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
  header("location: index.php?erro=Erro ao encontrar avaria");
  exit;
}

$conn = open_database();
ensure_profile_schema($conn);

/* 1) Marca a avaria como resolvida */
$stmt = $conn->prepare("UPDATE avarias SET resolvido = 1 WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close(); // <-- importante

/* 2) Verifica se ainda existem avarias não resolvidas do mesmo equipamento */
$stmt = $conn->prepare("
  SELECT 1
  FROM avarias
  WHERE equipamento_id = (SELECT equipamento_id FROM avarias WHERE id = ?)
    AND resolvido = 0
  LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();          // aqui agora fica OK
$tem_pendentes = ($result && $result->num_rows > 0);

if ($result) $result->free();           // <-- libera
$stmt->close();                         // <-- fecha

/* 3) Se não tem pendentes, atualiza status do equipamento */
if (!$tem_pendentes) {
  $stmt = $conn->prepare("
    UPDATE equipamentos e
    SET status = 'ok'
    WHERE e.id = (SELECT equipamento_id FROM avarias WHERE id = ?)
      AND NOT EXISTS (
        SELECT 1 FROM avarias
        WHERE equipamento_id = e.id AND resolvido = 0
      )
  ");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();
}

log_user_activity($conn, (int)$_SESSION['usuario_id'], 'avaria_resolvida', 'Marcou a avaria #' . $id . ' como resolvida.');

$conn->close();

header("Location: index.php?msg=Avaria resolvida");
exit;
