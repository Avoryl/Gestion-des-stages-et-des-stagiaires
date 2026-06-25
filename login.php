<?php
require_once 'config.php';

if (is_logged_in()) {
    redirect('Acceuil.php');
}

$oldIdentifier = '';
if (isset($_SESSION['old_login_identifier'])) {
    $oldIdentifier = (string) $_SESSION['old_login_identifier'];
    unset($_SESSION['old_login_identifier']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    $_SESSION['old_login_identifier'] = $identifier;

    if ($identifier === '' || $password === '') {
        set_flash('error', 'Veuillez renseigner votre identifiant et votre mot de passe.');
        redirect('login.php');
    }

    $pdo = db();
    $user = null;
    $role = null;

    // On vérifie d'abord les gestionnaires
    $stmt = $pdo->prepare("
        SELECT id, nom, prenom, email, mot_de_passe 
        FROM gestionnaires 
        WHERE email = :identifier 
           OR nom = :identifier 
           OR prenom = :identifier 
           OR CONCAT(prenom, ' ', nom) = :identifier 
           OR CONCAT(nom, ' ', prenom) = :identifier 
        LIMIT 1");
    $stmt->execute(['identifier' => $identifier]);
    $manager = $stmt->fetch();

    if ($manager && password_verify($password, $manager['mot_de_passe'])) {
        $user = $manager;
        $role = 'gestionnaire';
    } else {
        // Sinon on vérifie les admins
        $stmt = $pdo->prepare("
            SELECT id, nom, prenom, email, mot_de_passe 
            FROM admins 
            WHERE email = :identifier 
               OR nom = :identifier 
               OR prenom = :identifier 
               OR CONCAT(prenom, ' ', nom) = :identifier 
               OR CONCAT(nom, ' ', prenom) = :identifier 
            LIMIT 1");
        $stmt->execute(['identifier' => $identifier]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['mot_de_passe'])) {
            $user = $admin;
            $role = 'admin';
        }
    }

    if ($user && $role !== null) {
        $fullName = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
        if ($fullName === '') {
            $fullName = (string) ($user['nom'] ?? $identifier);
        }

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_role'] = $role;
        $_SESSION['user_name'] = $fullName;

        unset($_SESSION['old_login_identifier']);

        set_flash('success', 'Connexion réussie. Bienvenue ' . $fullName . ' !');
        redirect('Acceuil.php');
    }

    set_flash('error', 'Identifiant ou mot de passe incorrect.');
    redirect('login.php');
}

$flash = get_flash();
$errorMessage = ($flash && $flash['type'] === 'error') ? $flash['message'] : null;
$successMessage = ($flash && $flash['type'] === 'success') ? $flash['message'] : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Connexion - Gestion des stages</title>
    <style>
        :root {
            --primary: #1d4ed8;
            --primary-dark: #1e3a8a;
            --bg: linear-gradient(135deg, #dbeafe 0%, #eff6ff 45%, #f8fafc 100%);
            --card: #ffffff;
            --text: #1f2937;
            --text-muted: #6b7280;
            --border: #dbeafe;
            --danger-bg: #fee2e2;
            --danger-text: #991b1b;
            --success-bg: #dcfce7;
            --success-text: #166534;
            --shadow: 0 24px 60px rgba(30, 58, 138, 0.12);
            --radius: 22px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Work Sans', system-ui, -apple-system, sans-serif;
            color: var(--text);
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 980px;
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            background: var(--card);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .login-brand {
            padding: 48px 40px;
            background: linear-gradient(180deg, rgba(29, 78, 216, 0.96), rgba(30, 58, 138, 0.96));
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-brand img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.12);
            padding: 20px;
            border-radius: 18px;
        }

        .login-brand h1 {
            margin: 0 0 14px;
            font-size: 2rem;
            line-height: 1.2;
            font-weight: 800;
        }

        .login-brand p {
            margin: 0 0 10px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.92);
        }

        .login-panel {
            padding: 48px 36px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-panel h2 {
            margin: 0 0 8px;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .subtitle {
            margin: 0 0 26px;
            color: var(--text-muted);
            font-size: 1.05rem;
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .alert.error {
            background: var(--danger-bg);
            color: var(--danger-text);
            border-color: #fecaca;
        }

        .alert.success {
            background: var(--success-bg);
            color: var(--success-text);
            border-color: #bbf7d0;
        }

        form {
            display: grid;
            gap: 1.5rem;
        }

        label {
            display: block;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        input {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s;
            background: #fafbff;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.12);
            background: #fff;
        }

        button {
            border: none;
            border-radius: 12px;
            padding: 1.125rem 1.5rem;
            background: var(--primary);
            color: #fff;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .helper-text {
            margin-top: 1.5rem;
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
            text-align: center;
        }

        .helper-text a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }

        @media (max-width: 860px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                margin: 1rem;
                max-width: none;
            }

            .login-brand,
            .login-panel {
                padding: 2.5rem 2rem;
            }

            body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <section class="login-brand">
            <img src="logo.png" alt="Logo">
            <h1>Gestion des stages et des stagiaires</h1>
            <p>Accédez à l'espace de gestion pour suivre les candidatures, valider les stagiaires et administrer les stages.</p>
            <p>Connexion réservée au personnel autorisé.</p>
        </section>

        <section class="login-panel">
            <h2>Connexion</h2>
            <p class="subtitle">Identifiez-vous avec votre email ou votre nom.</p>

            <?php if ($errorMessage): ?>
                <div class="alert error"><?php echo e($errorMessage); ?></div>
            <?php endif; ?>

            <?php if ($successMessage): ?>
                <div class="alert success"><?php echo e($successMessage); ?></div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <div>
                    <label for="identifier">Email ou nom</label>
                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        value="<?php echo e($oldIdentifier); ?>"
                        placeholder="exemple@domaine.com ou votre nom"
                        required
                    >
                </div>

                <div>
                    <label for="password">Mot de passe</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Votre mot de passe"
                        required
                    >
                </div>

                <button type="submit">Se connecter</button>
            </form>

            <p class="helper-text" style="opacity: 0.7;">
                Espace de gestion sécurisé.
            </p>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="index.php" class="btn" style="
                    display: inline-flex; align-items: center; justify-content: center; 
                    padding: 0.875rem 1.75rem; border-radius: 12px; text-decoration: none; 
                    font-weight: 600; font-size: 0.95rem; transition: all 0.3s; 
                    background: #6b7280; color: #fff; border: none; cursor: pointer;
                ">
                    ← Retour à l'accueil
                </a>
            </div>
        </section>
    </div>
</body>
</html>
