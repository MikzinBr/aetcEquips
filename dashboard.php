<?php
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

$tipo_usuario = $_SESSION['usuario_tipo'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Utilizador';
?>

<div class="container-fluid px-0" style="width: 90vw;">
  <section class="page-hero m-4">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <span class="badge bg-white text-primary mb-3 px-3 py-2">Painel principal</span>
        <h1 class="display-6 fw-bold mb-2">Olá, <?= htmlspecialchars($nome_usuario) ?>.</h1>
        <p class="mb-0">Aceda rapidamente aos módulos do sistema e acompanhe a gestão de equipamentos, salas, avarias e perfis com uma navegação muito mais clara.</p>
      </div>
    </div>
  </section>

  <div class="row g-4">
    <div class="col-lg-<?= $tipo_usuario == "Direção" ? "4" : "6" ?> col-xl-<?= $tipo_usuario == "Direção" ? "3" : "4" ?>">
      <a href="<?php echo BASEURL; ?>equipamentos" class="text-decoration-none text-reset d-block h-100">
        <div class="quick-link-card h-100">
          <div class="icon-wrap bg-success-subtle text-success"><i class="fas fa-tools"></i></div>
          <h3 class="h5 fw-bold">Equipamentos</h3>
          <p class="text-muted mb-4">Consulte inventário, estado, sala associada e ações rápidas do parque tecnológico.</p>
          <div class="fw-semibold text-primary">Abrir módulo <i class="fas fa-arrow-right ms-2"></i></div>
        </div>
      </a>
    </div>

    <div class="col-lg-<?= $tipo_usuario == "Direção" ? "4" : "6" ?> col-xl-<?= $tipo_usuario == "Direção" ? "3" : "4" ?>">
      <a href="<?php echo BASEURL; ?>avarias" class="text-decoration-none text-reset d-block h-100">
        <div class="quick-link-card h-100">
          <div class="icon-wrap bg-warning-subtle text-warning"><i class="fas fa-exclamation-triangle"></i></div>
          <h3 class="h5 fw-bold">Avarias</h3>
          <p class="text-muted mb-4">Registe ocorrências, acompanhe resolução e mantenha histórico detalhado.</p>
          <div class="fw-semibold text-primary">Abrir módulo <i class="fas fa-arrow-right ms-2"></i></div>
        </div>
      </a>
    </div>

    <div class="col-lg-<?= $tipo_usuario == "Direção" ? "4" : "6" ?> col-xl-<?= $tipo_usuario == "Direção" ? "3" : "4" ?>">
      <a href="<?php echo BASEURL; ?>salas" class="text-decoration-none text-reset d-block h-100">
        <div class="quick-link-card h-100">
          <div class="icon-wrap bg-info-subtle text-info"><i class="fas fa-door-open"></i></div>
          <h3 class="h5 fw-bold">Salas</h3>
          <p class="text-muted mb-4">Visualize espaços, organização física e equipamentos distribuídos por sala.</p>
          <div class="fw-semibold text-primary">Abrir módulo <i class="fas fa-arrow-right ms-2"></i></div>
        </div>
      </a>
    </div>

    <?php if ($tipo_usuario === 'Direção') : ?>
      <div class="col-lg-6 col-xl-3">
        <a href="<?php echo BASEURL; ?>direcao" class="text-decoration-none text-reset d-block h-100">
          <div class="quick-link-card h-100">
            <div class="icon-wrap bg-primary-subtle text-primary"><i class="fas fa-chart-pie"></i></div>
            <h3 class="h5 fw-bold">Painel da Direção</h3>
            <p class="text-muted mb-4">Indicadores visuais, resumo executivo e apoio à decisão para a direção.</p>
            <div class="fw-semibold text-primary">Abrir módulo <i class="fas fa-arrow-right ms-2"></i></div>
          </div>
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once FOOTER_TEMPLATE; ?>
