<?php
require_once 'config.php';
require_once './inc/helpers.php';
require_once DBAPI;
session_start();

$conn = open_database();
ensure_profile_schema($conn);

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

$stmt = $conn->prepare(
  "SELECT id, nome, senha, tipo, foto FROM usuarios WHERE email = ?"
);

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
  header("location: index.php?erro=Email não cadastrado.");
  exit;
}

if (!password_verify($senha, $user['senha'])) {
  header("location: index.php?erro=Senha incorreta.");
  exit;
}

$_SESSION['usuario_id']   = $user['id'];
$_SESSION['usuario_nome'] = $user['nome'];
$_SESSION['usuario_tipo'] = $user['tipo'];
$_SESSION['usuario_foto'] = $user['foto'] ?? '';

$stmt = $conn->prepare('UPDATE usuarios SET ultimo_login_at = NOW() WHERE id = ?');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$stmt->close();

log_user_activity($conn, (int)$user['id'], 'login', 'Entrou no sistema.');

header("Location: dashboard.php");
exit;
