<!doctype html>
<html lang="pt">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' · ' : '' ?>AETC Equips</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo BASEURL; ?>css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo BASEURL; ?>css/fontawesome-free-5.15.4-web/css/all.min.css">
  <link rel="stylesheet" href="<?php echo BASEURL; ?>css/styles.css">
</head>
<?php $body_class = $body_class ?? 'app-body'; ?>
<body class="<?= htmlspecialchars($body_class) ?>">
