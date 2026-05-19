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

$graficoPizza = [
  [
    'label' => 'OK',
    'valor' => $equipamentosOk,
    'percent' => $percentOk,
    'cor' => '#198754'
  ],
  [
    'label' => 'Avariado',
    'valor' => $equipamentosAvariados,
    'percent' => $percentAvariados,
    'cor' => '#dc3545'
  ]
];

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

  .pie-chart-wrap {
    width: 240px;
    margin: 0 auto;
  }

  .pie-chart-svg {
    width: 240px;
    height: 240px;
    display: block;
    margin: 0 auto;
    overflow: visible;
  }

  .pie-chart-label {
    font-weight: 700;
    fill: #ffffff;
    text-anchor: middle;
    dominant-baseline: middle;
    pointer-events: none;
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

<div class="container-fluid px-0 mb-4 mt-4 dashboard-reveal" style="width: 90vw;" data-dashboard-animate>
  <section class="page-hero m-4">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <span class="badge bg-white text-primary mb-3 px-3 py-2">Administração</span>
        <div class="h2 mb-2">Painel administrativo</div>
        <p class="mb-0">Veja relatórios rápidos sobre o estado dos equipamentos e das salas</p>
      </div>
      <div class="col-lg-4">
        <div class="d-flex gap-2 justify-content-lg-end flex-wrap">
          <a href="<?= BASEURL ?>equipamentos" class="btn btn-success me-2 mb-2 mb-lg-0">
            <i class="fas fa-tools me-2"></i>Gerir equipamentos
          </a>
          <a href="<?= BASEURL ?>avarias" class="btn btn-success">
            <i class="fas fa-exclamation-triangle me-2"></i>Ver avarias
          </a>
        </div>
      </div>
    </div>
  </section>

  <div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
      <div class="card dashboard-card h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small">Equipamentos registados</div>
            <div class="display-6 fw-bold mb-0" data-count-to="<?= $totalEquipamentos ?>">0</div>
            <div class="small text-muted mt-1"><span class="metric-chip"><i class="fas fa-layer-group text-primary"></i><span>Quantidade total: <span data-count-to="<?= $quantidadeTotal ?>">0</span></span></span></div>
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
            <div class="display-6 fw-bold mb-0" data-count-to="<?= $equipamentosOk ?>">0</div>
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
            <div class="display-6 fw-bold mb-0" data-count-to="<?= $avariasPendentes ?>">0</div>
            <div class="small text-muted mt-1"><span class="metric-chip"><i class="fas fa-clipboard-list text-warning"></i><span>Total de registos: <span data-count-to="<?= $totalAvarias ?>">0</span></span></span></div>
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
            <div class="display-6 fw-bold mb-0" data-count-to="<?= $valorTotal ?>" data-prefix="€">0</div>
            <div class="small text-muted mt-1"><span class="metric-chip"><i class="fas fa-door-open text-info"></i><span>Salas registadas: <span data-count-to="<?= $totalSalas ?>">0</span></span></span></div>
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
          <div class="fw-semibold mb-4 h5">Estado dos equipamentos</div>

          <?php
            $cx = 120;
            $cy = 120;
            $r = 105;
            $anguloInicial = -90;
          ?>

          <div class="pie-chart-wrap">
            <svg viewBox="0 0 240 240" class="pie-chart-svg" aria-label="Gráfico de pizza do estado dos equipamentos">
              <?php if ($totalEquipamentos > 0) : ?>

                <?php foreach ($graficoPizza as $item) : ?>
                  <?php
                    if ($item['valor'] <= 0) {
                      continue;
                    }

                    $anguloFat = ($item['valor'] / $totalEquipamentos) * 360;
                    $anguloFinal = $anguloInicial + $anguloFat;

                    if ($item['percent'] >= 100) :
                  ?>
                      <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>" fill="<?= $item['cor'] ?>"></circle>

                      <text
                        x="<?= $cx ?>"
                        y="<?= $cy ?>"
                        class="pie-chart-label"
                        style="font-size: 28px;"
                      >
                        <?= $item['percent'] ?>%
                      </text>

                  <?php
                      $anguloInicial = $anguloFinal;
                      continue;
                    endif;

                    $x1 = $cx + $r * cos(deg2rad($anguloInicial));
                    $y1 = $cy + $r * sin(deg2rad($anguloInicial));
                    $x2 = $cx + $r * cos(deg2rad($anguloFinal));
                    $y2 = $cy + $r * sin(deg2rad($anguloFinal));

                    $largeArc = $anguloFat > 180 ? 1 : 0;
                    $path = "M $cx,$cy L $x1,$y1 A $r,$r 0 $largeArc,1 $x2,$y2 Z";

                    $anguloMeio = $anguloInicial + ($anguloFat / 2);
                    $textoPercent = $item['percent'] . '%';

                    if ($anguloFat <= 20) {
                      $labelR = $r * 0.80;
                    } elseif ($anguloFat <= 45) {
                      $labelR = $r * 0.70;
                    } elseif ($anguloFat <= 180) {
                      $labelR = $r * 0.58;
                    } else {
                      $labelR = $r * 0.45;
                    }

                    $labelX = $cx + $labelR * cos(deg2rad($anguloMeio));
                    $labelY = $cy + $labelR * sin(deg2rad($anguloMeio));

                    if ($anguloFat > 180) {
                      $fontSize = 28;
                    } elseif ($anguloFat > 120) {
                      $fontSize = 24;
                    } elseif ($anguloFat > 60) {
                      $fontSize = 20;
                    } else {
                      $larguraDisponivel = 2 * $labelR * sin(deg2rad($anguloFat / 2));
                      $fontSize = (int) floor($larguraDisponivel / (max(strlen($textoPercent), 1) * 0.62));
                      $fontSize = max(11, min(20, $fontSize));
                    }
                  ?>

                  <path d="<?= $path ?>" fill="<?= $item['cor'] ?>" stroke="#ffffff" stroke-width="1"></path>

                  <text
                    x="<?= $labelX ?>"
                    y="<?= $labelY ?>"
                    class="pie-chart-label"
                    style="font-size: <?= $fontSize ?>px;"
                  >
                    <?= $textoPercent ?>
                  </text>

                  <?php $anguloInicial = $anguloFinal; ?>
                <?php endforeach; ?>

              <?php else : ?>
                <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>" fill="#e9ecef"></circle>

                <text
                  x="<?= $cx ?>"
                  y="<?= $cy ?>"
                  class="pie-chart-label"
                  style="font-size: 28px; fill: #212529;"
                >
                  0%
                </text>
              <?php endif; ?>
            </svg>
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
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-7">
      <div class="card chart-card h-100">
        <div class="card-body">
          <div class="fw-semibold mb-4 h5">Equipamentos por sala</div>
          <?php if (!empty($salas)) : ?>
            <div class="d-grid gap-3">
              <?php foreach ($salas as $sala) : ?>
                <?php
                  $total = (int)$sala['total'];
                  $quantidadeSala = (int)$sala['quantidade_total'];
                  $largura = $maiorSala > 0 ? round(($total / $maiorSala) * 100) : 0;
                ?>
                <div>
                  <div class="d-flex justify-content-between align-items-center mb-1" style="overflow: auto">
                    <div>
                      <span class="fw-semibold">Sala <?= htmlspecialchars($sala['numero_sala']) ?></span>
                      <span class="text-muted small ms-2"><?= $quantidadeSala ?> unidade(s)</span>
                    </div>
                    <span class="text-muted small"><?= $total ?> tipo(s) de equipamento</span>
                  </div>
                  <div class="room-bar"><span data-bar-width="<?= $largura ?>"></span></div>
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
          <div class="fw-semibold mb-1 h5">Resumo rápido</div>

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
              <div class="fw-semibold h5">Últimas avarias registadas</div>
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
