<?php
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$nome = $_SESSION['usuario_nome'] ?? '';
$tipo = $_SESSION['usuario_tipo'] ?? '';
$foto = $_SESSION['usuario_foto'] ?? '';

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
$active_profile = is_active($path, 'profile.php');
$sessionUser = ['foto' => $foto];
?>

<nav class="navbar navbar-expand-xl app-navbar fixed-top">
  <div class="container-fluid app-navbar-inner">
    <a class="navbar-brand d-flex align-items-center gap-3" href="<?= BASEURL ?>dashboard.php">
        <img src="<?= BASEURL ?>/images/logotipo-agr-tc-pt-vertical.png" height="42" alt="AETC">
    </a>

    <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav nav-pill-custom mx-auto">
        <?php if ($tipo === 'Direção') : ?>
          <li class="nav-item">
            <a class="nav-link <?= is_active($path, 'direcao') ? 'active' : '' ?>" href="<?= BASEURL ?>direcao"><i class="fas fa-chart-pie"></i><span>Painel</span></a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= is_active($path, 'usuarios') ? 'active' : '' ?>" href="<?= BASEURL ?>usuarios"><i class="fas fa-users"></i><span>Perfis</span></a>
          </li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link <?= $active_dashboard ? 'active' : '' ?>" href="<?= BASEURL ?>dashboard.php"><i class="fas fa-home"></i><span>Início</span></a></li>
        <li class="nav-item"><a class="nav-link <?= $active_equip ? 'active' : '' ?>" href="<?= BASEURL ?>equipamentos"><i class="fas fa-tools"></i><span>Equipamentos</span></a></li>
        <li class="nav-item"><a class="nav-link <?= $active_avarias ? 'active' : '' ?>" href="<?= BASEURL ?>avarias"><i class="fas fa-exclamation-triangle"></i><span>Avarias</span></a></li>
        <li class="nav-item"><a class="nav-link <?= $active_salas ? 'active' : '' ?>" href="<?= BASEURL ?>salas"><i class="fas fa-door-open"></i><span>Salas</span></a></li>
      </ul>

      <div class="app-user-box ms-xl-3 mt-3 mt-xl-0">
        <a href="<?= BASEURL ?>usuarios/profile.php?id=<?= (int)($_SESSION['usuario_id'] ?? 0) ?>" class="user-chip text-decoration-none <?= $active_profile ? 'active' : '' ?>">
          <img src="<?= htmlspecialchars(get_avatar_url($sessionUser)) ?>" alt="Perfil" class="user-chip-avatar">
          <span class="user-chip-copy">
            <strong><?= htmlspecialchars($nome) ?></strong>
            <small><?= $tipo ? htmlspecialchars($tipo) : 'Utilizador' ?></small>
          </span>
        </a>
        <a href="<?= BASEURL ?>logout.php" class="btn btn-danger-soft btn-sm ms-2"><i class="fas fa-sign-out-alt me-1"></i>Sair</a>
      </div>
    </div>
  </div>
</nav>

<div class="app-top-spacer"></div>
