<?php
require_once '../config.php';
require_once '../inc/helpers.php';
require_once DBAPI;

$page_title = 'Equipamentos';
$page_subtitle = 'Inventário de equipamentos';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../index.php");
  exit;
}

$erro = $_GET["erro"] ?? "";
$msg = $_GET["msg"] ?? "";

$conn = open_database();

$sql = "
  SELECT e.*, s.numero_sala
  FROM equipamentos e
  LEFT JOIN salas s ON e.sala_id = s.id
";

if (!empty($_GET['sala_id'])) {
  $sala_id = intval($_GET['sala_id']);
  $sala_num = $conn->query("SELECT numero_sala FROM salas WHERE id = " . $sala_id)->fetch_assoc();
  $sql .= " WHERE e.sala_id = ?";
}

$sql .= " ORDER BY e.id";

$stmt = $conn->prepare($sql);

if (!empty($sala_id)) {
  $stmt->bind_param("i", $sala_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container-fluid px-0" style="width: 90vw;">

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
      <?php if (isset($sala_id)) : ?>
        <div class="h5 mb-0">Equipamentos <?= htmlspecialchars($sala_num["numero_sala"]) ?></div>
        <div class="d-flex gap-2">
          <a href="index.php?sala_id=<?= $sala_id ?>" class="btn btn-outline-secondary btn-sm" title="Atualizar">
            <i class="fas fa-sync-alt"></i>
          </a>
          <a href="add.php?sala_id=<?= $sala_id ?>" class="btn btn-success btn-sm">
            <i class="fas fa-plus me-1"></i>
            Novo equipamento
          </a>
        </div>
      <?php else : ?>
        <div class="h5 mb-0">Equipamentos</div>
        <div class="d-flex gap-2">
          <a href="index.php" class="btn btn-outline-secondary btn-sm" title="Atualizar">
            <i class="fas fa-sync-alt"></i>
          </a>
          <a href="add.php" class="btn btn-success btn-sm">
            <i class="fas fa-plus me-1"></i>
            Novo equipamento
          </a>
        </div>
      <?php endif; ?>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <div class="d-flex align-items-center gap-2 mb-3" style="max-width: 360px;">
        <span class="text-muted"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control form-control-sm" placeholder="Pesquisar equipamentos..." data-table-filter="equipTable">
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="equipTable">
          <thead class="table-light">
            <tr>
              <th>Nome</th>
              <th>Nº Série</th>
              <th>Categoria</th>
              <th>Localização</th>
              <th>Estado</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($e = $result->fetch_assoc()): ?>
              <tr>
                <td class="fw-semibold"><?= limitText(htmlspecialchars($e['nome']), 28) ?></td>
                <td><?= htmlspecialchars($e['codigo'] ?? '—') ?></td>
                <td><?= limitText(htmlspecialchars($e['descricao'] ?? '—'), 24) ?></td>
                <td><?= htmlspecialchars($e['numero_sala'] ? ('sala ' . $e['numero_sala']) : '—') ?></td>
                <td>
                  <?php
                  $status = strtolower($e['status'] ?? '');
                  $badge = ($status === 'ok' || $status === 'ativo') ? 'success' : 'danger';
                  $label = $status ? ucfirst($status) : '—';
                  ?>
                  <span class="badge rounded-pill bg-<?= $badge ?>-subtle text-<?= $badge ?> border border-<?= $badge ?>-subtle">
                    <?= htmlspecialchars($label) ?>
                  </span>
                </td>
                <td class="text-end">
                  <div class="btn-group" role="group" aria-label="Ações">
                    <a href="edit.php?id=<?= $e['id'] ?>" class="btn btn-outline-secondary btn-sm" title="Editar">
                      <i class="fas fa-pen"></i>
                    </a>
                    <a href="../avarias/reportar.php?id=<?= $e['id'] ?>" class="btn btn-outline-warning btn-sm" title="Reportar avaria">
                      <i class="fas fa-exclamation-triangle"></i>
                    </a>
                    <a href="delete.php?id=<?= $e['id'] ?>" class="btn btn-outline-danger btn-sm" title="Remover" onclick="return confirm('Deseja realmente remover o equipamento?')">
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

</div>

<?php require_once FOOTER_TEMPLATE; ?>
