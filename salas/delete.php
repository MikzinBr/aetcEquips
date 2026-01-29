<?php
require_once '../config.php';
require_once DBAPI;
session_start();

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../login.php");
  exit;
}

if ($_SESSION['usuario_tipo'] != 'Direção') {
  header('Location: index.php?erro=Você não tem permissão para remover uma sala');
  exit;
}

if (!isset($_GET['id'])) {
  header('Location: index.php');
  exit;
}

$id_sala = intval($_GET['id']);

$conn = open_database();

$stmt = $conn->prepare(
  "DELETE FROM salas WHERE id = ?"
);

$stmt->bind_param("i", $id_sala);
$stmt->execute();

header("Location: index.php?msg=Sala removida com sucesso");
exit;
