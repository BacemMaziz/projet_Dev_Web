<?php
/**
 * Projet : Site de prévision météo pour la France
 * Module : Développement Web
 * Réalisateurs : Bacem Maziz, Hamlat Arslane
 * Tous droits réservés - © 2024
 */

declare(strict_types=1);

// Fonction pour définir un cookie sécurisé
function setThemeCookie($theme, $duration = 365) {
    setcookie(
        'user_theme',
        $theme,
        time() + ($duration * 24 * 60 * 60),
        '/',
        '',
        isset($_SERVER['HTTPS']), // Secure en HTTPS
        true // HttpOnly
    );
}

// Détermination du thème
$default_theme = 'jour'; // Thème par défaut
$theme = $default_theme;

// 1. Vérification du paramètre GET (changement immédiat)
if (isset($_GET['theme'])) {
    $theme = ($_GET['theme'] === 'nuit') ? 'nuit' : 'jour';
    if (isset($_COOKIE['cookie_mode']) && $_COOKIE['cookie_mode'] === 'true') {
        setThemeCookie($theme);
    }
} 
// 2. Sinon vérification du cookie existant
elseif (isset($_COOKIE['user_theme']) && ($_COOKIE['cookie_accepted'] === 'true')) {
    $_GET['theme'] = $_COOKIE['user_theme'];
    $theme = ($_COOKIE['user_theme'] === 'nuit') ? 'nuit' : 'jour';
}
// 3. Sinon création du cookie avec la valeur par défaut
else {
    if (isset($_COOKIE['cookie_mode']) && $_COOKIE['cookie_mode'] === 'true') {
        setThemeCookie($default_theme);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content="<?php echo htmlspecialchars($description ?? 'Site de prévision météo pour la France'); ?>"/>
    <title><?php echo htmlspecialchars($titre ?? 'ActuMeteo'); ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($theme); ?>.css" id="theme-style"/>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&amp;display=swap" rel="stylesheet"/>
    <link rel="icon" type="image/png" href="Images/favicon.png"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="script.js" ></script>
    
</head>
<body class="<?php echo htmlspecialchars($theme); ?>-theme">
    <header class="header-ultra">
        <canvas id="navCanvas" class="nav-canvas" aria-hidden="true"></canvas>
        <div class="nav-container">
            <!-- Logo avec animation -->
            <a href="index.php" class="nav-logo">
                <span class="logo-icon">⛅</span>
                <span class="logo-text">Actu<span>Meteo</span></span>
                <span class="logo-pulse"></span>
            </a>

            <!-- Bouton de bascule thème -->
            <div class="theme-switcher">
                <?php
                $params = [];
                $params['theme'] = ($theme === 'jour') ? 'nuit' : 'jour';
                if (isset($_GET['region'])) {
                    $params['region'] = $_GET['region'];
                }
                if (isset($_GET['departement'])) {
                    $params['departement'] = $_GET['departement'];
                }
                if (isset($_GET['commune'])) {
                    $params['commune'] = $_GET['commune'];
                }
                $href = '?' . http_build_query($params);
                ?>
                <a href="<?php echo htmlspecialchars($href); ?>"
                   class="theme-toggle <?php echo htmlspecialchars($theme); ?>"
                   title="Basculer en mode <?php echo ($theme === 'jour') ? 'nuit' : 'jour'; ?>">
                    <span class="theme-icon"><?php echo ($theme === 'jour') ? '🌙' : '☀️'; ?></span>
                    <span class="theme-text">Mode <?php echo ($theme === 'jour') ? 'nuit' : 'jour'; ?></span>
                </a>
            </div>

            <!-- Menu Hamburger Premium -->
            <button class="hamburger-ultra" id="hamburger" aria-label="Menu">
                <span class="line top"></span>
                <span class="line middle"></span>
                <span class="line bottom"></span>
            </button>
        </div>

        <div class="menu-ultra" id="menu">
            <button class="close-menu-btn" id="closeMenuBtn" aria-label="Fermer le menu">×</button>
            <div class="menu-content">
                <ul class="menu-list">
                    <li class="menu-item">
                        <a href="index.php" class="menu-link">
                            <span class="link-icon">🌤️</span>
                            <span class="link-text">Accueil</span>
                            <span class="link-underline"></span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="statistiques.php" class="menu-link">
                            <span class="link-icon">📊</span>
                            <span class="link-text">Statistiques</span>
                            <span class="link-underline"></span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="contact.php" class="menu-link">
                            <span class="link-icon">✉️</span>
                            <span class="link-text">Contact</span>
                            <span class="link-underline"></span>
                        </a>
                    </li>
                </ul>
                <div class="menu-footer">
                    <div class="social-icons">
                        <a href="#" class="social-icon" aria-label="Twitter">
                            <svg class="icon" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <use xlink:href="#twitter-icon"/>
                            </svg> 
                        </a>
                        <a href="#" class="social-icon" aria-label="Facebook">
                           <svg class="icon" xmlns:xlink="http://www.w3.org/1999/xlink">
                           <use xlink:href="#facebook-icon"/>
                           </svg>
                       </a>
                       <a href="#" class="social-icon" aria-label="Instagram">
                          <svg class="icon" xmlns:xlink="http://www.w3.org/1999/xlink">
                          <use xlink:href="#instagram-icon"/></svg>
                       </a>
                    </div>
                    <p class="copyright">© 2025 ActuMeteo Pro</p>
                </div>
            </div>
        </div>

        <div class="overlay-ultra" id="overlay">

        </div>
        <div class="weather-progress">
            <div class="weather-track">
                <div class="weather-indicator" data-weather="sunny">

                </div>
            </div>
        </div>
</header>