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
          <a href="<?php echo BASEURL; ?>salas" class="btn btn-primary w-100">Acessar</a>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
          <h5 class="card-title">Equipamentos</h5>
          <p class="card-text">
            Gerenciar os equipamentos.
          </p>
          <a href="<?php echo BASEURL; ?>equipamentos" class="btn btn-primary w-100">Acessar</a>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
          <h5 class="card-title">Avarias</h5>
          <p class="card-text">
            Visualizar histórico de avarias.
          </p>
          <div class="row d-flex justify-content-center px-2">
            <a href="<?php echo BASEURL ?>avarias" class="btn btn-primary rounded-end-0 col">Acessar</a>
            <a href="<?php echo BASEURL ?>avarias/reportar.php" class="btn btn-warning rounded-start-0 col">Reportar</a>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

</body>

</html>
