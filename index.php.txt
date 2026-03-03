<?php

require_once "config.php";
require_once HEADER_TEMPLATE;
session_start();

if (isset($_SESSION["usuario_id"])) {
  header("location: dashboard.php");
  exit();
}

?>

<style>
  html, body {
    height: 100%;
    margin: 0;
  }

  body.index-login-signup {
    overflow: hidden;

    background-image:
      linear-gradient(
        rgba(0, 0, 0, 0.5),
        rgba(0, 0, 0, 0.5)
      ),
      url('<?php echo BASEURL; ?>images/aetc.jpg');

    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
  }
</style>

<div class="d-flex justify-content-center align-items-center vh-100">
  <div class="card p-4 shadow" style="width: 350px;">
    <h3 class="text-center mb-4">Bem-vindo</h3>

    <a href="<?php echo BASEURL; ?>login.php" class="btn btn-primary mb-2 w-100">Login</a>
    <a href="<?php echo BASEURL; ?>signup.php" class="btn btn-outline-secondary w-100">Criar Conta</a>
  </div>
</div>

</body>
</html>