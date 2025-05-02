<?php
define('PAGE_TITLE', 'Edit Listing');
require_once '../includes/header.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Ensure listing ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../index.php');
    exit;
}

$listing_id = (int)$_GET['id'];

// Fetch listing details
try {
    $stmt = $pdo->prepare("
        SELECT l.* 
        FROM listings l 
        WHERE l.id = ? AND l.user_id = ? AND l.status = 'approved'
    ");
    $stmt->execute([$listing_id, $_SESSION['user_id']]);
    $listing = $stmt->fetch();

    if (!$listing) {
        header('Location: ../index.php?message=Listing+not+found+or+you+are+not+authorized');
        exit;
    }
} catch (PDOException $e) {
    echo "Error fetching listing: " . $e->getMessage();
    exit;
}

// Fetch categories for dropdown
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = (int)$_POST['category_id'];

    // Basic validation
    $errors = [];
    if (empty($title)) {
        $errors[] = "Title is required.";
    }
    if (empty($description)) {
        $errors[] = "Description is required.";
    }
    if ($category_id <= 0) {
        $errors[] = "Please select a valid category.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE listings 
                SET title = ?, description = ?, category_id = ?, updated_at = NOW() 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$title, $description, $category_id, $listing_id, $_SESSION['user_id']]);
            header('Location: ../listing_details.php?id=' . $listing_id . '&message=Listing+updated+successfully');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Error updating listing: " . $e->getMessage();
        }
    }
}
?>

<main class="container mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold mb-6">Edit Listing</h1>

    <!-- Display Errors -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger mb-6">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Edit Form -->
    <form method="POST" class="max-w-lg">
        <!-- Title -->
        <div class="mb-4">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($listing['title']); ?>" class="w-full">
        </div>

        <!-- Category -->
        <div class="mb-4">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" class="w-full">
                <option value="0">Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo $listing['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5" class="w-full"><?php echo htmlspecialchars($listing['description']); ?></textarea>
        </div>

        <!-- Submit Button -->
        <div class="flex space-x-4">
            <button type="submit" class="btn btn-primary">Update Listing</button>
            <a href="../listing_details.php?id=<?php echo $listing['id']; ?>" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</main>

<?php require_once '../includes/footer.php'; ?>