<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$siteId = $_GET['id'] ?? null;
if (!$siteId) {
    header('Location: index.php');
    exit();
}

// Récupération des informations du site
try {
    $stmt = $pdo->prepare("SELECT * FROM sites WHERE id = ? AND agency_id = ?");
    $stmt->execute([$siteId, $_SESSION['user_id']]);
    $site = $stmt->fetch();
    
    if (!$site) {
        header('Location: index.php');
        exit();
    }
} catch(PDOException $e) {
    header('Location: index.php');
    exit();
}

function formatUrl($url) {
    return preg_replace('/^https?:\/\/(www\.)?/i', '', $url);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(formatUrl($site['url'])); ?> - Gestion Projets SEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1><?php echo htmlspecialchars(formatUrl($site['url'])); ?></h1>
                <p class="text-muted">
                    <i class="bi bi-link"></i> 
                    <a href="<?php echo htmlspecialchars($site['url']); ?>" target="_blank">
                        <?php echo htmlspecialchars($site['url']); ?>
                    </a>
                </p>
            </div>
        </div>

        <ul class="nav nav-tabs mb-4" id="siteTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="notes-tab" data-bs-toggle="tab" href="#notes" role="tab">Notes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="objectives-tab" data-bs-toggle="tab" href="#objectives" role="tab">Objectifs</a>
            </li>
        </ul>

        <div class="tab-content" id="siteTabContent">
            <div class="tab-pane fade show active" id="notes" role="tabpanel">
                <?php if (checkRole('editor')): ?>
                <div class="mb-4">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                        <i class="bi bi-plus-lg"></i> Ajouter une note
                    </button>
                </div>
                <?php endif; ?>
                <div id="notes-list">
                    <!-- Les notes seront chargées ici via AJAX -->
                </div>
            </div>
            
            <div class="tab-pane fade" id="objectives" role="tabpanel">
                <?php if (checkRole('editor')): ?>
                <div class="mb-4">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addObjectiveModal">
                        <i class="bi bi-plus-lg"></i> Ajouter un objectif
                    </button>
                </div>
                <?php endif; ?>
                <div id="objectives-list">
                    <!-- Les objectifs seront chargés ici via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal d'ajout de note -->
    <div class="modal fade" id="addNoteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addNoteForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="noteTitle" class="form-label">Titre (optionnel)</label>
                            <input type="text" class="form-control" id="noteTitle">
                        </div>
                        <div class="mb-3">
                            <label for="noteContent" class="form-label">Contenu</label>
                            <textarea class="form-control" id="noteContent" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal d'ajout d'objectif -->
    <div class="modal fade" id="addObjectiveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un objectif</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addObjectiveForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="objectiveTitle" class="form-label">Titre</label>
                            <input type="text" class="form-control" id="objectiveTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="objectiveDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="objectiveDescription" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="objectiveDeadline" class="form-label">Date limite</label>
                            <input type="date" class="form-control" id="objectiveDeadline">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const SITE_ID = <?php echo $siteId; ?>;
    </script>
    <script src="assets/js/site.js"></script>
</body>
</html>