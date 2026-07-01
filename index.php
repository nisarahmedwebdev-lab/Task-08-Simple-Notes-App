<?php
// index.php
$host = 'localhost';
$dbname = 'notes_app';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Notes App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f0f2f5;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        h1 {
            color: #1a1a2e;
            font-size: 2rem;
        }
        h1 i {
            color: #4a6cf7;
            margin-right: 10px;
        }
        .btn-add {
            background: #4a6cf7;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(74, 108, 247, 0.3);
        }
        .btn-add:hover {
            background: #3a5cd5;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(74, 108, 247, 0.4);
        }
        .btn-add i {
            font-size: 1.1rem;
        }
        .notes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .note-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s;
            position: relative;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .note-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        .note-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #1a1a2e;
            word-break: break-word;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .note-title i {
            color: #4a6cf7;
            font-size: 1rem;
        }
        .note-content {
            color: #4a4a6a;
            line-height: 1.5;
            flex-grow: 1;
            word-break: break-word;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .note-date {
            font-size: 0.8rem;
            color: #8888aa;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .note-date i {
            font-size: 0.75rem;
        }
        .note-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            justify-content: flex-end;
        }
        .note-actions button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 6px;
            transition: all 0.2s;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-edit {
            color: #4a6cf7;
            background: rgba(74, 108, 247, 0.05);
        }
        .btn-edit:hover {
            background: rgba(74, 108, 247, 0.15);
            transform: scale(1.05);
        }
        .btn-delete {
            color: #e74c6f;
            background: rgba(231, 76, 111, 0.05);
        }
        .btn-delete:hover {
            background: rgba(231, 76, 111, 0.15);
            transform: scale(1.05);
        }
        .btn-delete i, .btn-edit i {
            font-size: 0.9rem;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
            backdrop-filter: blur(4px);
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 30px;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .modal-content h2 {
            margin-bottom: 20px;
            color: #1a1a2e;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .modal-content h2 i {
            color: #4a6cf7;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #2a2a4a;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .form-group label i {
            color: #4a6cf7;
            width: 18px;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e2ea;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s;
            font-family: inherit;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4a6cf7;
            box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.1);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .color-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .color-option {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 3px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        .color-option:hover {
            transform: scale(1.15);
        }
        .color-option.selected {
            border-color: #1a1a2e;
            transform: scale(1.15);
            box-shadow: 0 0 0 2px #4a6cf7;
        }
        .color-option.selected::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #1a1a2e;
            font-size: 1rem;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 10px;
        }
        .form-actions button {
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-submit {
            background: #4a6cf7;
            color: white;
        }
        .btn-submit:hover {
            background: #3a5cd5;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74, 108, 247, 0.3);
        }
        .btn-cancel {
            background: #e8eaef;
            color: #2a2a4a;
        }
        .btn-cancel:hover {
            background: #d5d8e0;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #8888aa;
            grid-column: 1 / -1;
        }
        .empty-state i {
            font-size: 4rem;
            color: #d0d2e0;
            margin-bottom: 20px;
            display: block;
        }
        .empty-state p {
            font-size: 1.1rem;
        }
        .error-message {
            color: #e74c6f;
            font-size: 0.85rem;
            margin-top: 5px;
            display: none;
            align-items: center;
            gap: 4px;
        }
        .error-message.show {
            display: flex;
        }
        .note-card.deleting {
            animation: fadeOut 0.3s ease forwards;
        }
        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: scale(0.9);
            }
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: #8888aa;
        }
        .loading i {
            font-size: 2rem;
            animation: spin 1s linear infinite;
            margin-bottom: 10px;
            display: block;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @media (max-width: 768px) {
            .notes-grid {
                grid-template-columns: 1fr;
            }
            .header {
                flex-direction: column;
                align-items: stretch;
            }
            .btn-add {
                justify-content: center;
            }
        }
        @media (min-width: 769px) and (max-width: 1024px) {
            .notes-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (min-width: 1025px) {
            .notes-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-sticky-note"></i> My Notes</h1>
            <button class="btn-add" onclick="openAddModal()">
                <i class="fas fa-plus-circle"></i> Add Note
            </button>
        </div>
        <div id="notesContainer" class="notes-grid">
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No notes yet. Click "Add Note" to create your first note!</p>
            </div>
        </div>
    </div>

    <div id="noteModal" class="modal">
        <div class="modal-content">
            <h2><i class="fas fa-pencil-alt"></i> <span id="modalTitle">Add Note</span></h2>
            <form id="noteForm">
                <input type="hidden" id="noteId" value="">
                <div class="form-group">
                    <label for="title"><i class="fas fa-heading"></i> Title *</label>
                    <input type="text" id="title" placeholder="Enter note title" required>
                    <div class="error-message" id="titleError">
                        <i class="fas fa-exclamation-circle"></i> Title is required
                    </div>
                </div>
                <div class="form-group">
                    <label for="content"><i class="fas fa-align-left"></i> Content</label>
                    <textarea id="content" placeholder="Write your note here..."></textarea>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-palette"></i> Color</label>
                    <div class="color-options" id="colorOptions">
                        <div class="color-option selected" style="background:#ffffff;" data-color="#ffffff"></div>
                        <div class="color-option" style="background:#ffd3d3;" data-color="#ffd3d3"></div>
                        <div class="color-option" style="background:#d3f0ff;" data-color="#d3f0ff"></div>
                        <div class="color-option" style="background:#d3ffd3;" data-color="#d3ffd3"></div>
                        <div class="color-option" style="background:#fff5d3;" data-color="#fff5d3"></div>
                        <div class="color-option" style="background:#f0d3ff;" data-color="#f0d3ff"></div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php
?>
    <script>
        let selectedColor = '#ffffff';

        document.querySelectorAll('.color-option').forEach(el => {
            el.addEventListener('click', function() {
                document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                selectedColor = this.dataset.color;
            });
        });

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Note';
            document.getElementById('noteId').value = '';
            document.getElementById('title').value = '';
            document.getElementById('content').value = '';
            document.getElementById('titleError').classList.remove('show');
            document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
            document.querySelector('.color-option').classList.add('selected');
            selectedColor = '#ffffff';
            document.getElementById('noteModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('noteModal').classList.remove('active');
        }

        function openEditModal(id, title, content, color) {
            console.log('Editing note:', { id, title, content, color }); // Debug log
            document.getElementById('modalTitle').textContent = 'Edit Note';
            document.getElementById('noteId').value = id;
            document.getElementById('title').value = title;
            document.getElementById('content').value = content;
            document.getElementById('titleError').classList.remove('show');
            
            document.querySelectorAll('.color-option').forEach(opt => {
                opt.classList.remove('selected');
                if (opt.dataset.color === color) {
                    opt.classList.add('selected');
                }
            });
            selectedColor = color;
            document.getElementById('noteModal').classList.add('active');
        }

        document.getElementById('noteForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            const id = document.getElementById('noteId').value;
            
            if (!title) {
                document.getElementById('titleError').classList.add('show');
                return;
            }
            document.getElementById('titleError').classList.remove('show');
            
            const data = {
                title: title,
                content: content,
                color: selectedColor
            };
            
            let url = 'api.php';
            let method = 'POST';
            let action = 'added';
            
            if (id) {
                url = `api.php?id=${id}`;
                method = 'PUT';
                action = 'updated';
            }
            
            console.log('Saving note:', { url, method, data }); // Debug log
            
            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                console.log('Server response:', result); // Debug log
                
                if (result.success) {
                    closeModal();
                    await fetchNotes();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: `Note ${action} successfully!`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Something went wrong'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error saving note: ' + error.message
                });
            }
        });

        async function deleteNote(id) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c6f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash"></i> Yes, delete it!',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel'
            });
            
            if (!result.isConfirmed) return;
            
            try {
                const response = await fetch(`api.php?id=${id}`, {
                    method: 'DELETE'
                });
                
                const data = await response.json();
                if (data.success) {
                    await fetchNotes();
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Note has been deleted successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Could not delete note'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error deleting note'
                });
            }
        }

        async function fetchNotes() {
            const container = document.getElementById('notesContainer');
            container.innerHTML = `
                <div class="loading">
                    <i class="fas fa-spinner"></i>
                    Loading notes...
                </div>
            `;
            
            try {
                const response = await fetch('api.php');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const notes = await response.json();
                console.log('Fetched notes:', notes); // Debug log
                
                if (!notes || notes.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No notes yet. Click "Add Note" to create your first note!</p>
                        </div>
                    `;
                    return;
                }
                
                container.innerHTML = notes.map(note => `
                    <div class="note-card" data-id="${note.id}" style="background-color: ${note.color};">
                        <div class="note-title">
                            <i class="fas fa-sticky-note"></i>
                            ${escapeHtml(note.title)}
                        </div>
                        <div class="note-content">${escapeHtml(note.content || '')}</div>
                        <div class="note-date">
                            <i class="far fa-calendar-alt"></i>
                            ${formatDate(note.created_at)}
                        </div>
                        <div class="note-actions">
                            <button class="btn-edit" onclick="openEditModal('${note.id}', '${escapeHtml(note.title)}', '${escapeHtml(note.content || '')}', '${note.color}')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn-delete" onclick="deleteNote('${note.id}')">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error fetching notes:', error);
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Error loading notes. Please refresh the page.</p>
                    </div>
                `;
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        document.getElementById('noteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Initial load
        fetchNotes();
    </script>
</body>
</html>