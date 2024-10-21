<?php
// Add edit_tip.php for editing functionality
require_once 'config.php';
require_once 'auth.php';
require_once 'categories.php'; // Include categories

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$tip_id = $_GET['id'] ?? null;
if (!$tip_id) {
    header('Location: my_tips.php');
    exit();
}

// Get tip details
$stmt = $pdo->prepare("SELECT * FROM tips WHERE id = ? AND user_id = ?");
$stmt->execute([$tip_id, $_SESSION['user_id']]);
$tip = $stmt->fetch();

if (!$tip) {
    header('Location: my_tips.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    
    $stmt = $pdo->prepare("UPDATE tips SET title = ?, content = ?, category = ? WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$title, $content, $category, $tip_id, $_SESSION['user_id']])) {
        header('Location: my_tips.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Tip - TipsNow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Edit Tip</h1>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" required 
                       value="<?php echo htmlspecialchars($tip['title']); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-control" rows="4" required><?php 
                    echo htmlspecialchars($tip['content']); 
                ?></textarea>
            </div>
           
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-select" required>
                    <?php foreach (TIP_CATEGORIES as $key => $label): ?>
                        <option value="<?php echo htmlspecialchars($key); ?>" 
                            <?php echo $tip['category'] === $key ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Tip</button>
            <a href="my_tips.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
