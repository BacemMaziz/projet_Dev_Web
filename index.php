<?php
declare(strict_types=1);

require_once './include/functions.php';

if (isset($_GET['commune'])) {
    $communeData = getCommuneData($_GET['commune']);

$commune = $communeData['name'];

setLastVisitedCity($commune);

}

$titre = "Accueil";
$description = "Bienvenue sur notre site dédié à la météo en France.";

// Détection de la localisation avec ipinfo.io
$localWeather = null;
try {
    $ip = $_SERVER['REMOTE_ADDR'];
    $geo = json_decode(file_get_contents("https://ipinfo.io/{$ip}?token=37ee27659f0f07"));
    
    if ($geo && isset($geo->city)) {
        // Récupérer les prévisions avec WeatherAPI
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


if (isset($_COOKIE['last_visited_city'])) {
    try {
        $lastVisit = json_decode($_COOKIE['last_visited_city'], true);
        if ($lastVisit && isset($lastVisit['city'])) {
            $cookieWeatherData = getWeatherForecast($lastVisit['city'],'5beeb6db94ca420a97c93516250604');
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
// Vérifier si une région a été sélectionnée
$region_id = $_GET['region'] ?? null;
$regions = getRegions();
$region_name = $_GET['region'] ?? null;// Fonction pour récupérer une bannière aléatoire
function getRandomBanner() {
    $bannersDir = './pictures/'; // Créez ce sous-dossier dans "photos"
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $banners = [];

    // Vérifier si le dossier existe
    if (is_dir($bannersDir)) {
        // Scanner le dossier
        $files = scandir($bannersDir);
        foreach ($files as $file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($extension, $allowedExtensions)) {
                $banners[] = $file;
            }
        }
    }

    // Retourner une bannière aléatoire ou l'image par défaut
    if (!empty($banners)) {
        $randomBanner = $banners[array_rand($banners)];
        return [
            'path' => $bannersDir . $randomBanner,
            'name' => pathinfo($randomBanner, PATHINFO_FILENAME)
        ];
    } 
}

// Récupérer la bannière aléatoire
$currentBanner = getRandomBanner();

require_once './include/header.inc.php';
?>

<main class="main-content">
<div class="full-width-banner">
    <img src="<?= htmlspecialchars($currentBanner['path']) ?>" 
         alt="Météo: <?= htmlspecialchars($currentBanner['name']) ?>"
         class="dynamic-banner">
    
    <!-- Overlay texte optionnel (comme sur Météo-France) -->
    <div class="banner-overlay">
        <h1>ActuMeteo</h1>
    </div>
</div>
    <?php if ($localWeather && !isset($_GET['region'])): ?>
        <?php
// Determine which weather to display (cookie takes priority)
$weatherDataToDisplay = $cookieWeather ?? $localWeather;
$isAutoLocation = ($weatherDataToDisplay === $localWeather);
?>

<section id="auto-weather-section">
    <div id="auto-weather-header">
        <h2 id="auto-weather-title">Météo à <?= htmlspecialchars($weatherDataToDisplay['city']) ?></h2>
        <p id="auto-location-badge">
            <span class="location-icon"><?= $isAutoLocation ? '📍' : '🕒' ?></span>
            <span class="location-text">
                <?= $isAutoLocation ? 'Position détectée automatiquement' : 'Dernière ville consultée' ?>
            </span>
            <?php if (!$isAutoLocation): ?>
                <span class="location-date">
                    (le <?= date('d/m/Y', strtotime($weatherDataToDisplay['date'])) ?>)
                </span>
            <?php endif; ?>
        </p>
    </div>
    <?php if (is_array($weatherDataToDisplay)) {
    displayWeatherForecast($weatherData, true);
} else {
    echo "Impossible de récupérer les données météo.";
} ?>
</section>
<?php endif; ?>

    <?php if (!$region_id): ?>
        <!-- Afficher la carte de France si aucune région n'est sélectionnée -->
        <div class="france-map-container">
            <img src="Images/france.png" alt="Carte des régions de France" usemap="#francemap" id="main-france-map">
            <map name="francemap" id="france-map">
            <!-- Guadeloupe -->
            <area shape="rect" coords="360,555,424,611" alt="Guadeloupe" href="?region=Guadeloupe" title="Guadeloupe">
            <!-- Martinique -->
            <area shape="rect" coords="490,568,525,610" alt="Martinique" href="?region=Martinique" title="Martinique">
            <!-- Guyane -->
            <area shape="poly" coords="102,432,158,475,168,499,107,600,13,602,40,543,23,501,16,461,24,442,45,414" alt="Guyane" href="?region=Guyane" title="Guyane">
            <!-- La Réunion -->
            <area shape="rect" coords="432,565,482,612" alt="La Réunion" href="?region=La Réunion" title="La Réunion">
            <!-- Mayotte -->
            <area shape="rect" coords="531,584,554,611" alt="Mayotte" href="?region=Mayotte" title="Mayotte">
            <!-- Île-de-France -->
            <area shape="poly" coords="344,195,332,184,317,186,303,167,297,148,304,140,321,138,334,142,360,144,371,157,371,183,357,178,354,187" alt="Île-de-France" href="/?region=Île-de-France" title="Île-de-France">
            <!-- Centre-Val de Loire -->
            <area shape="poly" coords="326,193,352,201,347,250,353,268,328,292,288,295,272,274,264,262,252,261,240,249,247,231,275,207,271,186,276,166,292,160,315,195,302,186,359,205,346,216" alt="Centre-Val de Loire" href="?region=Centre-Val de Loire" title="Centre-Val de Loire">
            <!-- Bourgogne-Franche-Comté -->
            <area shape="poly" coords="390,281,364,275,357,244,359,224,365,214,358,187,377,184,385,204,402,212,427,209,441,231,459,229,480,210,498,214,513,225,509,246,496,261,485,279,470,301,461,306,445,289,428,295,419,303,400,309,399,296" alt="Bourgogne-Franche-Comté" href="?region=Bourgogne-Franche-Comté" title="Bourgogne-Franche-Comté">
            <!-- Normandie -->
            <area shape="poly" coords="184,105,157,106,179,172,215,172,237,171,259,185,267,178,267,158,289,153,291,138,300,130,299,112,300,99,291,89,241,110,234,129" alt="Normandie" href="?region=Normandie" title="Normandie">
            <!-- Hauts-de-France -->
            <area shape="poly" coords="314,27,299,39,300,76,300,87,312,112,310,134,332,135,352,136,376,150,377,127,394,117,400,94,397,75,382,75,377,65,360,64,360,49,342,45,332,30" alt="Hauts-de-France" href="?region=Hauts-de-France" title="Hauts-de-France">
            <!-- Grand Est -->
            <area shape="poly" coords="496,123,488,121,472,116,463,120,453,112,438,104,432,111,430,81,421,93,410,92,405,113,400,129,388,132,378,178,397,204,432,201,447,221,466,207,494,197,532,231,559,150,519,138,511,143,501,135" alt="Grand Est" href="?region=Grand Est" title="Grand Est">
            <!-- Pays de la Loire -->
            <area shape="poly" coords="261,217,241,227,236,253,197,266,207,300,180,303,140,265,139,240,157,231,178,225,198,208,199,182,225,179,245,184,262,199" alt="Pays de la Loire" href="?region=Pays de la Loire" title="Pays de la Loire">
            <!-- Bretagne -->
            <area shape="poly" coords="111,147,40,160,48,200,64,229,117,250,152,222,176,211,190,200,189,180,176,184,166,173,161,157" alt="Bretagne" href="?region=Bretagne" title="Bretagne">
            <!-- Nouvelle-Aquitaine -->
            <area shape="poly" coords="210,270,212,297,191,313,191,368,176,479,217,508,233,475,220,453,225,437,242,432,262,429,270,412,284,389,291,375,315,378,320,361,330,350,327,332,333,313,317,303,289,307,274,296,261,276,240,260" alt="Nouvelle-Aquitaine" href="?region=Nouvelle-Aquitaine" title="Nouvelle-Aquitaine">
            <!-- Occitanie -->
            <area shape="poly" coords="300,382,280,406,278,428,228,447,228,460,238,467,235,484,223,505,235,513,260,513,261,501,311,521,320,532,331,526,337,534,358,526,361,508,369,481,406,464,422,450,425,436,420,424,400,425,386,396,363,387,358,406,347,391,329,408,315,391,308,388" alt="Occitanie" href="?region=Occitanie" title="Occitanie">
            <!-- Auvergne-Rhône-Alpes -->
            <area shape="poly" coords="481,376,500,374,523,359,512,344,515,327,506,297,493,303,483,319,470,310,452,306,439,295,426,318,420,308,401,318,387,308,390,297,380,284,352,280,342,286,332,296,343,323,337,334,338,365,331,359,320,385,326,399,335,393,344,379,356,395,361,378,377,382,398,398,401,419,426,416,440,412,457,428,451,407,465,400,487,385" alt="Auvergne-Rhône-Alpes" href="?region=Auvergne-Rhône-Alpes" title="Auvergne-Rhône-Alpes">
            <!-- Provence-Alpes-Côte d'Azur -->
            <area shape="poly" coords="443,426,426,421,433,439,413,467,433,480,461,485,482,502,513,482,536,459,544,434,522,431,512,422,511,406,517,394,507,386,499,379,495,388,511,399,493,381,499,388,507,388,505,386,500,378,498,383,491,377,491,391,482,392,477,399,467,405,462,415,470,425,459,436,500,378,507,388,495,388,493,381,499,388" alt="Provence-Alpes-Côte d'Azur" href="?region=Provence-Alpes-Côte d'Azur" title="Provence-Alpes-Côte d'Azur">
            <!-- Corse -->
            <area shape="poly" coords="611,499,622,521,624,545,607,592,594,579,576,540" alt="Corse" href="?region=Corse" title="Corse">
        </div>
        
        <?php else: ?>
    <!-- Afficher la carte de la région si une région est sélectionnée -->
    <div class="region-map-container" id="region-map-container">
        <h2 id="region-title"><?php echo htmlspecialchars($region_name); ?></h2>
        <img src="Images/regions/region-<?php echo htmlspecialchars($region_id); ?>.png" alt="Carte de la région <?php echo htmlspecialchars($region_name); ?>">
        <a href="?" id="back-to-france">← Retour à la carte de France</a>
    </div>
    <?php endif; ?>  



    <div id="listedr" class="container">

      
    <?php
        listederoulante();
        
        if (isset($_GET['commune']) && !empty($_GET['commune'])) {

            $communeData = getCommuneData($_GET['commune']);
            $commune = $communeData['name'];
        
            $csvFile = 'villeconsult.csv';
            $communes = [];
        
            // Lire le fichier ligne par ligne
            if (file_exists($csvFile)) {
                $lines = file($csvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    [$name, $visits] = explode(',', $line);
                    $communes[$name] = (int)$visits;
                }
            }
        
            // Incrémenter ou ajouter la commune
            if (array_key_exists($commune, $communes)) {
                $communes[$commune]++;
            } else {
                $communes[$commune] = 1; // Commence à 1 pour la première visite
            }
        
            // Réécrire le fichier avec les nouvelles données
            $fp = fopen($csvFile, 'w');
            foreach ($communes as $name => $visits) {
                fputcsv($fp, [$name, $visits]);
            }
            fclose($fp);

            if ($communeData) {
                $weatherData = getWeatherForecast($communeData['latitude'].','.$communeData['longitude']);
                echo '<section class="weather-section selected-location">';
                echo '<div class="weather-header">';
                
                echo '</div>';
                displayWeatherForecast($weatherData);
                echo '</section>';
            }
        }


        ?>


    </div>
    <!-- Après la liste déroulante dans index.php -->
    
     


</main>

<?php require_once "./include/footer.inc.php"; ?>


