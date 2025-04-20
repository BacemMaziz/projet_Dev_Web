<?php


/**
 * Projet : Site de prévision météo pour la France
 * Module : Développement Web
 * 
 * Réalisateurs :
 * - Bacem Maziz
 * - Hamlat Arslane
 * 
 * Tous droits réservés - © 2024
 */


declare(strict_types=1);



/**
 * Lit un fichier CSV et retourne un tableau de lignes (chaque ligne étant un tableau).
 *
 * @param string $filename Nom du fichier CSV.
 * @return array Les lignes du CSV (sans l'en-tête).
 */
function lireCSV($filename) {
    $data = [];
    if (($handle = fopen($filename, "r")) !== FALSE) {
        // Lire et ignorer la ligne d'en-tête
        fgetcsv($handle, 1000, ",");
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $data[] = $row;
        }
        fclose($handle);
    }
    return $data;
}

/**
 * Génère un tableau associatif contenant les régions, départements et communes.
 *
 * @return array Tableau structuré avec régions, départements et communes.
 */
function Tabasso_reg_dep_comm() {
    // Chemins vers les fichiers CSV
    $fichierRegions = "v_region_2024.csv";
    $fichierDepartements = "v_departement_2024.csv";
    $fichierCommunes = "20230823-communes-departement-region.csv";

    // Charger les données
    $regionsData = lireCSV($fichierRegions);
    $departementsData = lireCSV($fichierDepartements);
    $communesData = lireCSV($fichierCommunes);

    // Tableau final associatif : clé = libellé de la région, valeur = tableau des départements
    $resultat = [];

    // Associer les départements aux régions
    foreach ($regionsData as $region) {
        $codeRegion = $region[0];      // Code région (ex: "01")
        $libelleRegion = $region[5];   // Libellé de la région (ex: "Guadeloupe")
        
        $resultat[$libelleRegion] = [];

        foreach ($departementsData as $dept) {
            $deptRegionCode = $dept[1];  // Code région associé au département
            if ($deptRegionCode === $codeRegion) {
                $deptNum = $dept[0];     // Numéro du département
                $deptLibelle = $dept[6]; // Libellé du département
                
                // Initialiser le tableau des communes
                $communes = [];
                
                // Associer les communes au département
                foreach ($communesData as $commune) {
                    if ($commune[11] === $deptNum) { // Code département de la commune
                        $communes[] = [
                            "code" => $commune[0],
                            "nom" => $commune[1],
                            "code_postal" => $commune[2],
                            "latitude" => $commune[5],
                            "longitude" => $commune[6]
                        ];
                    }
                }

                // Ajouter le département et ses communes à la région
                $resultat[$libelleRegion][] = [
                    "num" => $deptNum,
                    "nom" => $deptLibelle,
                    "communes" => $communes
                ];
            }
        }
    }
    return $resultat;
}





/**
 * Génère un système de menus déroulants hiérarchiques pour la sélection géographique française
 * (Région → Département → Commune) en fonction des choix de l'utilisateur.
 *
 * La fonction s'adapte dynamiquement pour afficher :
 * - La liste des régions si aucun choix n'a été fait
 * - Les départements d'une région sélectionnée
 * - Les communes d'un département sélectionné
 *
 * @return void Affiche directement le formulaire HTML approprié
 */

