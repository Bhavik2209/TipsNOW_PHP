<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'categories.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    
    if (!array_key_exists($category, TIP_CATEGORIES)) {
        $category = 'general';
    }
    
    $stmt = $pdo->prepare("INSERT INTO tips (user_id, title, content, category) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$_SESSION['user_id'], $title, $content, $category])) {
        header('Location: index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Share a Tip - TipsNow</title>
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
        <h1>Share a Tip</h1>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-control" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-select" required>
                    <option value="">Select a Category</option>
                    <?php foreach (TIP_CATEGORIES as $key => $label): ?>
                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Share Tip</button>
        </form>
    </div>
</body>
</html>
