<?php
require_once '../config.php';
require_once '../inc/helpers.php';
require_once DBAPI;
session_start();

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

if (($_SESSION['usuario_tipo'] ?? '') !== 'Direção') {
  header('Location: index.php?erro=Você não tem permissão para remover um utilizador');
  exit;
}

if (!isset($_GET['id'])) {
  header('Location: index.php');
  exit;
}

$id_usuario = intval($_GET['id']);
$conn = open_database();
ensure_profile_schema($conn);

$stmt = $conn->prepare('SELECT nome, foto FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$alvo = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$alvo) {
  header('Location: index.php?erro=Perfil não encontrado');
  exit;
}

if ($id_usuario === (int)$_SESSION['usuario_id']) {
  header('Location: index.php?erro=Você não pode remover o seu próprio perfil');
  exit;
}

$stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->close();

delete_profile_photo($alvo['foto'] ?? null);
log_user_activity($conn, (int)$_SESSION['usuario_id'], 'gestao_utilizadores', 'Removeu o perfil de ' . ($alvo['nome'] ?? 'utilizador') . '.');

header("Location: index.php?msg=Perfil removido com sucesso");
exit;
