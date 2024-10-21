<?php
require_once 'config.php';
require_once 'auth.php';

if (!isLoggedIn() || !isset($_POST['tip_id'])) {
    header('Location: index.php');
    exit();
}

$tip_id = $_POST['tip_id'];
$user_id = $_SESSION['user_id'];

// Check if user already liked this tip
$stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND tip_id = ?");
$stmt->execute([$user_id, $tip_id]);

if (!$stmt->fetch()) {
    // Add like
    $stmt = $pdo->prepare("INSERT INTO likes (user_id, tip_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $tip_id]);
    
    // Update likes count
    $stmt = $pdo->prepare("UPDATE tips SET likes = likes + 1 WHERE id = ?");
    $stmt->execute([$tip_id]);
}

header('Location: index.php');
?>