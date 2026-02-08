<?php
require_once '../config.php';
require_once DBAPI;
session_start();

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../login.php");
  exit;
}

if ($_SESSION['usuario_tipo'] === 'professor') {
  header("Location: index.php?erro=Você não tem permissão para refazer avarias");
  exit;
}

$id = intval($_GET['id']) ?? "";

if (!$id) {
  header("location: index.php?erro=Erro ao encontrar avaria");
  exit;
}

$conn = open_database();

$stmt = $conn->prepare("UPDATE avarias SET resolvido = 0 WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$stmt = $conn->prepare("
  UPDATE equipamentos e
  SET status = 'avariado'
  WHERE e.id = (
    SELECT equipamento_id FROM avarias WHERE id = ?
  )
  AND NOT EXISTS (
    SELECT 1 FROM avarias
    WHERE equipamento_id = e.id AND resolvido = 0
  )
");

$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: index.php?msg=Avaria refeita");
exit;
