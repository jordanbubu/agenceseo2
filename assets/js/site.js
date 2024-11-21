$(document).ready(function() {
    loadNotes();
    loadObjectives();

    // Gestionnaires d'événements pour les onglets
    $('#siteTab a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });

    // Gestionnaire pour l'ajout d'une note
    $('#addNoteForm').on('submit', function(e) {
        e.preventDefault();
        const noteData = {
            title: $('#noteTitle').val(),
            content: $('#noteContent').val()
        };

        $.ajax({
            url: `api/notes.php?site_id=${SITE_ID}`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(noteData),
            success: function(response) {
                $('#addNoteModal').modal('hide');
                loadNotes();
                $('#addNoteForm')[0].reset();
            },
            error: function(xhr) {
                alert('Erreur lors de l\'ajout de la note');
            }
        });
    });

    // Gestionnaire pour l'ajout d'un objectif
    $('#addObjectiveForm').on('submit', function(e) {
        e.preventDefault();
        const objectiveData = {
            title: $('#objectiveTitle').val(),
            description: $('#objectiveDescription').val(),
            deadline: $('#objectiveDeadline').val() || null
        };

        $.ajax({
            url: `api/objectives.php?site_id=${SITE_ID}`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(objectiveData),
            success: function(response) {
                $('#addObjectiveModal').modal('hide');
                loadObjectives();
                $('#addObjectiveForm')[0].reset();
            },
            error: function(xhr) {
                alert('Erreur lors de l\'ajout de l\'objectif');
            }
        });
    });
});

function loadNotes() {
    $.ajax({
        url: `api/notes.php?site_id=${SITE_ID}`,
        method: 'GET',
        success: function(notes) {
            const notesList = $('#notes-list');
            notesList.empty();

            if (notes.length === 0) {
                notesList.append(`
                    <div class="alert alert-info">
                        Aucune note n'a été ajoutée pour le moment.
                    </div>
                `);
                return;
            }

            notes.forEach(note => {
                notesList.append(createNoteCard(note));
            });
        },
        error: function(xhr) {
            $('#notes-list').html(`
                <div class="alert alert-danger">
                    Erreur lors du chargement des notes
                </div>
            `);
        }
    });
}

function loadObjectives() {
    $.ajax({
        url: `api/objectives.php?site_id=${SITE_ID}`,
        method: 'GET',
        success: function(objectives) {
            const objectivesList = $('#objectives-list');
            objectivesList.empty();

            if (objectives.length === 0) {
                objectivesList.append(`
                    <div class="alert alert-info">
                        Aucun objectif n'a été ajouté pour le moment.
                    </div>
                `);
                return;
            }

            objectives.forEach(objective => {
                objectivesList.append(createObjectiveCard(objective));
            });
        },
        error: function(xhr) {
            $('#objectives-list').html(`
                <div class="alert alert-danger">
                    Erreur lors du chargement des objectifs
                </div>
            `);
        }
    });
}

function createNoteCard(note) {
    const date = new Date(note.created_at).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    return $(`
        <div class="card mb-3">
            <div class="card-body">
                ${note.title ? `<h5 class="card-title">${escapeHtml(note.title)}</h5>` : ''}
                <p class="card-text">${escapeHtml(note.content)}</p>
                <p class="card-text">
                    <small class="text-muted">
                        Par ${escapeHtml(note.author_name)} le ${date}
                    </small>
                </p>
            </div>
        </div>
    `);
}

function createObjectiveCard(objective) {
    const deadline = objective.deadline 
        ? new Date(objective.deadline).toLocaleDateString('fr-FR')
        : 'Non définie';

    const statusClasses = {
        'not_started': 'bg-secondary',
        'in_progress': 'bg-primary',
        'completed': 'bg-success'
    };

    const statusLabels = {
        'not_started': 'Non commencé',
        'in_progress': 'En cours',
        'completed': 'Terminé'
    };

    return $(`
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <h5 class="card-title">${escapeHtml(objective.title)}</h5>
                    <span class="badge ${statusClasses[objective.status]}">
                        ${statusLabels[objective.status]}
                    </span>
                </div>
                <p class="card-text">${escapeHtml(objective.description)}</p>
                <p class="card-text">
                    <small class="text-muted">
                        Date limite: ${deadline}
                    </small>
                </p>
                <div class="btn-group">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                            type="button" 
                            data-bs-toggle="dropdown">
                        Changer le statut
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="#" 
                               onclick="updateObjectiveStatus(${objective.id}, 'not_started')">
                                Non commencé
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" 
                               onclick="updateObjectiveStatus(${objective.id}, 'in_progress')">
                                En cours
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" 
                               onclick="updateObjectiveStatus(${objective.id}, 'completed')">
                                Terminé
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    `);
}

function updateObjectiveStatus(objectiveId, status) {
    $.ajax({
        url: `api/objectives.php?site_id=${SITE_ID}&id=${objectiveId}`,
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({ status: status }),
        success: function(response) {
            loadObjectives();
        },
        error: function(xhr) {
            alert('Erreur lors de la mise à jour du statut');
        }
    });
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}