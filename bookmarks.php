<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'categories.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get user's bookmarked tips
$stmt = $pdo->prepare("
    SELECT tips.*, users.username, bookmarks.created_at as bookmarked_at 
    FROM bookmarks 
    JOIN tips ON bookmarks.tip_id = tips.id 
    JOIN users ON tips.user_id = users.id 
    WHERE bookmarks.user_id = ? 
    ORDER BY bookmarks.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookmarked_tips = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Saved Tips - TipsNow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <h1>My Saved Tips</h1>
        
        <?php if (empty($bookmarked_tips)): ?>
            <div class="alert alert-info">
                You haven't saved any tips yet.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($bookmarked_tips as $tip): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($tip['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($tip['content']); ?></p>
                                <p class="card-text">
                                    <span class="badge bg-primary">
                                        <?php echo htmlspecialchars(TIP_CATEGORIES[$tip['category']]); ?>
                                    </span>
                                </p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        By <?php echo htmlspecialchars($tip['username']); ?> |
                                        Saved on: <?php echo date('M j, Y', strtotime($tip['bookmarked_at'])); ?>
                                    </small>
                                </p>
                                <form action="remove_bookmark.php" method="post" class="d-inline">
                                    <input type="hidden" name="tip_id" value="<?php echo $tip['id']; ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-bookmark-x"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>