<?php
require_once '../config.php';
require_once DBAPI;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

if ($_SESSION['usuario_tipo'] == 'Técnico') {
  header('Location: index.php?erro=Você não tem permissão para remover um equipamento');
  exit;
}

$conn = open_database();

if (!isset($_GET['id'])) {
  header("Location: index.php");
  exit;
}

$id = intval($_GET["id"]);

$stmt = $conn->prepare("DELETE FROM avarias WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("location: index.php?msg=Avaria removida com sucesso");