function listederoulante(){
    $resultat = Tabasso_reg_dep_comm(); 
    
    if (isset($_GET['departement']) && isset($_GET['region'])) {
        $departementChoisi = $_GET['departement'];
        $regionChoisie = $_GET['region'];
      
        
        $theme = htmlspecialchars($_GET['theme'] ?? 'default');
        echo '<form action="#region-map-container" method="get">';
        echo '<input type="hidden" name="theme" value="' . htmlspecialchars($theme) . '"/>';
        echo '<input type="hidden" name="region" value="' .htmlspecialchars($regionChoisie).'"/>';
        echo '<input type="hidden" name="departement" value="' . htmlspecialchars($departementChoisi) . '"/>';
        echo '<select name="commune">';
        echo '<option value="">-- Choisissez une ville --</option>';
        $regionChoisie = str_replace('_', ' ', $regionChoisie);
        if (isset($resultat[$regionChoisie])) {
            foreach ($resultat[$regionChoisie] as $dept) {
                if ($dept["num"] === $departementChoisi && isset($dept["communes"])) {
                    foreach ($dept["communes"] as $commune) {
                        echo '<option value="' . htmlspecialchars($commune["code"]) . '">' . htmlspecialchars($commune["nom"]) . '</option>';
                    }
                }
            }
        }
    
        echo '</select>';
        echo '<button type="submit">Valider</button>';
        echo '</form>';
    } else {
        if(isset($_GET['region']) && empty($_GET['departement'])) {
            $regionChoisie = $_GET['region'];
            $theme = htmlspecialchars($_GET['theme'] ?? 'default');
            echo '<form action="#region-map-container" method="get">';
            echo '<input type="hidden" name="theme" value="' . htmlspecialchars($theme) . '"/>';
            echo '<input type="hidden" name="region" value="' . htmlspecialchars($regionChoisie) . '"/>';
            echo '<select name="departement">';
            echo '<option value="">-- Choisissez un département --</option>';
            $regionChoisie = str_replace('_',' ', $regionChoisie);

            $departement = $resultat[$regionChoisie];
            foreach ($departement as $dept): 
                echo '<option value="' . htmlspecialchars($dept["num"]) . '">' . htmlspecialchars($dept["nom"]) . '</option>';
            endforeach;

            echo '</select>';
            echo '<button type="submit">Valider</button>';
            echo '</form>';
        } else {
            $regionChoisie = '-- Choisissez une région --';
           
            $theme = htmlspecialchars($_GET['theme'] ?? 'default');
            echo '<form action="#main-france-map" method="get">';
            echo '<input type="hidden" name="theme" value="' . htmlspecialchars($theme) . '"/>';
            echo '<select name="region">';
            foreach ($resultat as $region => $departements): 

                echo '<option value="' . htmlspecialchars(str_replace(' ','_', $region)) . '">' . htmlspecialchars($region) . '</option>';
            endforeach; 
            echo '</select>';
            echo '<button type="submit">Valider</button>';  
            echo '</form>';
        }
    }
}

/**
 * Récupère les prévisions météorologiques pour un lieu donné via l'API WeatherAPI.com
 * 
 * Cette fonction interroge l'API WeatherAPI pour obtenir les prévisions sur 7 jours
 * dans la langue française. Elle gère les erreurs potentielles et log les problèmes.
 *
 * @param string $location Le lieu pour lequel récupérer la météo (ville, code postal, etc.)
 * @param string $apiKey La clé API pour WeatherAPI (valeur par défaut fournie)
 * 
 * @return array|null Retourne les données météo décodées ou null en cas d'échec
 * @throws Exception En cas d'erreur de connexion ou de réponse API invalide
 */
function getWeatherForecast(string $location, string $apiKey = '7f5fd2ac83464be9849115713252004') {
    // Encoder le paramètre de localisation
    $encodedLocation = urlencode($location);
    $url = "http://api.weatherapi.com/v1/forecast.json?key={$apiKey}&q={$encodedLocation}&days=7&lang=fr";
    
    try {
        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true // Pour lire la réponse même en cas d'erreur HTTP
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception("Impossible de se connecter au service météo");
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['error'])) {
            throw new Exception("Erreur API: " . $data['error']['message']);
        }
        
        return $data;
    } catch (Exception $e) {
        error_log("Erreur météo pour {$location}: " . $e->getMessage());
        return null;
    }
}







/**
 * Affiche les prévisions météorologiques sous forme de cartes
 * 
 * Cette fonction prend les données météo brutes et les affiche dans un format visuel organisé.
 * Elle gère différents cas d'affichage (page d'accueil vs pages dédiées) et inclut une gestion d'erreur.
 *
 * @param array $weatherData Les données météo provenant de l'API WeatherAPI
 * @param bool $isHomepage Détermine si l'affichage est pour la page d'accueil (affichage minimal)
 * 
 * @return void Affiche directement le HTML généré
 */


