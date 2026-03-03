<?php require_once 'config.php'; ?>
<?php require_once HEADER_TEMPLATE ?>
<?php $erro = $_GET['erro'] ?? ''; ?>

<style>
  html,
  body {
    height: 100%;
    margin: 0;
  }

  body.index-login-signup {
    overflow: hidden;

    background-image:
      linear-gradient(rgba(0, 0, 0, 0.5),
        rgba(0, 0, 0, 0.5)),
      url('<?php echo BASEURL; ?>images/aetc.jpg');

    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
  }
</style>

<div class="d-flex justify-content-center align-items-center vh-100">
  <div class="card p-4 shadow" style="width: 350px;">

    <?php if ($erro) : ?>
      <div class="alert alert-danger">
        <?= htmlspecialchars($erro) ?>
      </div>
    <?php endif; ?>

    <h4 class="text-center mb-3">Login</h4>

    <form action="<?php echo BASEURL; ?>process_login.php" method="POST">
      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Senha</label>
        <input type="password" name="senha" class="form-control" required>
      </div>

      <button class="btn btn-primary w-100">Entrar</button>
    </form>
  </div>
</div>

</body>

</html>
