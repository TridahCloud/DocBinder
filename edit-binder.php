<?php
// edit-binder.php - Edit binder and manage documents
require_once 'config.php';

$page_title = "Edit Binder";

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

include 'header.php';

$current_user = get_current_user_data();
$binder_id = (int)($_GET['id'] ?? 0);

if (!$binder_id) {
    header('Location: dashboard.php');
    exit;
}

try {
    // Get binder info
    $stmt = $pdo->prepare("SELECT * FROM binders WHERE id = ? AND user_id = ?");
    $stmt->execute([$binder_id, $current_user['id']]);
    $binder = $stmt->fetch();
    
    if (!$binder) {
        header('Location: dashboard.php');
        exit;
    }
    
    // Get documents
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE binder_id = ? ORDER BY sort_order, created_at");
    $stmt->execute([$binder_id]);
    $documents = $stmt->fetchAll();
    
} catch (PDOException $e) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_binder') {
        $title = sanitize_input($_POST['title'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $is_public = isset($_POST['is_public']) ? 1 : 0;
        
        if (empty($title)) {
            $error = 'Please enter a title for your binder.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE binders SET title = ?, description = ?, is_public = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$title, $description, $is_public, $binder_id, $current_user['id']]);
                $success = 'Binder updated successfully!';
                $binder['title'] = $title;
                $binder['description'] = $description;
                $binder['is_public'] = $is_public;
            } catch (PDOException $e) {
                $error = 'An error occurred. Please try again.';
            }
        }
    }
}
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Edit Binder: <?php echo htmlspecialchars($binder['title']); ?></h1>
        <div style="display: flex; gap: 0.5rem;">
            <a href="view-binder.php?id=<?php echo $binder_id; ?>" class="btn btn-outline">
                <i class="fas fa-eye"></i> View Binder
            </a>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <div class="grid grid-cols-2">
        <!-- Binder Settings -->
        <div class="card">
            <div class="card-header">
                <h3>Binder Settings</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_binder">
                    
                    <div class="form-group">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" id="title" name="title" class="form-input" 
                               value="<?php echo htmlspecialchars($binder['title']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-input form-textarea"><?php echo htmlspecialchars($binder['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="is_public" value="1" 
                                   <?php echo $binder['is_public'] ? 'checked' : ''; ?>>
                            Make this binder public
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Binder
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Add Document -->
        <div class="card">
            <div class="card-header">
                <h3>Add Document</h3>
            </div>
            <div class="card-body">
                <form id="addDocumentForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="doc_title" class="form-label">Document Title *</label>
                        <input type="text" id="doc_title" name="title" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="doc_intro" class="form-label">Introduction Text</label>
                        <textarea id="doc_intro" name="intro_text" class="form-input form-textarea" 
                                  placeholder="Optional introduction text that appears before the document content..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Document Type *</label>
                        <select name="file_type" id="file_type" class="form-input form-select" required onchange="toggleDocumentType()">
                            <option value="">Select document type</option>
                            <option value="file">Upload File (PDF/Image)</option>
                            <option value="text">Write Text</option>
                        </select>
                    </div>
                    
                    <div id="fileUploadSection" class="form-group" style="display: none;">
                        <label class="form-label">Upload File</label>
                        <div class="file-upload" onclick="document.getElementById('file_input').click()">
                            <div class="file-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-upload-text">Click to upload or drag and drop</div>
                            <div class="file-upload-hint">PDF, JPG, PNG, GIF (max 10MB)</div>
                            <input type="file" id="file_input" name="file" accept=".pdf,.jpg,.jpeg,.png,.gif" style="display: none;">
                        </div>
                        <div id="file-preview" style="display: none; margin-top: 1rem; padding: 1rem; background: var(--bg-tertiary); border-radius: var(--radius-md);">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="fas fa-file" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                                <div>
                                    <div id="file-name" style="font-weight: 500;"></div>
                                    <div id="file-size" style="font-size: 0.875rem; color: var(--text-muted);"></div>
                                </div>
                                <button type="button" onclick="clearFile()" style="margin-left: auto; background: none; border: none; color: var(--text-muted); cursor: pointer;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="textEditorSection" class="form-group" style="display: none;">
                        <label for="text_content" class="form-label">Text Content</label>
                        <div class="rich-text-editor">
                            <div class="editor-toolbar">
                                <button type="button" class="toolbar-btn" data-command="bold" title="Bold">
                                    <i class="fas fa-bold"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-command="italic" title="Italic">
                                    <i class="fas fa-italic"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-command="underline" title="Underline">
                                    <i class="fas fa-underline"></i>
                                </button>
                                <div class="toolbar-separator"></div>
                                <button type="button" class="toolbar-btn" data-command="formatBlock" data-value="h1" title="Heading 1">
                                    <i class="fas fa-heading"></i> H1
                                </button>
                                <button type="button" class="toolbar-btn" data-command="formatBlock" data-value="h2" title="Heading 2">
                                    <i class="fas fa-heading"></i> H2
                                </button>
                                <button type="button" class="toolbar-btn" data-command="formatBlock" data-value="h3" title="Heading 3">
                                    <i class="fas fa-heading"></i> H3
                                </button>
                                <div class="toolbar-separator"></div>
                                <button type="button" class="toolbar-btn" data-command="insertUnorderedList" title="Bullet List">
                                    <i class="fas fa-list-ul"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-command="insertOrderedList" title="Numbered List">
                                    <i class="fas fa-list-ol"></i>
                                </button>
                                <div class="toolbar-separator"></div>
                                <button type="button" class="toolbar-btn" data-command="indent" title="Indent">
                                    <i class="fas fa-indent"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-command="outdent" title="Outdent">
                                    <i class="fas fa-outdent"></i>
                                </button>
                                <div class="toolbar-separator"></div>
                                <button type="button" class="toolbar-btn" data-command="justifyLeft" title="Align Left">
                                    <i class="fas fa-align-left"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-command="justifyCenter" title="Align Center">
                                    <i class="fas fa-align-center"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-command="justifyRight" title="Align Right">
                                    <i class="fas fa-align-right"></i>
                                </button>
                                <div class="toolbar-separator"></div>
                                <button type="button" class="toolbar-btn" data-command="removeFormat" title="Remove Formatting">
                                    <i class="fas fa-remove-format"></i>
                                </button>
                                <button type="button" class="toolbar-btn" onclick="clearAllFormatting()" title="Clear All Formatting">
                                    <i class="fas fa-eraser"></i>
                                </button>
                            </div>
                            <div class="editor-content" id="text_content" contenteditable="true" 
                                 placeholder="Write your text content here..."></div>
                            <textarea name="text_content" id="text_content_hidden" style="display: none;"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="doc_outro" class="form-label">Conclusion Text</label>
                        <textarea id="doc_outro" name="outro_text" class="form-input form-textarea" 
                                  placeholder="Optional conclusion text that appears after the document content..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Document
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Documents List -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3>Documents (<?php echo count($documents); ?>)</h3>
        </div>
        <div class="card-body">
            <?php if (empty($documents)): ?>
                <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                    <i class="fas fa-file-plus" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p>No documents yet. Add your first document above.</p>
                </div>
            <?php else: ?>
                <div class="documents-list" id="documentsList">
                    <?php foreach ($documents as $index => $doc): ?>
                        <div class="document-item" data-document-id="<?php echo $doc['id']; ?>" data-sort-order="<?php echo $doc['sort_order']; ?>" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); margin-bottom: 0.5rem;">
                            <div style="display: flex; align-items: center;">
                                <div class="drag-handle">
                                    <i class="fas fa-grip-vertical"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($doc['title']); ?></div>
                                    <div style="font-size: 0.875rem; color: var(--text-muted);">
                                        <?php echo ucfirst($doc['file_type']); ?>
                                        <?php if ($doc['file_type'] === 'text'): ?>
                                            - Text Document
                                        <?php elseif ($doc['file_type'] === 'pdf'): ?>
                                            - PDF File
                                        <?php else: ?>
                                            - Image File
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="btn btn-outline btn-sm" onclick="editDocument(<?php echo $doc['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-outline btn-sm" onclick="deleteDocument(<?php echo $doc['id']; ?>)" style="color: var(--error-color, #dc2626);">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleDocumentType() {
    const fileType = document.getElementById('file_type').value;
    const fileSection = document.getElementById('fileUploadSection');
    const textSection = document.getElementById('textEditorSection');
    
    fileSection.style.display = fileType === 'file' ? 'block' : 'none';
    textSection.style.display = fileType === 'text' ? 'block' : 'none';
}

function editDocument(docId) {
    // Get document data
    fetch(`api/get-document.php?id=${docId}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                showEditModal(data.document);
            } else {
                showNotification(data.error || 'Failed to load document', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred: ' + error.message, 'error');
        });
}

function showEditModal(docData) {
    // Create modal HTML
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Document</h3>
                <button onclick="closeEditModal()" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editDocumentForm">
                    <input type="hidden" name="document_id" value="${docData.id}">
                    
                    <div class="form-group">
                        <label for="edit_title" class="form-label">Document Title *</label>
                        <input type="text" id="edit_title" name="title" class="form-input" value="${docData.title}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_intro" class="form-label">Introduction Text</label>
                        <textarea id="edit_intro" name="intro_text" class="form-input form-textarea">${docData.intro_text || ''}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Document Type</label>
                        <select name="file_type" id="edit_file_type" class="form-input form-select" onchange="toggleEditDocumentType()">
                            <option value="file" ${docData.file_type !== 'text' ? 'selected' : ''}>Upload File (PDF/Image)</option>
                            <option value="text" ${docData.file_type === 'text' ? 'selected' : ''}>Write Text</option>
                        </select>
                    </div>
                    
                    <div id="editFileUploadSection" class="form-group" style="display: ${docData.file_type !== 'text' ? 'block' : 'none'};">
                        <label class="form-label">Upload New File (optional)</label>
                        <div class="file-upload" onclick="document.getElementById('edit_file_input').click()">
                            <div class="file-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-upload-text">Click to upload or drag and drop</div>
                            <div class="file-upload-hint">PDF, JPG, PNG, GIF (max 10MB)</div>
                            <input type="file" id="edit_file_input" name="file" accept=".pdf,.jpg,.jpeg,.png,.gif" style="display: none;">
                        </div>
                        <div id="edit-file-preview" style="display: none; margin-top: 1rem; padding: 1rem; background: var(--bg-tertiary); border-radius: var(--radius-md);">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="fas fa-file" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                                <div>
                                    <div id="edit-file-name" style="font-weight: 500;"></div>
                                    <div id="edit-file-size" style="font-size: 0.875rem; color: var(--text-muted);"></div>
                                </div>
                                <button type="button" onclick="clearEditFile()" style="margin-left: auto; background: none; border: none; color: var(--text-muted); cursor: pointer;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="editTextEditorSection" class="form-group" style="display: ${docData.file_type === 'text' ? 'block' : 'none'};">
                        <label for="edit_text_content" class="form-label">Text Content</label>
                        <div class="rich-text-editor">
                            <div class="editor-toolbar">
                                <button type="button" class="toolbar-btn" data-command="bold" title="Bold">
                                    <i class="fas fa-bold"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-command="italic" title="Italic">
                                    <i class="fas fa-italic"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-command="underline" title="Underline">
                                    <i class="fas fa-underline"></i>
                                </button>
                                <div class="toolbar-separator"></div>
                                <button type="button" class="toolbar-btn" data-command="formatBlock" data-value="h1" title="Heading 1">
                                    <i class="fas fa-heading"></i> H1
                                </button>
                                <button type="button" class="toolbar-btn" data-command="formatBlock" data-value="h2" title="Heading 2">
                                    <i class="fas fa-heading"></i> H2
                                </button>
                                <button type="button" class="toolbar-btn" data-command="formatBlock" data-value="h3" title="Heading 3">
                                    <i class="fas fa-heading"></i> H3
                                </button>
                                <div class="toolbar-separator"></div>
                                <button type="button" class="toolbar-btn" data-command="insertUnorderedList" title="Bullet List">
                                    <i class="fas fa-list-ul"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-command="insertOrderedList" title="Numbered List">
                                    <i class="fas fa-list-ol"></i>
                                </button>
                                <div class="toolbar-separator"></div>
                                <button type="button" class="toolbar-btn" data-command="indent" title="Indent">
                                    <i class="fas fa-indent"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-command="outdent" title="Outdent">
                                    <i class="fas fa-outdent"></i>
                                </button>
                                <div class="toolbar-separator"></div>
                                <button type="button" class="toolbar-btn" data-command="justifyLeft" title="Align Left">
                                    <i class="fas fa-align-left"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-command="justifyCenter" title="Align Center">
                                    <i class="fas fa-align-center"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-command="justifyRight" title="Align Right">
                                    <i class="fas fa-align-right"></i>
                                </button>
                                <div class="toolbar-separator"></div>
                                <button type="button" class="toolbar-btn" data-command="removeFormat" title="Remove Formatting">
                                    <i class="fas fa-remove-format"></i>
                                </button>
                                <button type="button" class="toolbar-btn" onclick="clearAllFormattingEdit()" title="Clear All Formatting">
                                    <i class="fas fa-eraser"></i>
                                </button>
                            </div>
                            <div class="editor-content" id="edit_text_content" contenteditable="true" 
                                 placeholder="Write your text content here...">${docData.text_content || ''}</div>
                            <textarea name="text_content" id="edit_text_content_hidden" style="display: none;">${docData.text_content || ''}</textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_outro" class="form-label">Conclusion Text</label>
                        <textarea id="edit_outro" name="outro_text" class="form-input form-textarea">${docData.outro_text || ''}</textarea>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Document
                        </button>
                        <button type="button" onclick="closeEditModal()" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Setup file input handling
    const editFileInput = document.getElementById('edit_file_input');
    if (editFileInput) {
        editFileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                showEditFilePreview(e.target.files[0]);
            }
        });
    }
    
    // Setup form submission
    document.getElementById('editDocumentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Update hidden textarea with rich text content before submission
        const editor = document.getElementById('edit_text_content');
        const hiddenTextarea = document.getElementById('edit_text_content_hidden');
        if (editor && hiddenTextarea) {
            hiddenTextarea.value = editor.innerHTML;
            console.log('Edit rich text content being submitted:', editor.innerHTML);
        }
        
        const formData = new FormData(this);
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        submitBtn.disabled = true;
        
        fetch('api/update-document.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Document updated successfully', 'success');
                closeEditModal();
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.error || 'Failed to update document', 'error');
            }
        })
        .catch(error => {
            showNotification('An error occurred', 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    
    // Initialize rich text editor for edit modal
    initEditRichTextEditor();
}

function closeEditModal() {
    const modal = document.querySelector('.modal-overlay');
    if (modal) {
        modal.remove();
    }
}

function toggleEditDocumentType() {
    const fileType = document.getElementById('edit_file_type').value;
    const fileSection = document.getElementById('editFileUploadSection');
    const textSection = document.getElementById('editTextEditorSection');
    
    fileSection.style.display = fileType === 'file' ? 'block' : 'none';
    textSection.style.display = fileType === 'text' ? 'block' : 'none';
}

function showEditFilePreview(file) {
    const preview = document.getElementById('edit-file-preview');
    const fileName = document.getElementById('edit-file-name');
    const fileSize = document.getElementById('edit-file-size');
    
    fileName.textContent = file.name;
    fileSize.textContent = formatFileSize(file.size);
    preview.style.display = 'block';
}

function clearEditFile() {
    const fileInput = document.getElementById('edit_file_input');
    const preview = document.getElementById('edit-file-preview');
    
    fileInput.value = '';
    preview.style.display = 'none';
}

// Drag and drop reordering
function initDragAndDrop() {
    const documentsList = document.getElementById('documentsList');
    if (!documentsList) return;
    
    let draggedElement = null;
    
    // Add event listeners to each document item
    const documentItems = documentsList.querySelectorAll('.document-item');
    documentItems.forEach(item => {
        // Make the entire item draggable
        item.draggable = true;
        
        item.addEventListener('dragstart', function(e) {
            draggedElement = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', this.dataset.documentId);
            
            // Prevent text selection
            document.body.style.userSelect = 'none';
        });
        
        item.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
            draggedElement = null;
            
            // Re-enable text selection
            document.body.style.userSelect = '';
            
            // Remove drag-over classes
            documentItems.forEach(item => item.classList.remove('drag-over'));
        });
        
        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            
            if (draggedElement && draggedElement !== this) {
                // Remove drag-over from all items
                documentItems.forEach(item => item.classList.remove('drag-over'));
                
                // Add drag-over to this item
                this.classList.add('drag-over');
            }
        });
        
        item.addEventListener('drop', function(e) {
            e.preventDefault();
            
            if (draggedElement && draggedElement !== this) {
                // Determine if we should insert before or after
                const rect = this.getBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;
                
                if (e.clientY < midpoint) {
                    this.parentNode.insertBefore(draggedElement, this);
                } else {
                    this.parentNode.insertBefore(draggedElement, this.nextSibling);
                }
                
                // Save the new order
                saveDocumentOrder();
            }
            
            // Remove drag-over classes
            documentItems.forEach(item => item.classList.remove('drag-over'));
        });
    });
}

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.document-item:not(.dragging)')];
    
    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function saveDocumentOrder() {
    const documentsList = document.getElementById('documentsList');
    const documentItems = documentsList.querySelectorAll('.document-item');
    
    const documentOrders = Array.from(documentItems).map((item, index) => ({
        id: parseInt(item.dataset.documentId),
        order: index + 1
    }));
    
    fetch('api/reorder-documents.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            binder_id: <?php echo $binder_id; ?>,
            document_orders: documentOrders
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Document order updated', 'success');
        } else {
            showNotification('Failed to update document order', 'error');
        }
    })
    .catch(error => {
        showNotification('An error occurred', 'error');
    });
}

function deleteDocument(docId) {
    if (confirm('Are you sure you want to delete this document?')) {
        fetch('api/delete-document.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ document_id: docId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Document deleted successfully', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification('Failed to delete document', 'error');
            }
        })
        .catch(error => {
            showNotification('An error occurred', 'error');
        });
    }
}

// Function to strip color-related styling from HTML while preserving structure
function stripColorsFromHTML(html) {
    // Create a temporary div to parse the HTML
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    
    // Remove color-related attributes and styles
    const elements = tempDiv.querySelectorAll('*');
    elements.forEach(element => {
        // Remove color-related attributes
        element.removeAttribute('color');
        element.removeAttribute('bgcolor');
        element.removeAttribute('face');
        
        // Remove color-related CSS properties from style attribute
        if (element.hasAttribute('style')) {
            let style = element.getAttribute('style');
            // Remove color-related CSS properties
            style = style.replace(/color\s*:\s*[^;]+;?/gi, '');
            style = style.replace(/background-color\s*:\s*[^;]+;?/gi, '');
            style = style.replace(/background\s*:\s*[^;]+;?/gi, '');
            style = style.replace(/font-family\s*:\s*[^;]+;?/gi, '');
            style = style.replace(/font-size\s*:\s*[^;]+;?/gi, '');
            style = style.replace(/font-weight\s*:\s*[^;]+;?/gi, '');
            style = style.replace(/font-style\s*:\s*[^;]+;?/gi, '');
            style = style.replace(/text-decoration\s*:\s*[^;]+;?/gi, '');
            
            // Clean up any double semicolons or trailing semicolons
            style = style.replace(/;+/g, ';');
            style = style.replace(/^;|;$/g, '');
            
            if (style.trim()) {
                element.setAttribute('style', style);
            } else {
                element.removeAttribute('style');
            }
        }
    });
    
    return tempDiv.innerHTML;
}

// Function to clear all formatting from the main editor
function clearAllFormatting() {
    const editor = document.getElementById('text_content');
    if (editor) {
        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            const contents = range.extractContents();
            
            // Create a new text node with just the plain text
            const textNode = document.createTextNode(contents.textContent);
            range.insertNode(textNode);
            
            // Update hidden textarea
            const hiddenTextarea = document.getElementById('text_content_hidden');
            if (hiddenTextarea) {
                hiddenTextarea.value = editor.innerHTML;
            }
        }
    }
}

// Function to clear all formatting from the edit modal editor
function clearAllFormattingEdit() {
    const editor = document.getElementById('edit_text_content');
    if (editor) {
        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            const contents = range.extractContents();
            
            // Create a new text node with just the plain text
            const textNode = document.createTextNode(contents.textContent);
            range.insertNode(textNode);
            
            // Update hidden textarea
            const hiddenTextarea = document.getElementById('edit_text_content_hidden');
            if (hiddenTextarea) {
                hiddenTextarea.value = editor.innerHTML;
            }
        }
    }
}

// Rich Text Editor functionality for edit modal
function initEditRichTextEditor() {
    const editor = document.getElementById('edit_text_content');
    const hiddenTextarea = document.getElementById('edit_text_content_hidden');
    const toolbar = document.querySelector('.modal-overlay .editor-toolbar');
    
    if (!editor || !toolbar) return;
    
    // Handle toolbar button clicks
    toolbar.addEventListener('click', function(e) {
        const btn = e.target.closest('.toolbar-btn');
        if (!btn) return;
        
        e.preventDefault();
        
        const command = btn.dataset.command;
        const value = btn.dataset.value;
        
        // Focus editor before executing command
        editor.focus();
        
        if (command === 'formatBlock') {
            document.execCommand('formatBlock', false, value);
        } else if (command === 'indent') {
            document.execCommand('indent', false, null);
        } else if (command === 'outdent') {
            document.execCommand('outdent', false, null);
        } else {
            document.execCommand(command, false, null);
        }
        
        // Update toolbar button states
        updateEditToolbarState();
        
        // Update hidden textarea
        updateEditHiddenTextarea();
    });
    
    // Update toolbar button states based on current selection
    function updateEditToolbarState() {
        const commands = ['bold', 'italic', 'underline'];
        const formatBlocks = ['h1', 'h2', 'h3'];
        
        // Update format commands
        commands.forEach(cmd => {
            const btn = toolbar.querySelector(`[data-command="${cmd}"]`);
            if (btn) {
                btn.classList.toggle('active', document.queryCommandState(cmd));
            }
        });
        
        // Update format block buttons
        formatBlocks.forEach(block => {
            const btn = toolbar.querySelector(`[data-command="formatBlock"][data-value="${block}"]`);
            if (btn) {
                btn.classList.toggle('active', document.queryCommandValue('formatBlock') === block);
            }
        });
    }
    
    // Update hidden textarea with editor content
    function updateEditHiddenTextarea() {
        if (hiddenTextarea) {
            hiddenTextarea.value = editor.innerHTML;
        }
    }
    
    // Update toolbar state when selection changes
    editor.addEventListener('keyup', updateEditToolbarState);
    editor.addEventListener('mouseup', updateEditToolbarState);
    editor.addEventListener('input', updateEditHiddenTextarea);
    
    // Handle paste events to preserve formatting but strip colors
    editor.addEventListener('paste', function(e) {
        e.preventDefault();
        
        // Try to get HTML content first, fallback to plain text
        let htmlContent = '';
        let plainContent = '';
        
        if (e.clipboardData) {
            htmlContent = e.clipboardData.getData('text/html');
            plainContent = e.clipboardData.getData('text/plain');
        } else if (window.clipboardData) {
            htmlContent = window.clipboardData.getData('text/html');
            plainContent = window.clipboardData.getData('text/plain');
        }
        
        // Use HTML content if available, otherwise use plain text
        let contentToInsert = htmlContent || plainContent;
        
        if (contentToInsert && htmlContent) {
            // Strip color-related styling to maintain theme consistency
            contentToInsert = stripColorsFromHTML(contentToInsert);
            document.execCommand('insertHTML', false, contentToInsert);
        } else if (contentToInsert) {
            document.execCommand('insertText', false, contentToInsert);
        }
        
        updateEditHiddenTextarea();
    });
    
    // Initial update
    updateEditHiddenTextarea();
}

// Rich Text Editor functionality
function initRichTextEditor() {
    const editor = document.getElementById('text_content');
    const hiddenTextarea = document.getElementById('text_content_hidden');
    const toolbar = document.querySelector('.editor-toolbar');
    
    if (!editor || !toolbar) return;
    
    // Handle toolbar button clicks
    toolbar.addEventListener('click', function(e) {
        const btn = e.target.closest('.toolbar-btn');
        if (!btn) return;
        
        e.preventDefault();
        
        const command = btn.dataset.command;
        const value = btn.dataset.value;
        
        // Focus editor before executing command
        editor.focus();
        
        if (command === 'formatBlock') {
            document.execCommand('formatBlock', false, value);
        } else if (command === 'indent') {
            document.execCommand('indent', false, null);
        } else if (command === 'outdent') {
            document.execCommand('outdent', false, null);
        } else {
            document.execCommand(command, false, null);
        }
        
        // Update toolbar button states
        updateToolbarState();
        
        // Update hidden textarea
        updateHiddenTextarea();
    });
    
    // Update toolbar button states based on current selection
    function updateToolbarState() {
        const commands = ['bold', 'italic', 'underline'];
        const formatBlocks = ['h1', 'h2', 'h3'];
        
        // Update format commands
        commands.forEach(cmd => {
            const btn = toolbar.querySelector(`[data-command="${cmd}"]`);
            if (btn) {
                btn.classList.toggle('active', document.queryCommandState(cmd));
            }
        });
        
        // Update format block buttons
        formatBlocks.forEach(block => {
            const btn = toolbar.querySelector(`[data-command="formatBlock"][data-value="${block}"]`);
            if (btn) {
                btn.classList.toggle('active', document.queryCommandValue('formatBlock') === block);
            }
        });
    }
    
    // Update hidden textarea with editor content
    function updateHiddenTextarea() {
        if (hiddenTextarea) {
            hiddenTextarea.value = editor.innerHTML;
        }
    }
    
    // Update toolbar state when selection changes
    editor.addEventListener('keyup', updateToolbarState);
    editor.addEventListener('mouseup', updateToolbarState);
    editor.addEventListener('input', updateHiddenTextarea);
    
    // Handle paste events to preserve formatting but strip colors
    editor.addEventListener('paste', function(e) {
        e.preventDefault();
        
        // Try to get HTML content first, fallback to plain text
        let htmlContent = '';
        let plainContent = '';
        
        if (e.clipboardData) {
            htmlContent = e.clipboardData.getData('text/html');
            plainContent = e.clipboardData.getData('text/plain');
        } else if (window.clipboardData) {
            htmlContent = window.clipboardData.getData('text/html');
            plainContent = window.clipboardData.getData('text/plain');
        }
        
        // Use HTML content if available, otherwise use plain text
        let contentToInsert = htmlContent || plainContent;
        
        if (contentToInsert && htmlContent) {
            // Strip color-related styling to maintain theme consistency
            contentToInsert = stripColorsFromHTML(contentToInsert);
            document.execCommand('insertHTML', false, contentToInsert);
        } else if (contentToInsert) {
            document.execCommand('insertText', false, contentToInsert);
        }
        
        updateHiddenTextarea();
    });
    
    // Initial update
    updateHiddenTextarea();
}

document.getElementById('addDocumentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Update hidden textarea with rich text content before submission
    const editor = document.getElementById('text_content');
    const hiddenTextarea = document.getElementById('text_content_hidden');
    if (editor && hiddenTextarea) {
        hiddenTextarea.value = editor.innerHTML;
        console.log('Rich text content being submitted:', editor.innerHTML);
    }
    
    // Debug: Check form data
    const formData = new FormData(this);
    console.log('Form data:');
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }
    
    formData.append('binder_id', <?php echo $binder_id; ?>);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Document...';
    submitBtn.disabled = true;
    
    fetch('api/add-document.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showNotification('Document added successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.error || 'Failed to add document', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred: ' + error.message, 'error');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Setup drag and drop for file upload
document.addEventListener('DOMContentLoaded', function() {
    const fileUploadArea = document.querySelector('.file-upload');
    const fileInput = document.getElementById('file_input');
    
    if (fileUploadArea && fileInput) {
        // Handle file input change
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                showFilePreview(e.target.files[0]);
            }
        });
        
        // Setup drag and drop
        setupDragAndDrop(fileUploadArea, function(file) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
            showFilePreview(file);
        });
    }
    
    // Initialize document reordering
    initDragAndDrop();
    
    // Initialize rich text editor
    initRichTextEditor();
});

function showFilePreview(file) {
    const preview = document.getElementById('file-preview');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    
    fileName.textContent = file.name;
    fileSize.textContent = formatFileSize(file.size);
    preview.style.display = 'block';
}

function clearFile() {
    const fileInput = document.getElementById('file_input');
    const preview = document.getElementById('file-preview');
    
    fileInput.value = '';
    preview.style.display = 'none';
}
</script>

<?php include 'footer.php'; ?>
