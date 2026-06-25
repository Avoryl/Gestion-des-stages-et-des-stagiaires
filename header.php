<?php
require_once 'config.php';
$user_role = current_user_role();
$user_name = current_user_name();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1d4ed8;
            --primary-dark: #1e3a8a;
            --success: #16a34a;
            --success-dark: #15803d;
            --danger: #dc2626;
            --danger-dark: #b91c1c;
            --bg: linear-gradient(135deg, #dbeafe 0%, #eff6ff 45%, #f8fafc 100%);
            --card: #ffffff;
            --text: #1f2937;
            --text-muted: #6b7280;
            --border: #dbeafe;
            --shadow: 0 24px 60px rgba(30, 58, 138, 0.12);
            --shadow-sm: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --radius: 22px;
            --radius-sm: 12px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            background: var(--bg); 
            color: var(--text); 
            line-height: 1.6; 
            min-height: 100vh;
            font-family: 'Work Sans', system-ui, -apple-system, sans-serif;
        }
        .main-container { max-width: 1200px; margin: 0 auto; padding: 24px; }
        h1 { text-align: center; color: var(--text); margin: 20px 0 40px; font-weight: 800; font-size: 2.25rem; letter-spacing: -0.025em; }
        
        .barre { 
            display: flex; align-items: center; justify-content: space-between; 
            background: linear-gradient(180deg, rgba(29, 78, 216, 0.96), rgba(30, 58, 138, 0.96)); 
            padding: 1rem 2rem; 
            border-radius: var(--radius-sm); 
            box-shadow: var(--shadow); 
            margin: 24px auto 2rem; 
            max-width: 1200px; 
            color: #fff; 
            gap: 1rem; 
        }
        .llgo { display: flex; align-items: center; gap: 0.8rem; }
        .logo { 
            height: 50px; width: auto; 
            background: rgba(255,255,255,0.2); 
            padding: 8px; 
            border-radius: 18px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.2); 
        }
        .user-info {
            color: rgba(255,255,255,0.9); font-size: 0.85rem; 
        }
        .logout { 
            color: #fee2e2; text-decoration: none; font-weight: 600; 
            padding: 0.5rem 0.8rem; border-radius: var(--radius-sm); 
            border: 1px solid rgba(254,226,226,0.5); 
            transition: all 0.3s; font-size: 0.85rem; 
            background: rgba(254,226,226,0.1);
        }
        .logout:hover { background: rgba(254,226,226,0.3); transform: translateY(-2px); }

        /* Bouton Menu (3 traits) */
        .menu-toggle {
            cursor: pointer;
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 10px;
            z-index: 1100;
        }
        .menu-toggle span {
            display: block;
            width: 30px;
            height: 3px;
            background: #fff;
            border-radius: 4px;
            transition: 0.3s;
        }

        /* Transformation en X */
        .menu-toggle.open span:nth-child(1) { transform: translateY(9px) rotate(45deg); }
        .menu-toggle.open span:nth-child(2) { opacity: 0; transform: translateX(-10px); }
        .menu-toggle.open span:nth-child(3) { transform: translateY(-9px) rotate(-45deg); }

        /* Slide Bar (Sidebar) */
        nav { 
            position: fixed;
            top: 0;
            right: -320px; /* Caché par défaut */
            width: 300px;
            height: 100vh;
            background: var(--primary-dark);
            padding: 6rem 1.5rem 2rem;
            z-index: 1000;
            transition: right 0.25s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: -10px 0 30px rgba(0,0,0,0.3);
            display: block !important;
            visibility: hidden; /* Empêche le flash au chargement */
        }
        nav.open { right: 0; visibility: visible; }

        nav ul { list-style: none; display: flex; flex-direction: column; gap: 0.5rem; }
        nav a { 
            color: rgba(255,255,255,0.9); text-decoration: none; 
            padding: 1rem 1.5rem; border-radius: var(--radius-sm); 
            font-weight: 600; font-size: 1rem; transition: all 0.3s; 
            white-space: nowrap;
            display: block;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        nav a:hover { background: rgba(255,255,255,0.1); color: #fff; transform: translateX(5px); }
        nav a.active { background: rgba(255,255,255,0.25); color: #fff; }
        nav a i {
            width: 24px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Overlay (fond sombre quand le menu est ouvert) */
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; z-index: 999; backdrop-filter: blur(4px); }
        .overlay.active { display: block; }

        .table-container { 
            background: var(--card); border-radius: var(--radius-sm); overflow-x: auto; 
            box-shadow: var(--shadow-sm); border: 1px solid rgba(219,234,254,0.5); 
            margin-bottom: 2rem; 
        }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { 
            background: #f8fafc; color: var(--text-muted); font-weight: 700; 
            font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; 
            padding: 1.25rem 1.75rem; border-bottom: 2px solid var(--border); 
        }
        td { padding: 1.25rem 1.75rem; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f8fafc; }

        .btn { 
            display: inline-flex; align-items: center; justify-content: center; 
            padding: 0.75rem 1.5rem; border-radius: var(--radius-sm); text-decoration: none; 
            font-weight: 600; font-size: 0.9rem; transition: all 0.3s; 
            border: none; cursor: pointer; gap: 0.5rem; white-space: nowrap; 
        }
        .btn-primary { background: var(--primary); color: #fff; box-shadow: var(--shadow-sm); }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: var(--shadow); }
        .btn-success { background: var(--success); color: #fff; }
        .btn-success:hover { background: var(--success-dark); transform: translateY(-2px); }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-danger:hover { background: var(--danger-dark); transform: translateY(-2px); }

        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .card { 
            background: var(--card); padding: 2rem; border-radius: var(--radius-sm); 
            text-align: center; box-shadow: var(--shadow-sm); 
            border: 1px solid var(--border); transition: transform 0.3s; 
        }
        .card:hover { transform: translateY(-4px); }
        .card p { color: var(--text-muted); font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
        .card h2 { color: var(--primary); font-size: 2.75rem; font-weight: 800; margin: 0; }

        .alert { 
            padding: 1.25rem 1.5rem; border-radius: var(--radius-sm); margin-bottom: 2rem; 
            font-size: 0.95rem; font-weight: 600; border: 1px solid transparent; 
        }
        .alert-success { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border-color: #fecaca; }

        form { 
            background: var(--card); padding: 2.5rem; border-radius: var(--radius-sm); 
            box-shadow: var(--shadow-sm); border: 1px solid var(--border); 
            max-width: 800px; margin: 0 auto 2rem; 
        }
        .form-group { margin-bottom: 1.75rem; }
        label { display: block; font-weight: 700; margin-bottom: 0.75rem; font-size: 0.9rem; color: var(--text); }
        input, select, textarea { 
            width: 100%; padding: 1rem 1.25rem; border: 2px solid #e2e8f0; 
            border-radius: var(--radius-sm); font-size: 1rem; font-family: inherit;
            transition: all 0.3s; background: #fafbff; 
        }
        input:focus, select:focus, textarea:focus { 
            outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(29,78,216,0.15); 
            background: #fff; 
        }

        /* Loader Styles */
        #page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8); /* Fond semi-transparent */
            z-index: 9999; /* Assure qu'il est au-dessus de tout */
            display: flex;
            justify-content: center;
            align-items: center;
            visibility: hidden; /* Caché par défaut */
            opacity: 0;
            transition: opacity 0.2s ease-out;
        }
        #page-loader.show {
            visibility: visible;
            opacity: 1;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(29, 78, 216, 0.2);
            border-top: 5px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

@media (max-width: 768px) {
            .barre { padding: 1rem; }
            .main-container { padding: 16px; }
            h1 { font-size: 1.875rem; }
            .stats { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; }
            .back-btn { bottom: 20px; right: 20px; width: 55px; height: 55px; font-size: 1.3rem; }
        }
    </style>
</head>

<body>
    <div class="main-container">
    <?php if (is_logged_in()): ?>
    <div class="barre">
        <div class="llgo">
            <img src="logo.png" class="logo" alt="Logo">
            <div class="user-info">
                Bonjour, <strong><?= e($user_name) ?></strong><br>
                <small style="opacity: 0.8;"><?= ucfirst(e($user_role)) ?></small>
            </div>
            <a href="logout.php" class="logout">Déconnexion</a>
        </div>

        <div class="menu-toggle" id="menuToggle">
            <span></span><span></span><span></span>
        </div>

        <nav>
            <ul>
                <li><a href="Acceuil.php"><i class="fa-solid fa-house"></i> Accueil</a></li>
                <li><a href="ValidationStagiaire.php"><i class="fa-solid fa-user-clock"></i> Candidatures</a></li>
                <li><a href="ListeStagiairesValides.php"><i class="fa-solid fa-user-graduate"></i> Nos Stagiaires</a></li>
                <li><a href="Historique.php"><i class="fa-solid fa-clock-rotate-left"></i> Historique</a></li>
                <?php if (is_gestionnaire()): ?>
                    <li><a href="GestionDepartements.php"><i class="fa-solid fa-building"></i> Départements</a></li>
                    <li><a href="AddStage.php"><i class="fa-solid fa-briefcase"></i> Gestion Stages</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <div class="overlay" id="overlay"></div>
    <?php endif; ?>

    <!-- Affichage centralisé des messages flash -->
    <?php $flash = get_flash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
    <?php endif; ?>

    <?php 
    $currentPage = basename($_SERVER['PHP_SELF']);
    if (is_logged_in() && $currentPage !== 'Acceuil.php'): ?>
        <div style="margin-bottom: 1.5rem;">
            <a href="javascript:history.back()" class="btn" style="background: #6b7280; color: #fff; padding: 0.6rem 1.2rem; font-size: 0.85rem;">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>
    <?php endif; ?>

    <div id="page-loader">
        <div class="spinner"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const nav = document.querySelector('nav');
            const overlay = document.getElementById('overlay');

            // Function to reset menu state
            function resetMenu() {
                nav.style.transition = 'none'; // Désactive l'animation pour un reset instantané
                menuToggle.classList.remove('open');
                nav.classList.remove('open');
                overlay.classList.remove('active');
                document.body.style.overflow = ''; // Restore scroll
                nav.offsetHeight; // Force un repaint
                nav.style.transition = ''; // Rétablit l'animation
            }
            function toggleMenu() {
                menuToggle.classList.toggle('open');
                nav.classList.toggle('open');
                overlay.classList.toggle('active');
                document.body.style.overflow = nav.classList.contains('open') ? 'hidden' : '';
            }
            
            if (menuToggle && overlay) {
                menuToggle.addEventListener('click', toggleMenu);
                overlay.addEventListener('click', toggleMenu);

                // Ensure menu is closed on page load
                resetMenu();
            }

            // Loader logic
            const pageLoader = document.getElementById('page-loader');
            const navLinks = document.querySelectorAll('a'); // Sélectionne tous les liens

            // Fix for browser back/forward arrows (BFCache)
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    pageLoader.classList.remove('show');
                    resetMenu(); // Ferme le menu immédiatement si on revient en arrière
                }
            });

            // Show loader when a navigation link is clicked
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    // Only show loader for internal links, not external or anchor links
                    // And exclude download scripts which don't trigger a page reload
                    if (this.href && this.hostname === window.location.hostname && this.hash === '' && !this.href.includes('download.php') && !this.href.includes('generer_recepisse.php')) {
                        pageLoader.classList.add('show');
                    }
                });
            });

            // Hide loader when the page finishes loading
            window.addEventListener('load', function() {
                pageLoader.classList.remove('show');
            });
        });
    </script>
