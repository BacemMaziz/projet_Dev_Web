document.addEventListener('DOMContentLoaded', () => {
    // Configuration
    const SCROLL_THRESHOLD = 50;
    
    // Éléments DOM
    const header = document.querySelector('.header-ultra');
    const hamburger = document.getElementById('hamburger');
    const menu = document.getElementById('menu');
    const overlay = document.getElementById('overlay');
    const closeMenuBtn = document.getElementById('closeMenuBtn');
    const navCanvas = document.getElementById('navCanvas');
    const weatherIndicator = document.querySelector('.weather-indicator');
    const menuLinks = document.querySelectorAll('.menu-link');
    const logo = document.querySelector('.nav-logo');
    
    // Gestion du scroll
    function handleScroll() {
        const scrollPosition = window.scrollY || document.documentElement.scrollTop;
        header.classList.toggle('scrolled', scrollPosition > SCROLL_THRESHOLD);
    }
    
    // Menu mobile
    function toggleMobileMenu() {
        const isOpening = !menu.classList.contains('active');
        const mainContent = document.querySelector('.main-content');
        const footer = document.querySelector('.footer');
        
        if (isOpening) {
            // Ouverture du menu
            menu.style.display = 'flex';
            overlay.style.display = 'block';
            
            setTimeout(() => {
                menu.classList.add('active');
                overlay.classList.add('active');
                hamburger.style.opacity = '0';
                hamburger.style.pointerEvents = 'none';
            }, 10);
            if (mainContent) {
                mainContent.classList.add('blur-effect');
            }
            
            // Désactive le scroll
            document.body.style.overflow = 'hidden';
        } else {
            // Fermeture du menu
            menu.classList.remove('active');
            overlay.classList.remove('active');
            hamburger.style.opacity = '1';
            hamburger.style.pointerEvents = 'auto';
            if (mainContent) {
                mainContent.classList.remove('blur-effect');
            }
            setTimeout(() => {
                menu.style.display = 'none';
                overlay.style.display = 'none';
                // Réactive le scroll
                document.body.style.overflow = '';
            }, 500);
        }
    }
    
    // Animation de la météo
    function animateWeather() {
        const weatherTypes = ['sunny', 'cloudy', 'rainy'];
        let currentWeather = 0;
        
        setInterval(() => {
            currentWeather = (currentWeather + 1) % weatherTypes.length;
            weatherIndicator.setAttribute('data-weather', weatherTypes[currentWeather]);
            
            // Réinitialiser l'animation
            weatherIndicator.style.width = '0';
            void weatherIndicator.offsetWidth;
            weatherIndicator.style.width = '100%';
        }, 15000);
    }
    
    // Animation du canvas navbar
    function initNavCanvas() {
        if (!navCanvas) return;
        
        navCanvas.width = navCanvas.offsetWidth;
        navCanvas.height = navCanvas.offsetHeight;
        
        const ctx = navCanvas.getContext('2d');
        const particles = [];
        const particleCount = Math.floor(navCanvas.width / 10);
        
        // Création des particules
        for (let i = 0; i < particleCount; i++) {
            particles.push({
                x: Math.random() * navCanvas.width,
                y: Math.random() * navCanvas.height,
                size: Math.random() * 2 + 0.5,
                speed: Math.random() * 0.5 + 0.1,
                opacity: Math.random() * 0.2 + 0.05,
                color: `rgba(74, 137, 220, ${Math.random() * 0.3 + 0.1})`
            });
        }
        
        // Animation
        function animate() {
            ctx.clearRect(0, 0, navCanvas.width, navCanvas.height);
            
            particles.forEach(p => {
                p.y -= p.speed;
                if (p.y < 0) {
                    p.y = navCanvas.height;
                    p.x = Math.random() * navCanvas.width;
                }
                
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                ctx.fillStyle = p.color;
                ctx.globalAlpha = p.opacity;
                ctx.fill();
            });
            
            requestAnimationFrame(animate);
        }
        
        animate();
        
        // Redimensionnement
        window.addEventListener('resize', () => {
            navCanvas.width = navCanvas.offsetWidth;
            navCanvas.height = navCanvas.offsetHeight;
        });
    }
    
    // Fermer le menu mobile au clic sur un lien
    function closeMobileMenuOnClick() {
        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (menu.classList.contains('active')) {
                    toggleMobileMenu();
                }
            });
        });
    }
    
    // Initialisation
    function init() {
        window.addEventListener('scroll', handleScroll);
        handleScroll();
        
        if (hamburger) {
            hamburger.addEventListener('click', function(e) {
                e.stopPropagation(); // Empêche la propagation
                toggleMobileMenu();
            });
        }
        
        if (overlay) {
            overlay.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleMobileMenu();
            });
        }
        
        if (closeMenuBtn) {
            closeMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleMobileMenu();
            });
        }
        
        if (weatherIndicator) {
            animateWeather();
        }
        
        initNavCanvas();
        closeMobileMenuOnClick();
        
        // Animation des liens du menu au survol
        menuLinks.forEach(link => {
            link.addEventListener('mousemove', (e) => {
                const rect = link.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                link.style.setProperty('--mouse-x', `${x}px`);
                link.style.setProperty('--mouse-y', `${y}px`);
            });
        });
    }
    
    init();
});

