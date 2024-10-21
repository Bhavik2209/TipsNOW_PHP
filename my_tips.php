<?php
require_once 'config.php';
require_once 'auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get user's tips
$stmt = $pdo->prepare("SELECT tips.*, users.username,
                       (SELECT COUNT(*) FROM likes WHERE tip_id = tips.id) as like_count
                       FROM tips 
                       JOIN users ON tips.user_id = users.id
                       WHERE tips.user_id = ?
                       ORDER BY tips.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$my_tips = $stmt->fetchAll();

// Get total likes received
$stmt = $pdo->prepare("SELECT COUNT(*) as total_likes FROM likes 
                       JOIN tips ON likes.tip_id = tips.id
                       WHERE tips.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_likes = $stmt->fetch()['total_likes'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Tips - TipsNow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">TipsNow</a>
            <div class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <a class="nav-link" href="my_tips.php">My Tips</a>
                    <a class="nav-link" href="create_tip.php">Share Tip</a>
                    <a class="nav-link" href="bookmarks.php">Saved Tips</a>
                    <a class="nav-link" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">Login</a>
                    <a class="nav-link" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Stats Section -->
        
            
            <div class="card mb-4">
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-3">
                <h4>Total Tips</h4>
                <p class="h2"><?php echo count($my_tips); ?></p>
            </div>
            <div class="col-md-3">
                <h4>Total Likes</h4>
                <p class="h2"><?php echo $total_likes; ?></p>
            </div>
            <div class="col-md-3">
                <h4>Avg. Likes per Tip</h4>
                <p class="h2"><?php echo count($my_tips) > 0 ? round($total_likes / count($my_tips), 1) : 0; ?></p>
            </div>
            <div class="col-md-3">
                <h4>Most Popular Category</h4>
                <p class="h2"><?php 
                    $category_counts = array_count_values(array_column($my_tips, 'category'));
                    echo !empty($category_counts) ? array_search(max($category_counts), $category_counts) : 'N/A';
                ?></p>
            </div>
        </div>
    
</div>
        </div>

        <h2 class="mb-4">My Tips</h2>
        
        <?php if (empty($my_tips)): ?>
            <div class="alert alert-info">
                You haven't shared any tips yet. 
                <a href="create_tip.php" class="alert-link">Share your first tip!</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($my_tips as $tip): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($tip['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($tip['content']); ?></p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        Category: <?php echo htmlspecialchars($tip['category']); ?> |
                                        Likes: <?php echo $tip['like_count']; ?><br>
                                        Shared on: <?php echo date('M j, Y', strtotime($tip['created_at'])); ?>
                                    </small>
                                </p>
                                <div class="card-footer">
                                    <form action="delete_tip.php" method="post" class="d-inline" 
                                    onsubmit="return confirm('Are you sure you want to delete this tip?');">
                                        <input type="hidden" name="tip_id" value="<?php echo $tip['id']; ?>">
                                        <a href="edit_tip.php?id=<?php echo $tip['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                                <!-- Add Edit/Delete functionality here if desired -->
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>