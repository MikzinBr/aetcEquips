<?php
require_once 'config.php';
require_once './inc/database.php';
require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

$page_title = 'Bem-vindo ao AETC Equips';
$page_subtitle = 'Selecione uma opção para continuar';

if (!isset($_SESSION['usuario_id'])) {
  header("Location: index.php");
  exit;
}

$tipo_usuario = $_SESSION["usuario_tipo"];

?>

<div class="row g-4">
  <div class="col-lg-4">
    <a href="<?php echo BASEURL; ?>equipamentos" class="text-decoration-none text-reset">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex gap-3 align-items-start">
          <div class="p-3 rounded-3 bg-success-subtle text-success">
            <i class="fas fa-tools"></i>
          </div>
          <div>
            <div class="fw-semibold">Equipamentos</div>
            <div class="text-muted small">Ver todos os equipamentos registados</div>
          </div>
        </div>
      </div>
    </a>
  </div>

  <div class="col-lg-4">
    <a href="<?php echo BASEURL; ?>avarias" class="text-decoration-none text-reset">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex gap-3 align-items-start">
          <div class="p-3 rounded-3 bg-warning-subtle text-warning">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
          <div>
            <div class="fw-semibold">Avarias</div>
            <div class="text-muted small">Reportar e consultar avarias</div>
          </div>
        </div>
      </div>
    </a>
  </div>

  <div class="col-lg-4">
    <a href="<?php echo BASEURL; ?>salas" class="text-decoration-none text-reset">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex gap-3 align-items-start">
          <div class="p-3 rounded-3 bg-info-subtle text-info">
            <i class="fas fa-door-open"></i>
          </div>
          <div>
            <div class="fw-semibold">Salas</div>
            <div class="text-muted small">Ver salas e os seus equipamentos</div>
          </div>
        </div>
      </div>
    </a>
  </div>
</div>

<?php require_once FOOTER_TEMPLATE; ?>