// Animation du footer
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('weatherCanvas');
    if (canvas) {
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
        
        const ctx = canvas.getContext('2d');
        const particles = [];
        const particleCount = window.innerWidth < 768 ? 30 : 80;
        
        const weatherTypes = [
            { color: 'rgba(74, 137, 220, 0.8)', speed: 2, size: 1.5, type: 'rain' },
            { color: 'rgba(255, 255, 255, 0.8)', speed: 1, size: 2.5, type: 'snow' },
            { color: 'rgba(200, 200, 255, 0.6)', speed: 0.5, size: 4, type: 'cloud' }
        ];
        
        for (let i = 0; i < particleCount; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                type: weatherTypes[Math.floor(Math.random() * weatherTypes.length)],
                opacity: Math.random() * 0.5 + 0.1,
                sway: Math.random() * 2 - 1
            });
        }
        
        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            particles.forEach(p => {
                p.y += p.type.speed;
                p.x += p.sway * 0.1;
                
                if (p.y > canvas.height) {
                    p.y = -10;
                    p.x = Math.random() * canvas.width;
                }
                
                ctx.beginPath();
                ctx.globalAlpha = p.opacity;
                
                if (p.type.type === 'rain') {
                    ctx.moveTo(p.x, p.y);
                    ctx.lineTo(p.x - 2, p.y + 10);
                    ctx.strokeStyle = p.type.color;
                    ctx.lineWidth = p.type.size;
                    ctx.stroke();
                } 
                else if (p.type.type === 'snow') {
                    ctx.arc(p.x, p.y, p.type.size, 0, Math.PI * 2);
                    ctx.fillStyle = p.type.color;
                    ctx.fill();
                    
                    for (let i = 0; i < 6; i++) {
                        ctx.beginPath();
                        ctx.moveTo(p.x, p.y);
                        ctx.lineTo(
                            p.x + Math.cos(i * Math.PI / 3) * p.type.size * 1.5,
                            p.y + Math.sin(i * Math.PI / 3) * p.type.size * 1.5
                        );
                        ctx.strokeStyle = p.type.color;
                        ctx.lineWidth = 1;
                        ctx.stroke();
                    }
                }
                else if (p.type.type === 'cloud') {
                    ctx.arc(p.x, p.y, p.type.size, 0, Math.PI * 2);
                    ctx.arc(p.x + p.type.size, p.y, p.type.size * 0.8, 0, Math.PI * 2);
                    ctx.arc(p.x - p.type.size, p.y, p.type.size * 0.6, 0, Math.PI * 2);
                    ctx.fillStyle = p.type.color;
                    ctx.fill();
                }
            });
            
            requestAnimationFrame(animateParticles);
        }
        
        animateParticles();
        
        window.addEventListener('resize', () => {
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
        });
    }
    
    const footer = document.querySelector('.footer');
    if (footer) {
        document.addEventListener('scroll', () => {
            const scrollPercent = (window.scrollY + window.innerHeight - footer.offsetTop) / window.innerHeight;
            footer.style.setProperty('--scroll-effect', scrollPercent);
        });
    }
    
    const sections = document.querySelectorAll('.footer-section');
    sections.forEach(section => {
        section.addEventListener('mousemove', (e) => {
            const rect = section.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            section.style.setProperty('--mouse-x', `${x / rect.width * 100}%`);
            section.style.setProperty('--mouse-y', `${y / rect.height * 100}%`);
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Création des éléments météo
    const footer = document.querySelector('.footer');
    const weatherCanvas = document.getElementById('weatherCanvas');
    
    // Animation de soleil (si canvas existe)
    if (weatherCanvas) {
        weatherCanvas.width = footer.offsetWidth;
        weatherCanvas.height = footer.offsetHeight;
        const ctx = weatherCanvas.getContext('2d');
        
        // Rayons de soleil
        function drawSun() {
            ctx.clearRect(0, 0, weatherCanvas.width, weatherCanvas.height);
            
            const centerX = weatherCanvas.width * 0.8;
            const centerY = weatherCanvas.height * 0.2;
            const radius = 40;
            const rayCount = 12;
            
            // Dessin du soleil
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255, 212, 100, 0.15)';
            ctx.fill();
            
            // Dessin des rayons
            for (let i = 0; i < rayCount; i++) {
                const angle = (i * Math.PI * 2) / rayCount;
                const rayLength = radius * 2 + Math.sin(Date.now() / 500 + i) * 10;
                const x2 = centerX + Math.cos(angle) * rayLength;
                const y2 = centerY + Math.sin(angle) * rayLength;
                
                ctx.beginPath();
                ctx.moveTo(centerX, centerY);
                ctx.lineTo(x2, y2);
                ctx.lineWidth = 2;
                ctx.strokeStyle = `rgba(255, 212, 100, ${0.3 + Math.sin(Date.now()/700 + i)*0.2})`;
                ctx.stroke();
            }
            
            requestAnimationFrame(drawSun);
        }
        
        drawSun();
    }
    function createClouds() {
        const footer = document.querySelector('footer'); // Sélection explicite
        if (!footer) { // Sécurité si l'élément n'existe pas
            console.error("Erreur : Élément 'footer' introuvable !");
            return;
        }
    
        const cloudCount = 5;
        for (let i = 0; i < cloudCount; i++) {
            const cloud = document.createElement('div');
            cloud.className = 'cloud';
            
            const size = Math.random() * 100 + 50;
            cloud.style.width = `${size}px`;
            cloud.style.height = `${size/2}px`;
            cloud.style.top = `${Math.random() * 100}%`;
            cloud.style.left = `${Math.random() * 20 - 30}%`;
            cloud.style.animationDuration = `${Math.random() * 40 + 40}s`;
            cloud.style.animationDelay = `${Math.random() * 20}s`;
            
            footer.appendChild(cloud);
        }
    }
    
    // Exécution après chargement du DOM


    /*
    // Création de nuages
    function createClouds() {
        const cloudCount = 5;
        
        for (let i = 0; i < cloudCount; i++) {
            const cloud = document.createElement('div');
            cloud.className = 'cloud';
            
            // Position et taille aléatoire
            const size = Math.random() * 100 + 50;
            const posY = Math.random() * 100;
            const duration = Math.random() * 40 + 40;
            
            cloud.style.width = `${size}px`;
            cloud.style.height = `${size/2}px`;
            cloud.style.top = `${posY}%`;
            cloud.style.left = `${Math.random() * 20 - 30}%`;
            cloud.style.animationDuration = `${duration}s`;
            cloud.style.animationDelay = `${Math.random() * 20}s`;
            
            footer.appendChild(cloud);
        }
    }
    */
    // Création d'effets de pétales (alternative à la neige)


    function createPetals() {
        const footer = document.querySelector('footer'); // Sélection du footer
        if (!footer) { // Vérification pour éviter l'erreur
            console.error("Erreur : aucun élément 'footer' trouvé !");
            return;
        }
    
        const petalCount = 15;
        const types = ['🌸', '🍃', '❀', '✿', '🌼'];
        
        for (let i = 0; i < petalCount; i++) {
            const petal = document.createElement('div');
            petal.className = 'petal';
            petal.textContent = types[Math.floor(Math.random() * types.length)];
            
            // Styles aléatoires
            petal.style.left = `${Math.random() * 100}%`;
            petal.style.fontSize = `${Math.random() * 10 + 10}px`;
            petal.style.opacity = Math.random() * 0.5 + 0.3;
            petal.style.animation = `fallPetals ${Math.random() * 15 + 10}s linear infinite`;
            petal.style.animationDelay = `${Math.random() * 5}s`;
            petal.style.transform = `rotate(${Math.random() * 360}deg)`;
            
            footer.appendChild(petal); // Ajout sécurisé
        }
    }
    
    /*
    function createPetals() {
        const petalCount = 15;
        const types = ['🌸', '🍃', '❀', '✿', '🌼'];
        
        for (let i = 0; i < petalCount; i++) {
            const petal = document.createElement('div');
            petal.className = 'petal';
            
            // Choix aléatoire du type
            const type = types[Math.floor(Math.random() * types.length)];
            petal.textContent = type;
            
            // Position et animation aléatoire
            petal.style.left = `${Math.random() * 100}%`;
            petal.style.fontSize = `${Math.random() * 10 + 10}px`;
            petal.style.opacity = Math.random() * 0.5 + 0.3;
            petal.style.animation = `fallPetals ${Math.random() * 15 + 10}s linear infinite`;
            petal.style.animationDelay = `${Math.random() * 5}s`;
            petal.style.transform = `rotate(${Math.random() * 360}deg)`;
            
            footer.appendChild(petal);
        }
    }
    */
    // Lancement des animations
    createClouds();
    createPetals();
    
    // Ajout de CSS dynamique pour les pétales
    const style = document.createElement('style');
    style.textContent = `
        .petal {
            position: absolute;
            top: -20px;
            z-index: 10;
            pointer-events: none;
            user-select: none;
            will-change: transform;
        }
        
        @keyframes fallPetals {
            0% {
                transform: translateY(-20px) rotate(0deg);
            }
            100% {
                transform: translateY(calc(100vh + 20px)) rotate(360deg);
            }
        }
    `;
    document.head.appendChild(style);
    
    // Effet de scintillement aléatoire
    setInterval(() => {
        const sparkles = document.querySelectorAll('.footer-section');
        sparkles.forEach(section => {
            if (Math.random() > 0.8) {
                const spark = document.createElement('div');
                spark.style.position = 'absolute';
                spark.style.width = '10px';
                spark.style.height = '10px';
                spark.style.background = 'radial-gradient(circle, rgba(255,255,255,0.8) 0%, rgba(255,212,100,0) 70%)';
                spark.style.borderRadius = '50%';
                spark.style.left = `${Math.random() * 100}%`;
                spark.style.top = `${Math.random() * 100}%`;
                spark.style.animation = 'sparkle 1s ease-out';
                
                section.appendChild(spark);
                setTimeout(() => spark.remove(), 1000);
            }
        });
    }, 500);
});

document.addEventListener('DOMContentLoaded', function() {
    const footer = document.querySelector('.footer');
    const weatherContainer = document.getElementById('weather-elements');
    const weatherCanvas = document.getElementById('weatherCanvas');

    
    function createSunRays() {
        // Sélection sécurisée
        const weatherContainer = document.getElementById('weather-container');
        if (!weatherContainer) {
            console.error("Erreur : L'élément 'weather-container' est introuvable");
            return;
        }
    
        const rayCount = 8;
        for (let i = 0; i < rayCount; i++) {
            const ray = document.createElement('div');
            ray.className = 'weather-element sun-ray';
            ray.style.left = '85%';
            ray.style.bottom = '90%';
            ray.style.transform = `rotate(${i * (360/rayCount)}deg)`;
            ray.style.animationDelay = `${i * 0.2}s`;
            weatherContainer.appendChild(ray);
        }
    }
    
    /*
    // Créer des rayons de soleil
    function createSunRays() {
        const rayCount = 8;
        for (let i = 0; i < rayCount; i++) {
            const ray = document.createElement('div');
            ray.className = 'weather-element sun-ray';
            ray.style.left = '85%';
            ray.style.bottom = '90%';
            ray.style.transform = `rotate(${i * (360/rayCount)}deg)`;
            ray.style.animationDelay = `${i * 0.2}s`;
            weatherContainer.appendChild(ray);
        }
    }
    */
    // Créer des nuages
    function createClouds() {
        const cloudCount = 5;
        for (let i = 0; i < cloudCount; i++) {
            const cloud = document.createElement('div');
            cloud.className = 'weather-element weather-cloud';
            
            // Taille aléatoire entre 80px et 200px
            const size = Math.random() * 120 + 80;
            cloud.style.width = `${size}px`;
            cloud.style.height = `${size/2}px`;
            
            // Position aléatoire
            cloud.style.top = `${Math.random() * 30}%`;
            cloud.style.left = `${Math.random() * 20 - 30}%`;
            
            // Animation aléatoire
            cloud.style.animationDuration = `${Math.random() * 40 + 60}s`;
            cloud.style.animationDelay = `${Math.random() * 20}s`;
            
            weatherContainer.appendChild(cloud);
        }
    }
    
    // Initialiser le canvas
    function initWeatherCanvas() {
        if (weatherCanvas) {
            weatherCanvas.width = footer.offsetWidth;
            weatherCanvas.height = footer.offsetHeight;
            const ctx = weatherCanvas.getContext('2d');
            
            function drawSun() {
                ctx.clearRect(0, 0, weatherCanvas.width, weatherCanvas.height);
                
                // Dessiner un soleil avec gradient
                const gradient = ctx.createRadialGradient(
                    weatherCanvas.width * 0.85, weatherCanvas.height * 0.15, 30,
                    weatherCanvas.width * 0.85, weatherCanvas.height * 0.15, 70
                );
                gradient.addColorStop(0, 'rgba(255, 236, 168, 0.3)');
                gradient.addColorStop(1, 'rgba(255, 236, 168, 0)');
                
                ctx.beginPath();
                ctx.arc(
                    weatherCanvas.width * 0.85, 
                    weatherCanvas.height * 0.15, 
                    70, 0, Math.PI * 2
                );
                ctx.fillStyle = gradient;
                ctx.fill();
                
                requestAnimationFrame(drawSun);
            }
            
            drawSun();
        }
    }
    
    // Lancer toutes les animations
    initWeatherCanvas();
    createSunRays();
    createClouds();
});

document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('snowCanvas');
    if (!canvas) return;

    // Configuration artistique
    const config = {
        particleCount: 80,
        types: [
            { size: 2.5, speed: 0.5, sway: 0.3, blur: 2, alpha: 0.8, color: [255, 255, 255] },
            { size: 3.5, speed: 0.7, sway: 0.5, blur: 3, alpha: 0.9, color: [230, 240, 255] },
            { size: 4.5, speed: 0.3, sway: 0.2, blur: 4, alpha: 1.0, color: [200, 220, 255] }
        ],
        wind: 0.1,
        turbulence: 0.05,
        sparkleFrequency: 0.02
    };

    // Initialisation
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;
    const ctx = canvas.getContext('2d');
    const particles = [];

    // Création des particules
    class Snowflake {
        constructor() {
            this.type = config.types[Math.floor(Math.random() * config.types.length)];
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * -canvas.height;
            this.z = Math.random() * 0.5 + 0.5;
            this.rotation = Math.random() * Math.PI * 2;
            this.rotationSpeed = (Math.random() - 0.5) * 0.05;
            this.windOffset = Math.random() * Math.PI * 2;
            this.windSpeed = Math.random() * 0.01 + 0.01;
            this.sizeVariation = Math.random() * 0.3 + 0.85;
            this.sparkle = Math.random() > 0.8;
        }

        update() {
            // Physique de chute
            this.y += this.type.speed * this.z;
            this.x += Math.sin(Date.now() * this.windSpeed + this.windOffset) * this.type.sway * config.wind;
            this.rotation += this.rotationSpeed;

            // Réapparition en haut
            if (this.y > canvas.height) {
                this.y = Math.random() * -50;
                this.x = Math.random() * canvas.width;
                this.sparkle = Math.random() > 0.9;
            }

            // Variation aléatoire de taille
            this.sizeVariation = 0.85 + Math.sin(Date.now() * 0.001) * 0.15;
        }

        draw() {
            const size = this.type.size * this.z * this.sizeVariation;
            const alpha = this.type.alpha * this.z;
            const blur = this.type.blur * this.z;

            ctx.save();
            ctx.translate(this.x, this.y);
            ctx.rotate(this.rotation);
            ctx.globalAlpha = alpha;

            // Effet de flou
            ctx.shadowBlur = blur;
            ctx.shadowColor = `rgba(${this.type.color.join(',')}, ${alpha})`;

            // Dessin du flocon (forme hexagonale complexe)
            ctx.beginPath();
            for (let i = 0; i < 6; i++) {
                const angle = (i * Math.PI * 2) / 6;
                const radius = size;
                const x = Math.cos(angle) * radius;
                const y = Math.sin(angle) * radius;
                
                if (i === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);

                // Branches avec courbes
                const branchLength = radius * 1.8;
                const branchX = Math.cos(angle) * branchLength;
                const branchY = Math.sin(angle) * branchLength;
                
                const cp1x = x + (branchX - x) * 0.3;
                const cp1y = y + (branchY - y) * 0.3;
                const cp2x = x + (branchX - x) * 0.7;
                const cp2y = y + (branchY - y) * 0.7;
                
                ctx.moveTo(x, y);
                ctx.bezierCurveTo(cp1x, cp1y, cp2x, cp2y, branchX, branchY);
            }

            // Effet scintillant occasionnel
            if (this.sparkle && Math.random() < config.sparkleFrequency) {
                ctx.fillStyle = `rgba(255, 255, 255, ${alpha * 1.5})`;
                ctx.fill();
            } else {
                ctx.strokeStyle = `rgba(${this.type.color.join(',')}, ${alpha})`;
                ctx.lineWidth = 1.5;
                ctx.stroke();
            }

            ctx.restore();
        }
    }

    // Initialisation des particules
    for (let i = 0; i < config.particleCount; i++) {
        particles.push(new Snowflake());
        particles[i].y = Math.random() * canvas.height; // Répartir sur l'écran
    }

    // Animation
    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Fond dégradé pour effet de profondeur
        const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
        gradient.addColorStop(0, 'rgba(10, 20, 40, 0.1)');
        gradient.addColorStop(1, 'rgba(5, 10, 20, 0.3)');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Mise à jour et dessin
        particles.forEach(flake => {
            flake.update();
            flake.draw();
        });

        requestAnimationFrame(animate);
    }

    // Redimensionnement
    window.addEventListener('resize', () => {
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
    });

    // Démarrer l'animation
    animate();

    // Interaction avec le vent
    document.addEventListener('mousemove', (e) => {
        const mouseX = e.clientX;
        const center = canvas.width / 2;
        config.wind = (mouseX - center) / center * 0.3;
    });
});