<?php
define('PAGE_TITLE', 'Browse Categories');
require_once 'includes/header.php';

// Fetch all categories
try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error fetching categories: " . $e->getMessage();
    exit;
}

// Fetch listings for the selected category
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$listings = [];

if ($category_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT l.*, c.name AS category_name, u.name AS user_name 
            FROM listings l 
            JOIN categories c ON l.category_id = c.id 
            JOIN users u ON l.user_id = u.id 
            WHERE l.category_id = ? AND l.status = 'approved' 
            ORDER BY l.created_at DESC
        ");
        $stmt->execute([$category_id]);
        $listings = $stmt->fetchAll();
    } catch (PDOException $e) {
        echo "Error fetching listings: " . $e->getMessage();
        exit;
    }
}
?>

<main class="container mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-6">Browse Listings by Category</h1>

    <!-- Category Filter -->
    <form action="categories.php" method="GET" class="mb-8">
        <select name="category" onchange="this.form.submit()" class="w-full md:w-1/4 p-3 border-2 border-accent rounded-lg bg-neutral text-primary focus:outline-none focus:ring-2 focus:ring-accent">
            <option value="0">Select a Category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Listings Display -->
    <?php if ($category_id == 0): ?>
        <div class="text-center py-10">
            <p class="text-lg text-muted">Please select a category to view listings.</p>
        </div>
    <?php elseif (empty($listings)): ?>
        <div class="text-center py-10">
            <p class="text-lg text-muted">No listings found in this category.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($listings as $listing): ?>
                <?php include 'templates/listing_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>