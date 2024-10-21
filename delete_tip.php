<?php
// Add delete_tip.php for delete functionality
require_once 'config.php';
require_once 'auth.php';

if (!isLoggedIn() || !isset($_POST['tip_id'])) {
    header('Location: my_tips.php');
    exit();
}

$tip_id = $_POST['tip_id'];

// First, delete associated likes
$stmt = $pdo->prepare("DELETE FROM likes WHERE tip_id = ?");
$stmt->execute([$tip_id]);

// Then, delete the tip
$stmt = $pdo->prepare("DELETE FROM tips WHERE id = ? AND user_id = ?");
if ($stmt->execute([$tip_id, $_SESSION['user_id']])) {
    header('Location: my_tips.php');
    exit();
} else {
    // Handle error
    echo "Failed to delete the tip.";
}

?>
