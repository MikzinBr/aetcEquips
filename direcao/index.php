<?php
require_once '../config.php';
require_once DBAPI;

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION['usuario_id'])) {
  header('Location: ../index.php');
  exit;
}

if (($_SESSION['usuario_tipo'] ?? '') !== 'Direção') {
  header('Location: ../dashboard.php');
  exit;
}

$page_title = 'Painel da Direção';
$page_subtitle = 'Visão geral dos equipamentos, avarias e salas';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

$conn = open_database();

function fetch_scalar(mysqli $conn, string $sql): int
{
  $result = $conn->query($sql);
  if (!$result) {
    return 0;
  }
  $row = $result->fetch_row();
  return (int)($row[0] ?? 0);
}

$totalEquipamentos = fetch_scalar($conn, 'SELECT COUNT(*) FROM equipamentos');
$totalSalas = fetch_scalar($conn, 'SELECT COUNT(*) FROM salas');
$totalAvarias = fetch_scalar($conn, 'SELECT COUNT(*) FROM avarias');
$avariasPendentes = fetch_scalar($conn, 'SELECT COUNT(*) FROM avarias WHERE resolvido = 0');
$equipamentosOk = fetch_scalar($conn, "SELECT COUNT(*) FROM equipamentos WHERE status = 'ok'");
$equipamentosAvariados = fetch_scalar($conn, "SELECT COUNT(*) FROM equipamentos WHERE status = 'avariado'");
$quantidadeTotal = fetch_scalar($conn, 'SELECT COALESCE(SUM(quantidade), 0) FROM equipamentos');
$valorTotal = fetch_scalar($conn, 'SELECT COALESCE(SUM(quantidade * custo_unitario), 0) FROM equipamentos');

$percentOk = $totalEquipamentos > 0 ? round(($equipamentosOk / $totalEquipamentos) * 100) : 0;
$percentAvariados = $totalEquipamentos > 0 ? 100 - $percentOk : 0;

$salas = [];
$maiorSala = 0;
$sqlSalas = "
  SELECT s.numero_sala, COUNT(e.id) AS total, COALESCE(SUM(e.quantidade), 0) AS quantidade_total
  FROM salas s
  LEFT JOIN equipamentos e ON e.sala_id = s.id
  GROUP BY s.id, s.numero_sala
  ORDER BY total DESC, s.numero_sala ASC
  LIMIT 6
";
$resultSalas = $conn->query($sqlSalas);
if ($resultSalas) {
  while ($row = $resultSalas->fetch_assoc()) {
    $salas[] = $row;
    $maiorSala = max($maiorSala, (int)$row['total']);
  }
}

$ultimasAvarias = [];
$sqlRecentes = "
  SELECT a.id, a.descricao, a.data_registro, a.resolvido, e.nome AS equipamento, u.nome AS usuario
  FROM avarias a
  INNER JOIN equipamentos e ON e.id = a.equipamento_id
  INNER JOIN usuarios u ON u.id = a.usuario_id
  ORDER BY a.data_registro DESC
  LIMIT 6
";
$resultRecentes = $conn->query($sqlRecentes);
if ($resultRecentes) {
  while ($row = $resultRecentes->fetch_assoc()) {
    $ultimasAvarias[] = $row;
  }
}

$equipPorEstado = [
  'ok' => $equipamentosOk,
  'avariado' => $equipamentosAvariados,
];
?>

<style>
  .dashboard-card,
  .chart-card {
    border: 0;
    border-radius: 1rem;
    box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08);
  }

  .mini-icon {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
  }

  .donut {
    width: 220px;
    height: 220px;
    border-radius: 50%;
    margin: 0 auto;
    position: relative;
  }

  .donut::after {
    content: '';
    position: absolute;
    inset: 32px;
    background: #fff;
    border-radius: 50%;
  }

  .donut-center {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1;
    text-align: center;
    font-weight: 600;
  }

  .legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
  }

  .room-bar {
    height: 10px;
    background: #e9ecef;
    border-radius: 999px;
    overflow: hidden;
  }

  .room-bar span {
    display: block;
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #0d6efd 0%, #6ea8fe 100%);
  }

  .soft-panel {
    background: linear-gradient(135deg, rgba(13,110,253,.08), rgba(25,135,84,.08));
    border-radius: 1rem;
  }
</style>

