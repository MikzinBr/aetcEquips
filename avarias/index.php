<?php
require_once '../config.php';
require_once DBAPI;

$page_title = 'Avarias';
$page_subtitle = 'Reportar e consultar avarias';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header('location: ../index.php');
  exit;
}

$conn = open_database();

$erro = $_GET['erro'] ?? '';
$msg = $_GET['msg'] ?? '';

$tipo = $_SESSION['usuario_tipo'];
$usuario_id = (int)$_SESSION['usuario_id'];

$sql = "
  SELECT a.*, e.nome AS equipamento, u.id AS usuario_perfil_id, u.nome AS usuario, u.tipo AS usuario_tipo, u.foto AS usuario_foto
  FROM avarias a
  JOIN equipamentos e ON a.equipamento_id = e.id
  JOIN usuarios u ON a.usuario_id = u.id
";

if ($tipo === 'Professor') {
  $sql .= " WHERE a.usuario_id = $usuario_id";
}

$sql .= ' ORDER BY a.data_registro DESC';

$result = $conn->query($sql);
$avarias = [];
$equipamentosMap = [];
$usuariosMap = [];
$equipamentosNovoRelato = $conn->query("SELECT id, nome FROM equipamentos ORDER BY nome");
while ($row = $result->fetch_assoc()) {
  $avarias[] = $row;
  $equipamentosMap[$row['equipamento']] = true;
  $usuariosMap[$row['usuario']] = true;
}
ksort($equipamentosMap, SORT_NATURAL | SORT_FLAG_CASE);
ksort($usuariosMap, SORT_NATURAL | SORT_FLAG_CASE);
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
        <span class="badge bg-white text-primary mb-3 px-3 py-2">Ocorrências</span>
        <div class="h2 mb-2">Avarias</div>
        <p class="mb-0">Centralize registos, histórico e acompanhamento de resolução num painel mais claro e profissional.</p>
      </div>
      <div class="col-lg-4">
        <div class="d-flex gap-2 justify-content-lg-end flex-wrap">
          <a href="index.php" class="btn btn-light btn-sm" title="Atualizar"><i class="fas fa-sync-alt me-1"></i>Atualizar</a>
          <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#novaAvariaModal"><i class="fas fa-plus me-1"></i>Reportar avaria</button>
        </div>
      </div>
    </div>
  </section>

  <div class="card border-0 shadow-sm mb-3 filter-shell">
    <div class="card-body p-4">
      <div class="accordion filter-accordion" id="avariasFiltersAccordion">
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#avariasFiltersCollapse" aria-expanded="false">
              <span><i class="fas fa-bug me-2 text-primary"></i>Filtros avançados</span>
            </button>
          </h2>
          <div id="avariasFiltersCollapse" class="accordion-collapse collapse" data-bs-parent="#avariasFiltersAccordion">
            <div class="accordion-body px-0 pb-0">
              <p class="text-muted mb-3">Refine as avarias por estado, equipamento, utilizador e intervalo de datas.</p>
              <form class="row g-3 align-items-end filter-grid" data-advanced-filter-form="avariasTable" data-empty-state="avariasEmptyState" data-results-count="avariasCount">
        <div class="col-12 col-md-6 col-xl-3">
          <label class="form-label small text-muted">Pesquisa geral</label>
          <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control form-control-sm" placeholder="Título, descrição, equipamento..." data-global-search>
          </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <label class="form-label small text-muted">Severidade</label>
          <select class="form-select form-select-sm" data-filter-key="severidade">
            <option value="all">Todas</option>
            <option value="baixa">Baixa</option>
            <option value="média">Média</option>
            <option value="media">Média</option>
            <option value="crítica">Crítica</option>
            <option value="critica">Crítica</option>
          </select>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <label class="form-label small text-muted">Estado</label>
          <select class="form-select form-select-sm" data-filter-key="estado">
            <option value="all">Todos</option>
            <option value="pendente">Pendente</option>
            <option value="resolvida">Resolvida</option>
          </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
          <label class="form-label small text-muted">Equipamento</label>
          <select class="form-select form-select-sm" data-filter-key="equipamento">
            <option value="all">Todos</option>
            <?php foreach (array_keys($equipamentosMap) as $equipamento): ?>
              <option value="<?= htmlspecialchars($equipamento) ?>"><?= htmlspecialchars($equipamento) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php if ($tipo !== 'Professor') : ?>
          <div class="col-12 col-md-6 col-xl-2">
            <label class="form-label small text-muted">Reportado por</label>
            <select class="form-select form-select-sm" data-filter-key="usuario">
              <option value="all">Todos</option>
              <?php foreach (array_keys($usuariosMap) as $usuario): ?>
                <option value="<?= htmlspecialchars($usuario) ?>"><?= htmlspecialchars($usuario) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>
        <div class="col-6 col-md-3 col-xl-2">
          <label class="form-label small text-muted">Data inicial</label>
          <input type="date" class="form-control form-control-sm" data-filter-key="data" data-filter-mode="date-min">
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <label class="form-label small text-muted">Data final</label>
          <input type="date" class="form-control form-control-sm" data-filter-key="data" data-filter-mode="date-max">
        </div>
        <div class="col-12 col-md-6 col-xl-2">
          <label class="form-label small text-muted">Ordenar por data</label>
          <select class="form-select form-select-sm" data-sort-key="data" data-sort-type="date">
            <option value="desc" selected>Mais recentes</option>
            <option value="asc">Mais antigas</option>
          </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
          <label class="form-label small text-muted">Ordenar por título</label>
          <select class="form-select form-select-sm" data-sort-key="titulo" data-sort-type="text">
            <option value="default" selected>Padrão</option>
            <option value="asc">A-Z</option>
            <option value="desc">Z-A</option>
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
          <div class="table-panel-title">Lista de avarias</div>
          <div class="table-panel-subtitle"><span id="avariasCount"><?= count($avarias) ?></span> avaria(s) encontrada(s)</div>
        </div>
        <div class="table-panel-hint">Você pode combinar estado, severidade, período e equipamento.</div>
      </div>

      <div class="table-responsive table-modern-wrap">
        <table class="table table-hover align-middle mb-0 table-modern-like" id="avariasTable">
          <thead>
            <tr>
              <th>Reportado por</th>
              <th>Equipamento</th>
              <th>Estado</th>
              <th>Data</th>
              <th class="text-end">Ação</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($avarias as $a): ?>
              <?php
              $resolvida = (bool)($a['resolvido'] ?? 0);
              $sev = strtolower(trim((string)($a['severidade'] ?? '')));
              $sevBadge = ($sev === 'critica' || $sev === 'crítica') ? 'danger' : (($sev === 'media' || $sev === 'média') ? 'warning' : 'secondary');
              ?>
              <tr
                data-titulo="<?= htmlspecialchars(mb_strtolower($a['titulo'] ?? '')) ?>"
                data-equipamento="<?= htmlspecialchars($a['equipamento']) ?>"
                data-usuario="<?= htmlspecialchars($a['usuario']) ?>"
                data-severidade="<?= htmlspecialchars($sev) ?>"
                data-estado="<?= $resolvida ? 'resolvida' : 'pendente' ?>"
                data-data="<?= htmlspecialchars(date('Y-m-d', strtotime($a['data_registro'] ?? 'now'))) ?>"
                data-search="<?= htmlspecialchars(mb_strtolower(trim(($a['titulo'] ?? '') . ' ' . ($a['descricao'] ?? '') . ' ' . ($a['equipamento'] ?? '') . ' ' . ($a['usuario'] ?? '') . ' ' . ($a['usuario_tipo'] ?? '') . ' ' . ($a['severidade'] ?? '')))) ?>"
              >
                <td>
                  <a href="../usuarios/profile.php?id=<?= (int)$a['usuario_perfil_id'] ?>" class="table-user-link" title="Ver perfil de <?= htmlspecialchars($a['usuario']) ?>">
                    <img src="<?= htmlspecialchars(get_avatar_url(['foto' => $a['usuario_foto'] ?? null])) ?>" alt="Foto de <?= htmlspecialchars($a['usuario']) ?>" class="table-avatar">
                    <span class="table-main-stack">
                      <span class="table-user-name"><?= htmlspecialchars($a['usuario']) ?></span>
                      <span class="table-user-role"><?= htmlspecialchars($a['usuario_tipo'] ?? 'Utilizador') ?></span>
                    </span>
                  </a>
                </td>
                <td><span class="table-linkish"><?= htmlspecialchars($a['equipamento']) ?></span></td>
                <td>
                  <span class="badge rounded-pill bg-<?= $resolvida ? 'success' : 'danger' ?>-subtle text-<?= $resolvida ? 'success' : 'danger' ?> border border-<?= $resolvida ? 'success' : 'danger' ?>-subtle">
                    <?= $resolvida ? 'Resolvida' : 'Pendente' ?>
                  </span>
                  <?php if ($_SESSION['usuario_tipo'] != 'Professor') : ?>
                    <a href="<?= $resolvida ? 'reavariar' : 'resolver' ?>.php?id=<?= $a['id'] ?>" class="status-toggle-btn <?= $resolvida ? 'is-on' : 'is-off' ?>" title="<?= $resolvida ? 'Marcar como pendente' : 'Marcar como resolvida' ?>" onclick="return confirm('Deseja alterar o estado desta avaria?')">
                      <span class="status-toggle-track"><span class="status-toggle-thumb"></span></span>
                      <span class="status-toggle-label"><?= $resolvida ? 'Resolvida' : 'Pendente' ?></span>
                    </a>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($a['data_registro'] ?? '—') ?></td>
                <td class="text-end">
                  <div class="btn-group" role="group" aria-label="Ações">
                    <button type="button" class="btn btn-outline-info btn-sm" title="Detalhes" data-bs-toggle="modal" data-bs-target="#detalhesAvaria-<?= $a['id'] ?>">
                      <i class="fas fa-info-circle"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm <?= ($_SESSION['usuario_tipo'] == 'Direção' || $a['usuario_id'] == $_SESSION['usuario_id']) ? '' : 'disabled' ?>" data-bs-toggle="<?= ($_SESSION['usuario_tipo'] == 'Direção' || $_SESSION['usuario_id'] == $a['usuario_id']) ? 'modal' : '' ?>" data-bs-target="#editarAvaria-<?= $a['id'] ?>">
                      <i class="fas fa-pen"></i>
                    </button>
                    <?php if ($_SESSION['usuario_tipo'] == 'Direção' || $_SESSION['usuario_id'] == $a['usuario_id']) : ?>
                      <a href="delete.php?id=<?= $a['id'] ?>" class="btn btn-outline-danger btn-sm" title="Remover" onclick="return confirm('Deseja realmente remover esta avaria?')">
                        <i class="fas fa-trash"></i>
                      </a>
                    <?php else : ?>
                      <button class="btn btn-outline-secondary btn-sm disabled"><i class="fas fa-trash"></i></button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>

              <?php $equipamentos = $conn->query('SELECT id, nome FROM equipamentos ORDER BY nome'); ?>
              <?php
              ob_start();
              ?>
