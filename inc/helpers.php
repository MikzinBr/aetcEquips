<?php

function format_euro(int $centavos): string
{
  return number_format($centavos / 100, 2, ',', '.') . ' €';
}

function limitText($text, $maxLength)
{
  if (mb_strlen($text, 'UTF-8') <= $maxLength) {
    return $text;
  }
  return mb_substr($text, 0, $maxLength, 'UTF-8') . '...';
}

function ensure_profile_schema(mysqli $conn): void
{
  static $done = false;
  if ($done) {
    return;
  }

  $databaseName = DB_NAME;
  $columns = [
    'foto' => "ALTER TABLE usuarios ADD COLUMN foto VARCHAR(255) NULL AFTER tipo",
    'bio' => "ALTER TABLE usuarios ADD COLUMN bio TEXT NULL AFTER foto",
    'telefone' => "ALTER TABLE usuarios ADD COLUMN telefone VARCHAR(30) NULL AFTER bio",
    'ultimo_login_at' => "ALTER TABLE usuarios ADD COLUMN ultimo_login_at DATETIME NULL AFTER telefone",
    'criado_em' => "ALTER TABLE usuarios ADD COLUMN criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER ultimo_login_at"
  ];

  foreach ($columns as $column => $sql) {
    $stmt = $conn->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = ? LIMIT 1");
    $stmt->bind_param('ss', $databaseName, $column);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_row();
    $stmt->close();

    if (!$exists) {
      $conn->query($sql);
    }
  }

  $stmt = $conn->prepare("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'usuario_atividades' LIMIT 1");
  $stmt->bind_param('s', $databaseName);
  $stmt->execute();
  $tableExists = $stmt->get_result()->fetch_row();
  $stmt->close();

  if (!$tableExists) {
    $conn->query(
      "CREATE TABLE usuario_atividades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        acao VARCHAR(100) NOT NULL,
        descricao TEXT NOT NULL,
        criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_usuario_atividades_usuario (usuario_id),
        CONSTRAINT fk_usuario_atividades_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
  }

  $done = true;
}

function log_user_activity(mysqli $conn, int $usuarioId, string $acao, string $descricao): void
{
  ensure_profile_schema($conn);
  $stmt = $conn->prepare('INSERT INTO usuario_atividades (usuario_id, acao, descricao) VALUES (?, ?, ?)');
  $stmt->bind_param('iss', $usuarioId, $acao, $descricao);
  $stmt->execute();
  $stmt->close();
}

function get_avatar_url(?array $usuario): string
{
  $foto = trim((string)($usuario['foto'] ?? ''));
  if ($foto !== '') {
    return BASEURL . ltrim($foto, '/');
  }
  return BASEURL . 'images/profile-default.svg';
}

function ensure_profile_upload_dir(): string
{
  $dir = ABSPATH . 'uploads/profiles/';
  if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
    throw new RuntimeException('Não foi possível preparar a pasta de uploads.');
  }

  if (!is_writable($dir)) {
    @chmod($dir, 0755);
  }

  if (!is_writable($dir)) {
    throw new RuntimeException('A pasta de fotos de perfil não tem permissão de escrita.');
  }

  return $dir;
}

function save_profile_photo_data_url(string $dataUrl): ?string
{
  $dataUrl = trim($dataUrl);
  if ($dataUrl === '') {
    return null;
  }

  if (!preg_match('#^data:image/(png|jpeg|jpg|webp);base64,#i', $dataUrl)) {
    throw new RuntimeException('Formato da imagem ajustada inválido.');
  }

  $binary = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);
  if ($binary === false) {
    throw new RuntimeException('Não foi possível processar a imagem ajustada.');
  }

  if (strlen($binary) > 5 * 1024 * 1024) {
    throw new RuntimeException('A imagem ajustada ficou muito grande.');
  }

  $dir = ensure_profile_upload_dir();
  $filename = 'perfil_' . uniqid('', true) . '.jpg';
  $target = $dir . $filename;

  if (@file_put_contents($target, $binary) === false) {
    throw new RuntimeException('Não foi possível guardar a imagem ajustada.');
  }

  return 'uploads/profiles/' . $filename;
}

function save_profile_photo(array $file, ?string $croppedDataUrl = null): ?string
{
  $croppedDataUrl = trim((string)$croppedDataUrl);
  if ($croppedDataUrl !== '') {
    return save_profile_photo_data_url($croppedDataUrl);
  }

  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    return null;
  }

  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    throw new RuntimeException('Não foi possível enviar a foto.');
  }

  $allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif'
  ];

  $tmp = $file['tmp_name'] ?? '';
  $mime = is_file($tmp) ? mime_content_type($tmp) : '';
  if (!isset($allowed[$mime])) {
    throw new RuntimeException('Formato de imagem inválido. Use JPG, PNG, WEBP ou GIF.');
  }

  if (($file['size'] ?? 0) > 3 * 1024 * 1024) {
    throw new RuntimeException('A foto deve ter no máximo 3 MB.');
  }

  $dir = ensure_profile_upload_dir();
  $filename = 'perfil_' . uniqid('', true) . '.' . $allowed[$mime];
  $target = $dir . $filename;

  if (!move_uploaded_file($tmp, $target)) {
    if (!@copy($tmp, $target)) {
      throw new RuntimeException('Não foi possível guardar a foto enviada.');
    }
  }

  return 'uploads/profiles/' . $filename;
}

function delete_profile_photo(?string $relativePath): void
{
  $relativePath = trim((string)$relativePath);
  if ($relativePath === '') {
    return;
  }

  $absolutePath = ABSPATH . ltrim($relativePath, '/');
  if (is_file($absolutePath)) {
    @unlink($absolutePath);
  }
}
