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
$stmt->close();

$stmt = $conn->prepare("
  UPDATE equipamentos e
  JOIN avarias a_target ON a_target.equipamento_id = e.id
  SET e.status = 'avariado'
  WHERE a_target.id = ?
    AND EXISTS (
      SELECT 1
      FROM avarias a_open
      WHERE a_open.equipamento_id = e.id
        AND a_open.resolvido = 0
    )
");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: index.php?msg=Avaria refeita");
exit;
