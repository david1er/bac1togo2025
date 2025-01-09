<?php
/**
 * Plugin Gestion du BAC1
 * (c) 2015 Ghislain VLAVONOU
 * Licence GNU/GPL
 */

if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

/*
 * Un fichier d'autorisations permet de regrouper
 * les fonctions d'autorisations de votre plugin
 */

// declaration vide pour ce pipeline.
function configuration_autoriser()
{}

// -----------------
// Objet classes

// bouton de menu
function autoriser_configuration_menu_dist($faire, $type, $id, $qui, $opts)
{
    // list($email, $serveur) = explode('@', $GLOBALS['auteur_session']['email']);
    // if ($GLOBALS['connect_statut'] == '0minirezo'
    //     || $email == 'inspection') {
    //     return true;
    // } else {
    //     return false;
    // }
    return in_array($qui['statut'], array('0minirezo'));
}

// creer
function autoriser_configuration_creer_dist($faire, $type, $id, $qui, $opt)
{
    return in_array($qui['statut'], array('0minirezo', '1comite'));
}

// voir les fiches completes
function autoriser_configuration_voir_dist($faire, $type, $id, $qui, $opt)
{
    //return ($id == $qui['id_auteur']);
    return true;
}

// modifier
function autoriser_configuration_modifier_dist($faire, $type, $id, $qui, $opt)
{
    return $qui['statut'] == '0minirezo' and !$qui['restreint'];
}

// supprimer
function autoriser_configuration_supprimer_dist($faire, $type, $id, $qui, $opt)
{
    return autoriser('webmestre', '', '', $qui);
}
