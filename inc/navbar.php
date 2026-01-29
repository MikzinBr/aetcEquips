<?php

session_start();

$nome = $_SESSION['usuario_nome'];
$tipo = $_SESSION['usuario_tipo'];

?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a href="<?php echo BASEURL; ?>dashboard.php" class="navbar-brand">Gestão de equipamentos</a>

        <div class="d-flex">
            <span class="navbar-text text-white me-3">
                <?= htmlspecialchars($nome) ?> (<?= htmlspecialchars($tipo) ?>)
            </span>
            <a href="<?php echo BASEURL; ?>logout.php" class="btn btn-outline-danger btn-sm">
                Sair
            </a>
        </div>
    </div>
</nav>