function displayWeatherForecast(array $weatherData, bool $isHomepage = false) {
    if (!$weatherData || !isset($weatherData['forecast']['forecastday'])) {
       // if(!$weatherData) {
        //    echo '<div class="weather-error">tab vide</div>';
        //} else {
        //    echo '<div class="weather-error">donne mkhaltine</div>';
       // }
        echo '<div class="weather-error">Données météo non disponibles</div>';
        return;
    }
    
    $locationName = $weatherData['location']['name'];
    
    echo '<div>';
    
    if (!$isHomepage) {
        echo '<h2 class="weather-location-title">Prévisions à '.htmlspecialchars($locationName).'</h2>';
    }
    
    echo '<div class="weather-grid">';
    
    foreach ($weatherData['forecast']['forecastday'] as $day) {
        $date = new DateTime($day['date']);
        $weatherCode = $day['day']['condition']['code'];
        
        echo '<div class="weather-card">';
        echo '<div class="weather-date">'.$date->format('D j M').'</div>';
        echo '<div class="weather-icon"><img src="'.$day['day']['condition']['icon'].'" alt="'.$day['day']['condition']['text'].'"/></div>';
        echo '<div class="weather-temp">'.round($day['day']['avgtemp_c']).'°C</div>';
        echo '<div class="weather-details">';
        echo '<span class="weather-max">↑ '.round($day['day']['maxtemp_c']).'°</span>';
        echo '<span class="weather-min">↓ '.round($day['day']['mintemp_c']).'°</span>';
        echo '<span class="weather-humidity">💧 '.$day['day']['avghumidity'].'%</span>';
        echo '</div>';
        echo '<div class="weather-condition">'.$day['day']['condition']['text'].'</div>';
        echo '</div>';
    }
    
    echo '</div></div>';
}







/**
 * Recherche les données géographiques d'une commune française à partir de son code INSEE
 * 
 * Cette fonction parcourt la structure hiérarchique des données géographiques françaises
 * (régions > départements > communes) pour trouver et retourner les informations
 * d'une commune spécifique identifiée par son code INSEE.
 *
 * @param string $communeCode Le code INSEE de la commune à rechercher
 * 
 * @return array|null Retourne un tableau associatif contenant :
 *                    - 'name' => Nom de la commune
 *                    - 'latitude' => Coordonnée latitude
 *                    - 'longitude' => Coordonnée longitude
 *                    Ou null si la commune n'est pas trouvée
 */

function getCommuneData(string $communeCode) {
    $resultat = Tabasso_reg_dep_comm();
    
    foreach ($resultat as $region) {
        foreach ($region as $dept) {
            foreach ($dept['communes'] as $commune) {
                if ($commune['code'] === $communeCode) {
                    return [
                        'name' => $commune['nom'],
                        'latitude' => $commune['latitude'],
                        'longitude' => $commune['longitude']
                    ];
                }
            }
        }
    }
    return null;
}


/**
 * Incrémente et retourne un compteur de visites stocké dans un fichier CSV.
 * 
 * Cette fonction lit le nombre actuel de visites depuis un fichier, l'incrémente de 1,
 * sauvegarde le nouveau nombre dans le fichier, et retourne l'ancienne valeur.
 * Si le fichier n'existe pas ou n'est pas lisible, elle commence à compter à partir de 0.
 * 
 * @return string Un message indiquant combien de fois la page a été visitée
 *                (avant la visite actuelle), suivi de "<br/>"
 */


function incrementerVisite() {
    $chemin = 'nbvisite.csv';
    

    $nombre = 0;
    
   
    if (file_exists($chemin) && is_readable($chemin)) {
        
        $contenu = file_get_contents($chemin);
        if ($contenu !== false && $contenu !== '') {
            $nombre = (int) trim($contenu);
        }
    }
    
    $result = "$nombre fois<br/>";
    $nombre++;
    
   
    $handle = fopen($chemin, 'w');
    if ($handle === false) {
        
        return $result;
    }
    
    fwrite($handle, (string)$nombre);  
    fclose($handle);
    
    return $result;
}





/**
 * Définit un cookie pour la dernière ville visitée
 * @param string $city Le nom de la ville à enregistrer
 * @return bool True si le cookie a été défini avec succès, false sinon
 */
