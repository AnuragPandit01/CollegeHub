<?php
define('PAGE_TITLE', 'Report Lost or Found Item');
require_once '../includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Please login to report a lost or found item.");
    exit;
}

// Fetch categories for dropdown
try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error fetching categories: " . $e->getMessage();
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = $_POST['type'] ?? '';
    $category_id = (int)($_POST['category'] ?? 0);
    $date = $_POST['date'] ?? '';
    $contact_info = trim($_POST['contact_info'] ?? '');
    $image = $_FILES['image'] ?? null;

    // Validation
    if (empty($title)) {
        $errors[] = "Title is required.";
    }
    if (empty($description)) {
        $errors[] = "Description is required.";
    }
    if (!in_array($type, ['lost', 'found'])) {
        $errors[] = "Please select if the item is lost or found.";
    }
    if ($category_id <= 0) {
        $errors[] = "Please select a category.";
    }
    if (empty($date)) {
        $errors[] = "Date is required.";
    } elseif (!strtotime($date)) {
        $errors[] = "Invalid date format.";
    }
    if (empty($contact_info)) {
        $errors[] = "Contact information is required.";
    }

    // Image validation
    $image_path = null;
    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($image['type'], $allowed_types)) {
            $errors[] = "Only JPEG, PNG, and GIF images are allowed.";
        }
        if ($image['size'] > $max_size) {
            $errors[] = "Image size must be less than 5MB.";
        }

        if (empty($errors)) {
            $upload_dir = '../assets/images/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $image_name = uniqid() . '-' . basename($image['name']);
            $image_path = $upload_dir . $image_name;

            if (!move_uploaded_file($image['tmp_name'], $image_path)) {
                $errors[] = "Failed to upload image.";
            } else {
                $image_path = 'assets/images/uploads/' . $image_name;
            }
        }
    }

    // If no errors, save to database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO lost_found (user_id, title, type, description, category_id, date, contact_info, image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
            $result = $stmt->execute([$_SESSION['user_id'], $title, $type, $description, $category_id, $date, $contact_info, $image_path]);

            if ($result) {
                $success = "Item reported successfully! It will be visible after admin approval.";
                // Reset form fields
                $title = $description = $type = '';
                $category_id = 0;
                $date = $contact_info = '';
            } else {
                $errors[] = "Failed to report item. Please try again.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<main class="container mx-auto px-4 py-16 flex items-center justify-center min-h-screen">
    <div class="content-card bg-white dark:bg-gray-800 p-8 w-full max-w-2xl rounded-lg shadow-lg">
        <h2 class="text-3xl font-bold mb-6 text-gray-900 dark:text-gray-100 text-center">Report a Lost or Found Item</h2>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-100 p-4 rounded mb-6">
                <?php foreach ($errors as $error): ?>
                    <p class="text-sm"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100 p-4 rounded mb-6">
                <p class="text-sm"><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title ?? ''); ?>" class="mt-1 p-2 w-full border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea name="description" id="description" rows="4" class="mt-1 p-2 w-full border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-secondary"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
            </div>
            <div class="mb-4">
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Item Type</label>
                <select name="type" id="type" class="mt-1 p-2 w-full border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-secondary">
                    <option value="" disabled <?php echo !isset($type) || empty($type) ? 'selected' : ''; ?>>Select type</option>
                    <option value="lost" <?php echo $type === 'lost' ? 'selected' : ''; ?>>Lost</option>
                    <option value="found" <?php echo $type === 'found' ? 'selected' : ''; ?>>Found</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                <select name="category" id="category" class="mt-1 p-2 w-full border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-secondary">
                    <option value="0" disabled <?php echo $category_id == 0 ? 'selected' : ''; ?>>Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Loss/Find</label>
                <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($date ?? ''); ?>" class="mt-1 p-2 w-full border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>
            <div class="mb-4">
                <label for="contact_info" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Information</label>
                <input type="text" name="contact_info" id="contact_info" value="<?php echo htmlspecialchars($contact_info ?? ''); ?>" class="mt-1 p-2 w-full border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-secondary" placeholder="e.g., Phone number or email">
            </div>
            <div class="mb-6">
                <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Image (optional)</label>
                <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/gif" class="mt-1 p-2 w-full border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-secondary">
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Max 5MB, JPEG/PNG/GIF only</p>
            </div>
            <button type="submit" class="w-full bg-secondary text-white p-2 rounded-lg hover:bg-opacity-90 transition-colors">Report Item</button>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>