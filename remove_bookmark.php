<?php
require_once 'config.php';
require_once 'auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tip_id'])) {
    $tip_id = (int)$_POST['tip_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE user_id = ? AND tip_id = ?");
        $stmt->execute([$_SESSION['user_id'], $tip_id]);
        $_SESSION['message'] = "Bookmark removed successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error removing bookmark. Please try again.";
    }
}

// Redirect back to bookmarks page
header('Location: bookmarks.php');
exit();