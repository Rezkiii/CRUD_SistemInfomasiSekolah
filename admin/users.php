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
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $full_name = $_POST['full_name'] ?? '';
            $email = $_POST['email'] ?? '';
            
            if (empty($username) || empty($password) || empty($full_name) || empty($email)) {
                $error = 'Semua field harus diisi';
            } else {
                try {
                    // Check if username or email already exists
                    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                    $stmt->execute([$username, $email]);
                    if ($stmt->rowCount() > 0) {
                        $error = 'Username atau email sudah digunakan';
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$username, $hashed_password, $full_name, $email]);
                        $message = 'Pengguna berhasil ditambahkan';
                    }
                } catch (PDOException $e) {
                    $error = 'Gagal menambahkan pengguna: ' . $e->getMessage();
                }
            }
        } elseif ($_POST['action'] == 'edit' && isset($_POST['id'])) {
            $username = $_POST['username'] ?? '';
            $full_name = $_POST['full_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            try {
                if (!empty($password)) {
                    // Update with new password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, full_name = ?, email = ? WHERE id = ?");
                    $stmt->execute([$username, $hashed_password, $full_name, $email, $_POST['id']]);
                } else {
                    // Update without changing password
                    $stmt = $conn->prepare("UPDATE users SET username = ?, full_name = ?, email = ? WHERE id = ?");
                    $stmt->execute([$username, $full_name, $email, $_POST['id']]);
                }
                $message = 'Pengguna berhasil diperbarui';
            } catch (PDOException $e) {
                $error = 'Gagal memperbarui pengguna: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] == 'delete' && isset($_POST['id'])) {
            // Prevent deleting the current user
            if ($_POST['id'] == $_SESSION['user_id']) {
                $error = 'Tidak dapat menghapus akun yang sedang digunakan';
            } else {
                try {
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $message = 'Pengguna berhasil dihapus';
                } catch (PDOException $e) {
                    $error = 'Gagal menghapus pengguna: ' . $e->getMessage();
                }
            }
        }
    }
}

// Fetch all users
$stmt = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - Sistem Informasi Sekolah</title>
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
                    <a href="admissions.php">
                        <i class="fas fa-user-graduate me-2"></i> Penerimaan Siswa
                    </a>
                    <a href="documents.php">
                        <i class="fas fa-file-alt me-2"></i> Dokumen
                    </a>
                    <a href="users.php" class="active">
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
                    <h2>Manajemen Pengguna</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus"></i> Tambah Pengguna
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

                <!-- Users Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>Email</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $user['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- Edit User Modal -->
                                    <div class="modal fade" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Pengguna</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="edit">
                                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label for="username" class="form-label">Username</label>
                                                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="password" class="form-label">Password</label>
                                                            <input type="password" class="form-control" id="password" name="password">
                                                            <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="full_name" class="form-label">Nama Lengkap</label>
                                                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="email" class="form-label">Email</label>
                                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 