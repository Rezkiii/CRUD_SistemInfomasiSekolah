<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch statistics
$stats = [
    'activities' => $conn->query("SELECT COUNT(*) FROM activities")->fetchColumn(),
    'admissions' => $conn->query("SELECT COUNT(*) FROM admissions")->fetchColumn(),
    'documents' => $conn->query("SELECT COUNT(*) FROM documents")->fetchColumn()
];

// Fetch recent activities
$stmt = $conn->query("SELECT * FROM activities ORDER BY date DESC LIMIT 5");
$recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent documents
$stmt = $conn->query("SELECT * FROM documents ORDER BY created_at DESC LIMIT 5");
$recent_documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Informasi Sekolah</title>
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
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
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
                    <a href="dashboard.php" class="active">
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
                    <h2>Dashboard</h2>
                    <span class="text-muted">Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-card bg-primary">
                            <h3><?php echo $stats['activities']; ?></h3>
                            <p class="mb-0">Total Kegiatan</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card bg-success">
                            <h3><?php echo $stats['admissions']; ?></h3>
                            <p class="mb-0">Jadwal Penerimaan</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card bg-info">
                            <h3><?php echo $stats['documents']; ?></h3>
                            <p class="mb-0">Dokumen</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Kegiatan Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <?php foreach ($recent_activities as $activity): ?>
                                    <a href="activities.php?id=<?php echo $activity['id']; ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($activity['title']); ?></h6>
                                            <small><?php echo date('d M Y', strtotime($activity['date'])); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars(substr($activity['description'], 0, 100)) . '...'; ?></p>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Documents -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Dokumen Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <?php foreach ($recent_documents as $document): ?>
                                    <a href="documents.php?id=<?php echo $document['id']; ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($document['title']); ?></h6>
                                            <small><?php echo date('d M Y', strtotime($document['created_at'])); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars(substr($document['description'], 0, 100)) . '...'; ?></p>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 