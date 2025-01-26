<?php
$host = 'localhost';
$dbname = 'auto_accept_taxi';
$username = 'root';  // Sesuaikan dengan user MySQL Anda
$password = '';      // Sesuaikan dengan password MySQL Anda

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fungsi untuk generate kode unik
    function generateUniqueCode($length = 8) {
        return strtoupper(bin2hex(random_bytes($length / 2)));
    }

    // Menyimpan kode unik ke dalam database dengan durasi tertentu
    if (isset($_POST['generate_code'])) {
        $uniqueCode = generateUniqueCode(10);
        $duration = (int) $_POST['duration'];  // Ambil durasi dalam minggu dari input form
        $expiryDate = date('Y-m-d H:i:s', strtotime("+$duration weeks"));

        $stmt = $pdo->prepare("INSERT INTO unique_codes (code, in_use, expired_at, duration_weeks) VALUES (:code, 0, :expired_at, :duration)");
        $stmt->bindParam(':code', $uniqueCode);
        $stmt->bindParam(':expired_at', $expiryDate);
        $stmt->bindParam(':duration', $duration);
        $stmt->execute();
        
        $message = "Kode unik berhasil dibuat: $uniqueCode (Berlaku selama $duration minggu)";
    }

    // Ambil semua kode yang masih aktif
    $stmt = $pdo->query("SELECT * FROM unique_codes WHERE expired_at > NOW() ORDER BY created_at DESC");
    $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kode Unik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Manajemen Kode Unik</h2>

        <?php if (isset($message)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6 offset-md-3">
                <form method="POST">
                    <div class="mb-3">
                        <label for="duration" class="form-label">Durasi Kode Unik (Minggu)</label>
                        <input type="number" name="duration" id="duration" class="form-control" required min="1" placeholder="Masukkan durasi dalam minggu">
                    </div>
                    <button type="submit" name="generate_code" class="btn btn-primary w-100 mb-3">Generate Kode Unik</button>
                </form>
            </div>
        </div>

        <div class="mt-4">
            <h4>Daftar Kode Unik Aktif</h4>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Kode</th>
                        <th>Status</th>
                        <th>Tanggal Dibuat</th>
                        <th>Expired At</th>
                        <th>Durasi (Minggu)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($codes as $code): ?>
                        <tr>
                            <td><?= $code['id']; ?></td>
                            <td><?= htmlspecialchars($code['code']); ?></td>
                            <td><?= $code['in_use'] ? 'Digunakan' : 'Tersedia'; ?></td>
                            <td><?= $code['created_at']; ?></td>
                            <td><?= $code['expired_at']; ?></td>
                            <td><?= $code['duration_weeks']; ?> minggu</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
