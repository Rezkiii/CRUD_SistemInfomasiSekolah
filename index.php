<?php
require_once 'config/database.php';

// Fetch recent activities
$stmt = $conn->query("SELECT * FROM activities ORDER BY date DESC LIMIT 3");
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch admission schedule
$stmt = $conn->query("SELECT * FROM admissions ORDER BY start_date DESC LIMIT 1");
$admission = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch available documents
$stmt = $conn->query("SELECT * FROM documents ORDER BY created_at DESC");
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/images/school-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
        }
        .activity-card {
            transition: transform 0.3s;
        }
        .activity-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Sekolah Kita</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#activities">Kegiatan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#admission">Penerimaan Siswa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#documents">Dokumen</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4">Selamat Datang di Sekolah Kita</h1>
            <p class="lead">Membentuk Generasi Unggul dengan Pendidikan Berkualitas</p>
        </div>
    </section>

    <!-- Activities Section -->
    <section id="activities" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Kegiatan Terbaru</h2>
            <div class="row">
                <?php foreach ($activities as $activity): ?>
                <div class="col-md-4 mb-4">
                    <div class="card activity-card">
                        <?php if ($activity['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($activity['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($activity['title']); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($activity['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($activity['description']); ?></p>
                            <p class="card-text"><small class="text-muted">Tanggal: <?php echo date('d F Y', strtotime($activity['date'])); ?></small></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Admission Section -->
    <section id="admission" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4">Penerimaan Siswa Baru</h2>
            <?php if ($admission): ?>
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title"><?php echo htmlspecialchars($admission['title']); ?></h3>
                    <p class="card-text"><?php echo htmlspecialchars($admission['description']); ?></p>
                    <p class="card-text">
                        <strong>Periode Pendaftaran:</strong><br>
                        <?php echo date('d F Y', strtotime($admission['start_date'])); ?> - 
                        <?php echo date('d F Y', strtotime($admission['end_date'])); ?>
                    </p>
                    <h4>Persyaratan:</h4>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($admission['requirements'])); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Documents Section -->
    <section id="documents" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Dokumen Penting</h2>
            <div class="row">
                <?php foreach ($documents as $document): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($document['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($document['description']); ?></p>
                            <a href="<?php echo htmlspecialchars($document['file_path']); ?>" class="btn btn-primary" download>
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Sekolah Kita</h5>
                    <p>Alamat: Jl. Pendidikan No. 123<br>
                    Telepon: (021) 1234-5678<br>
                    Email: info@sekolahkita.sch.id</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5>Ikuti Kami</h5>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Sekolah Kita. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 