function setLastVisitedCity($city) {
    // Validation de l'entrée
    if (!is_string($city) || empty(trim($city))) {
        error_log("Erreur: Nom de ville invalide");
        return false;
    }
    
    $cookieData = [
        'city' => htmlspecialchars(trim($city), ENT_QUOTES, 'UTF-8'),
        'date' => date('Y-m-d H:i:s'),
        'expires' => time() + 30 * 24 * 60 * 60 // 30 jours
    ];

    

    // Définition sécurisée du cookie
    return setcookie(
        'last_visited_city',          // Nom du cookie
        json_encode($cookieData),     // Valeur encodée en JSON
        [
            'expires' => $cookieData['expires'],
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax' // Protection contre les attaques CSRF
        ]
    );
}










/**
 * Récupère les données de la dernière ville visitée depuis les cookies
 * @return array|null Tableau des données ou null si non disponible/invalide
 */
function getLastVisitedCity() {
    if (!isset($_COOKIE['last_visited_city'])) {
        return null;
    }

    // Décodage sécurisé
    $data = json_decode($_COOKIE['last_visited_city'], true);

    // Validation des données
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Erreur de décodage JSON du cookie: " . json_last_error_msg());
        return null;
    }

    if (!isset($data['city']) || !isset($data['date'])) {
        error_log("Erreur: Structure de cookie invalide");
        return null;
    }

    // Nettoyage des données
    $data['city'] = htmlspecialchars($data['city'], ENT_QUOTES, 'UTF-8');
    
    // Validation de la date
    if (!strtotime($data['date'])) {
        $data['date'] = date('Y-m-d H:i:s');
    }

    return $data;
}





// Fonction pour récupérer les informations sur une région
function getRegionName(array $regions, ?string $region_id): string {
    return $region_id && array_key_exists($region_id, $regions) ? $regions[$region_id] : '';
}



// Fonction pour récupérer la liste des régions
function getRegions(): array {
    return [
        '01' => 'Guadeloupe',
        '02' => 'Martinique',
        '03' => 'Guyane',
        '04' => 'La Réunion',
        '06' => 'Mayotte',
        '11' => 'Île-de-France',
        '24' => 'Centre-Val de Loire',
        '27' => 'Bourgogne-Franche-Comté',
        '28' => 'Normandie',
        '32' => 'Hauts-de-France',
        '44' => 'Grand Est',
        '52' => 'Pays de la Loire',
        '53' => 'Bretagne',
        '75' => 'Nouvelle-Aquitaine',
        '76' => 'Occitanie',
        '84' => 'Auvergne-Rhône-Alpes',
        '93' => 'Provence-Alpes-Côte d\'Azur',
        '94' => 'Corse'
    ];
}

/**
 * Sélectionne et retourne une bannière aléatoire parmi les images disponibles
 * 
 * Cette fonction scanne le dossier spécifié à la recherche d'images valides
 * et en retourne une sélectionnée aléatoirement avec ses informations.
 *
 * @return array|null Retourne un tableau associatif contenant :
 *                    - 'path' : Chemin complet vers l'image
 *                    - 'name' : Nom du fichier sans extension
 *                    Retourne null si aucune bannière valide n'est trouvée
 */

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



/**
 * Gère l'affichage météo d'une commune et le suivi des visites
 * 
 * @param string $codeCommune Le code INSEE de la commune
 * @return void Affiche les données météo et met à jour le compteur de visites
 */
function gererMeteoCommune($codeCommune) {
    // 1. Récupération des données de la commune
    $donneesCommune = getCommuneData($codeCommune);
    if (!$donneesCommune) {
        echo '<div class="erreur">Commune introuvable</div>';
        return;
    }

    $nomCommune = $donneesCommune['name'];
    $fichierCSV = 'villeconsult.csv';
    $visitesCommunes = [];

    // 2. Lecture du fichier de visites (optimisé mémoire)
    if (file_exists($fichierCSV)) {
        $fichier = fopen($fichierCSV, 'r');
        while (($ligne = fgetcsv($fichier)) !== false) {
            if (count($ligne) >= 2) {
                $visitesCommunes[$ligne[0]] = (int)$ligne[1];
            }
        }
        fclose($fichier);
    }

    // 3. Mise à jour du compteur
    $visitesCommunes[$nomCommune] = ($visitesCommunes[$nomCommune] ?? 0) + 1;

    // 4. Sauvegarde sécurisée des visites
    $fichierTemp = tempnam(sys_get_temp_dir(), 'visites');
    $fichier = fopen($fichierTemp, 'w');
    foreach ($visitesCommunes as $nom => $visites) {
        fputcsv($fichier, [$nom, $visites]);
    }
    fclose($fichier);
    rename($fichierTemp, $fichierCSV);

    // 5. Affichage météo
    $donneesMeteo = getWeatherForecast($donneesCommune['latitude'].','.$donneesCommune['longitude']);
    
    echo '<section id="auto-weather-section">';
    displayWeatherForecast($donneesMeteo);
    
   
    
    echo '</section>';
}




