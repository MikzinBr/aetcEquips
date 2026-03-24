<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$nome = $_SESSION['usuario_nome'] ?? '';
$tipo = $_SESSION['usuario_tipo'] ?? '';

// Detectar rota ativa
$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$path = rtrim($path, '/');

function is_active($path, $needle): bool
{
  $base = rtrim(BASEURL, '/');
  return $needle === 'dashboard'
    ? (str_ends_with($path, 'dashboard.php') || ($base && str_ends_with($path, $base)))
    : (strpos($path, $needle) !== false);
}

$active_dashboard = is_active($path, 'dashboard');
$active_equip = is_active($path, 'equipamentos');
$active_avarias = is_active($path, 'avarias');
$active_salas = is_active($path, 'salas');

?>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg bg-light fixed-top shadow-sm">
  <div class="container-fluid">

    <!-- Logo -->
    <a class="navbar-brand">
      <img 
      href="<?= BASEURL ?>dashboard.php" 
      src= "<?= BASEURL ?>/images/logotipo-agr-tc-pt-vertical.png"
      height= "50"
      >
      </img>
    </a>


    <!-- Botão mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Conteúdo -->
    <div class="collapse navbar-collapse justify-content-center" id="navbarNav">

      <!-- MENU PILL -->
      <ul class="navbar-nav nav-pill-custom">

        <?php if ($tipo === 'Direção') : ?>
          <li class="nav-item">
            <a class="nav-link <?= is_active($path, 'direcao') ? 'active' : '' ?>" href="<?= BASEURL ?>direcao">
              <i class="fas fa-chart-pie"></i> Painel
            </a>
          </li>
          
          <li class="nav-item">
            <a class="nav-link <?= is_active($path, 'usuarios') ? 'active' : '' ?>" href="<?= BASEURL ?>usuarios">
              <i class="fas fa-users"></i> Usuários
            </a>
          </li>
        <?php endif; ?>

        <li class="nav-item">
          <a class="nav-link <?= $active_dashboard ? 'active' : '' ?>" href="<?= BASEURL ?>dashboard.php">
            <i class="fas fa-home"></i> Início
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= $active_equip ? 'active' : '' ?>" href="<?= BASEURL ?>equipamentos">
            <i class="fas fa-tools"></i> Equipamentos
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= $active_avarias ? 'active' : '' ?>" href="<?= BASEURL ?>avarias">
            <i class="fas fa-exclamation-triangle"></i> Avarias
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= $active_salas ? 'active' : '' ?>" href="<?= BASEURL ?>salas">
            <i class="fas fa-door-open"></i> Salas
          </a>
        </li>

      </ul>

      <!-- Utilizador -->
      <div class="position-absolute end-0 me-3 d-flex align-items-center gap-3">
        <span class="text-dark small">
          <?= htmlspecialchars($nome) ?>
          <?= $tipo ? ' (' . htmlspecialchars($tipo) . ')' : '' ?>
        </span>

        <a href="<?= BASEURL ?>logout.php" class="btn btn-outline-danger btn-sm">
          Sair
        </a>
      </div>

    </div>
  </div>
</nav>

<!-- ESPAÇO PARA NÃO SOBREPOR -->
<div style="height: 80px;"></div>

<!-- CSS -->
<style>
.nav-pill-custom {
  background: #e9e9e9;
  border-radius: 50px;
  padding: 6px;
  display: flex;
  gap: 6px;
}

.nav-pill-custom .nav-link {
  border-radius: 50px;
  padding: 8px 18px;
  color: #333;
  font-weight: 500;
  transition: all 0.3s ease;
}

.nav-pill-custom .nav-link:hover {
  background: #dcdcdc;
}

.nav-pill-custom .nav-link.active {
  background: #007bff;
  color: #fff;
  font-weight: 600;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
</style>