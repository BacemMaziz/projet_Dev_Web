<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Page tech</title>
    <link rel="stylesheet" href="jour.css" />
    <link rel="icon" type="Image/x-icon" href="Images/api.ico" />
    
</head>
<body class="developper-page specific-page">
    <section>
        <h2 id="om">Utilisation d'API</h2>

    
        <!-- Article 1 : Image du jour de la NASA -->
        <article>
            <h2>L'image du jour depuis l'API de la Nasa</h2>
            <div id="nasa-container">
                <?php
                // Clé API pour accéder à l'API de la NASA
                $key = "NnMh0PNJhzC5AH8dFfdmgV6fHaKH3yFJsvcorvBL";

                // Configuration des options pour la requête HTTP
                $opts = array(
                    'http' => array(
                        'method' => 'GET', // Utilisation de la méthode GET
                        'header' => 'User-Agent: PHP' // En-tête de la requête
                    )
                );
                // Création du contexte pour la requête
                $context = stream_context_create($opts);

                // Récupération de la date du jour au format YYYY-MM-DD
                $date_du_jour = date("Y-m-d");

                // Construction de l'URL de l'API de la NASA pour l'image du jour
                $requete = "https://api.nasa.gov/planetary/apod?api_key=$key&date=$date_du_jour";

                // Envoi de la requête et récupération de la réponse
                $reponse = file_get_contents($requete, false, $context);

                // Vérification si la requête a échoué
                if ($reponse === FALSE) {
                    // Affichage d'un message d'erreur si la requête échoue
                    echo "<p class='error-message'>Erreur lors de la récupération des données</p>";
                } else {
                    // Décodage de la réponse JSON en un tableau associatif
                    $resultat = json_decode($reponse, true);

                    // Affichage du média (image ou vidéo)
                    echo "<div class='media-container'>";
                    if ($resultat['media_type'] === "image") {
                        // Si le média est une image, afficher une balise <img>
                        echo "<img src='$resultat[url]' alt='$resultat[title]' class='nasa-image' />";
                    } elseif ($resultat['media_type'] === "video") {
                        // Si le média est une vidéo, afficher une balise <video>
                        echo "<video src='$resultat[url]' controls class='nasa-video'></video>";
                    }else{
                        echo "<p> Aujourd'hui pas de Media  </p>";
                    }
                    echo "</div>";

                    // Affichage du titre et de la description
                    echo "<div class='description-container'>";
                    echo "<h2 class='nasa-title'>$resultat[title]</h2>"; // Titre de l'image/vidéo
                    echo "<p class='nasa-explanation'>$resultat[explanation]</p>"; // Description
                    echo "</div>";
                }
                ?>
            </div>
        </article>

        <!-- Article 2 : Coordonnées géographiques avec GeoPlugin -->
        <article>
            <h2>Vos coordonnées géographiques selon GeoPlugin</h2>
            <?php
            // Récupération de l'adresse IP de l'utilisateur
            $user_IP = $_SERVER['REMOTE_ADDR'];

            // Configuration des options pour la requête HTTP
            $opts = array(
                'http' => array(
                    'method' => 'GET', // Utilisation de la méthode GET
                    'header' => 'User-Agent: PHP' // En-tête de la requête
                )
            );
            // Création du contexte pour la requête
            $context = stream_context_create($opts);

            // Construction de l'URL de l'API GeoPlugin pour obtenir les informations géographiques
            $requete = "http://www.geoplugin.net/xml.gp?ip=$user_IP";

            // Envoi de la requête et récupération de la réponse
            $reponse = file_get_contents($requete, false, $context);

            // Vérification si la requête a échoué
            if ($reponse === FALSE) {
                // Affichage d'un message d'erreur si la requête échoue
                echo "<p class='error-message'>Les données ont été mal récupérées</p>";
            } else {
                // Conversion de la réponse XML en un objet SimpleXML
                $xml = simplexml_load_string($reponse);

                // Vérification si la conversion XML a échoué
                if ($xml === FALSE) {
                    // Affichage d'un message d'erreur si le XML est invalide
                    echo "<p class='error-message' >Erreur : la réponse de GeoPlugin est invalide.</p>";
                } else {
                    // Affichage des informations géographiques sous forme de liste
                    echo "<ul>";
                    if (!empty($xml->geoplugin_countryName)) {
                        // Affichage du pays
                        echo "<li>Pays : " . $xml->geoplugin_countryName . "</li>";
                    }
                    if (!empty($xml->geoplugin_region)) {
                        // Affichage de la région
                        echo "<li>Région : " . $xml->geoplugin_region . "</li>";
                    }
                    if (!empty($xml->geoplugin_regionName)) {
                        // Affichage du département
                        echo "<li>Département : " . $xml->geoplugin_regionName . "</li>";
                    }
                    if (!empty($xml->geoplugin_city)) {
                        // Affichage de la ville
                        echo "<li>Ville : " . $xml->geoplugin_city . "</li>";
                    }
                    if (!empty($xml->geoplugin_latitude)) {
                        // Affichage de la latitude
                        echo "<li>Latitude : " . $xml->geoplugin_latitude . "</li>";
                    }
                    if (!empty($xml->geoplugin_longitude)) {
                        // Affichage de la longitude
                        echo "<li>Longitude : " . $xml->geoplugin_longitude . "</li>";
                    }
                    echo "</ul>";
                }
            }
            ?>
        </article>

        <!-- Article 3 : Informations de connexion avec ipinfo.io -->
        <article>
            <h2>Vos informations de connexion sont :</h2>
            <?php
            // Token d'accès pour l'API ipinfo.io
            $token = "37ee27659f0f07";

            // Récupération de l'adresse IP de l'utilisateur
            $user_IP = $_SERVER['REMOTE_ADDR'];

            // Configuration des options pour la requête HTTP
            $opts = array(
                'http' => array(
                    'method' => 'GET', // Utilisation de la méthode GET
                    'header' => 'User-Agent: PHP' // En-tête de la requête
                )
            );
            // Création du contexte pour la requête
            $context = stream_context_create($opts);

            // Construction de l'URL de l'API ipinfo.io pour obtenir les informations de connexion
            $requete = "https://ipinfo.io/$user_IP?token=$token";

            // Envoi de la requête et récupération de la réponse
            $reponse = file_get_contents($requete, false, $context);

            // Vérification si la requête a échoué
            if ($reponse === FALSE) {
                // Affichage d'un message d'erreur si la requête échoue
                echo "<p class='error-message'>Erreur lors de la récupération des informations</p>";
            } else {
                // Décodage de la réponse JSON en un tableau associatif
                $data = json_decode($reponse, true);

                // Vérification si le décodage JSON a échoué
                if ($data === FALSE) {
                    // Affichage d'un message d'erreur si le JSON est invalide
                    echo "<p class='error-message'>Erreur lors de la réponse de l'API</p>";
                } else {
                    // Affichage des informations de connexion sous forme de liste
                    echo "<ul>";
                    echo "<li>Votre adresse IP : " . $user_IP . "</li>"; // Adresse IP
                    echo "<li>Votre hostname : " . $data['hostname'] . "</li>"; // Nom d'hôte
                    echo "<li>Votre Ville : " . $data['city'] . "</li>"; // Ville
                    echo "<li>Votre Région : " . $data['region'] . "</li>"; // Région
                    echo "<li>Votre Pays : " . $data['country'] . "</li>"; // Pays
                    echo "<li>Votre localisation : " . $data['loc'] . "</li>"; // Coordonnées géographiques
                    echo "<li>Votre Code postal : " . $data['postal'] . "</li>"; // Code postal
                    echo "<li>Votre timezone : " . $data['timezone'] . "</li>"; // Fuseau horaire
                    echo "</ul>";
                }
            }
            ?>
        </article>

        <!-- Article 4 : Adresse IP avec whatismyip.com -->
        <article>
            <h2>Votre Adresse IP est :</h2>
            <?php
            // Récupération de l'adresse IP de l'utilisateur
            $user_IP = $_SERVER['REMOTE_ADDR'];

            // Construction de l'URL de l'API whatismyip.com pour obtenir l'adresse IP
            $requete = "https://api.whatismyip.com/ip.php?key=c4c2ee5367f54b1848d18ec64d28760f&input=$user_IP&outpour=xml";

            // Configuration des options pour la requête HTTP
            $opts = array(
                'http' => array(
                    'method' => 'GET', // Utilisation de la méthode GET
                    'header' => 'User-Agent: PHP' // En-tête de la requête
                )
            );
            // Création du contexte pour la requête
            $context = stream_context_create($opts);

            // Envoi de la requête et récupération de la réponse
            $reponse = file_get_contents($requete, false, $context);

            // Vérification si la requête a échoué
            if ($reponse === FALSE) {
                // Affichage d'un message d'erreur si la requête échoue
                echo "<p class='error-message'>Erreur lors de la récupération des informations</p>";
            } else {
                // Affichage de l'adresse IP
                echo "<p class='psg'>Les informations sur votre connexion sont : " . $reponse . "</p>";
            }
            ?>
        </article>
    
        </section>
    <!-- Bouton de retour en haut -->
     <button id="scrollToTopBtn" title="Retour en haut">↑</button>
     <script src="script.js"></script>
</body>
</html>