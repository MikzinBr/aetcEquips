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
                    <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#detalhesAvaria-<?= $a['id'] ?>">
                      <i class="fas fa-info-circle"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm <?= ($_SESSION['usuario_tipo'] == 'Direção' || $a['usuario_id'] == $_SESSION['usuario_id']) ? '' : 'disabled' ?>" data-bs-toggle="modal" data-bs-target="#editarAvaria-<?= $a['id'] ?>">
                      <i class="fas fa-pen"></i>
                    </button>
                    <a href="delete.php?id=<?= $a['id'] ?>" class="btn btn-outline-danger btn-sm <?= ($_SESSION['usuario_tipo'] == 'Direção' || $a['usuario_id'] == $_SESSION['usuario_id']) ? '' : 'disabled' ?>" title="Remover" onclick="return confirm('Deseja realmente remover esta avaria?')">
                      <i class="fas fa-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>

              <!-- Modal edição de avaria -->
              <?php

              $equipamentos = $conn->query("SELECT id, nome FROM equipamentos");

              ?>
              <div class="modal fade" id="editarAvaria-<?= $a['id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h1 class="modal-title fs-5" id="staticBackdropLabel">Editar avaria</h1>
                    </div>
                    <div class="modal-body">
                      <form method="POST" class="mt-3" action="edit.php?id=<?= $a['id'] ?>">

                        <div class="mb-3">
                          <label>Equipamento</label>
                          <select name="equipamento_id" class="form-select">
                            <?php while ($e = $equipamentos->fetch_assoc()): ?>
                              <option value="<?= $e['id'] ?>"
                                <?= $a['equipamento_id'] == $e['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e['nome']) ?>
                              </option>
                            <?php endwhile; ?>
                          </select>
                        </div>

                        <div class="mb-3">
                          <label>Descrição</label>
                          <textarea name="descricao" class="form-control" style="max-height: 50vh;"><?= htmlspecialchars($a['descricao']) ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" class="btn btn-success" onclick="return confirm('Deseja realmente salvar as alterações?')">Salvar</button>
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">cancelar</button>
                    </div>
                    </form>
                  </div>
                </div>
              </div>

              <!-- Modal detalhes da avaria -->

              <?php

              $equipamento = $conn->query("SELECT id, nome FROM equipamentos WHERE id = " . $a['equipamento_id'])->fetch_assoc();

              ?>

              <div class="modal fade" id="detalhesAvaria-<?= $a['id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h1 class="modal-title fs-5" id="staticBackdropLabel">Detalhes</h1>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label>Equipamento</label>
                        <input type="text" class="form-control" value="<?= $equipamento['nome'] ?>" disabled>
                      </div>

                      <div class="mb-3">
                        <label>Descrição</label>
                        <textarea name="descricao" class="form-control" disabled><?= htmlspecialchars($a['descricao']) ?></textarea>
                      </div>
                    </div>
                  </div>
                </div>

              <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<?php require_once FOOTER_TEMPLATE; ?>
