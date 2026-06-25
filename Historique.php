<?php
require_once 'config.php';
require_login();

$connection = db();

// Suppression définitive
if (isset($_GET['delete_permanent'])) {
    $id = (int)$_GET['delete_permanent'];
    try {
        $stmt = $connection->prepare("DELETE FROM stagiaires WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('success', 'Enregistrement supprimé définitivement.');
    } catch (PDOException $e) {
        set_flash('error', 'Erreur lors de la suppression.');
    }
    redirect('Historique.php');
}

// Récupération de tous les stagiaires (sauf ceux en attente si vous voulez séparer, 
// mais ici on affiche tout pour un historique complet)
$query = $connection->query("
    SELECT s.id, s.nom, s.prenom, s.email, s.status, st.titre AS stage_name, s.date_validation, s.date_fin, s.date_refus
    FROM stagiaires s
    LEFT JOIN stages st ON s.stage_id = st.id
    ORDER BY s.id DESC
");
$stagiaires = $query->fetchAll();

include 'header.php';
?>
    <h1>Historique des Candidatures & Stages</h1>

    <?php if (empty($stagiaires)): ?>
        <div style="text-align: center; padding: 4rem 2rem; background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow-sm); margin: 2rem 0;">
            <i class="fa-solid fa-clock-rotate-left" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
            <h2 style="color: var(--text-muted); font-weight: 700; margin-bottom: 0.5rem;">Aucun historique</h2>
            <p style="color: var(--text-muted);">Aucune candidature ou stage dans l'historique pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Stagiaire</th>
                        <th>Stage</th>
                        <th>Statut</th>
                        <th>Dates Clés</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stagiaires as $s): 
                        $badgeStyle = "";
                        $statusLabel = "";
                        switch($s['status']) {
                            case 'valide': $badgeStyle = "background: #dcfce7; color: #166534;"; $statusLabel = "Actif"; break;
                            case 'termine': $badgeStyle = "background: #dbeafe; color: #1e40af;"; $statusLabel = "Terminé"; break;
                            case 'refuse': $badgeStyle = "background: #fee2e2; color: #991b1b;"; $statusLabel = "Refusé"; break;
                            case 'en_attente': $badgeStyle = "background: #fef9c3; color: #854d0e;"; $statusLabel = "En attente"; break;
                        }
                    ?>
                        <tr>
                            <td>
                                <strong><?= e($s['nom']) ?> <?= e($s['prenom']) ?></strong><br>
                                <small><?= e($s['email']) ?></small>
                            </td>
                            <td><?= e($s['stage_name'] ?? 'N/A') ?></td>
                            <td>
                                <span style="padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; <?= $badgeStyle ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td style="font-size: 0.85rem;">
                                <?php if ($s['status'] === 'valide') echo "Validé le : " . date('d/m/Y', strtotime($s['date_validation'])); ?>
                                <?php if ($s['status'] === 'termine') echo "Fini le : " . date('d/m/Y', strtotime($s['date_fin'])); ?>
                                <?php if ($s['status'] === 'refuse') echo "Refusé le : " . ($s['date_refus'] ? date('d/m/Y', strtotime($s['date_refus'])) : 'N/A'); ?>
                            </td>
                            <td style="text-align: center; display: flex; gap: 5px; justify-content: center;">
                                <a href="DetailStagiaire.php?id=<?= $s['id'] ?>" class="btn btn-primary" style="padding: 0.5rem; font-size: 0.75rem;">Profil</a>
                                <a href="?delete_permanent=<?= $s['id'] ?>" class="btn btn-danger" style="padding: 0.5rem; font-size: 0.75rem;" onclick="return confirm('Supprimer définitivement cet enregistrement ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</body>
</html>
