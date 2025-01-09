<?php
if (!defined("_ECRIRE_INC_VERSION")) return;
$GLOBALS['mysql_rappel_nom_base'] = false; /* echec de test_rappel_nom_base_mysql a l'installation. */
defined('_MYSQL_SET_SQL_MODE') || define('_MYSQL_SET_SQL_MODE',true);
$GLOBALS['spip_connect_version'] = 0.8;
spip_connect_db('localhost','','root','','bac12024','mysql', '','','');
?>