function checkCookieConsent() {
    if (isset($_COOKIE['cookie_consent']) && $_COOKIE['cookie_consent'] === 'accepted') {
        return true;
    }
    
    // Afficher la bannière si pas encore accepté
    echo '<div class="cookie-banner">';
    echo '<button id="accept-cookies">Accepter</button>';
    echo '</div>';
    
    // Script JS pour enregistrer le choix
    echo <<<HTML
    <script>
    document.getElementById('accept-cookies').addEventListener('click', () => {
        fetch('/accept-cookies.php')
        .then(() => location.reload());
    });
    </script>
    HTML;
    
    return false;
}
/**
 * Récupère les prévisions météo horaires détaillées
 * 
 * @param string $location Localisation (latitude,longitude ou nom de ville)
 * @param string $apiKey Clé API WeatherAPI
 * @return array|null Données météo ou null en cas d'erreur
 */
function getDetailedHourlyForecast(string $location, string $apiKey = '7f5fd2ac83464be9849115713252004') {
    $encodedLocation = urlencode($location);
    $url = "http://api.weatherapi.com/v1/forecast.json?key={$apiKey}&q={$encodedLocation}&days=1&aqi=yes&alerts=yes";
    
    try {
        $response = file_get_contents($url);
        if ($response === false) {
            throw new Exception("Erreur de connexion à l'API");
        }
        
        $data = json_decode($response, true);
        return $data;
    } catch (Exception $e) {
        error_log("Erreur météo détaillée: " . $e->getMessage());
        return null;
    }
}

/**
 * Affiche les prévisions météo horaires détaillées
 * 
 * @param array $weatherData Données de l'API
 * @param string $communeName Nom de la commune
 */
/**
 * Affiche les prévisions météo horaires détaillées
 * 
 * @param array $weatherData Données de l'API
 * @param string $communeName Nom de la commune
 */
/**
 * Affiche les prévisions météo horaires détaillées
 * 
 * @param array $weatherData Données de l'API
 * @param string $communeName Nom de la commune
 */
