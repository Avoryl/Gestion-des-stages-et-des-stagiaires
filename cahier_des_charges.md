# Cahier des Charges - Système de Gestion des Stages et Stagiaires

## 1. Contexte et Objectifs

### 1.1 Présentation du Projet
Le système **Gestion des Stages et Stagiaires** est une application web développée pour le **Ministère du Tourisme, de la Culture et des Arts (M.T.C.A.) de la République du Bénin**. Il vise à digitaliser et automatiser l'ensemble du processus de gestion des stages, depuis la publication des offres jusqu'au suivi des stagiaires actifs.

### 1.2 Objectifs Fonctionnels
- **Public** : Permettre aux postulants de consulter les offres et de candidater en ligne sans création de compte.
- **Gestionnaires** : Fournir un espace sécurisé pour valider/refuser les candidatures, suivre les stagiaires actifs et générer des documents officiels.
- **Administration** : Gérer les départements et les offres de stages.
- **Automatisation** : Notifications par email, génération de PDF, calcul automatique des statuts de stage.

### 1.3 Portée
- Gestion complète du cycle de vie des stages (publication → candidature → validation → suivi → clôture).
- Support des stages individuels et binômes.
- Distinction stage professionnel/académique avec pièces justificatives adaptées.

## 2. Acteurs et Rôles

| Acteur | Description | Fonctionnalités |
|--------|-------------|-----------------|
| **Postulant (Public)** | Étudiants/Jeunes diplômés | Consultation offres, candidature en ligne, téléchargement récépissé PDF |
| **Gestionnaire RH** | Personnel autorisé du ministère | Connexion sécurisée, validation/refus candidatures, suivi stagiaires actifs, statistiques |
| **Administrateur** | Super-admin (optionnel) | Gestion départements/offres de stages |

## 3. Architecture Technique

### 3.1 Stack Technologique
```
Backend    : PHP 8.2+ (procédural avec PDO)
Base de données : MySQL 8.0+ (InnoDB, UTF8MB4)
PDF        : FPDF 1.86
Email      : PHPMailer (SMTP Gmail)
Frontend   : HTML5/CSS3/JS Vanilla + Google Fonts (Work Sans)
Dépendances: Composer (PHPMailer)
```

### 3.2 Structure de la Base de Données
```
Tables principales :
├── departements (id, nom, sigle)
├── stages (id, titre, description, duree, departement_id)
├── gestionnaires (id, nom, prenom, email, mot_de_passe)
├── admins (id, nom, prenom, email, mot_de_passe) 
└── stagiaires (id, nom, prenom, email, ... 35+ champs incluant binôme, status, dates)
```

**Schéma ERD** : [Importez stagiaire.sql pour visualiser]

## 4. Fonctionnalités Détaillées

### 4.1 Module Public (Postulants)

#### 4.1.1 Consultation des Offres (**ListStage.php**)
- Affichage stages groupés par département
- Filtres : Département, Durée
- Bouton \"Postuler\" par offre

#### 4.1.2 Formulaire de Candidature (**postuler.php**)
**Champs obligatoires** :
```
Identité : nom*, prénom*, email*, date_naissance*, téléphone*, adresse*
Formation : formation*, spécialité*
Type stage* : Professionnel / Académique
Modalité* (académique) : Solo / Binôme 
Pièces jointes* : CV(PDF), LM(PDF)
+ Attestation diplôme (pro), Documents binôme (si applicable)
Établissement (académique) : nom, directeur, adresse, téléphone, email
Motif* : Renforcement/Mémoire/Rapport
Durée* : 2/3 mois
```

**Validations** :
- PDF uniquement (≤5Mo, vérification MIME)
- Noms fichiers uniques (hash + timestamp)
- Unicité candidature par email/stage
- Logique dynamique (binôme/école)

#### 4.1.3 Génération Récépissé (**generer_recepisse.php**)
- PDF officiel FPDF avec logo ministère
- Récapitulatif complet candidature
- Horodatage Africa/Porto-Novo

### 4.2 Module Gestionnaire (Sécurisé)

#### 4.2.1 Authentification (**login.php**)
```
Connexion multi-champs : email/nom/prénom/combinaison
Hash Bcrypt + password_verify
Sessions sécurisées avec rôles
```

#### 4.2.2 Tableau de Bord (**Acceuil.php**)
```
KPIs temps réel :
- Total stages / Postulants / Validés / Refusés
- 10 dernières candidatures (avec statut)
```

#### 4.2.3 Validation Candidatures (**ValidationStagiaire.php**)
```
Flux pour chaque stagiaire \"en_attente\" :
1. [Profil] → Détails complets
2. [Modifier] → Saisir dates début/fin → Validation finale
3. [Refuser] → Email automatique de refus
```

#### 4.2.4 Suivi Actifs (**ListeStagiairesValides.php**)
**Pastilles de statut automatiques** :
```
🔵 À venir     : date_debut > today
🟢 En cours    : today ∈ [debut..fin] ET >7j avant fin
🟠 Bientôt fin : today ∈ [debut..fin] ET ≤7j avant fin
🔵 Terminé     : date_fin < today
[Terminer] → status='termine'
```

### 4.3 Module Administration
- **GestionDépartements.php** : CRUD départements
- **AddStage.php** : Publication/Modification offres

## 5. Flux Métier

```
1. Admin → Publie stages (départements)
   ↓
2. Public → Consulte → Postule → Récépissé PDF
   ↓
3. Gestionnaire → Dashboard → Valide/Refuse → Emails auto
   ↓
4. Suivi actifs → Terminer
```

## 6. Sécurité et Contrôles

| Mesure | Implémentation |
|--------|---------------|
| **SQL Injection** | PDO préparées partout |
| **XSS** | `htmlspecialchars()` systématique via `e()` |
| **Upload** | PDF uniquement, MIME validation, renommage unique |
| **Sessions** | `require_login()`, `require_role()` |
| **Hash mots de passe** | Bcrypt `$2y$10$` |

## 7. Interface Utilisateur

### 7.1 Design System
```
Couleurs : Primary #1d4ed8, Success #16a34a, Danger #dc2626
Typographie : Work Sans (Google Fonts)
Responsive : Mobile-first
```

## 8. Déploiement

```
1. Importer stagiaire.sql
2. composer install  
3. config.php (DB/SMTP)
4. chmod 777 uploads/
```

**Comptes** : gestionnaire@stage.com / admin123

## 9. Évolutions
```
[ ] 2FA, API REST, Export Excel, Graphs
```

---
**© Realise par Mrs AVOCE Ulrich (A.U.I.D.) et SEGBEDJI Oswald .......en mai 2026 pour le Ministère du Tourisme,de la Culture et des Arts - Bénin**
 Nous disposons tout droits reserves sur cette application 
