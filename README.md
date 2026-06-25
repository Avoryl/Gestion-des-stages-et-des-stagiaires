# 🎓 Système de Gestion des Stages et Stagiaires (M.T.C.A)

## 📌 Présentation
Cette application est une solution complète de gestion du cycle de vie des stagiaires, spécifiquement conçue pour le **Ministère du Tourisme, de la Culture et des Arts (Bénin)**. Elle automatise le processus depuis la postulation publique jusqu'à la fin du stage, en passant par la validation administrative et le suivi opérationnel.

---

## ⚙️ Architecture Technique
*   **Backend :** PHP 8.2+ (Architecture procédurale sécurisée).
*   **Base de données :** MySQL (InnoDB) avec intégrité référentielle.
*   **Moteur Email :** PHPMailer via SMTP (Gmail) pour des notifications HTML professionnelles.
*   **Génération PDF :** FPDF 1.86 pour la création de récépissés officiels.
*   **Frontend :** Design moderne et responsive (CSS3, Google Fonts "Work Sans", FontAwesome).

---

## 🚀 Fonctionnalités Détaillées

### 1. Parcours du Postulant (Public)
Le processus est conçu pour être simple et transparent, sans création de compte requise :
*   **Consultation (ListStage.php) :** Visualisation des offres par département (DSI, DPAF, RH, etc.).
*   **Candidature Dynamique (postuler.php) :** 
    *   **Stage Professionnel :** Requiert une attestation de diplôme.
    *   **Stage Académique :** Permet la modalité **Solo** ou **Binôme**. En cas de binôme, le formulaire s'étend pour collecter les informations complètes du partenaire.
    *   **Informations Établissement :** Collecte des données de l'école (nom, directeur, adresse, email).
*   **Gestion Documentaire :** Téléversement sécurisé de CV et LM au format PDF uniquement. Les fichiers sont renommés dynamiquement (`cv_nom_prenom_timestamp.pdf`) pour éviter les collisions.
*   **Récépissé de Candidature :** Génération immédiate d'un PDF (`generer_recepisse.php`) contenant un tableau récapitulatif de toutes les données saisies, horodaté au fuseau `Africa/Porto-Novo`.

### 2. Espace Gestionnaire (Sécurisé)
Le gestionnaire dispose d'un tableau de bord complet pour piloter l'activité :
*   **Authentification Flexible (login.php) :** Connexion via email, nom, prénom ou nom complet. Utilisation de `password_verify` pour les hashs Bcrypt.
*   **Tableau de Bord (Acceuil.php) :** Statistiques en temps réel (Total stages, postulants, validés, refusés) et liste des 10 dernières candidatures.
*   **Flux de Validation (ValidationStagiaire.php) :** 
    *   **Refus :** Envoi automatique d'un email de regret via PHPMailer.
    *   **Validation intelligente :** Cliquer sur "Valider" redirige vers l'édition du profil pour définir la **Période de stage** (Date de début/fin) avant confirmation.
*   **Notification Automatique :** Lors de la validation finale, un email HTML riche est envoyé au postulant (et à son binôme) incluant les dates exactes, le département et le nom du stage.
*   **Suivi des Stagiaires Actifs (ListeStagiairesValides.php) :** 
    *   Affichage d'une **Pastille de statut** calculée dynamiquement :
        *   🔵 **À venir :** Date de début future.
        *   🟢 **En cours :** Stage commencé.
        *   🟠 **Bientôt fini :** Moins de 7 jours restants.
        *   🔵 **Terminé :** Date de fin dépassée.
*   **Historique (Historique.php) :** Archive complète de toutes les actions pour audit, avec possibilité de suppression définitive.

### 3. Administration Système
*   **Gestion des Départements :** Création et modification des structures (Sigles et Noms complets).
*   **Gestion des Offres (AddStage.php) :** Publication, modification et suppression des missions de stage rattachées aux départements.

---

## 🛡️ Sécurité et UX
*   **Protection XSS :** Échappement systématique via une fonction `e()`.
*   **Sécurité SQL :** Utilisation exclusive de requêtes préparées PDO.
*   **Bouton Retour Intelligent :** Intégré dans le header, il suit l'historique de navigation de l'utilisateur.
*   **UI Loader :** Un spinner de chargement géré en JS pour améliorer la perception de fluidité (exclu sur les scripts de téléchargement).
*   **Gestion des Erreurs :** Système de messages "Flash" (Succès/Erreur) stockés en session.

---

## 🛠️ Installation et Configuration

1.  **Base de données :** Importer le fichier `database.sql` dans PHPMyAdmin.
2.  **Dépendances :** 
    *   Exécuter `composer install` pour installer PHPMailer.
    *   S'assurer que le dossier `fpdf/` et son sous-dossier `font/` sont présents.
3.  **Configuration (config.php) :** 
    *   Renseigner les accès DB.
    *   Configurer les paramètres SMTP (Host, User, Pass d'application Google).
4.  **Dossiers :** Créer un dossier `uploads/` à la racine avec les droits d'écriture.

---
© 2026 ** Réalisé par Mrs AVOCE Ulrich (A.U.I.D.) et SEGBEDJI Oswald .......en mai 2026 pour le Ministère du Tourisme,de la Culture et des Arts - Bénin**
 Nous disposons tout droits réservés sur cette application. 
 