<?php
require_once 'config.php';
require_once HEADER_TEMPLATE;
require_once NAVBAR_TEMPLATE;

if (!isset($_SESSION['usuario_id'])) {
  header("Location: login.php");
  exit;
}

$tipo_usuario = $_SESSION["usuario_tipo"];

?>

<div class="container mt-4">

  <div class="alert alert-success">
    Bem-vindo ao sistema, <strong><?= htmlspecialchars($nome) ?></strong>!
  </div>

  <div class="row g-4">

    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
          <h5 class="card-title">Salas</h5>
          <p class="card-text">
            Gerenciar as salas da escola.
          </p>
          <a href="<?php echo BASEURL; ?>salas" class="btn btn-success w-100">Acessar</a>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
          <h5 class="card-title">Equipamentos</h5>
          <p class="card-text">
            Visualizar e organizar os equipamentos.
          </p>
          <a href="<?php echo BASEURL; ?>equipamentos" class="btn btn-primary w-100">Acessar</a>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
          <?php if ($tipo_usuario === "Professor") : ?>
            <h5 class="card-title">Reportar Problema</h5>
            <p class="card-text">
              Informar avarias em equipamentos.
            </p>
            <a href="<?php echo BASEURL ?>avarias/reportar.php" class="btn btn-warning w-100">Reportar</a>
          <?php else : ?>
            <h5 class="card-title">Ver avarias</h5>
            <p class="card-text">
              Visualizar histórico de avarias.
            </p>
            <a href="<?php echo BASEURL ?>avarias" class="btn btn-primary w-100">Acessar</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>

</body>

</html>
