<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'categories.php';

// Get Tip of the Day
$today = date('Y-m-d');
$tip_of_day_query = $pdo->query("SELECT tips.*, users.username 
                                FROM tips 
                                JOIN users ON tips.user_id = users.id 
                                ORDER BY RAND(UNIX_TIMESTAMP(DATE('$today'))) 
                                LIMIT 1")->fetch();

// Handle search and filter
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

$where_conditions = [];
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "(tips.title LIKE ? OR users.username LIKE ?)";
    $search_term = "%{$_GET['search']}%";
    $params[] = $search_term;
    $params[] = $search_term;
}

if (isset($_GET['category']) && !empty($_GET['category']) && array_key_exists($_GET['category'], TIP_CATEGORIES)) {
    $where_conditions[] = "tips.category = ?";
    $params[] = $_GET['category'];
}

// Build query
$latest_tips_query = "SELECT tips.*, users.username 
                      FROM tips 
                      JOIN users ON tips.user_id = users.id";

if (!empty($where_conditions)) {
    $latest_tips_query .= " WHERE " . implode(" AND ", $where_conditions);
}

// Get total count for pagination
$count_query = str_replace("tips.*, users.username", "COUNT(*) as count", $latest_tips_query);
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_tips = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
$total_pages = ceil($total_tips / $per_page);

// Add pagination
$latest_tips_query .= " ORDER BY tips.created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($latest_tips_query);
$stmt->execute($params);
$latest_tips = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>TipsNow - Share & Learn</title>
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
        <!-- Search and Filter Section -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form class="d-flex" method="GET">
                    <input type="text" name="search" class="form-control me-2" 
                           placeholder="Search by title or username..." 
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    <select name="category" class="form-select me-2">
                        <option value="">All Categories</option>
                        <?php foreach (TIP_CATEGORIES as $key => $label): ?>
                            <option value="<?php echo $key; ?>" 
                                <?php echo (isset($_GET['category']) && $_GET['category'] === $key) ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if (!empty($_GET)): ?>
                        <a href="index.php" class="btn btn-secondary ms-2">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Category Quick Filters -->
        <div class="mb-4">
            <div class="d-flex flex-wrap gap-2">
                <?php foreach (TIP_CATEGORIES as $key => $label): ?>
                    <a href="?category=<?php echo $key; ?>" 
                       class="btn <?php echo (isset($_GET['category']) && $_GET['category'] === $key) 
                                        ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <?php echo $label; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tip of the Day Section -->
        <?php if ($tip_of_day_query): ?>
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Tip of the Day</h3>
            </div>
            <div class="card-body">
                <h4 class="card-title"><?php echo htmlspecialchars($tip_of_day_query['title']); ?></h4>
                <p class="card-text"><?php echo htmlspecialchars($tip_of_day_query['content']); ?></p>
                <p class="card-text">
                    <span class="badge bg-primary">
                        <?php echo htmlspecialchars(TIP_CATEGORIES[$tip_of_day_query['category']]); ?>
                    </span>
                </p>
                <p class="card-text">
                    <small class="text-muted">
                        By <?php echo htmlspecialchars($tip_of_day_query['username']); ?> |
                        Likes: <?php echo $tip_of_day_query['likes']; ?>
                    </small>
                </p>
                <?php if (isLoggedIn()): ?>
                    <form action="like_tip.php" method="post" class="d-inline">
                        <input type="hidden" name="tip_id" value="<?php echo $tip_of_day_query['id']; ?>">
                        <button type="submit" class="btn btn-primary">Like</button>
                    </form>
                    <form action="bookmark_tip.php" method="post" class="d-inline">
                        <input type="hidden" name="tip_id" value="<?php echo $tip['id']; ?>">
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-bookmark"></i> Save
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Latest Tips Section -->
        <h2 class="mb-4">Latest Tips</h2>
        <div class="row">
            <?php foreach ($latest_tips as $tip): ?>
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
                                    Likes: <?php echo $tip['likes']; ?>
                                </small>
                            </p>
                            <?php if (isLoggedIn()): ?>
                                <form action="like_tip.php" method="post" class="d-inline">
                                    <input type="hidden" name="tip_id" value="<?php echo $tip['id']; ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">Like</button>
                                </form>
                                <form action="bookmark_tip.php" method="post" class="d-inline ms-2">
                            <input type="hidden" name="tip_id" value="<?php echo $tip['id']; ?>">
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-bookmark"></i> Save
                            </button>
                        </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php 
                            echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; 
                            echo isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : ''; 
                        ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</body>
</html>