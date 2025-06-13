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
            $date = $_POST['date'] ?? '';
            $location = $_POST['location'] ?? '';
            
            // Handle image upload
            $image_url = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $upload_dir = '../uploads/activities/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image_url = 'uploads/activities/' . $new_filename;
                }
            }
            
            try {
                $stmt = $conn->prepare("INSERT INTO activities (title, description, date, location, image_url) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $date, $location, $image_url]);
                $message = 'Kegiatan berhasil ditambahkan';
            } catch (PDOException $e) {
                $error = 'Gagal menambahkan kegiatan: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] == 'delete' && isset($_POST['id'])) {
            try {
                // Get image URL before deleting
                $stmt = $conn->prepare("SELECT image_url FROM activities WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $activity = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Delete the activity
                $stmt = $conn->prepare("DELETE FROM activities WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                
                // Delete the image file if it exists
                if ($activity && $activity['image_url']) {
                    $image_path = '../' . $activity['image_url'];
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                
                $message = 'Kegiatan berhasil dihapus';
            } catch (PDOException $e) {
                $error = 'Gagal menghapus kegiatan: ' . $e->getMessage();
            }
        }
    }
}

// Fetch all activities
$stmt = $conn->query("SELECT * FROM activities ORDER BY date DESC");
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kegiatan - Sistem Informasi Sekolah</title>
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
        .activity-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
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
                    <a href="activities.php" class="active">
                        <i class="fas fa-calendar-alt me-2"></i> Kegiatan
                    </a>
                    <a href="admissions.php">
                        <i class="fas fa-user-graduate me-2"></i> Penerimaan Siswa
                    </a>
                    <a href="documents.php">
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
                    <h2>Manajemen Kegiatan</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addActivityModal">
                        <i class="fas fa-plus"></i> Tambah Kegiatan
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

                <!-- Activities Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Gambar</th>
                                        <th>Judul</th>
                                        <th>Tanggal</th>
                                        <th>Lokasi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activities as $activity): ?>
                                    <tr>
                                        <td>
                                            <?php if ($activity['image_url']): ?>
                                            <img src="../<?php echo htmlspecialchars($activity['image_url']); ?>" class="activity-image" alt="<?php echo htmlspecialchars($activity['title']); ?>">
                                            <?php else: ?>
                                            <span class="text-muted">Tidak ada gambar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($activity['title']); ?></td>
                                        <td><?php echo date('d F Y', strtotime($activity['date'])); ?></td>
                                        <td><?php echo htmlspecialchars($activity['location']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewActivityModal<?php echo $activity['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editActivityModal<?php echo $activity['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kegiatan ini?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $activity['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- View Activity Modal -->
                                    <div class="modal fade" id="viewActivityModal<?php echo $activity['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detail Kegiatan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php if ($activity['image_url']): ?>
                                                    <img src="../<?php echo htmlspecialchars($activity['image_url']); ?>" class="img-fluid mb-3" alt="<?php echo htmlspecialchars($activity['title']); ?>">
                                                    <?php endif; ?>
                                                    <h4><?php echo htmlspecialchars($activity['title']); ?></h4>
                                                    <p><strong>Tanggal:</strong> <?php echo date('d F Y', strtotime($activity['date'])); ?></p>
                                                    <p><strong>Lokasi:</strong> <?php echo htmlspecialchars($activity['location']); ?></p>
                                                    <p><strong>Deskripsi:</strong></p>
                                                    <p><?php echo nl2br(htmlspecialchars($activity['description'])); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Activity Modal -->
                                    <div class="modal fade" id="editActivityModal<?php echo $activity['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Kegiatan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" enctype="multipart/form-data">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="edit">
                                                        <input type="hidden" name="id" value="<?php echo $activity['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label for="title" class="form-label">Judul</label>
                                                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($activity['title']); ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="description" class="form-label">Deskripsi</label>
                                                            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($activity['description']); ?></textarea>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="date" class="form-label">Tanggal</label>
                                                            <input type="date" class="form-control" id="date" name="date" value="<?php echo $activity['date']; ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="location" class="form-label">Lokasi</label>
                                                            <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($activity['location']); ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="image" class="form-label">Gambar</label>
                                                            <?php if ($activity['image_url']): ?>
                                                            <div class="mb-2">
                                                                <img src="../<?php echo htmlspecialchars($activity['image_url']); ?>" class="activity-image" alt="Current image">
                                                            </div>
                                                            <?php endif; ?>
                                                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Activity Modal -->
    <div class="modal fade" id="addActivityModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kegiatan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Judul</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="date" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">Lokasi</label>
                            <input type="text" class="form-control" id="location" name="location" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Gambar</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah Kegiatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 