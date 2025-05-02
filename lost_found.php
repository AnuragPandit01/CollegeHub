<?php
define('PAGE_TITLE', 'Lost & Found');
require_once 'includes/header.php';

// Fetch categories for filter
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Fetch approved lost/found items (with optional search and category filter)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$sql = "SELECT lf.*, c.name AS category_name 
        FROM lost_found lf 
        JOIN categories c ON lf.category_id = c.id 
        WHERE lf.status = 'approved'";
$params = [];

if ($search) {
    $sql .= " AND (lf.title LIKE ? OR lf.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category_id) {
    $sql .= " AND lf.category_id = ?";
    $params[] = $category_id;
}
$sql .= " ORDER BY lf.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lost_found_items = $stmt->fetchAll();
?>

<section class="py-16 bg-primary animate-fade-in relative" style="animation-delay: 0.4s">
    <div class="wave-top absolute top-0 left-0 w-full overflow-hidden rotate-180">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none" class="relative block w-full h-12">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="fill-neutral"></path>
        </svg>
    </div>
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-light mb-8 text-center">Lost & Found</h2>
        <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-4">
            <form action="lost_found.php" method="GET" class="w-full md:w-1/2">
                <div class="flex">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search lost/found items..." class="w-full p-3 border-2 border-accent rounded-l-lg bg-neutral text-primary focus:outline-none focus:ring-2 focus:ring-accent placeholder-muted">
                    <button type="submit" class="bg-accent text-light p-3 rounded-r-lg hover:bg-opacity-90 transition-colors duration-200"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <form action="lost_found.php" method="GET" class="w-full md:w-1/4">
                <select name="category" onchange="this.form.submit()" class="w-full p-3 border-2 border-accent rounded-lg bg-neutral text-primary focus:outline-none focus:ring-2 focus:ring-accent">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (empty($lost_found_items)): ?>
                <div class="col-span-full text-center py-10">
                    <i class="fas fa-search text-4xl text-accent mb-4"></i>
                    <p class="text-lg text-muted">No items found. Try adjusting your search criteria.</p>
                </div>
            <?php else: ?>
                <?php foreach ($lost_found_items as $item): ?>
                    <?php include 'templates/lost_found_card.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="wave-bottom absolute bottom-0 left-0 w-full overflow-hidden">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none" class="relative block w-full h-12">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="fill-neutral"></path>
        </svg>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>