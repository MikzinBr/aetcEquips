<?php
require_once '../config.php';
require_once '../inc/helpers.php';
require_once DBAPI;

$page_title = 'Salas';
$page_subtitle = 'Espaços com equipamentos agrupados';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header('Location: ../index.php');
  exit;
}

$erro = $_GET['erro'] ?? '';
$msg = $_GET['msg'] ?? '';

$conn = open_database();
$result = $conn->query("SELECT s.*, COUNT(e.id) AS total_equipamentos FROM salas s LEFT JOIN equipamentos e ON e.sala_id = s.id GROUP BY s.id ORDER BY s.numero_sala");
$salas = [];
$salaModals = [];
while ($row = $result->fetch_assoc()) {
  $salas[] = $row;
}
$equipamentosDisponiveisNovo = $conn->query("SELECT id, nome FROM equipamentos WHERE sala_id IS NULL ORDER BY nome");
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
        <span class="badge bg-white text-primary mb-3 px-3 py-2">Espaços</span>
        <div class="h2 mb-2">Salas</div>
        <p class="mb-0">Consulte rapidamente os espaços, veja ocupação e mantenha a organização física do inventário.</p>
      </div>
      <div class="col-lg-4">
        <div class="d-flex gap-2 justify-content-lg-end flex-wrap">
          <a href="index.php" class="btn btn-light btn-sm" title="Atualizar"><i class="fas fa-sync-alt me-1"></i>Atualizar</a>
          <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#novaSalaModal"><i class="fas fa-plus me-1"></i>Criar sala</button>
        </div>
      </div>
    </div>
  </section>

  <div class="card border-0 shadow-sm mb-3 filter-shell">
    <div class="card-body p-4">
      <div class="accordion filter-accordion" id="salasFiltersAccordion">
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#salasFiltersCollapse" aria-expanded="false">
              <span><i class="fas fa-school me-2 text-primary"></i>Filtros avançados</span>
            </button>
          </h2>
          <div id="salasFiltersCollapse" class="accordion-collapse collapse" data-bs-parent="#salasFiltersAccordion">
            <div class="accordion-body px-0 pb-0">
              <p class="text-muted mb-3">Organize as salas por número, descrição e quantidade de equipamentos.</p>
              <form class="row g-3 align-items-end filter-grid" data-advanced-filter-form="salasTable" data-empty-state="salasEmptyState" data-results-count="salasCount">
        <div class="col-12 col-md-5 col-xl-3">
          <label class="form-label small text-muted">Pesquisa geral</label>
          <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control form-control-sm" placeholder="Número, descrição..." data-global-search>
          </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <label class="form-label small text-muted">Nº mínimo</label>
          <input type="number" class="form-control form-control-sm" data-filter-key="numero" data-filter-mode="number-min" min="1">
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <label class="form-label small text-muted">Nº máximo</label>
          <input type="number" class="form-control form-control-sm" data-filter-key="numero" data-filter-mode="number-max" min="1">
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <label class="form-label small text-muted">Descrição</label>
          <select class="form-select form-select-sm" data-filter-key="descricao" data-filter-mode="presence">
            <option value="all">Todas</option>
            <option value="with">Com descrição</option>
            <option value="without">Sem descrição</option>
          </select>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
          <label class="form-label small text-muted">Equipamentos</label>
          <select class="form-select form-select-sm" data-filter-key="ocupacao">
            <option value="all">Todas</option>
            <option value="vazia">Vazia</option>
            <option value="ocupada">Com equipamentos</option>
          </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
          <label class="form-label small text-muted">Ordenar por nº</label>
          <select class="form-select form-select-sm" data-sort-key="numero" data-sort-type="number">
            <option value="asc" selected>Menor número</option>
            <option value="desc">Maior número</option>
          </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
          <label class="form-label small text-muted">Ordenar por ocupação</label>
          <select class="form-select form-select-sm" data-sort-key="total" data-sort-type="number">
            <option value="default" selected>Padrão</option>
            <option value="desc">Mais equipamentos</option>
            <option value="asc">Menos equipamentos</option>
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

  <?php if (count($salas) === 0) : ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body text-center py-5">
        <div class="text-muted">Nenhuma sala registada.</div>
      </div>
    </div>
  <?php else : ?>
    <div class="card border-0 shadow-sm table-card-shell">
      <div class="card-body p-3 p-lg-4">
        <div class="table-panel-header mb-3">
          <div>
            <div class="table-panel-title">Lista de salas</div>
            <div class="table-panel-subtitle"><span id="salasCount"><?= count($salas) ?></span> sala(s) encontrada(s)</div>
          </div>
          <div class="table-panel-hint">Filtre por intervalo de número, descrição e ocupação.</div>
        </div>

        <div class="table-responsive table-modern-wrap">
          <table class="table table-hover align-middle mb-0 table-modern-like" id="salasTable">
            <thead>
              <tr>
                <th>Número</th>
                <th>Descrição</th>
                <th>Equipamentos</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($salas as $sala): ?>
                <?php $ocupacao = ((int)$sala['total_equipamentos'] > 0) ? 'ocupada' : 'vazia'; ?>
                <tr
                  data-numero="<?= (int)$sala['numero_sala'] ?>"
                  data-descricao="<?= htmlspecialchars(trim((string)($sala['descricao'] ?? ''))) ?>"
                  data-ocupacao="<?= $ocupacao ?>"
                  data-total="<?= (int)$sala['total_equipamentos'] ?>"
                  data-search="<?= htmlspecialchars(mb_strtolower(trim('sala ' . $sala['numero_sala'] . ' ' . ($sala['descricao'] ?? '') . ' ' . $ocupacao))) ?>"
                >
                  <td>
                    <div class="table-main-cell">
                      <span class="table-main-icon"><i class="fas fa-door-open"></i></span>
                      <div class="table-main-stack">
                        <div class="table-main-title">Sala <?= htmlspecialchars($sala['numero_sala']) ?></div>
                        <div class="table-main-meta"><?= htmlspecialchars(limitText($sala['descricao'], 50)) ?></div>
                      </div>
                    </div>
                  </td>
                  <td><?= htmlspecialchars(limitText($sala['descricao'], 50)) ?></td>
                  <td>
                    <span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle">
                      <?= (int)$sala['total_equipamentos'] ?> item(ns)
                    </span>
                  </td>
                  <td class="text-end">
                    <div class="btn-group" role="group" aria-label="Ações">
                      <a href="../equipamentos?sala_id=<?= $sala['id'] ?>" class="btn btn-outline-info btn-sm" title="Adicionar equipamentos">
                        <i class="fas fa-desktop"></i>
                      </a>
                      <button type="button" class="btn btn-outline-<?= $_SESSION['usuario_tipo'] == 'Direção' ? 'success' : 'secondary disabled' ?> btn-sm" data-bs-toggle="<?= $_SESSION['usuario_tipo'] == 'Direção' ? 'modal' : '' ?>" data-bs-target="#addEquip<?= $sala['id'] ?>">
                        <i class="fas fa-plus"></i>
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-sm <?= $_SESSION['usuario_tipo'] != 'Direção' ? 'disabled' : '' ?>" data-bs-toggle="<?= $_SESSION['usuario_tipo'] == 'Direção' ? 'modal' : '' ?>" data-bs-target="#editarSala<?= $sala['id'] ?>">
                        <i class="fas fa-pen"></i>
                      </button>
                      <?php if ($_SESSION['usuario_tipo'] == 'Direção') : ?>
                        <a href="delete.php?id=<?= $sala['id'] ?>" class="btn btn-outline-danger btn-sm" title="Remover" onclick="return confirm('Remover esta sala?')">
                          <i class="fas fa-trash"></i>
                        </a>
                      <?php else : ?>
                        <button class="btn btn-outline-secondary btn-sm disabled"><i class="fas fa-trash"></i></button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>

                <?php $equipamentos = $conn->query("SELECT id, nome FROM equipamentos WHERE sala_id IS NULL ORDER BY nome"); ?>
              <?php
              ob_start();
              ?>
