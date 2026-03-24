<?php
require_once '../config.php';
require_once DBAPI;

$page_title = 'Avarias';
$page_subtitle = 'Reportar e consultar avarias';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION["usuario_id"])) {
  header("location: ../index.php");
  exit;
}

$conn = open_database();

$erro = $_GET["erro"] ?? "";
$msg = $_GET["msg"] ?? "";

$tipo = $_SESSION['usuario_tipo'];
$usuario_id = $_SESSION['usuario_id'];

$sql = "
  SELECT a.*, e.nome AS equipamento, u.nome AS usuario
  FROM avarias a
  JOIN equipamentos e ON a.equipamento_id = e.id
  JOIN usuarios u ON a.usuario_id = u.id
";

if ($tipo === 'Professor') {
  $sql .= " WHERE a.usuario_id = $usuario_id";
}

$sql .= " ORDER BY a.data_registro DESC";

$result = $conn->query($sql);
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
    <div class="h5 mb-0">Avarias</div>
    <div class="d-flex gap-2">
      <a href="index.php" class="btn btn-outline-secondary btn-sm" title="Atualizar">
        <i class="fas fa-sync-alt"></i>
      </a>
      <a href="reportar.php" class="btn btn-success btn-sm">
        <i class="fas fa-plus me-1"></i>
        Reportar Avaria
      </a>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <div class="d-flex align-items-center gap-2 mb-3" style="max-width: 360px;">
        <span class="text-muted"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control form-control-sm" placeholder="Pesquisar avarias..." data-table-filter="avariasTable">
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="avariasTable">
          <thead class="table-light">
            <tr>
              <th>Título</th>
              <th>Equipamento</th>
              <th>Severidade</th>
              <th>Estado</th>
              <th>Data</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody>

            <?php while ($a = $result->fetch_assoc()): ?>
              <?php
              $resolvida = (bool)($a['resolvido'] ?? 0);
              $sev = strtolower($a['severidade'] ?? '');
              $sevBadge = $sev === 'critica' || $sev === 'crítica' ? 'danger' : ($sev === 'media' || $sev === 'média' ? 'warning' : 'secondary');
              ?>
              <tr>
                <td class="fw-semibold"><?= htmlspecialchars($a['titulo'] ?? '—') ?></td>
                <td><?= htmlspecialchars($a['equipamento']) ?></td>
                <td>
                  <span class="badge rounded-pill bg-<?= $sevBadge ?>-subtle text-<?= $sevBadge ?> border border-<?= $sevBadge ?>-subtle">
                    <?= htmlspecialchars($a['severidade'] ?? '—') ?>
                  </span>
                </td>
                <td>
                  <span class="badge rounded-pill bg-<?= $resolvida ? 'success' : 'danger' ?>-subtle text-<?= $resolvida ? 'success' : 'danger' ?> border border-<?= $resolvida ? 'success' : 'danger' ?>-subtle">
                    <?= $resolvida ? 'Resolvida' : 'Pendente' ?>
                  </span>

                  <?php if ($_SESSION['usuario_tipo'] != 'Professor') : ?>
                    <a href="<?= $resolvida ? 'reavariar' : 'resolver' ?>.php?id=<?= $a['id'] ?>" class="btn btn-link btn-sm px-2 text-<?= $resolvida ? 'success' : 'danger' ?>" title="Alterar estado">
                      <i class="far fa-<?= $resolvida ? 'check-square' : 'square' ?>"></i>
                    </a>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($a['data_registro'] ?? '—') ?></td>
                <td class="text-end">
                  <div class="btn-group" role="group" aria-label="Ações">
                    <a href="detalhes.php?id=<?= $a['id'] ?>" class="btn btn-outline-info btn-sm" title="Detalhes">
                      <i class="fas fa-info-circle"></i>
                    </a>
                    <a href="edit.php?id=<?= $a['id'] ?>" class="btn btn-outline-secondary btn-sm <?= ($_SESSION['usuario_tipo'] == 'Direção' || $a['usuario_id'] == $_SESSION['usuario_id']) ? '' : 'disabled' ?>" title="Editar">
                      <i class="fas fa-pen"></i>
                    </a>
                    <a href="delete.php?id=<?= $a['id'] ?>" class="btn btn-outline-danger btn-sm <?= ($_SESSION['usuario_tipo'] == 'Direção' || $a['usuario_id'] == $_SESSION['usuario_id']) ? '' : 'disabled' ?>" title="Remover" onclick="return confirm('Deseja realmente remover esta avaria?')">
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