function displayDetailedWeatherForecast(array $weatherData, string $communeName) {
    if (!isset($weatherData['forecast']['forecastday'][0]['hour'])) {
        echo '<div class="weather-error">Données horaires non disponibles</div>';
        return;
    }
    
    $currentHour = (int)date('G');
    $currentData = $weatherData['current'];
    $forecastDay = $weatherData['forecast']['forecastday'][0];
    
    echo '<div class="detailed-weather-container">';
    echo '<div class="detailed-weather-header">';
    echo '<h2>Prévisions détaillées</h2>';
    echo '<div class="location-tag">';
    echo '<span class="location-icon">📍</span>';
    echo '<span class="location-text">'.htmlspecialchars($communeName).'</span>';
    echo '</div>';
    echo '</div>';
    
    // Indicateurs clés
    echo '<div class="weather-key-indicators">';
    
    // Température actuelle
    echo '<div class="indicator-card">';
    echo '<div class="indicator-header">';
    echo '<div class="indicator-icon">🌡️</div>';
    echo '<h3 class="indicator-title">Température</h3>';
    echo '</div>';
    echo '<div class="indicator-value">'.round($currentData['temp_c']).'°C</div>';
    echo '<p class="indicator-desc">Ressentie: '.round($currentData['feelslike_c']).'°C</p>';
    echo '</div>';
    
    // UV
    echo '<div class="indicator-card">';
    echo '<div class="indicator-header">';
    echo '<div class="indicator-icon">☀️</div>';
    echo '<h3 class="indicator-title">Indice UV</h3>';
    echo '</div>';
    echo '<div class="indicator-value">'.$currentData['uv'].'</div>';
    echo '<p class="indicator-desc">'.getUVDescription($currentData['uv']).'</p>';
    echo '</div>';
    
    // Qualité de l'air
    if (isset($currentData['air_quality'])) {
        $aqi = $currentData['air_quality']['us-epa-index'] ?? 0;
        echo '<div class="indicator-card">';
        echo '<div class="indicator-header">';
        echo '<div class="indicator-icon">🍃</div>';
        echo '<h3 class="indicator-title">Qualité de l\'air</h3>';
        echo '</div>';
        echo '<div class="indicator-value">'.$aqi.'/5</div>';
        echo '<p class="indicator-desc">'.getAirQualityDescription($aqi).'</p>';
        echo '</div>';
    }
    
    // Vent
    echo '<div class="indicator-card">';
    echo '<div class="indicator-header">';
    echo '<div class="indicator-icon">💨</div>';
    echo '<h3 class="indicator-title">Vent</h3>';
    echo '</div>';
    echo '<div class="indicator-value">'.round($currentData['wind_kph']).' km/h</div>';
    echo '<p class="indicator-desc">Direction: '.$currentData['wind_dir'].'</p>';
    echo '</div>';
    
    // Humidité
    echo '<div class="indicator-card">';
    echo '<div class="indicator-header">';
    echo '<div class="indicator-icon">💧</div>';
    echo '<h3 class="indicator-title">Humidité</h3>';
    echo '</div>';
    echo '<div class="indicator-value">'.$currentData['humidity'].'%</div>';
    echo '<p class="indicator-desc">Point de rosée: '.round($currentData['dewpoint_c']).'°C</p>';
    echo '</div>';
    
    // Pression
    echo '<div class="indicator-card">';
    echo '<div class="indicator-header">';
    echo '<div class="indicator-icon">📊</div>';
    echo '<h3 class="indicator-title">Pression</h3>';
    echo '</div>';
    echo '<div class="indicator-value">'.$currentData['pressure_mb'].' hPa</div>';
    echo '<p class="indicator-desc">Tendance: '.($currentData['pressure_mb'] > 1013 ? 'Haut' : 'Bas').'</p>';
    echo '</div>';
    
    echo '</div>'; // Fin weather-key-indicators
    
    // Prévisions horaires
    echo '<div class="hourly-forecast-section">';
    echo '<div class="hourly-scroll-container">';
    echo '<button class="scroll-nav-btn prev">❮</button>';
    echo '<button class="scroll-nav-btn next">❯</button>';
    echo '<div class="hourly-forecast">';
    
    foreach ($forecastDay['hour'] as $hour) {
        $hourTime = date('G', strtotime($hour['time']));
        $isCurrentHour = $hourTime === $currentHour;
        
        echo '<div class="hourly-card'.($isCurrentHour ? ' current-hour' : '').'">';
        echo '<div class="hour-time">'.date('H:i', strtotime($hour['time'])).'</div>';
        echo '<div class="hour-icon"><img src="'.$hour['condition']['icon'].'" alt="'.$hour['condition']['text'].'"/></div>';
        echo '<div class="hour-temp">'.round($hour['temp_c']).'°C</div>';
        echo '<div class="hour-details-grid">';
        echo '<div class="hour-detail-item"><span class="hour-detail-value">'.$hour['humidity'].'%</span><span class="hour-detail-label">Humidité</span></div>';
        echo '<div class="hour-detail-item"><span class="hour-detail-value">'.round($hour['wind_kph']).' km/h</span><span class="hour-detail-label">Vent</span></div>';
        echo '<div class="hour-detail-item"><span class="hour-detail-value">'.$hour['chance_of_rain'].'%</span><span class="hour-detail-label">Pluie</span></div>';
        echo '<div class="hour-detail-item"><span class="hour-detail-value">'.$hour['chance_of_snow'].'%</span><span class="hour-detail-label">Neige</span></div>';
        echo '<div class="hour-detail-item"><span class="hour-detail-value">'.$hour['uv'].'</span><span class="hour-detail-label">UV</span></div>';
        echo '<div class="hour-detail-item"><span class="hour-detail-value">'.$hour['gust_kph'].' km/h</span><span class="hour-detail-label">Rafales</span></div>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>'; // Fin hourly-forecast
    echo '</div>'; // Fin hourly-scroll-container
    
    // Graphique (affiché uniquement si les données sont valides)
    if (!empty($forecastDay['hour'])) {
        echo '<div class="weather-chart-container">';
        echo '<canvas id="weatherChart" data-weather="'.htmlspecialchars(json_encode($forecastDay['hour'])).'"></canvas>';
        echo '</div>';
    }
    
    echo '</div>'; // Fin hourly-forecast-section
    echo '</div>'; // Fin detailed-weather-container
}
function getUVDescription($uvIndex) {
    $levels = [
        ['min' => 0, 'max' => 2, 'desc' => 'Faible', 'color' => '#4CAF50'],
        ['min' => 3, 'max' => 5, 'desc' => 'Modéré', 'color' => '#FFC107'],
        ['min' => 6, 'max' => 7, 'desc' => 'Élevé', 'color' => '#FF9800'],
        ['min' => 8, 'max' => 10, 'desc' => 'Très élevé', 'color' => '#F44336'],
        ['min' => 11, 'max' => 20, 'desc' => 'Extrême', 'color' => '#9C27B0']
    ];
    
    foreach ($levels as $level) {
        if ($uvIndex >= $level['min'] && $uvIndex <= $level['max']) {
            return $level['desc'];
        }
    }
    
    return 'Inconnu';
}

function getAirQualityDescription($aqi) {
    $levels = [
        1 => 'Excellente',
        2 => 'Bonne',
        3 => 'Moyenne',
        4 => 'Médiocre',
        5 => 'Mauvaise'
    ];
    
    return $levels[$aqi] ?? 'Donnée indisponible';
}
/**
 * Affiche les prévisions météo sur 7 jours
 * 
 * @param array $weatherData Données de l'API
 */
function displayWeatherForecast1(array $weatherData) {
    if (!isset($weatherData['forecast']['forecastday'])) {
        echo '<div class="weather-error">Prévisions non disponibles</div>';
        return;
    }
    
    $forecastDays = $weatherData['forecast']['forecastday'];
    
    echo '<div class="weekly-forecast-container">';
    echo '<div class="weekly-forecast-header">';
    echo '<h2>Prévisions sur 7 jours</h2>';
    echo '</div>';
    
    echo '<div class="weekly-forecast">';
    
    foreach ($forecastDays as $day) {
        $date = new DateTime($day['date']);
        $dayName = $date->format('l'); // Nom du jour
        $dayName = str_replace(
            ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'],
            $dayName
        );
        
        echo '<div class="weekly-card">';
        echo '<div class="weekly-day">'.$dayName.'</div>';
        echo '<div class="weekly-icon"><img src="'.$day['day']['condition']['icon'].'" alt="'.$day['day']['condition']['text'].'"/></div>';
        echo '<div class="weekly-temp">'.round($day['day']['avgtemp_c']).'°C</div>';
        echo '<div class="weekly-desc">'.$day['day']['condition']['text'].'</div>';
        
        echo '<div class="weekly-details-grid">';
        echo '<div class="weekly-detail-item">';
        echo '<span class="weekly-detail-value">'.round($day['day']['maxtemp_c']).'°C</span>';
        echo '<span class="weekly-detail-label">Max</span>';
        echo '</div>';
        echo '<div class="weekly-detail-item">';
        echo '<span class="weekly-detail-value">'.round($day['day']['mintemp_c']).'°C</span>';
        echo '<span class="weekly-detail-label">Min</span>';
        echo '</div>';
        echo '<div class="weekly-detail-item">';
        echo '<span class="weekly-detail-value">'.$day['day']['maxwind_kph'].' km/h</span>';
        echo '<span class="weekly-detail-label">Vent</span>';
        echo '</div>';
        echo '<div class="weekly-detail-item">';
        echo '<span class="weekly-detail-value">'.$day['day']['daily_chance_of_rain'].'%</span>';
        echo '<span class="weekly-detail-label">Pluie</span>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>'; // Fin weekly-card
    }
    
    echo '</div>'; // Fin weekly-forecast
    echo '</div>'; // Fin weekly-forecast-container
}
?>

