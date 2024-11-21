$(document).ready(function() {
    loadSites();

    // Gestionnaire pour l'ajout d'un nouveau site
    $('#addSiteForm').on('submit', function(e) {
        e.preventDefault();
        const url = $('#siteUrl').val();
        const siteData = {
            url: url
        };

        $.ajax({
            url: 'api/sites.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(siteData),
            success: function(response) {
                $('#addSiteModal').modal('hide');
                loadSites();
                $('#addSiteForm')[0].reset();
            },
            error: function(xhr) {
                alert('Erreur lors de l\'ajout du site');
            }
        });
    });
});

function formatUrl(url) {
    return url.replace(/^https?:\/\//i, '').replace(/^www\./i, '');
}

function loadSites() {
    $.ajax({
        url: 'api/sites.php',
        method: 'GET',
        success: function(sites) {
            const sitesList = $('#sites-list');
            sitesList.empty();

            if (sites.length === 0) {
                sitesList.append(`
                    <div class="alert alert-info">
                        Aucun site n'a été ajouté pour le moment.
                    </div>
                `);
                return;
            }

            const row = $('<div class="row g-4"></div>');
            sites.forEach(site => {
                const card = createSiteCard(site);
                row.append(card);
            });
            sitesList.append(row);
        },
        error: function(xhr) {
            $('#sites-list').html(`
                <div class="alert alert-danger">
                    Erreur lors du chargement des sites
                </div>
            `);
        }
    });
}

function createSiteCard(site) {
    const progressPercentage = site.total_objectives > 0 
        ? Math.round((site.completed_objectives / site.total_objectives) * 100)
        : 0;

    const lastNoteDate = site.last_note_date 
        ? new Date(site.last_note_date).toLocaleDateString('fr-FR')
        : 'Aucune note';

    const formattedUrl = formatUrl(site.url);

    return $(`
        <div class="col-md-4">
            <div class="card site-card h-100">
                <div class="card-body">
                    <h5 class="card-title">${escapeHtml(formattedUrl)}</h5>
                    <p class="card-text">
                        <small class="text-muted">
                            <i class="bi bi-link"></i> 
                            <a href="${escapeHtml(site.url)}" target="_blank">${escapeHtml(formattedUrl)}</a>
                        </small>
                    </p>
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" 
                             style="width: ${progressPercentage}%" 
                             aria-valuenow="${progressPercentage}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            ${progressPercentage}%
                        </div>
                    </div>
                    <p class="card-text">
                        <small class="text-muted">
                            Objectifs: ${site.completed_objectives}/${site.total_objectives}
                        </small>
                    </p>
                    <p class="card-text">
                        <small class="text-muted">
                            Dernière note: ${lastNoteDate}
                        </small>
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="site.php?id=${site.id}" class="btn btn-primary btn-sm">
                        Voir les détails
                    </a>
                </div>
            </div>
        </div>
    `);
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}