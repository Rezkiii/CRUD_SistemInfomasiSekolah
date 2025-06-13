<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            
            // Handle file upload
            if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                $upload_dir = '../uploads/documents/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
                    try {
                        $stmt = $conn->prepare("INSERT INTO documents (title, description, file_path, file_type, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $title,
                            $description,
                            'uploads/documents/' . $new_filename,
                            $file_extension,
                            $_FILES['file']['size'],
                            $_SESSION['user_id']
                        ]);
                        $message = 'Dokumen berhasil diunggah';
                    } catch (PDOException $e) {
                        $error = 'Gagal mengunggah dokumen: ' . $e->getMessage();
                        // Delete the uploaded file if database insertion fails
                        unlink($upload_path);
                    }
                } else {
                    $error = 'Gagal mengunggah file';
                }
            } else {
                $error = 'Pilih file untuk diunggah';
            }
        } elseif ($_POST['action'] == 'delete' && isset($_POST['id'])) {
            try {
                // Get file path before deleting
                $stmt = $conn->prepare("SELECT file_path FROM documents WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $document = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Delete the document record
                $stmt = $conn->prepare("DELETE FROM documents WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                
                // Delete the file if it exists
                if ($document && $document['file_path']) {
                    $file_path = '../' . $document['file_path'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
                
                $message = 'Dokumen berhasil dihapus';
            } catch (PDOException $e) {
                $error = 'Gagal menghapus dokumen: ' . $e->getMessage();
            }
        }
    }
}

// Fetch all documents
$stmt = $conn->query("SELECT d.*, u.username as uploaded_by_username 
                     FROM documents d 
                     LEFT JOIN users u ON d.uploaded_by = u.id 
                     ORDER BY d.created_at DESC");
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Dokumen - Sistem Informasi Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .sidebar .active {
            background-color: #0d6efd;
        }
        .main-content {
            padding: 20px;
        }
        .file-icon {
            font-size: 2rem;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <h4 class="text-white text-center mb-4">Admin Panel</h4>
                <nav>
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a href="activities.php">
                        <i class="fas fa-calendar-alt me-2"></i> Kegiatan
                    </a>
                    <a href="admissions.php">
                        <i class="fas fa-user-graduate me-2"></i> Penerimaan Siswa
                    </a>
                    <a href="documents.php" class="active">
                        <i class="fas fa-file-alt me-2"></i> Dokumen
                    </a>
                    <a href="users.php">
                        <i class="fas fa-users me-2"></i> Pengguna
                    </a>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manajemen Dokumen</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                        <i class="fas fa-plus"></i> Upload Dokumen
                    </button>
                </div>

                <?php if ($message): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <!-- Documents Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Dokumen</th>
                                        <th>Deskripsi</th>
                                        <th>Ukuran</th>
                                        <th>Diunggah Oleh</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documents as $document): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php
                                                $icon_class = 'fa-file';
                                                switch ($document['file_type']) {
                                                    case 'pdf':
                                                        $icon_class = 'fa-file-pdf';
                                                        break;
                                                    case 'doc':
                                                    case 'docx':
                                                        $icon_class = 'fa-file-word';
                                                        break;
                                                    case 'xls':
                                                    case 'xlsx':
                                                        $icon_class = 'fa-file-excel';
                                                        break;
                                                    case 'jpg':
                                                    case 'jpeg':
                                                    case 'png':
                                                        $icon_class = 'fa-file-image';
                                                        break;
                                                }
                                                ?>
                                                <i class="fas <?php echo $icon_class; ?> file-icon"></i>
                                                <?php echo htmlspecialchars($document['title']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($document['description']); ?></td>
                                        <td><?php echo number_format($document['file_size'] / 1024, 2) . ' KB'; ?></td>
                                        <td><?php echo htmlspecialchars($document['uploaded_by_username']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($document['created_at'])); ?></td>
                                        <td>
                                            <a href="../<?php echo htmlspecialchars($document['file_path']); ?>" class="btn btn-sm btn-info" download>
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $document['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Document Modal -->
    <div class="modal fade" id="addDocumentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Judul Dokumen</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="file" class="form-label">File</label>
                            <input type="file" class="form-control" id="file" name="file" required>
                            <small class="text-muted">Format yang didukung: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 