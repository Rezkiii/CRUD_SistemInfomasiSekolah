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
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            $requirements = $_POST['requirements'] ?? '';
            
            try {
                $stmt = $conn->prepare("INSERT INTO admissions (title, description, start_date, end_date, requirements) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $start_date, $end_date, $requirements]);
                $message = 'Jadwal penerimaan siswa baru berhasil ditambahkan';
            } catch (PDOException $e) {
                $error = 'Gagal menambahkan jadwal: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] == 'edit' && isset($_POST['id'])) {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            $requirements = $_POST['requirements'] ?? '';
            
            try {
                $stmt = $conn->prepare("UPDATE admissions SET title = ?, description = ?, start_date = ?, end_date = ?, requirements = ? WHERE id = ?");
                $stmt->execute([$title, $description, $start_date, $end_date, $requirements, $_POST['id']]);
                $message = 'Jadwal penerimaan siswa baru berhasil diperbarui';
            } catch (PDOException $e) {
                $error = 'Gagal memperbarui jadwal: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] == 'delete' && isset($_POST['id'])) {
            try {
                $stmt = $conn->prepare("DELETE FROM admissions WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = 'Jadwal penerimaan siswa baru berhasil dihapus';
            } catch (PDOException $e) {
                $error = 'Gagal menghapus jadwal: ' . $e->getMessage();
            }
        }
    }
}

// Fetch all admissions
$stmt = $conn->query("SELECT * FROM admissions ORDER BY start_date DESC");
$admissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Penerimaan Siswa - Sistem Informasi Sekolah</title>
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
                    <a href="admissions.php" class="active">
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
                    <h2>Manajemen Penerimaan Siswa</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdmissionModal">
                        <i class="fas fa-plus"></i> Tambah Jadwal
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

                <!-- Admissions Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Judul</th>
                                        <th>Periode</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admissions as $admission): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($admission['title']); ?></td>
                                        <td>
                                            <?php echo date('d F Y', strtotime($admission['start_date'])); ?> - 
                                            <?php echo date('d F Y', strtotime($admission['end_date'])); ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewAdmissionModal<?php echo $admission['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editAdmissionModal<?php echo $admission['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $admission['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- View Admission Modal -->
                                    <div class="modal fade" id="viewAdmissionModal<?php echo $admission['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detail Jadwal Penerimaan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <h4><?php echo htmlspecialchars($admission['title']); ?></h4>
                                                    <p><strong>Periode:</strong><br>
                                                    <?php echo date('d F Y', strtotime($admission['start_date'])); ?> - 
                                                    <?php echo date('d F Y', strtotime($admission['end_date'])); ?></p>
                                                    <p><strong>Deskripsi:</strong></p>
                                                    <p><?php echo nl2br(htmlspecialchars($admission['description'])); ?></p>
                                                    <p><strong>Persyaratan:</strong></p>
                                                    <p><?php echo nl2br(htmlspecialchars($admission['requirements'])); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Admission Modal -->
                                    <div class="modal fade" id="editAdmissionModal<?php echo $admission['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Jadwal Penerimaan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="edit">
                                                        <input type="hidden" name="id" value="<?php echo $admission['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label for="title" class="form-label">Judul</label>
                                                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($admission['title']); ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="description" class="form-label">Deskripsi</label>
                                                            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($admission['description']); ?></textarea>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                                                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $admission['start_date']; ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="end_date" class="form-label">Tanggal Selesai</label>
                                                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $admission['end_date']; ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="requirements" class="form-label">Persyaratan</label>
                                                            <textarea class="form-control" id="requirements" name="requirements" rows="5" required><?php echo htmlspecialchars($admission['requirements']); ?></textarea>
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

    <!-- Add Admission Modal -->
    <div class="modal fade" id="addAdmissionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jadwal Penerimaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
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
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="end_date" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="requirements" class="form-label">Persyaratan</label>
                            <textarea class="form-control" id="requirements" name="requirements" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 