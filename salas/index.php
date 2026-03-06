<?php
require_once '../config.php';
require_once '../inc/helpers.php';
require_once DBAPI;

$page_title = 'Salas';
$page_subtitle = 'Espaços com equipamentos agrupados';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

$erro = $_GET['erro'] ?? '';
$msg = $_GET["msg"] ?? "";

$conn = open_database();
$result = $conn->query("SELECT * FROM salas ORDER BY numero_sala");
?>

<div class="container-fluid px-0">

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
    <div class="h5 mb-0">Salas</div>
    <div class="d-flex gap-2">
      <a href="index.php" class="btn btn-outline-secondary btn-sm" title="Atualizar">
        <i class="fas fa-sync-alt"></i>
      </a>
      <a href="add.php" class="btn btn-success btn-sm">
        <i class="fas fa-plus me-1"></i>
        Criar Sala
      </a>
    </div>
  </div>

  <?php if ($result->num_rows === 0) : ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body text-center py-5">
        <div class="text-muted">Nenhuma sala registada.</div>
      </div>
    </div>
  <?php else : ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-3" style="max-width: 360px;">
          <span class="text-muted"><i class="fas fa-search"></i></span>
          <input type="text" class="form-control form-control-sm" placeholder="Pesquisar salas..." data-table-filter="salasTable">
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="salasTable">
            <thead class="table-light">
              <tr>
                <th>Número</th>
                <th>Descrição</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($sala = $result->fetch_assoc()): ?>
                <tr>
                  <td class="fw-semibold">Sala <?= htmlspecialchars($sala['numero_sala']) ?></td>
                  <td><?= htmlspecialchars(limitText($sala['descricao'], 50)) ?></td>
                  <td class="text-end">
                    <div class="btn-group" role="group" aria-label="Ações">
                      <a href="../equipamentos?sala_id=<?= $sala['id'] ?>" class="btn btn-outline-info btn-sm" title="Ver equipamentos">
                        <i class="fas fa-desktop"></i>
                      </a>
                      <a href="add_equipamentos.php?sala_id=<?= $sala['id'] ?>" class="btn btn-outline-success btn-sm" title="Adicionar equipamentos">
                        <i class="fas fa-plus"></i>
                      </a>
                      <a href="edit.php?sala_id=<?= $sala['id'] ?>" class="btn btn-outline-secondary btn-sm" title="Editar">
                        <i class="fas fa-pen"></i>
                      </a>
                      <a href="delete.php?id=<?= $sala['id'] ?>" class="btn btn-outline-danger btn-sm" title="Remover" onclick="return confirm('Remover esta sala?')">
                        <i class="fas fa-trash"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>

</div>

<?php require_once FOOTER_TEMPLATE; ?>
