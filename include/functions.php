<?php



declare(strict_types=1);

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





// Afficher une liste deroulante 

function listederoulante(){
    $resultat = Tabasso_reg_dep_comm(); 
    
    if (isset($_GET['departement']) && isset($_GET['region'])) {
        $departementChoisi = $_GET['departement'];
        $regionChoisie = $_GET['region'];
    
        echo '<form action="#region-map-container" method="get">';
        echo '<input type="hidden" name="region" value="' . htmlspecialchars($regionChoisie) . '">';
        echo '<input type="hidden" name="departement" value="' . htmlspecialchars($departementChoisi) . '">';
        echo '<select name="commune">';
        echo '<option value="">-- Choisissez une ville --</option>';
    
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
            echo '<form action="#region-map-container" method="get">'; // Modifié ici
            echo '<input type="hidden" name="region" value="' . htmlspecialchars($regionChoisie) . '">';
            echo '<select name="departement">';
            echo '<option value="">-- Choisissez un département --</option>';

            $departement = $resultat[$regionChoisie];
            foreach ($departement as $dept): 
                echo '<option value="' . htmlspecialchars($dept["num"]) . '">' . htmlspecialchars($dept["nom"]) . '</option>';
            endforeach;

            echo '</select>';
            echo '<button type="submit">Valider</button>';
            echo '</form>';
        } else {
            $regionChoisie = '-- Choisissez une région --';
            echo '<form action="#main-france-map" method="get">'; // Modifié ici
            echo '<select name="region">';
            foreach ($resultat as $region => $departements): 
                echo '<option value="' . htmlspecialchars($region) . '">' . htmlspecialchars($region) . '</option>';
            endforeach; 
            echo '</select>';
            echo '<button type="submit">Valider</button>';  
            echo '</form>';
        }
    }
}

// Ajoutez cette fonction pour récupérer les données météo
function getWeatherForecast(string $location, string $apiKey = '5beeb6db94ca420a97c93516250604') {
    $url = "http://api.weatherapi.com/v1/forecast.json?key={$apiKey}&q={$location}&days=7&lang=fr";
    
    try {
        $response = file_get_contents($url);
        if ($response === false) {
            throw new Exception("Erreur de connexion à l'API météo");
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['error'])) {
            throw new Exception($data['error']['message']);
        }
        
        return $data;
    } catch (Exception $e) {
        error_log("Erreur météo: " . $e->getMessage());
        return null;
    }
}

// Fonction pour afficher les prévisions météo
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
    
    echo '<div class="weather-container'.($isHomepage ? ' homepage-weather' : '').'">';
    
    if (!$isHomepage) {
        echo '<h2 class="weather-location-title">Prévisions à '.htmlspecialchars($locationName).'</h2>';
    }
    
    echo '<div class="weather-grid">';
    
    foreach ($weatherData['forecast']['forecastday'] as $day) {
        $date = new DateTime($day['date']);
        $weatherCode = $day['day']['condition']['code'];
        
        echo '<div class="weather-card">';
        echo '<div class="weather-date">'.$date->format('D j M').'</div>';
        echo '<div class="weather-icon"><img src="'.$day['day']['condition']['icon'].'" alt="'.$day['day']['condition']['text'].'"></div>';
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





function incrementerVisite() {
    $chemin = 'nbvisite.csv';
    
    // Initialize default value
    $nombre = 0;
    
    // Check if file exists and is readable
    if (file_exists($chemin) && is_readable($chemin)) {
        // Handle empty file case
        $contenu = file_get_contents($chemin);
        if ($contenu !== false && $contenu !== '') {
            $nombre = (int) trim($contenu);
        }
    }
    
    $result = "$nombre fois<br/>";
    $nombre++;
    
    // Try to write the new count (converting to string)
    $handle = fopen($chemin, 'w');
    if ($handle === false) {
        // If we can't write, return the current count
        return $result;
    }
    
    fwrite($handle, (string)$nombre);  // Explicitly cast to string
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
        'expires' => time() + (30 * 24 * 60 * 60) // 30 jours
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





?>

