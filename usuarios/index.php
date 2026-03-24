<?php
require_once '../config.php';
require_once '../inc/helpers.php';
require_once DBAPI;

$page_title = 'Usuários';
$page_subtitle = 'Gestão de contas';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

if (($_SESSION['usuario_tipo'] ?? '') !== 'Direção') {
  header("Location: ../dashboard.php");
  exit;
}

$erro = $_GET['erro'] ?? '';
$msg  = $_GET['msg'] ?? '';

$conn = open_database();
$stmt = $conn->prepare("SELECT id, nome, email, tipo FROM usuarios ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container-fluid px-0" style="width: 90vw">

  <?php if ($erro) : ?>
    <div class="alert alert-danger">
      <?= htmlspecialchars($erro) ?>
    </div>
  <?php endif; ?>

  <?php if ($msg) : ?>
    <div class="alert alert-success">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <div class="d-flex align-items-center justify-content-between mb-3">
    <div class="h5 mb-0">Usuários</div>
    <div class="d-flex gap-2">
      <a href="index.php" class="btn btn-outline-secondary btn-sm" title="Atualizar">
        <i class="fas fa-sync-alt"></i>
      </a>
      <a href="signup.php" class="btn btn-success btn-sm">
        <i class="fas fa-plus me-1"></i>
        Novo usuário
      </a>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <div class="d-flex align-items-center gap-2 mb-3" style="max-width: 360px;">
        <span class="text-muted"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control form-control-sm" placeholder="Pesquisar usuários..." data-table-filter="usersTable">
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="usersTable">
          <thead class="table-light">
            <tr>
              <th>Nome</th>
              <th>Email</th>
              <th>Cargo</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($u = $result->fetch_assoc()): ?>
              <tr>
                <td class="fw-semibold"><?= limitText(htmlspecialchars($u['nome']), 32) ?></td>
                <td><?= limitText(htmlspecialchars($u['email']), 40) ?></td>
                <td>
                  <?php
                  $tipo = (string)($u['tipo'] ?? '');
                  $badge = ($tipo === 'Direção') ? 'primary' : (($tipo === 'Técnico') ? 'warning' : 'secondary');
                  ?>
                  <span class="badge rounded-pill bg-<?= $badge ?>-subtle text-<?= $badge ?> border border-<?= $badge ?>-subtle">
                    <?= htmlspecialchars($tipo ?: '—') ?>
                  </span>
                </td>
                <td class="text-end">
                  <div class="btn-group" role="group" aria-label="Ações">
                    <a href="edit.php?id=<?= (int)$u['id'] ?>" class="btn btn-outline-secondary btn-sm" title="Editar">
                      <i class="fas fa-pen"></i>
                    </a>
                    <?php if ($_SESSION['usuario_id'] != $u['id']) : ?>
                      <a href="delete.php?usuario_id=<?= $u['id'] ?>" class="btn btn-outline-danger btn-sm" title="Remover" onclick="return confirm('Deseja realmente remover o usuário?')">
                        <i class="fas fa-trash"></i>
                      </a>
                    <?php else : ?>
                      <button class="btn btn-outline-secondary disabled btn-sm">
                        <i class="fas fa-trash"></i>
                      </button>
                    <?php endif; ?>

                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <?php if ($result->num_rows === 0): ?>
        <div class="text-muted small mt-3">Nenhum usuário encontrado.</div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php require_once FOOTER_TEMPLATE; ?>