<div class="modal fade" id="editarAvaria-<?= $a['id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h1 class="modal-title fs-5">Editar avaria</h1>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form method="POST" class="mt-3" action="edit.php?id=<?= $a['id'] ?>">
                        <div class="mb-3">
                          <label>Equipamento</label>
                          <select name="equipamento_id" class="form-select">
                            <?php while ($e = $equipamentos->fetch_assoc()): ?>
                              <option value="<?= $e['id'] ?>" <?= $a['equipamento_id'] == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['nome']) ?></option>
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
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                    </form>
                  </div>
                </div>
              </div>

              <?php $equipamento = $conn->query('SELECT id, nome FROM equipamentos WHERE id = ' . (int)$a['equipamento_id'])->fetch_assoc(); ?>
              <div class="modal fade" id="detalhesAvaria-<?= $a['id'] ?>" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h1 class="modal-title fs-5">Detalhes</h1>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label>Equipamento</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($equipamento['nome'] ?? '') ?>" disabled>
                      </div>
                      <div class="mb-3">
                        <label>Descrição</label>
                        <textarea class="form-control" style="max-height: 50vh;" disabled><?= htmlspecialchars($a['descricao']) ?></textarea>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            
              <?php
              $avariaModals[] = ob_get_clean();
              ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div id="avariasEmptyState" class="text-muted small mt-3 d-none">Nenhuma avaria encontrada com os filtros selecionados.</div>

      <?php if (count($avarias) === 0): ?>
        <div class="text-muted small mt-3">Nenhuma avaria registada.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if (!empty($avariaModals)) echo implode("\n", $avariaModals); ?>

<div class="modal fade" id="novaAvariaModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5">Reportar avaria</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="reportar.php" method="POST">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Equipamento</label>
              <select name="equipamento_id" class="form-select" required>
                <?php while ($equipRelato = $equipamentosNovoRelato->fetch_assoc()): ?>
                  <option value="<?= (int)$equipRelato['id'] ?>"><?= htmlspecialchars($equipRelato['nome']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Descrição do Problema</label>
              <textarea name="descricao" class="form-control" rows="5" required></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning">Reportar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once FOOTER_TEMPLATE; ?>
