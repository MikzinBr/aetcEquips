<?php

/** O nome da base de dados*/
define('DB_NAME', 'escola_equipamentos');

/** Utilizador da base de dados MySQL */
define('DB_USER', 'root');

/** Password da base de dados MySQL */
define('DB_PASSWORD', '');

/** nome do host do MySQL */
define('DB_HOST', 'localhost');

/** caminho absoluto para a pasta do sistema */
if (!defined('ABSPATH')) {
  define('ABSPATH', dirname(__FILE__) . '/');
}

/** caminho no server para o sistema **/
if (!defined('BASEURL')) {
  // Define o BASEURL automaticamente com base na pasta do projeto.
  // Ex.: http://localhost/aetcEquips-master/
  // Se você colocar o projeto diretamente na raiz do servidor, o BASEURL vira '/'.
  $projectFolder = basename(__DIR__);
  define('BASEURL', ($projectFolder ? '/' . $projectFolder . '/' : '/'));
}

/** caminho do arquivo de base de dados **/
if (!defined('DBAPI')) {
  define('DBAPI', ABSPATH . 'inc/database.php');
}

define('HEADER_TEMPLATE', ABSPATH . 'inc/header.php');
define('NAVBAR_TEMPLATE', ABSPATH . 'inc/navbar.php');
