<?php
require_once 'config.php';

require_login();
$connection = db();

// Supprimer un stagiaire (Refuser)
if (isset($_GET['refuser'])) {
    $stagiaire_id = intval($_GET['refuser']);
    try {
        $stmt = $connection->prepare("SELECT nom, email, modalite, binome_nom, binome_email, (SELECT titre FROM stages WHERE id = stagiaires.stage_id) as titre FROM stagiaires WHERE id = ?");
        $stmt->execute([$stagiaire_id]);
        $s = $stmt->fetch();
        
        $query = $connection->prepare("UPDATE stagiaires SET status = 'refuse', date_refus = NOW() WHERE id = ?");
        $query->execute([$stagiaire_id]);
        
        if ($s) {
            envoyer_notification_stage($s['email'], $s['nom'], $s['titre'], 'refuse');
            if ($s['modalite'] === 'binome' && !empty($s['binome_email'])) {
                envoyer_notification_stage($s['binome_email'], $s['binome_nom'], $s['titre'], 'refuse');
            }
        }
        
        set_flash('success', 'La candidature a été refusée.');
        redirect('ValidationStagiaire.php');
    } catch (PDOException $e) {
        set_flash('error', 'Erreur lors du traitement.');
    }
}

// Récupérer les stagiaires en attente de validation
$query = $connection->prepare("
    SELECT s.*, st.titre 
    FROM stagiaires s 
    JOIN stages st ON s.stage_id = st.id 
    WHERE s.status = 'en_attente'
");
$query->execute();
$stagiaires = $query->fetchAll();

include 'header.php'; 
?>
    <h1>Liste des stagiaires en attente</h1>

    <div class="table-container">
    <table style="width: 100%; margin-bottom: 0;">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Stage Demandé</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($stagiaires as $stagiaire): ?>
            <tr>
                <td><?= e($stagiaire['nom']); ?></td>
                <td><?= e($stagiaire['prenom']); ?></td>
                <td><?= e($stagiaire['email']); ?></td>
                <td><?= e($stagiaire['titre']); ?></td>
                <td style="display: flex; gap: 10px; justify-content: center;">
                    <a class="btn btn-primary" href="DetailStagiaire.php?id=<?= $stagiaire['id']; ?>" style="padding: 0.5rem; font-size: 0.8rem;">Profil</a>
                    <a class="btn btn-success" href="ModifierStagiaire.php?id=<?= $stagiaire['id']; ?>" style="padding: 0.5rem; font-size: 0.8rem;">Valider</a>
                    <a class="btn btn-danger" href="?refuser=<?= $stagiaire['id']; ?>" onclick="return confirm('Refuser ce stagiaire ?');">Refuser</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($stagiaires)): ?>
            <tr><td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">Aucune candidature en attente de validation.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</body>
</html>