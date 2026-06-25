<?php
require_once 'config.php';
require_login();

$connection = db();

// Retirer un stagiaire de la liste active
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $connection->prepare("UPDATE stagiaires SET status = 'termine', date_fin = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('success', 'Le stage a été marqué comme terminé.');
    } catch (PDOException $e) {
        set_flash('error', 'Erreur lors de l\'opération.');
    }
    redirect('ListeStagiairesValides.php');
}

// Récupération des stagiaires validés avec les infos de stage et département
$query = $connection->query("
    SELECT s.id, s.nom, s.prenom, s.email, st.titre AS stage_name, d.sigle AS dept_sigle, s.date_validation, s.date_debut, s.date_fin
    FROM stagiaires s
    JOIN stages st ON s.stage_id = st.id
    LEFT JOIN departements d ON st.departement_id = d.id
    WHERE s.status = 'valide'
    ORDER BY s.date_validation DESC
");
$stagiaires = $query->fetchAll();

$today = new DateTime('today');

include 'header.php';
?>
    <h1>Nos Stagiaires Actifs</h1>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nom & Prénom</th>
                    <th>Email</th>
                    <th>Département</th>
                    <th>Stage Affecté</th>
                    <th>État Stage</th>
                    <th>Date de Validation</th>
                    <th style="text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stagiaires as $s): ?>
                    <?php
                    $debut = $s['date_debut'] ? new DateTime($s['date_debut']) : null;
                    $fin = $s['date_fin'] ? new DateTime($s['date_fin']) : null;
                    $statusText = "Non planifié";
                    $statusColor = "background: #f3f4f6; color: #6b7280;"; // Gris

                    if ($debut && $fin) {
                        if ($today < $debut) {
                            $statusText = "À venir";
                            $statusColor = "background: #dbeafe; color: #1e40af;"; // Bleu
                        } elseif ($today > $fin) {
                            $statusText = "Terminé";
                            $statusColor = "background: #dbeafe; color: #1e40af;"; // Bleu
                        } else {
                            $diff = $today->diff($fin);
                            if ($diff->days <= 7) {
                                $statusText = "Bientôt fini";
                                $statusColor = "background: #fef3c7; color: #92400e;"; // Orange
                            } else {
                                $statusText = "En cours";
                                $statusColor = "background: #dcfce7; color: #166534;"; // Vert
                            }
                        }
                    }
                    ?>
                    <tr>
                        <td>
                            <a href="DetailStagiaire.php?id=<?= $s['id'] ?>" style="color: var(--primary); font-weight: 700; text-decoration: none;">
                                <?= e($s['nom']) ?> <?= e($s['prenom']) ?>
                            </a>
                        </td>
                        <td><?= e($s['email']) ?></td>
                        <td><span class="badge" style="background: #e2e8f0; padding: 4px 8px; border-radius: 6px; font-weight: 700;"><?= e($s['dept_sigle'] ?? 'N/A') ?></span></td>
                        <td><?= e($s['stage_name']) ?></td>
                        <td>
                            <span style="padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; <?= $statusColor ?>">
                                <?= $statusText ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($s['date_validation'])) ?></td>
                        <td style="text-align: center; display: flex; gap: 5px; justify-content: center;">
                            <a href="DetailStagiaire.php?id=<?= $s['id'] ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem;">Voir Profil</a>
                            <a href="?delete=<?= $s['id'] ?>" class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.8rem;" onclick="return confirm('Marquer ce stage comme terminé ?');">Terminer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($stagiaires)): ?>
                    <tr><td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-muted);">Aucun stagiaire actif dans la base.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>