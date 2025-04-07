<?php
// util.inc.php

/**
 * Retourne le nom du navigateur de l'utilisateur.
 * @return string Le nom du navigateur.
 */
function get_navigateur() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Détection du navigateur
    if (strpos($user_agent, 'Firefox') !== false) {
        return 'Mozilla Firefox';
    } elseif (strpos($user_agent, 'Chrome') !== false) {
        return 'Google Chrome';
    } elseif (strpos($user_agent, 'Safari') !== false) {
        return 'Apple Safari';
    } elseif (strpos($user_agent, 'Opera') !== false || strpos($user_agent, 'OPR') !== false) {
        return 'Opera';
    } elseif (strpos($user_agent, 'Edge') !== false) {
        return 'Microsoft Edge';
    } elseif (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Trident') !== false) {
        return 'Internet Explorer';
    } else {
        return 'Navigateur inconnu';
    }
}
?>