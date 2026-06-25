<img width="920" height="415" alt="S9" src="https://github.com/user-attachments/assets/5e7ae986-ebf9-4afb-b9b0-6f65aa81b539" /># 🎓 Système de Gestion des Stages et des Stagiaires (M.T.C.A)

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


 <img width="877" height="436" alt="S1" src="https://github.com/user-attachments/assets/921f064c-639e-4b0a-bc21-08e36142dd24" />
<img width="926" height="425" alt="S2" src="https://github.com/user-attachments/assets/5f65351a-af6d-4040-acf3-521de5ddb3b3" />
<img width="927" height="430" alt="S3" src="https://github.com/user-attachments/assets/33fe2f18-28de-4390-af11-e3683525a924" />
<img width="933" height="428" alt="S4" src="https://github.com/user-attachments/assets/4013c150-a7dd-4070-ba0f-fbc1bfb05452" />
<img width="928" height="427" alt="S5" src="https://github.com/user-attachments/assets/f4592317-1341-4d13-82f7-794637095a60" />
<img width="926" height="424" alt="S6" src="https://github.com/user-attachments/assets/66352c30-3a93-44e9-ba90-9d7a3e6d22ca" />
<img width="926" height="403" alt="S7" src="https://github.com/user-attachments/assets/b8bd082b-6c91-4f6c-b18d-f15440728686" />
<img width="930" height="425" alt="S8" src="https://github.com/user-attachments/assets/f68728c3-337b-45b3-8f03-5eba95f624cc" />
<img width="920" height="415" alt="S9" src="https://github.com/user-attachments/assets/95d06ff8-2086-4afa-9f39-37a0d08f7476" />
<img width="917" height="286" alt="S10" src="https://github.com/user-attachments/assets/d3267ddb-003a-431c-b115-e2e23d651480" />
<img width="910" height="419" alt="S11" src="https://github.com/user-attachments/assets/15e5a0c5-75cf-4eeb-9f5d-af3553113a69" />
<img width="917" height="410" alt="S12" src="https://github.com/user-attachments/assets/d18d6f61-eca0-465b-a974-a195fec89e1a" />



