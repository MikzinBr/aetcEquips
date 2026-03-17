<?php
require_once 'config.php';
require_once DBAPI;
session_start();

$conn = open_database();

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

$stmt = $conn->prepare(
  "SELECT id, nome, senha, tipo FROM usuarios WHERE email = ?"
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
} else {

  $_SESSION['usuario_id']   = $user['id'];
  $_SESSION['usuario_nome'] = $user['nome'];
  $_SESSION['usuario_tipo'] = $user['tipo'];

  header("Location: dashboard.php");
  exit;
}

