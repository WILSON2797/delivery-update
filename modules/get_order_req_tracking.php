<?php
include '../php/config.php';
header('Content-Type: application/json');
// Disable output buffering untuk response lebih cepat
if (ob_get_level()) ob_end_clean();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}
try {
    $id = $_GET['id'] ?? null;

    // Validasi ID harus numeric
    if (empty($id) || !is_numeric($id)) {
        throw new Exception('ID tidak valid!');
    }

    // Cast ke integer untuk keamanan
    $id = (int) $id;
    // Query dengan SELECT spesifik field (lebih cepat dari SELECT *)
    // Jika tabel besar, pilih hanya field yang dibutuhkan
    $sql = "SELECT * FROM daily_report WHERE id = ? LIMIT 1";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $id);

    // Execute dengan error handling
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        throw new Exception('Data tidak ditemukan!');
    }
    $data = $result->fetch_assoc();

    // Clean up resources immediately
    $stmt->close();
    $conn->close();

    // Send response
    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);
} catch (Exception $e) {
    // Close connection on error
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();

    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
exit;
?>