<?php

session_start();

$nome = $_SESSION['usuario_nome'];
$tipo = $_SESSION['usuario_tipo'];

?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a href="<?php echo BASEURL; ?>dashboard.php" class="navbar-brand">
      Gestão de equipamentos
    </a>

    <button class="navbar-toggler" type="button"
      data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false"
      aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="<?php echo BASEURL; ?>salas">Salas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo BASEURL; ?>equipamentos">Equipamentos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo BASEURL; ?>avarias">Avarias</a>
        </li>
        <?php if ($tipo == "Direção") : ?>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo BASEURL; ?>usuarios">Usuarios</a>
          </li>
        <?php endif; ?>
      </ul>

      <!-- Right side (moves into collapse on small screens) -->
      <div class="d-flex align-items-center ms-lg-auto mt-3 mt-lg-0">
        <span class="navbar-text text-white me-3">
          <?= htmlspecialchars($nome) ?> (<?= htmlspecialchars($tipo) ?>)
        </span>
        <a href="<?php echo BASEURL; ?>logout.php" class="btn btn-outline-danger btn-sm">
          Sair
        </a>
      </div>
    </div>
  </div>
</nav>
