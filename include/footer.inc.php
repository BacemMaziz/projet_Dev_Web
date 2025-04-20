<footer class="meteo-footer">
<canvas id="snowCanvas" aria-hidden="true"></canvas>
    <!-- Animation de neige -->
    <div class="snowfall">
        <div class="snowflake">❄</div>
        <div class="snowflake">❄</div>
        <div class="snowflake">❄</div>
    </div>

    <div class="footer-content">
        <!-- Logo et nom du site -->
        <div class="footer-brand">
            <a href="index.php" class="footer-logo">
                <span class="logo-icon">⛅</span>
                <span class="logo-text">Actu<span>Meteo</span></span>
            </a>
            <p class="footer-slogan">Précision • Élégance • Innovation</p>
        </div>

        <!-- Liens vers les pages -->
        <div class="footer-links">
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="statistiques.php">Statistiques</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="developper.php">Développement</a></li>
            </ul>
        </div>

        <!-- Liens légaux -->
        <div class="footer-legal">
            <div class="legal-links">
                <a href="#">Confidentialité</a>
                <span>•</span>
                <a href="#">Cookies</a>
                <span>•</span>
                <a href="#">Conditions</a>
            </div>
            <p>Nombre de visite : <?php
                                   require_once './include/functions.php';
                                 echo incrementerVisite(); 
                                  ?></p>
            <p class="copyright">© 2025 ActuMeteo Pro</p>
        </div>
    </div>
</footer>
<?php if (isset($showCookieBanner) && $showCookieBanner): ?>
<div class="cookie-consent-banner">
    <div class="cookie-consent-content">
        <div class="cookie-consent-text">
            <p>Nous utilisons des cookies pour mémoriser vos préférences. En continuant, vous acceptez leur utilisation.</p>
        </div>
        <div class="cookie-consent-actions">
            <form method="POST">
                <button type="submit" name="cookie_choice" value="reject" class="cookie-consent-btn reject">
                    Refuser
                </button>
                <button type="submit" name="cookie_choice" value="accept" class="cookie-consent-btn accept">
                    Accepter
                </button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

</body>
</html>