<?php
require_once '../config.php';
require_once '../inc/helpers.php';
require_once DBAPI;
session_start();

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'Direção') {
  header('Location: index.php?erro=Você não tem permissão para criar utilizadores.');
  exit;
}

$conn = open_database();
ensure_profile_schema($conn);

$senha_confirmacao = $_POST['senha_confirmacao'] ?? '';
$stmt = $conn->prepare('SELECT senha FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $_SESSION['usuario_id']);
$stmt->execute();
$senha_direcao = $stmt->get_result()->fetch_array()[0] ?? '';
$stmt->close();

if (!password_verify($senha_confirmacao, $senha_direcao)) {
  header("location: signup.php?erro=Senha de confirmação incorreta");
  exit;
}

$nome      = trim($_POST['nome'] ?? '');
$email     = trim($_POST['email'] ?? '');
$telefone  = trim($_POST['telefone'] ?? '');
$bio       = trim($_POST['bio'] ?? '');
$senha     = $_POST['senha'] ?? '';
$confirmar = $_POST['confirmar_senha'] ?? '';
$tipo      = $_POST['tipo'] ?? '';
$tiposPermitidos = ['Professor', 'Técnico', 'Direção'];

if (!$nome || !$email || !$senha || !$confirmar || !in_array($tipo, $tiposPermitidos, true)) {
  header("Location: signup.php?erro=Preencha todos os campos obrigatórios.");
  exit;
}

if ($senha !== $confirmar) {
  header("Location: signup.php?erro=As senhas não coincidem.");
  exit;
}

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  header("Location: signup.php?erro=Este email já está cadastrado.");
  exit;
}
$stmt->close();

$fotoPath = null;
try {
  if (isset($_FILES['foto'])) {
    $fotoPath = save_profile_photo($_FILES['foto'], $_POST['cropped_foto_data'] ?? '');
  }
} catch (RuntimeException $e) {
  header('Location: signup.php?erro=' . urlencode($e->getMessage()));
  exit;
}

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
  "INSERT INTO usuarios (nome, email, senha, tipo, foto, bio, telefone)
   VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("sssssss", $nome, $email, $senha_hash, $tipo, $fotoPath, $bio, $telefone);
$stmt->execute();
$novoUsuarioId = $stmt->insert_id;
$stmt->close();

log_user_activity($conn, (int)$novoUsuarioId, 'perfil_criado', 'Perfil criado e conta registada no sistema.');
log_user_activity($conn, (int)$_SESSION['usuario_id'], 'gestao_utilizadores', 'Criou o perfil de ' . $nome . '.');

header("Location: index.php?msg=Perfil criado com sucesso");
exit;
