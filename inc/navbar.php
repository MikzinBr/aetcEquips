<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$nome = $_SESSION['usuario_nome'] ?? '';
$tipo = $_SESSION['usuario_tipo'] ?? '';

// Basic active-route detection
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

$section_title = $page_title ?? (
  $active_equip ? 'Equipamentos' : (
    $active_avarias ? 'Avarias' : (
      $active_salas ? 'Salas' : 'Início'
    )
  )
);

$section_subtitle = $page_subtitle ?? (
  $active_equip ? 'Inventário de equipamentos' : (
    $active_avarias ? 'Reportar e consultar avarias' : (
      $active_salas ? 'Espaços com equipamentos agrupados' : 'Selecione uma opção para continuar'
    )
  )
);

?>

<div class="d-flex" style="min-height: 100vh;">
  <!-- Sidebar -->
  <aside class="bg-dark text-white d-flex flex-column p-3" style="width: 260px;">
    <div class="d-flex align-items-center mb-3">
      <div class="rounded-circle bg-danger me-2" style="width: 10px; height: 10px;"></div>
      <span class="fw-semibold">AETC EQUIPS</span>
    </div>

    <nav class="nav nav-pills flex-column gap-1">
      <a class="nav-link text-white <?= $active_dashboard ? 'active' : '' ?>" href="<?= BASEURL ?>dashboard.php">
        <i class="fas fa-home me-2"></i>
        Início
      </a>
      <a class="nav-link text-white <?= $active_equip ? 'active' : '' ?>" href="<?= BASEURL ?>equipamentos">
        <i class="fas fa-tools me-2"></i>
        Equipamentos
      </a>
      <a class="nav-link text-white <?= $active_avarias ? 'active' : '' ?>" href="<?= BASEURL ?>avarias">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Avarias
      </a>
      <a class="nav-link text-white <?= $active_salas ? 'active' : '' ?>" href="<?= BASEURL ?>salas">
        <i class="fas fa-door-open me-2"></i>
        Salas
      </a>

      <?php if ($tipo === 'Direção') : ?>
        <a class="nav-link text-white <?= is_active($path, 'usuarios') ? 'active' : '' ?>" href="<?= BASEURL ?>usuarios">
          <i class="fas fa-users me-2"></i>
          Usuários
        </a>
      <?php endif; ?>
    </nav>

    <div class="mt-auto pt-3">
      <div class="d-flex align-items-center gap-2 border-bottom border-secondary mb-2">
        <span class="text-light medium d-none d-md-inline">
          <?= htmlspecialchars($nome) ?><?= $tipo ? ' (' . htmlspecialchars($tipo) . ')' : '' ?>
        </span>
      </div>
      <a href="<?= BASEURL ?>logout.php" class="btn btn-outline-danger w-100 btn-sm">
        <i class="fas fa-sign-out-alt me-2"></i>
        Sair
      </a>
    </div>
  </aside>

  <!-- Main -->
  <div class="flex-grow-1">
    <header class="bg-white border-bottom">
      <div class="container-fluid py-3 d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Gestão de Equipamentos</div>
          <div class="h5 mb-0"><?= htmlspecialchars($section_title) ?></div>
          <div class="text-muted small"><?= htmlspecialchars($section_subtitle) ?></div>
        </div>
      </div>
    </header>

    <main class="container-fluid py-4">
