<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once 'config.php';
require_once './inc/database.php';
require_once './inc/helpers.php';

$page_title = 'Painel inicial';
$page_subtitle = 'Acesso rápido às principais áreas';

require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: index.php");
  exit;
}

$conn = open_database();

$tipo_usuario = $_SESSION['usuario_tipo'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Utilizador';

$equipamentosOk = fetch_scalar($conn, "SELECT COUNT(*) FROM equipamentos WHERE status = 'ok'");
$equipamentosAvariados = fetch_scalar($conn, "SELECT COUNT(*) FROM equipamentos WHERE status = 'avariado'");
$avariasPendentes = fetch_scalar($conn, 'SELECT COUNT(*) FROM avarias WHERE resolvido = 0');
$totalSalas = fetch_scalar($conn, 'SELECT COUNT(*) FROM salas');
$totalEquipamentos = $equipamentosOk + $equipamentosAvariados;

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
?>

<style>
  .quick-link-card-admin {
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 0;
  }

  .admin-card-chart-area {
    margin-top: 1.25rem;
    padding-top: 0;
  }

  .dashboard-mini-pie-wrap {
    width: 155px;
    margin: 0 auto 1.25rem auto;
  }

  .dashboard-mini-pie-svg {
    width: 155px;
    height: 155px;
    display: block;
    margin: 0 auto;
  }

  .dashboard-mini-pie-label {
    font-size: 16px;
    font-weight: 700;
    fill: #ffffff;
    text-anchor: middle;
    dominant-baseline: middle;
  }

  .dashboard-mini-pie-label.dark {
    fill: #212529;
  }

  .dashboard-mini-legend {
    font-size: .9rem;
  }

  .dashboard-mini-dot {
    width: 11px;
    height: 11px;
    border-radius: 50%;
    display: inline-block;
  }
</style>

<div class="container-fluid px-0 dashboard-reveal" style="width: 90vw;" data-dashboard-animate>
  <section class="page-hero m-4">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <span class="badge bg-white text-primary mb-3 px-3 py-2">Painel principal</span>
        <h1 class="display-6 fw-bold mb-2">Olá, <?= htmlspecialchars($nome_usuario) ?>.</h1>
        <p class="mb-0">
          Aceda rapidamente aos módulos do sistema e acompanhe a gestão de equipamentos,
          salas, avarias e perfis com uma navegação muito mais clara.
        </p>
      </div>
    </div>
  </section>

  <?php if ($tipo_usuario === 'Direção') : ?>
    <div class="row g-4">
      <div class="col-lg-6 col-xl-3">
        <a href="<?php echo BASEURL; ?>direcao" class="text-decoration-none text-reset d-block h-100">
          <div class="quick-link-card quick-link-card-admin h-100">
            <div class="icon-wrap bg-primary-subtle text-primary">
              <i class="fas fa-chart-pie"></i>
            </div>

            <h3 class="h5 fw-bold">Painel administrativo</h3>

            <p class="text-muted mb-4">
              Indicadores visuais e resumo executivo.
            </p>

            <div class="admin-card-chart-area">
              <?php
              $cx = 95;
              $cy = 95;
              $r = 82;
              $anguloInicial = -90;
              ?>

              <div class="dashboard-mini-pie-wrap">
                <svg viewBox="0 0 190 190" class="dashboard-mini-pie-svg" aria-label="Gráfico de pizza do estado dos equipamentos">
                  <?php if ($totalEquipamentos > 0) : ?>
                    <?php
                    foreach ($graficoPizza as $item) :
                      if ($item['valor'] <= 0) {
                        continue;
                      }

                      $anguloFat = ($item['valor'] / $totalEquipamentos) * 360;
                      $anguloFinal = $anguloInicial + $anguloFat;

                      if ($item['percent'] >= 100) :
                    ?>
                        <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>" fill="<?= $item['cor'] ?>"></circle>
                        <text x="<?= $cx ?>" y="<?= $cy ?>" class="dashboard-mini-pie-label">
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

                      /*
                      * Para fatias pequenas, coloca o texto mais perto da borda,
                      * porque ali existe mais espaço horizontal dentro da fatia.
                      */
                      if ($anguloFat <= 20) {
                        $labelR = $r * 0.78;
                      } elseif ($anguloFat <= 40) {
                        $labelR = $r * 0.68;
                      } else {
                        $labelR = $r * 0.58;
                      }

                      $labelX = $cx + $labelR * cos(deg2rad($anguloMeio));
                      $labelY = $cy + $labelR * sin(deg2rad($anguloMeio));

                      $textoPercent = $item['percent'] . '%';

                      /*
                      * Calcula aproximadamente a largura disponível dentro da fatia
                      * na posição onde o texto vai ficar.
                      */
                      $larguraDisponivel = 2 * $labelR * sin(deg2rad($anguloFat / 2));

                      /*
                      * Estima um tamanho de fonte que caiba dentro da fatia.
                      * 0.62 é um fator aproximado de largura média por caractere.
                      */
                      $fontSize = (int) floor($larguraDisponivel / (max(strlen($textoPercent), 1) * 0.62));

                      /*
                      * Limites para não ficar gigante nem minúsculo demais.
                      */
                      $fontSize = max(9, min(16, $fontSize));
                    ?>
                      <path d="<?= $path ?>" fill="<?= $item['cor'] ?>" stroke="#ffffff" stroke-width="1"></path>
                      <text x="<?= $labelX ?>" y="<?= $labelY ?>" class="dashboard-mini-pie-label">
                        <?= $item['percent'] ?>%
                      </text>
                    <?php
                      $anguloInicial = $anguloFinal;
                    endforeach;
                    ?>
                  <?php else : ?>
                    <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>" fill="#e9ecef"></circle>
                    <text x="<?= $cx ?>" y="<?= $cy ?>" class="dashboard-mini-pie-label">0%</text>
                  <?php endif; ?>
                </svg>
              </div>

              <div class="dashboard-mini-legend">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <span class="dashboard-mini-dot bg-success"></span>
                    <span>OK</span>
                  </div>
                  <strong><?= $equipamentosOk ?></strong>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <span class="dashboard-mini-dot bg-danger"></span>
                    <span>Avariado</span>
                  </div>
                  <strong><?= $equipamentosAvariados ?></strong>
                </div>
              </div>
            </div>
            <div class="fw-semibold text-primary my-3">
              Abrir módulo <i class="fas fa-arrow-right ms-2"></i>
            </div>
          </div>
        </a>
      </div>
    <?php endif; ?>

    <div class="col">
      <div class="row mb-4">
        <div class="col-lg-6">
          <a href="<?php echo BASEURL; ?>salas" class="text-decoration-none text-reset d-block h-100">
            <div class="quick-link-card h-100">
              <div class="icon-wrap bg-info-subtle text-info">
                <i class="fas fa-door-open"></i>
              </div>

              <h3 class="h5 fw-bold">Salas</h3>

              <p class="text-muted mb-4">
                Visualize espaços, organização física e equipamentos distribuídos por sala.
              </p>

              <div class="fw-semibold text-primary">
                Abrir módulo <i class="fas fa-arrow-right ms-2"></i>
              </div>
            </div>
          </a>
        </div>

        <div class="col-lg-6">
          <a href="<?php echo BASEURL; ?>equipamentos" class="text-decoration-none text-reset d-block h-100">
            <div class="quick-link-card h-100">
              <div class="icon-wrap bg-success-subtle text-success">
                <i class="fas fa-tools"></i>
              </div>

              <h3 class="h5 fw-bold">Equipamentos</h3>

              <p class="text-muted mb-4">
                Consulte inventário, estado, sala associada e ações rápidas do parque tecnológico.
              </p>

              <div class="fw-semibold text-primary">
                Abrir módulo <i class="fas fa-arrow-right ms-2"></i>
              </div>
            </div>
          </a>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-6">
          <a href="<?php echo BASEURL; ?>avarias" class="text-decoration-none text-reset d-block h-100">
            <div class="quick-link-card h-100">
              <div class="icon-wrap bg-warning-subtle text-warning">
                <i class="fas fa-exclamation-triangle"></i>
              </div>

              <h3 class="h5 fw-bold">Avarias</h3>

              <p class="text-muted mb-4">
                Registe ocorrências, acompanhe resolução e mantenha histórico detalhado.
              </p>

              <div class="fw-semibold text-primary">
                Abrir módulo <i class="fas fa-arrow-right ms-2"></i>
              </div>
            </div>
          </a>
        </div>
        <div class="col-lg-6">
          <a href="<?php echo BASEURL; ?>usuarios" class="text-decoration-none text-reset d-block h-100">
            <div class="quick-link-card h-100">
              <div class="icon-wrap bg-primary-subtle text-primary">
                <i class="fas fa-users"></i>
              </div>

              <h3 class="h5 fw-bold">Usuários</h3>

              <p class="text-muted mb-4">
                Ver e gerenciar usuários que estão inscritos
              </p>

              <div class="fw-semibold text-primary">
                Abrir módulo <i class="fas fa-arrow-right ms-2"></i>
              </div>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once FOOTER_TEMPLATE; ?>
