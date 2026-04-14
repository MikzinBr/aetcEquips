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

$sala_id = null;
$sala_num = null;
if (!empty($_GET['sala_id'])) {
  $sala_id = intval($_GET['sala_id']);
  $sala_num = $conn->query("SELECT numero_sala FROM salas WHERE id = " . $sala_id)->fetch_assoc();
  $sql .= " WHERE e.sala_id = ?";
}

$sql .= " ORDER BY e.id DESC";

$stmt = $conn->prepare($sql);
if ($sala_id) {
  $stmt->bind_param("i", $sala_id);
}
$stmt->execute();
$result = $stmt->get_result();

$salasFilter = $conn->query("SELECT id, numero_sala FROM salas ORDER BY numero_sala");
$categorias = [];
$statuses = [];
$equipamentos = [];
$equipamentoModals = [];
$salasNovoEquipamento = $conn->query("SELECT id, numero_sala FROM salas ORDER BY numero_sala");
while ($row = $result->fetch_assoc()) {
  $equipamentos[] = $row;
  $categoria = trim((string)($row['descricao'] ?? ''));
  $status = trim((string)($row['status'] ?? ''));
  if ($categoria !== '') $categorias[$categoria] = true;
  if ($status !== '') $statuses[$status] = true;
}
ksort($categorias, SORT_NATURAL | SORT_FLAG_CASE);
ksort($statuses, SORT_NATURAL | SORT_FLAG_CASE);
?>