<div class="container-fluid px-0 mb-5" style="width: 90vw;">
  <div class="soft-panel p-4 mb-4 border">
    <div class="row align-items-center g-3">
      <div class="col-lg-8">
        <h2 class="h3 mb-2">Painel executivo da Direção</h2>
        <p class="text-muted mb-0">Acompanhe rapidamente o estado do inventário, as avarias pendentes e a distribuição dos equipamentos pelas salas.</p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <a href="<?= BASEURL ?>equipamentos" class="btn btn-primary me-2 mb-2 mb-lg-0">
          <i class="fas fa-tools me-2"></i>Gerir equipamentos
        </a>
        <a href="<?= BASEURL ?>avarias" class="btn btn-outline-secondary">
          <i class="fas fa-exclamation-triangle me-2"></i>Ver avarias
        </a>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
      <div class="card dashboard-card h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small">Equipamentos registados</div>
            <div class="display-6 fw-bold mb-0"><?= $totalEquipamentos ?></div>
            <div class="small text-muted mt-1">Quantidade total: <?= $quantidadeTotal ?></div>
          </div>
          <a href="../equipamentos" title="ver equipamentos" class="mini-icon bg-success-subtle text-success"><i class="fas fa-tools"></i></a>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="card dashboard-card h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small">Equipamentos operacionais</div>
            <div class="display-6 fw-bold mb-0"><?= $equipamentosOk ?></div>
            <div class="small text-muted mt-1"><?= $percentOk ?>% do inventário</div>
          </div>
          <a href="../equipamentos" title="ver equipamentos" class="mini-icon bg-primary-subtle text-primary border-0"><i class="fas fa-check-circle"></i></a>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="card dashboard-card h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small">Avarias pendentes</div>
            <div class="display-6 fw-bold mb-0"><?= $avariasPendentes ?></div>
            <div class="small text-muted mt-1">Total de registos: <?= $totalAvarias ?></div>
          </div>
          <a href="../avarias" title="ver avarias" class="mini-icon bg-warning-subtle text-warning border-0"><i class="fas fa-exclamation-triangle"></i></a>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="card dashboard-card h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small">Valor estimado do inventário</div>
            <div class="display-6 fw-bold mb-0">€<?= number_format($valorTotal, 0, ',', '.') ?></div>
            <div class="small text-muted mt-1">Salas registadas: <?= $totalSalas ?></div>
          </div>
          <a class="mini-icon bg-info-subtle text-info"><i class="fas fa-euro-sign"></i></a>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-xl-5">
      <div class="card chart-card h-100">
        <div class="card-body">
          <div class="fw-semibold mb-1">Estado dos equipamentos</div>
          <div class="text-muted small mb-4">Distribuição real com base no campo de estado do inventário</div>

          <div class="donut" style="background: conic-gradient(#198754 0 <?= $percentOk ?>%, #dc3545 <?= $percentOk ?>% 100%);">
            <div class="donut-center">
              <div class="fs-2"><?= $percentOk ?>%</div>
              <div class="text-muted small">operacionais</div>
            </div>
          </div>

          <div class="mt-4 d-grid gap-2">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2"><span class="legend-dot" style="background:#198754"></span><span>OK</span></div>
              <strong><?= $equipPorEstado['ok'] ?></strong>
            </div>
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2"><span class="legend-dot" style="background:#dc3545"></span><span>Avariado</span></div>
              <strong><?= $equipPorEstado['avariado'] ?></strong>
            </div>
            <div class="d-flex align-items-center justify-content-between text-muted small pt-2 border-top mt-2">
              <span>Percentagem com problema</span>
              <strong><?= $percentAvariados ?>%</strong>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-7">
      <div class="card chart-card h-100">
        <div class="card-body">
          <div class="fw-semibold mb-1">Equipamentos por sala</div>
          <div class="text-muted small mb-4">Top salas com mais equipamentos registados</div>

          <?php if (!empty($salas)) : ?>
            <div class="d-grid gap-3">
              <?php foreach ($salas as $sala) : ?>
                <?php
                  $total = (int)$sala['total'];
                  $quantidadeSala = (int)$sala['quantidade_total'];
                  $largura = $maiorSala > 0 ? round(($total / $maiorSala) * 100) : 0;
                ?>
                <div>
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <div>
                      <span class="fw-semibold">Sala <?= htmlspecialchars($sala['numero_sala']) ?></span>
                      <span class="text-muted small ms-2"><?= $quantidadeSala ?> unidade(s)</span>
                    </div>
                    <span class="text-muted small"><?= $total ?> tipo(s) de equipamento</span>
                  </div>
                  <div class="room-bar"><span style="width: <?= $largura ?>%"></span></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else : ?>
            <div class="text-muted">Ainda não existem salas com dados para apresentar.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-xl-5">
      <div class="card chart-card h-100">
        <div class="card-body">
          <div class="fw-semibold mb-1">Resumo rápido</div>
          <div class="text-muted small mb-4">Indicadores úteis para decisão</div>

          <div class="list-group list-group-flush">
            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
              <span>Equipamentos com estado OK</span>
              <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill"><?= $equipamentosOk ?></span>
            </div>
            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
              <span>Equipamentos avariados</span>
              <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill"><?= $equipamentosAvariados ?></span>
            </div>
            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
              <span>Avarias por resolver</span>
              <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill"><?= $avariasPendentes ?></span>
            </div>
            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
              <span>Total de salas</span>
              <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill"><?= $totalSalas ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-7">
      <div class="card chart-card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
              <div class="fw-semibold">Últimas avarias registadas</div>
              <div class="text-muted small">Baseado nos campos reais da tabela avarias</div>
            </div>
            <a href="<?= BASEURL ?>avarias" class="btn btn-outline-secondary btn-sm">Abrir módulo</a>
          </div>

          <div class="table-responsive" style="max-height: 15vw; overflow: auto">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Descrição</th>
                  <th>Equipamento</th>
                  <th>Utilizador</th>
                  <th>Estado</th>
                  <th>Data</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($ultimasAvarias)) : ?>
                  <?php foreach ($ultimasAvarias as $avaria) : ?>
                    <?php $resolvida = (int)($avaria['resolvido'] ?? 0) === 1; ?>
                    <tr>
                      <td class="fw-semibold"><?= htmlspecialchars($avaria['descricao'] ?: 'Sem descrição') ?></td>
                      <td><?= htmlspecialchars($avaria['equipamento'] ?? '—') ?></td>
                      <td><?= htmlspecialchars($avaria['usuario'] ?? '—') ?></td>
                      <td>
                        <span class="badge rounded-pill bg-<?= $resolvida ? 'success' : 'danger' ?>-subtle text-<?= $resolvida ? 'success' : 'danger' ?> border border-<?= $resolvida ? 'success' : 'danger' ?>-subtle">
                          <?= $resolvida ? 'Resolvida' : 'Pendente' ?>
                        </span>
                      </td>
                      <td><?= htmlspecialchars($avaria['data_registro'] ?? '—') ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else : ?>
                  <tr>
                    <td colspan="5" class="text-center text-muted py-4">Ainda não existem avarias registadas.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
close_database($conn);
require_once FOOTER_TEMPLATE;
?>
