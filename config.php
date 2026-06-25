<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Chargement de l'autoloader de Composer au sommet pour tout le projet
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Africa/Porto-Novo');

const DB_HOST = 'localhost';
const DB_NAME = 'stagiaire';
const DB_USER = 'root';
const DB_PASS = '';

// Configuration SMTP (Exemple pour Gmail)
const SMTP_HOST = 'smtp.gmail.com';
const SMTP_PORT = 587;
const SMTP_USER = 'valdosgd@gmail.com'; // Ton vrai email Gmail
const SMTP_PASS = 'xzwc ghtf pflv edtg';      // Ton code de 16 caractères généré par Google
const MAIL_FROM = 'valdosgd@gmail.com'; // Doit être identique à SMTP_USER pour Gmail

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit();
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_role'], $_SESSION['user_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Veuillez vous connecter pour accéder à cette page.');
        redirect('login.php');
    }
}

function require_role(string $role): void
{
    require_login();

    if (($_SESSION['user_role'] ?? null) !== $role) {
        set_flash('error', 'Vous n’êtes pas autorisé à accéder à cette page.');
        redirect('Acceuil.php');
    }
}

function current_user_role(): ?string
{
    return $_SESSION['user_role'] ?? null;
}

function current_user_name(): ?string
{
    return $_SESSION['user_name'] ?? null;
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function is_gestionnaire(): bool
{
    return current_user_role() === 'gestionnaire';
}

/**
 * Envoie un email de notification via PHPMailer
 */
function envoyer_notification_stage(string $to, string $nom, string $titre_stage, string $status, array $extra = []): bool
{
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) return false;

    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Destinataires
        $mail->setFrom(MAIL_FROM, 'Ministère du Tourisme - RH');
        $mail->addAddress($to, $nom);

        // Inclusion du logo local en tant que pièce jointe intégrée (CID)
        if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'logo.png')) {
            $mail->addEmbeddedImage(__DIR__ . DIRECTORY_SEPARATOR . 'logo.png', 'logo_ministere');
            $logoSrc = 'cid:logo_ministere';
        } else {
            $logoSrc = 'https://tourisme.gouv.bj/site/logo.png';
        }

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = ($status === 'valide') ? "Félicitations - Candidature Acceptée" : "Information sur votre candidature";

        $couleur = ($status === 'valide') ? '#16a34a' : '#dc2626';
        $message_titre = ($status === 'valide') ? "Candidature Acceptée !" : "Mise à jour de votre candidature";
        $introduction = ($status === 'valide') 
            ? "Nous avons le plaisir de vous informer que votre dossier pour le stage suivant a été retenu :" 
            : "Nous avons bien reçu et étudié votre candidature pour le stage suivant :";

        $mail->Body = "
        <div style='font-family: sans-serif; line-height: 1.6; color: #333; max-width: 600px; border: 1px solid #eee; padding: 20px; border-radius: 10px;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <img src='$logoSrc' alt='Logo Ministère' style='max-width: 200px;'>
            </div>
            <h2 style='color: $couleur;'>$message_titre</h2>
            <p>Bonjour <strong>$nom</strong>,</p>
            <p>$introduction</p>
            <div style='background: #f8fafc; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                <p style='margin: 5px 0;'><strong>🎯 Stage :</strong> $titre_stage</p>
                <p style='margin: 5px 0;'><strong>🏢 Département :</strong> " . ($extra['dept_nom'] ?? 'Non spécifié') . "</p>
            </div>";

        if ($status === 'valide') {
            $mail->Body .= "
            <h3 style='color: #16a34a;'>Informations pratiques</h3>
            <p>Votre stage se déroulera selon les modalités suivantes :</p>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Date de début :</strong></td>
                    <td style='padding: 8px; border-bottom: 1px solid #eee;'>" . date('d/m/Y', strtotime($extra['date_debut'])) . "</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Date de fin :</strong></td>
                    <td style='padding: 8px; border-bottom: 1px solid #eee;'>" . date('d/m/Y', strtotime($extra['date_fin'])) . "</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>Durée totale :</strong></td>
                    <td style='padding: 8px; border-bottom: 1px solid #eee;'>" . $extra['duree'] . "</td>
                </tr>
            </table>
            <p style='margin-top: 20px;'>Nous vous prions de vous présenter à la Direction des Ressources Humaines le jour de votre début de stage muni de votre pièce d'identité.</p>";
        } else {
            $mail->Body .= "<p>Après étude de votre dossier, nous avons le regret de vous informer que nous ne pouvons pas donner suite à votre candidature.</p>";
        }

        $mail->Body .= "
            <p>Cordialement,<br>La Direction des Ressources Humaines</p>
        </div>";

        return $mail->send();
    } catch (Exception $e) {
        error_log("Erreur PHPMailer : " . $mail->ErrorInfo);
        return false;
    }
}