<div class="container-fluid px-0" style="width: 90vw;">

  <?php if ($erro) : ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <?php if ($msg) : ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <section class="page-hero m-4">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <span class="badge bg-white text-primary mb-3 px-3 py-2">Inventário</span>
        <div class="h2 mb-2"><?php if ($sala_id) : ?>Equipamentos da sala <?= htmlspecialchars($sala_num["numero_sala"] ?? '') ?><?php else : ?>Equipamentos<?php endif; ?></div>
        <p class="mb-0">Visualize, filtre e faça a gestão do inventário com uma experiência mais moderna e focada nas ações principais.</p>
      </div>
      <div class="col-lg-4">
        <div class="d-flex gap-2 justify-content-lg-end flex-wrap">
          <a href="<?= $sala_id ? 'index.php?sala_id=' . $sala_id : 'index.php' ?>" class="btn btn-light btn-sm" title="Atualizar"><i class="fas fa-sync-alt me-1"></i>Atualizar</a>
          <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#novoEquipamentoModal"><i class="fas fa-plus me-1"></i>Novo equipamento</button>
        </div>
      </div>
    </div>
  </section>

  <div class="card border-0 shadow-sm mb-3 filter-shell">
    <div class="card-body p-4">
      <div class="accordion filter-accordion" id="equipFiltersAccordion">
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#equipFiltersCollapse" aria-expanded="false">
              <span><i class="fas fa-filter me-2 text-primary"></i>Filtros avançados</span>
            </button>
          </h2>
          <div id="equipFiltersCollapse" class="accordion-collapse collapse" data-bs-parent="#equipFiltersAccordion">
            <div class="accordion-body px-0 pb-0">
              <p class="text-muted mb-3">Encontre equipamentos por estado, localização, categoria e ordem de visualização.</p>
              <form class="row g-3 align-items-end filter-grid" data-advanced-filter-form="equipTable" data-empty-state="equipamentosEmptyState" data-results-count="equipamentosCount">
        <div class="col-12 col-md-4 col-xl-3">
          <label class="form-label small text-muted">Pesquisa geral</label>
          <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control form-control-sm" placeholder="Nome, série, categoria..." data-global-search>
          </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
          <label class="form-label small text-muted">Estado</label>
          <select class="form-select form-select-sm" data-filter-key="status">
            <option value="all">Todos</option>
            <?php foreach (array_keys($statuses) as $status): ?>
              <option value="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars(ucfirst($status)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
          <label class="form-label small text-muted">Localização</label>
          <select class="form-select form-select-sm" data-filter-key="sala">
            <option value="all">Todas</option>
            <option value="sem-sala">Sem sala</option>
            <?php while ($sala = $salasFilter->fetch_assoc()): ?>
              <option value="<?= htmlspecialchars((string)$sala['numero_sala']) ?>">Sala <?= htmlspecialchars($sala['numero_sala']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <label class="form-label small text-muted">Categoria</label>
          <select class="form-select form-select-sm" data-filter-key="categoria" data-filter-mode="includes">
            <option value="all">Todas</option>
            <?php foreach (array_keys($categorias) as $categoria): ?>
              <option value="<?= htmlspecialchars($categoria) ?>"><?= htmlspecialchars(limitText($categoria, 40)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
          <label class="form-label small text-muted">Nome</label>
          <select class="form-select form-select-sm" data-sort-key="nome" data-sort-type="text">
            <option value="default" selected>Padrão</option>
            <option value="asc">A-Z</option>
            <option value="desc">Z-A</option>
          </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
          <label class="form-label small text-muted">Ordem</label>
          <select class="form-select form-select-sm" data-sort-key="id" data-sort-type="number">
            <option value="desc" selected>Mais recentes</option>
            <option value="asc">Mais antigos</option>
            <option value="default">Ignorar</option>
          </select>
        </div>
        <div class="col-12 col-xl-1 filter-actions d-grid">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-filter-reset>Limpar</button>
        </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm table-card-shell">
    <div class="card-body p-3 p-lg-4">
      <div class="table-panel-header mb-3">
        <div>
          <div class="table-panel-title">Lista de equipamentos</div>
          <div class="table-panel-subtitle"><span id="equipamentosCount"><?= count($equipamentos) ?></span> equipamento(s) encontrado(s)</div>
        </div>
        <div class="table-panel-hint">Use vários filtros ao mesmo tempo para encontrar mais rápido.</div>
      </div>

      <div class="table-responsive table-modern-wrap">
        <table class="table table-hover align-middle mb-0 table-modern-like" id="equipTable">
          <thead>
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
            <?php foreach ($equipamentos as $e): ?>
              <?php
              $status = strtolower(trim((string)($e['status'] ?? '')));
              $badge = ($status === 'ok' || $status === 'ativo') ? 'success' : 'danger';
              $label = $status ? ucfirst($status) : '—';
              $numeroSala = $e['numero_sala'] ?? '';
              $categoria = trim((string)($e['descricao'] ?? ''));
              ?>
              <tr
                data-id="<?= (int)$e['id'] ?>"
                data-nome="<?= htmlspecialchars(mb_strtolower($e['nome'] ?? '')) ?>"
                data-categoria="<?= htmlspecialchars(mb_strtolower($categoria)) ?>"
                data-status="<?= htmlspecialchars($status) ?>"
                data-sala="<?= htmlspecialchars($numeroSala !== null && $numeroSala !== '' ? (string)$numeroSala : 'sem-sala') ?>"
                data-search="<?= htmlspecialchars(mb_strtolower(trim(($e['nome'] ?? '') . ' ' . ($e['codigo'] ?? '') . ' ' . $categoria . ' ' . ($numeroSala ? 'sala ' . $numeroSala : 'sem sala') . ' ' . $status))) ?>"
              >
                <td>
                  <div class="table-main-cell">
                    <span class="table-main-icon"><i class="fas fa-desktop"></i></span>
                    <div class="table-main-stack">
                      <div class="table-main-title"><?= limitText(htmlspecialchars($e['nome']), 28) ?></div>

                    </div>
                  </div>
                </td>
                <td><?= htmlspecialchars($e['codigo'] ?? '—') ?></td>
                <td><?= limitText(htmlspecialchars($categoria ?: '—'), 24) ?></td>
                <td><?= htmlspecialchars($numeroSala ? ('sala ' . $numeroSala) : '—') ?></td>
                <td>
                  <span class="badge rounded-pill bg-<?= $badge ?>-subtle text-<?= $badge ?> border border-<?= $badge ?>-subtle">
                    <?= htmlspecialchars($label) ?>
                  </span>
                </td>
                <td class="text-end">
                  <div class="btn-group" role="group" aria-label="Ações">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="<?= $_SESSION['usuario_tipo'] == 'Direção' ? 'modal' : '' ?>" data-bs-target="#editarEquipamento-<?= $e['id'] ?>">
                      <i class="fas fa-pen"></i>
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#reportarAvaria<?= $e['id'] ?>">
                      <i class="fas fa-exclamation-triangle"></i>
                    </button>
                    <?php if ($_SESSION['usuario_tipo'] == 'Direção') : ?>
                      <a href="delete.php?id=<?= $e['id'] ?>" class="btn btn-outline-danger btn-sm" title="Remover" onclick="return confirm('Deseja realmente remover o equipamento?')">
                        <i class="fas fa-trash"></i>
                      </a>
                    <?php else : ?>
                      <button class="btn btn-outline-secondary btn-sm disabled">
                        <i class="fas fa-trash"></i>
                      </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>

              <?php $salas = $conn->query("SELECT id, numero_sala FROM salas ORDER BY numero_sala"); ?>
              <?php
              ob_start();
              ?>
<div class="modal fade" id="editarEquipamento-<?= $e['id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h1 class="modal-title fs-5">Editar equipamento</h1>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form method="POST" class="mt-3" action="edit.php?id=<?= $e['id'] ?>">
                        <div class="mb-3">
                          <label>Nome</label>
                          <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($e['nome']) ?>" required>
                        </div>
                        <div class="mb-3">
                          <label>Sala</label>
                          <select name="sala_id" class="form-select">
                            <option value="">Sem sala</option>
                            <?php while ($s = $salas->fetch_assoc()): ?>
                              <option value="<?= $s['id'] ?>" <?= $e['sala_id'] == $s['id'] ? 'selected' : '' ?>>Sala <?= htmlspecialchars($s['numero_sala']) ?></option>
                            <?php endwhile; ?>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label>Código</label>
                          <input type="text" name="codigo" class="form-control" value="<?= htmlspecialchars($e['codigo']) ?>" required>
                        </div>
                        <div class="mb-3">
                          <label>Status</label>
                          <select name="status" class="form-select">
                            <option value="ok" <?= $e['status'] == 'ok' ? 'selected' : '' ?>>OK</option>
                            <option value="avariado" <?= $e['status'] == 'avariado' ? 'selected' : '' ?>>Avariado</option>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label>Descrição</label>
                          <textarea name="descricao" class="form-control" style="max-height: 20vh"><?= htmlspecialchars($e['descricao']) ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                      <button class="btn btn-success" onclick="return confirm('Deseja realmente salvar as alterações?')">Salvar Alterações</button>
                    </div>
                    </form>
                  </div>
                </div>
              </div>

              <div class="modal fade" id="reportarAvaria<?= $e['id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h1 class="modal-title fs-5">Reportar avaria</h1>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form method="POST" action="../avarias/reportar.php">
                        <div class="mb-3">
                          <label>Equipamento</label>
                          <select name="equipamento_id" class="form-select" required>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nome']) ?></option>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label>Descrição do Problema</label>
                          <textarea name="descricao" class="form-control" style="max-height: 50vh;" required></textarea>
                        </div>
                        <div class="mb-3">
                          <label>Severidade</label>
                          <select name="severidade" class="form-select" required>
                            <option value="Baixa">Baixa</option>
                            <option value="Média">Média</option>
                            <option value="Crítica">Crítica</option>
                          </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" class="btn btn-success">Reportar</button>
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                    </form>
                  </div>
                </div>
              </div>
            
              <?php
              $equipamentoModals[] = ob_get_clean();
              ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div id="equipamentosEmptyState" class="text-muted small mt-3 d-none">Nenhum equipamento encontrado com os filtros selecionados.</div>

      <?php if (count($equipamentos) === 0): ?>
        <div class="text-muted small mt-3">Nenhum equipamento encontrado.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if (!empty($equipamentoModals)) echo implode("\n", $equipamentoModals); ?>


<div class="modal fade" id="novoEquipamentoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5">Novo equipamento</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="add.php" method="POST">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label">Nome</label>
              <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Código</label>
              <input type="text" name="codigo" class="form-control">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Sala</label>
              <select name="sala_id" class="form-select">
                <?php if (!$sala_id): ?>
                  <option value="">Sem sala</option>
                <?php endif; ?>
                <?php while ($salaNovo = $salasNovoEquipamento->fetch_assoc()): ?>
                  <option value="<?= (int)$salaNovo['id'] ?>" <?= $sala_id && (int)$salaNovo['id'] === (int)$sala_id ? 'selected' : '' ?>>Sala <?= htmlspecialchars($salaNovo['numero_sala']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Quantidade</label>
              <input type="number" name="quantidade" class="form-control" value="1" min="1" required>
            </div>
            <div class="col-12">
              <label class="form-label">Descrição</label>
              <textarea name="descricao" class="form-control" rows="4"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once FOOTER_TEMPLATE; ?>
