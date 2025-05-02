<?php
session_start();

// Redirect if not logged in (must be before any output)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Please login to list an item.");
    exit;
}

define('PAGE_TITLE', 'List an Item');
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

// Initialize variables to avoid undefined variable errors
$title = '';
$description = '';
$category_id = 0;
$price = 0.00;
$errors = [];
$success = '';

// Fetch categories for dropdown
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $image = $_FILES['image'] ?? null;

    // Validation
    if (empty($title)) {
        $errors[] = "Title is required.";
    }
    if (empty($description)) {
        $errors[] = "Description is required.";
    }
    if ($category_id <= 0) {
        $errors[] = "Please select a category.";
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
            }
        }
    }

    // If no errors, save to database
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO listings (user_id, category_id, title, description, price, image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $result = $stmt->execute([$_SESSION['user_id'], $category_id, $title, $description, $price, $image_path ? 'assets/images/uploads/' . $image_name : null]);

        if ($result) {
            $success = "Item listed successfully! It will be visible after admin approval.";
            // Reset form fields
            $title = '';
            $description = '';
            $category_id = 0;
            $price = 0.00;
        } else {
            $errors[] = "Failed to list item. Please try again.";
        }
    }
}
?>

<main class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full bg-white dark:bg-gray-800 shadow-lg rounded-xl p-8 space-y-6">
        <h2 class="text-3xl font-bold text-primary dark:text-gray-100 text-center">
            <i class="fas fa-plus-circle mr-2 text-secondary dark:text-gray-300"></i> 
            List an Item on <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary to-secondary dark:from-gray-100 dark:to-gray-300">CollegeHub</span>
        </h2>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 dark:bg-red-900 border-l-4 border-red-500 dark:border-red-600 text-red-700 dark:text-red-100 p-4 rounded-lg">
                <?php foreach ($errors as $error): ?>
                    <p class="text-sm"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Success Message -->
        <?php if ($success): ?>
            <div class="bg-green-100 dark:bg-green-900 border-l-4 border-green-500 dark:border-green-600 text-green-700 dark:text-green-100 p-4 rounded-lg">
                <p class="text-sm"><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                <input 
                    type="text" 
                    name="title" 
                    id="title" 
                    value="<?php echo htmlspecialchars($title); ?>" 
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-200 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-secondary dark:focus:ring-gray-300 focus:border-transparent transition duration-200" 
                    required
                >
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea 
                    name="description" 
                    id="description" 
                    rows="4" 
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-200 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-secondary dark:focus:ring-gray-300 focus:border-transparent resize-none transition duration-200" 
                    required
                ><?php echo htmlspecialchars($description); ?></textarea>
            </div>

            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                <select 
                    name="category" 
                    id="category" 
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-200 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-secondary dark:focus:ring-gray-300 focus:border-transparent transition duration-200" 
                    required
                >
                    <option value="0" disabled <?php echo $category_id == 0 ? 'selected' : ''; ?>>Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price (₹, leave 0 for free)</label>
                <input 
                    type="number" 
                    name="price" 
                    id="price" 
                    value="<?php echo htmlspecialchars($price); ?>" 
                    min="0" 
                    step="0.01" 
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-200 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-secondary dark:focus:ring-gray-300 focus:border-transparent transition duration-200" 
                    required
                >
            </div>

            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Image (optional)</label>
                <input 
                    type="file" 
                    name="image" 
                    id="image" 
                    accept="image/jpeg,image/png,image/gif" 
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-200 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-secondary dark:focus:ring-gray-300 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-secondary dark:file:bg-gray-600 file:text-white dark:file:text-gray-200 hover:file:bg-opacity-90 dark:hover:file:bg-opacity-80 transition duration-200"
                >
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Max 5MB, JPEG/PNG/GIF only</p>
            </div>

            <button 
                type="submit" 
                class="w-full bg-secondary dark:bg-gray-600 text-white dark:text-gray-200 px-6 py-3 rounded-lg font-semibold hover:bg-opacity-90 dark:hover:bg-opacity-80 focus:outline-none focus:ring-2 focus:ring-secondary dark:focus:ring-gray-300 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-200 flex items-center justify-center"
            >
                <i class="fas fa-upload mr-2"></i> List Item
            </button>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>