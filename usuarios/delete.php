<?php
require_once '../config.php';
require_once DBAPI;
session_start();

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

if ($_SESSION['usuario_tipo'] != 'Direção') {
  header('Location: index.php?erro=Você não tem permissão para remover um usuario');
  exit;
}

if (!isset($_GET['id'])) {
  header('Location: index.php');
  exit;
}

$id_usuario = intval($_GET['id']);

$conn = open_database();

$stmt = $conn->prepare(
  "DELETE FROM usuarios WHERE id = ?"
);

$stmt->bind_param("i", $id_usuario);
$stmt->execute();

header("Location: index.php?msg=Usuario removido com sucesso");
exit;
