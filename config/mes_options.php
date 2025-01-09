<?php
// //appel de lecran de securite spip
// @include_once dirname(__FILE__).'/ecran_securite.php';

// // Eviter de nettoyer les flux RSS pour les entrees > 1 an
// $controler_dates_rss = false;

// // pour réduire fortement les temps de réponse du site,
// // ne pas lancer le cron par fsockopen/cURL
// // car la configuration du serveur ne le permet pas.
// define('_HTML_BG_CRON_FORCE', TRUE);

// Pour que l'interface privée de SPIP occupe 95% de la largeur de l'écran
define('_PETIT_ECRAN', '95%'); // Valeur par défaut : 960px
define('_GRAND_ECRAN', '95%'); // Valeur par défaut : 1280px

?>