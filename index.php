<?php
/**
 * Projet : Site de prévision météo pour la France
 * Module : Développement Web
 * Réalisateurs : Bacem Maziz, Hamlat Arslane
 * Tous droits réservés - © 2024
 */

declare(strict_types=1);
ini_set('memory_limit', '512M');

require_once './include/functions.php';

/**
 * @brief Traite les actions liées aux préférences de cookies.
 * 
 * Ce bloc vérifie si une requête POST est envoyée avec une action spécifique (`acceptAll`, `rejectAll`, `savePreferences`),
 * puis met à jour les cookies en conséquence.
 * 
 * @section Actions
 * - **acceptAll** : Accepte tous les cookies (stockage de la ville et du mode).
 * - **rejectAll** : Rejette tous les cookies sauf l'acceptation minimale.
 * - **savePreferences** : Enregistre les préférences personnalisées (mode sombre/ville).
 * 
 * @section Cookies
 * - `cookie_accepted` : Indique si l'utilisateur a accepté les cookies (durée : 5 jours).
 * - `cookie_mode` : Stocke la préférence du mode sombre/clair (durée : 5 jours).
 * - `cookie_ville` : Stocke l'autorisation de sauvegarder la ville (durée : 5 jours).
 * 
 * @note Redirige vers la même page après traitement pour éviter la resoumission du formulaire.
 * @warning Les cookies sont définis avec un chemin racine (`/`) pour une accessibilité globale.
 */

 function deleteAllDomainCookies() {
    if (isset($_SERVER['HTTP_COOKIE'])) {
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            setcookie($name, '', time() - 3600, '/');
            setcookie($name, '', time() - 3600, '/', $_SERVER['HTTP_HOST']);
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'acceptAll':
            deleteAllDomainCookies();
            setcookie('cookie_accepted', 'true', time() + (5 * 24 * 60 * 60), "/");
            setcookie('cookie_mode', 'true', time() + (5 * 24 * 60 * 60), "/");
            setcookie('cookie_ville', 'true', time() + (5 * 24 * 60 * 60), "/");
            break;
        case 'rejectAll':
            deleteAllDomainCookies();
            setcookie('cookie_accepted', 'false', time() + (5 * 24 * 60 * 60), "/");
            setcookie('cookie_mode', 'false', time() + (5 * 24 * 60 * 60), "/");
            setcookie('cookie_ville', 'false', time() + (5 * 24 * 60 * 60), "/");
            break;
        case 'savePreferences':
            deleteAllDomainCookies();

            $mode = isset($_POST['mode']) ? 'true' : 'false';
            $ville = isset($_POST['ville']) ? 'true' : 'false';
            if ($ville === 'true' || $mode === 'true') {
                setcookie('cookie_accepted', 'true', time() + (5 * 24 * 60 * 60), "/");
            } else {
                setcookie('cookie_accepted', 'false', time() + (5 * 24 * 60 * 60), "/");
            }
            setcookie('cookie_mode', $mode, time() + (5 * 24 * 60 * 60), "/");
            setcookie('cookie_ville', $ville, time() + (5 * 24 * 60 * 60), "/");
            break;
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
/**
 * @brief Gère la commune sélectionnée par l'utilisateur et sa persistance via cookie
 * 
 * Ce bloc de code traite la sélection d'une commune via le paramètre GET 'commune'.
 * Il vérifie également si l'utilisateur a autorisé la sauvegarde des préférences de localisation.
 * 
 * @section Fonctionnement
 * 1. Récupère le paramètre GET 'commune'
 * 2. Charge les données de la commune via getCommuneData()
 * 3. Si l'utilisateur a accepté les cookies de localisation (cookie_ville = true),
 *    sauvegarde la dernière commune visitée via setLastVisitedCity()
 * 
 * @param string $_GET['commune'] Identifiant de la commune sélectionnée
 * 
 * @see getCommuneData() Pour la récupération des données de la commune
 * @see setLastVisitedCity() Pour la sauvegarde de la commune dans les cookies
 * 
 * @note La persistance de la commune dépend du consentement cookie (cookie_ville)
 * @warning Nécessite que getCommuneData() soit définie et retourne un tableau avec au moins une clé 'name'
 */
// Gestion de la commune sélectionnée
if (isset($_GET['commune'])) {
    $communeData = getCommuneData($_GET['commune']);
    if ($communeData) {
        $commune = $communeData['name'];
        if (isset($_COOKIE['cookie_ville']) && $_COOKIE['cookie_ville'] === 'true') {
            setLastVisitedCity($commune);
        }
    }
}

$titre = "Accueil";
$description = "Bienvenue sur notre site dédié à la météo en France.";

/**
 * @brief Détection automatique de la localisation et récupération des données météo
 * 
 * Ce bloc tente de déterminer la localisation de l'utilisateur via son adresse IP
 * et récupère les données météo correspondantes.
 * 
 * @section Workflow
 * 1. Récupère l'IP du visiteur
 * 2. Interroge le service ipinfo.io pour la géolocalisation
 * 3. Si réussite, charge les données météo via getWeatherForecast()
 * 4. Structure les données dans $localWeather
 * 
 * @section Sécurité
 * - Utilise HTTPS pour l'appel à ipinfo.io
 * - Logge les erreurs sans les afficher à l'utilisateur
 * - Le token API est masqué dans la doc générée
 * 
 * @var array|null $localWeather Structure des données météo locales :
 *   [
 *     'city' => string,    // Ville détectée
 *     'region' => string,  // Région/État
 *     'weather' => array   // Données météo de getWeatherForecast()
 *   ]
 */
$localWeather = null;
try {
    $ip = $_SERVER['REMOTE_ADDR'];
    $geo = json_decode(file_get_contents("https://ipinfo.io/{$ip}?token=37ee27659f0f07"));
    if ($geo && isset($geo->city)) {
        $weatherData = getWeatherForecast($geo->city);
        $localWeather = [
            'city' => $geo->city,
            'region' => $geo->region,
            'weather' => $weatherData
        ];
    }
} catch (Exception $e) {
    error_log("Erreur géolocalisation: " . $e->getMessage());
}

/**
 * @brief Récupération et traitement des données de la dernière ville visitée
 * 
 * Ce bloc gère la lecture du cookie 'last_visited_city' et la récupération 
 * des données météo actualisées pour cette ville.
 * 
 * @section Workflow
 * 1. Vérifie l'existence du cookie 'last_visited_city'
 * 2. Décodage des données JSON du cookie
 * 3. Si valide, récupère les nouvelles données météo
 * 4. Structure les données dans $cookieWeather
 * 
 * @var array|null $cookieWeather Structure des données météo sauvegardées :
 *   [
 *     'city' => string,    // Nom de la ville
 *     'date' => string,    // Date de la dernière visite (format ISO)
 *     'weather' => array   // Données météo actualisées
 *   ]
 */
$cookieWeather = null;
if (isset($_COOKIE['last_visited_city'])&& $_COOKIE['cookie_accepted'] === 'true') {
    try {
        $lastVisit = json_decode($_COOKIE['last_visited_city'], true);
        if ($lastVisit && isset($lastVisit['city'])) {
            $cookieWeatherData = getWeatherForecast($lastVisit['city'], '7f5fd2ac83464be9849115713252004');
            $cookieWeather = [
                'city' => $lastVisit['city'],
                'date' => $lastVisit['date'],
                'weather' => $cookieWeatherData
            ];
        }
    } catch (Exception $e) {
        error_log("Erreur lecture cookie: " . $e->getMessage());
    }
}



/**
 * @file Page principale du site ActuMeteo
 * @brief Gère l'affichage des données météo et l'interface utilisateur
 * @details Cette page inclut :
 * - La gestion des préférences de cookies
 * - L'affichage de bannières dynamiques
 * - La géolocalisation automatique
 * - La carte interactive des régions françaises
 * - L'affichage des données météo
 */


$region_id = $_GET['region'] ?? null;
$regions = getRegions();
$region_name = $_GET['region'] ?? null;
$currentBanner = getRandomBanner();

require_once './include/header.inc.php';
?>
<main class="main-content">
    <!-- Modal de gestion des cookies -->
    <div id="cookieModal" style="display: <?php echo (!isset($_COOKIE['cookie_accepted']))? 'block' : 'none'; ?>;">
        <h3>Gestion des cookies</h3>
        <p>Nous utilisons des cookies pour améliorer votre expérience. Choisissez ce que vous souhaitez activer.</p>
        <form method="POST">
            <div class="cookie-buttons">
                <button name="action" value="acceptAll">Accepter tout</button>
                <button name="action" value="rejectAll">Refuser tout</button>
                <button type="button" onclick="document.getElementById('personalizeOptions').style.display = 'block'">Personnaliser</button>
            </div>
            <div id="personalizeOptions" style="display: none;">
                <label><input type="checkbox" name="mode" <?php echo (isset($_COOKIE['cookie_mode']) && $_COOKIE['cookie_mode'] === 'true') ? 'checked="checked"' : ''; ?>/> Mode jour/nuit</label>
                <label><input type="checkbox" name="ville" <?php echo (isset($_COOKIE['cookie_ville']) && $_COOKIE['cookie_ville'] === 'true') ? 'checked="checked"' : ''; ?>/> Sauvegarder ma dernière ville visitée</label>
                <button name="action" value="savePreferences">Enregistrer mes préférences</button>
            </div>
        </form>
    </div>

    <!-- Bannière dynamique -->
    <div class="full-width-banner">
        <img src="<?php echo htmlspecialchars($currentBanner['path']); ?>" 
             alt="Météo: <?php echo htmlspecialchars($currentBanner['name']); ?>"
             class="dynamic-banner"/>
        <div class="banner-overlay">
            <h1>ActuMeteo</h1>
        </div>
    </div>

    <!-- Section météo automatique -->
    <?php if ($localWeather && !isset($_GET['region'])): ?>
        <?php
        $weatherDataToDisplay = $cookieWeather ?? $localWeather;
        $isAutoLocation = ($weatherDataToDisplay === $localWeather);
        ?>
        <section id="auto-weather-section">
            <div id="auto-weather-header">
                <h2 id="auto-weather-title">Météo à <?php echo htmlspecialchars($weatherDataToDisplay['city']); ?></h2>
                <p id="auto-location-badge">
                    <span class="location-icon"><?php echo $isAutoLocation ? '📍' : '🕒'; ?></span>
                    <span class="location-text">
                        <?php echo $isAutoLocation ? 'Position détectée automatiquement' : 'Dernière ville consultée'; ?>
                    </span>
                    <?php if (!$isAutoLocation): ?>
                        <span class="location-date">
                            (le <?php echo date('d/m/Y', strtotime($weatherDataToDisplay['date'])); ?>)
                        </span>
                    <?php endif; ?>
                </p>
            </div>
            <?php
            if (is_array($weatherDataToDisplay['weather'])) {
                displayWeatherForecast($weatherDataToDisplay['weather'], true);
            } else {
                echo '<div class="weather-error">Impossible de récupérer les données météo.</div>';
            }
            ?>
        </section>
    <?php endif; ?>

    <!-- Carte de France ou région -->
    <?php if (!$region_id): ?>
        <div class="france-map-container">
            <img src="Images/france.png" alt="Carte des régions de France" usemap="#francemap" id="main-france-map"/>
            <map name="francemap" id="francemap">
                <area shape="rect" coords="360,555,424,611" alt="Guadeloupe" href="?region=Guadeloupe&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Guadeloupe"/>
                <area shape="rect" coords="490,568,525,610" alt="Martinique" href="?region=Martinique&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Martinique/"/>
                <area shape="poly" coords="102,432,158,475,168,499,107,600,13,602,40,543,23,501,16,461,24,442,45,414" alt="Guyane" href="?region=Guyane&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Guyane"/>
                <area shape="rect" coords="432,565,482,612" alt="La Réunion" href="?region=La-Réunion&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="La Réunion"/>
                <area shape="rect" coords="531,584,554,611" alt="Mayotte" href="?region=Mayotte&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Mayotte"/>
                <area shape="poly" coords="344,195,332,184,317,186,303,167,297,148,304,140,321,138,334,142,360,144,371,157,371,183,357,178,354,187" alt="Île-de-France" href="?region=Île-de-France&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Île-de-France"/>
                <area shape="poly" coords="326,193,352,201,347,250,353,268,328,292,288,295,272,274,264,262,252,261,240,249,247,231,275,207,271,186,276,166,292,160,315,195,302,186,359,205,346,216" alt="Centre-Val de Loire" href="?region=Centre-Val_de_Loire&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Centre-Val de Loire"/>
                <area shape="poly" coords="390,281,364,275,357,244,359,224,365,214,358,187,377,184,385,204,402,212,427,209,441,231,459,229,480,210,498,214,513,225,509,246,496,261,485,279,470,301,461,306,445,289,428,295,419,303,400,309,399,296" alt="Bourgogne-Franche-Comté" href="?region=Bourgogne-Franche-Comté&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Bourgogne-Franche-Comté"/>
                <area shape="poly" coords="184,105,157,106,179,172,215,172,237,171,259,185,267,178,267,158,289,153,291,138,300,130,299,112,300,99,291,89,241,110,234,129" alt="Normandie" href="?region=Normandie&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Normandie"/>
                <area shape="poly" coords="314,27,299,39,300,76,300,87,312,112,310,134,332,135,352,136,376,150,377,127,394,117,400,94,397,75,382,75,377,65,360,64,360,49,342,45,332,30" alt="Hauts-de-France" href="?region=Hauts-de-France&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Hauts-de-France"/>
                <area shape="poly" coords="496,123,488,121,472,116,463,120,453,112,438,104,432,111,430,81,421,93,410,92,405,113,400,129,388,132,378,178,397,204,432,201,447,221,466,207,494,197,532,231,559,150,519,138,511,143,501,135" alt="Grand Est" href="?region=Grand_Est&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Grand Est"/>
                <area shape="poly" coords="261,217,241,227,236,253,197,266,207,300,180,303,140,265,139,240,157,231,178,225,198,208,199,182,225,179,245,184,262,199" alt="Pays de la Loire" href="?region=Pays_de_la_Loire&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Pays de la Loire"/>
                <area shape="poly" coords="111,147,40,160,48,200,64,229,117,250,152,222,176,211,190,200,189,180,176,184,166,173,161,157" alt="Bretagne" href="?region=Bretagne&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Bretagne"/>
                <area shape="poly" coords="210,270,212,297,191,313,191,368,176,479,217,508,233,475,220,453,225,437,242,432,262,429,270,412,284,389,291,375,315,378,320,361,330,350,327,332,333,313,317,303,289,307,274,296,261,276,240,260" alt="Nouvelle-Aquitaine" href="?region=Nouvelle-Aquitaine&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Nouvelle-Aquitaine"/>
                <area shape="poly" coords="300,382,280,406,278,428,228,447,228,460,238,467,235,484,223,505,235,513,260,513,261,501,311,521,320,532,331,526,337,534,358,526,361,508,369,481,406,464,422,450,425,436,420,424,400,425,386,396,363,387,358,406,347,391,329,408,315,391,308,388" alt="Occitanie" href="?region=Occitanie&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Occitanie"/>
                <area shape="poly" coords="481,376,500,374,523,359,512,344,515,327,506,297,493,303,483,319,470,310,452,306,439,295,426,318,420,308,401,318,387,308,390,297,380,284,352,280,342,286,332,296,343,323,337,334,338,365,331,359,320,385,326,399,335,393,344,379,356,395,361,378,377,382,398,398,401,419,426,416,440,412,457,428,451,407,465,400,487,385" alt="Auvergne-Rhône-Alpes" href="?region=Auvergne-Rhône-Alpes&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Auvergne-Rhône-Alpes"/>
                <area shape="poly" coords="443,426,426,421,433,439,413,467,433,480,461,485,482,502,513,482,536,459,544,434,522,431,512,422,511,406,517,394,507,386,499,379,495,388,511,399,493,381,499,388,507,388,505,386,500,378,498,383,491,377,491,391,482,392,477,399,467,405,462,415,470,425,459,436,500,378,507,388,495,388,493,381,499,388" alt="Provence-Alpes-Côte d'Azur" href="?region=Provence-Alpes-Côte_d'Azur&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Provence-Alpes-Côte d'Azur"/>
                <area shape="poly" coords="611,499,622,521,624,545,607,592,594,579,576,540" alt="Corse" href="?region=Corse&amp;theme=<?php echo htmlspecialchars($_GET['theme'] ?? 'default'); ?>" title="Corse"/>
            </map>
        </div>
    <?php else: ?>
        <div class="region-map-container" id="region-map-container">
            <h2 id="region-title"><?php echo htmlspecialchars(str_replace('_', ' ', $region_name)); ?></h2>
            <img src="Images/regions/region-<?php echo htmlspecialchars($region_id); ?>.png" alt="Carte de la région <?php echo htmlspecialchars($region_name); ?>">
            <a href="?" id="back-to-france">← Retour à la carte de France</a>
        </div>
    <?php endif; ?>

    <!-- Liste déroulante -->
    <!-- Liste déroulante et affichage météo -->
<!-- Liste déroulante et affichage météo -->
<div id="listedr" class="container">
    <?php
    listederoulante();
    
    if (isset($_GET['commune'])) {
        $communeData = getCommuneData($_GET['commune']);
        
        if ($communeData) {
            echo '<div class="view-toggle-container">';
            echo '<div class="view-toggle">';
            
            
            // Bouton Vue Générale
            $paramsGeneral = $_GET;
            unset($paramsGeneral['view']);
            echo '<button onclick="location.href=\'?'.http_build_query($paramsGeneral).'\'" class="view-toggle-btn '.(!isset($_GET['view']) ? 'active' : '').'">Vue générale</button>';
            
            // Bouton Vue Détaillée
            $paramsDetailed = $_GET;
            $paramsDetailed['view'] = 'detailed';
            echo '<button onclick="location.href=\'?'.http_build_query($paramsDetailed).'\'" class="view-toggle-btn '.(isset($_GET['view']) && $_GET['view'] === 'detailed' ? 'active' : '').'">Vue détaillée</button>';
            
            echo '</div></div>';
            
            if (isset($_GET['view']) && $_GET['view'] === 'detailed') {
                $detailedWeather = getDetailedHourlyForecast($communeData['latitude'].','.$communeData['longitude']);
                displayDetailedWeatherForecast($detailedWeather, $communeData['name']);
            } else {
                $weatherData = getWeatherForecast($communeData['latitude'].','.$communeData['longitude']);
                displayWeatherForecast1($weatherData);
            }
        }
    }
    
    ?>
</div>

    <!-- Bouton pour modifier les préférences de cookies -->
    <button id="changeCookiePrefs">Modifier les préférences de cookies</button>
</main>

<?php require_once './include/footer.inc.php'; ?>

