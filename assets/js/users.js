$(document).ready(function() {
    loadUsers();

    // Gestionnaire pour l'ajout d'un utilisateur
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();
        const userData = {
            name: $('#userName').val(),
            email: $('#userEmail').val(),
            password: $('#userPassword').val(),
            role: $('#userRole').val()
        };

        $.ajax({
            url: 'api/users.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(userData),
            success: function(response) {
                $('#addUserModal').modal('hide');
                loadUsers();
                $('#addUserForm')[0].reset();
            },
            error: function(xhr) {
                const error = JSON.parse(xhr.responseText);
                alert(error.error || 'Erreur lors de l\'ajout de l\'utilisateur');
            }
        });
    });
});

function loadUsers() {
    $.ajax({
        url: 'api/users.php',
        method: 'GET',
        success: function(users) {
            const usersList = $('#users-list');
            usersList.empty();

            if (users.length === 0) {
                usersList.append(`
                    <div class="alert alert-info">
                        Aucun utilisateur n'a été ajouté pour le moment.
                    </div>
                `);
                return;
            }

            const table = $(`
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Date d'inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            `);

            users.forEach(user => {
                table.find('tbody').append(createUserRow(user));
            });

            usersList.append(table);
        },
        error: function(xhr) {
            $('#users-list').html(`
                <div class="alert alert-danger">
                    Erreur lors du chargement des utilisateurs
                </div>
            `);
        }
    });
}

function createUserRow(user) {
    const date = new Date(user.created_at).toLocaleDateString('fr-FR');
    const roleLabels = {
        'admin': 'Administrateur',
        'editor': 'Éditeur',
        'reader': 'Lecteur'
    };

    return $(`
        <tr>
            <td>${escapeHtml(user.name)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>
                <select class="form-select form-select-sm" 
                        onchange="updateUserRole(${user.id}, this.value)"
                        ${user.id === currentUserId ? 'disabled' : ''}>
                    <option value="reader" ${user.role === 'reader' ? 'selected' : ''}>Lecteur</option>
                    <option value="editor" ${user.role === 'editor' ? 'selected' : ''}>Éditeur</option>
                    <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Administrateur</option>
                </select>
            </td>
            <td>${date}</td>
            <td>
                ${user.id !== currentUserId ? `
                    <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                ` : ''}
            </td>
        </tr>
    `);
}

function updateUserRole(userId, newRole) {
    $.ajax({
        url: `api/users.php?id=${userId}`,
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({ role: newRole }),
        success: function(response) {
            loadUsers();
        },
        error: function(xhr) {
            alert('Erreur lors de la mise à jour du rôle');
            loadUsers(); // Recharger pour annuler le changement
        }
    });
}

function deleteUser(userId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
        return;
    }

    $.ajax({
        url: `api/users.php?id=${userId}`,
        method: 'DELETE',
        success: function(response) {
            loadUsers();
        },
        error: function(xhr) {
            const error = JSON.parse(xhr.responseText);
            alert(error.error || 'Erreur lors de la suppression de l\'utilisateur');
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