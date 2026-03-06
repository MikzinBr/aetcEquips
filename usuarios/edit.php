<?php
require_once '../config.php';
require_once '../inc/helpers.php';
require_once DBAPI;

$page_title = 'Editar usuário';
$page_subtitle = 'Atualizar dados do usuário';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

if (($_SESSION['usuario_tipo'] ?? '') !== 'Direção') {
  header('Location: index.php?erro=Você não tem permissão para editar usuários');
  exit;
}

$tipos = ['Direção', 'Técnico', 'Professor'];
$conn = open_database();

if (!isset($_GET['id'])) {
  header('Location: index.php');
  exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare('SELECT id, nome, email, tipo FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
  header('Location: index.php?erro=Usuário não encontrado');
  exit;
}

$usuario = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nome  = trim($_POST['nome'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $tipo  = $_POST['tipo'] ?? '';

  if (!$nome || !$email || !in_array($tipo, $tipos, true)) {
    header('Location: edit.php?id=' . $id . '&erro=Preencha todos os campos corretamente');
    exit;
  }

  $stmt = $conn->prepare('UPDATE usuarios SET nome = ?, email = ?, tipo = ? WHERE id = ?');
  $stmt->bind_param('sssi', $nome, $email, $tipo, $id);
  $stmt->execute();

  header('Location: index.php?msg=Usuário editado com sucesso');
  exit;
}

$erro = $_GET['erro'] ?? '';
?>

<div class="container-fluid px-0">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div class="h5 mb-0">Editar usuário</div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i>
      Voltar
    </a>
  </div>

  <?php if ($erro): ?>
    <div class="alert alert-danger">
      <?= htmlspecialchars($erro) ?>
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <div class="col-12 col-lg-7 col-xl-6">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Tipo</label>
              <select name="tipo" class="form-select" required>
                <?php foreach ($tipos as $t): ?>
                  <option value="<?= htmlspecialchars($t) ?>" <?= ($usuario['tipo'] === $t) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-success" onclick="return confirm('Deseja realmente salvar as alterações?')">
                <i class="fas fa-save me-1"></i>
                Salvar
              </button>
              <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5 col-xl-6">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="fw-semibold mb-2"><i class="fas fa-info-circle me-2 text-muted"></i>Dica</div>
          <div class="text-muted small">
            Se você mudar o tipo para <strong>Direção</strong>, o usuário passa a conseguir acessar a área de gestão de usuários.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once FOOTER_TEMPLATE; ?>
