<?php
require_once 'config.php';
require_once 'auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tip_id'])) {
    $tip_id = (int)$_POST['tip_id'];
    
    // First verify that the tip exists
    $check_tip = $pdo->prepare("SELECT id FROM tips WHERE id = ?");
    $check_tip->execute([$tip_id]);
    
    if ($check_tip->fetch()) {
        // Check if already bookmarked
        $check_stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND tip_id = ?");
        $check_stmt->execute([$_SESSION['user_id'], $tip_id]);
        
        if (!$check_stmt->fetch()) {
            try {
                // Add bookmark if not already bookmarked
                $stmt = $pdo->prepare("INSERT INTO bookmarks (user_id, tip_id) VALUES (?, ?)");
                $stmt->execute([$_SESSION['user_id'], $tip_id]);
                $_SESSION['message'] = "Tip saved successfully!";
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error saving tip. Please try again.";
            }
        }
    }
}

// Redirect back to previous page
header('Location: ' . $_SERVER['HTTP_REFERER'] ?? 'index.php');
exit();