<div class="modal fade" id="addEquip<?= $sala['id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h1 class="modal-title fs-5">Adicionar equipamentos</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <form method="POST" action="add_equipamentos.php?sala_id=<?= $sala['id'] ?>">
                          <div class="mb-3">
                            <label>Equipamentos disponíveis</label>
                            <select name="equipamento_ids[]" class="form-select" multiple size="8" required>
                              <?php while ($e = $equipamentos->fetch_assoc()): ?>
                                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nome']) ?></option>
                              <?php endwhile; ?>
                            </select>
                            <small class="text-muted">Use Ctrl (Windows) ou Cmd (Mac) para selecionar vários</small>
                          </div>
                      </div>
                      <div class="modal-footer">
                        <button class="btn btn-success">Adicionar</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                      </div>
                      </form>
                    </div>
                  </div>
                </div>

                <div class="modal fade" id="editarSala<?= $sala['id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h1 class="modal-title fs-5">Editar sala</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <form method="POST" class="mt-3" action="edit.php?sala_id=<?= $sala['id'] ?>">
                          <div class="mb-3">
                            <label>Número da sala</label>
                            <input type="number" name="numero_sala" class="form-control" min="1" value="<?= $sala['numero_sala'] ?>">
                          </div>
                          <div class="mb-3">
                            <label>Descrição</label>
                            <textarea name="descricao" class="form-control"><?= htmlspecialchars($sala['descricao']) ?></textarea>
                          </div>
                      </div>
                      <div class="modal-footer">
                        <button class="btn btn-success" onclick="return confirm('Deseja realmente salvar as alterações?')">Salvar Alterações</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                      </div>
                      </form>
                    </div>
                  </div>
                </div>
              
              <?php
              $salaModals[] = ob_get_clean();
              ?>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div id="salasEmptyState" class="text-muted small mt-3 d-none">Nenhuma sala encontrada com os filtros selecionados.</div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php if (!empty($salaModals)) echo implode("\n", $salaModals); ?>

<div class="modal fade" id="novaSalaModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5">Criar sala</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="add.php" method="POST">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label">Número da Sala</label>
              <input type="text" name="numero_sala" class="form-control" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Descrição</label>
              <textarea name="descricao" class="form-control" rows="1"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Equipamentos iniciais (opcional)</label>
              <select name="equipamento_ids[]" class="form-select" multiple size="8">
                <?php while ($equipNovo = $equipamentosDisponiveisNovo->fetch_assoc()): ?>
                  <option value="<?= (int)$equipNovo['id'] ?>"><?= htmlspecialchars($equipNovo['nome']) ?></option>
                <?php endwhile; ?>
              </select>
              <div class="form-text">Segure Ctrl (Windows) ou Cmd (Mac) para selecionar vários.</div>
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
