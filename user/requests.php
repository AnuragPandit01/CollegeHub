<?php
define('PAGE_TITLE', 'Item Requests');
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=Please login to submit or view requests');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle new request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title && $description) {
        try {
            $stmt = $pdo->prepare("INSERT INTO requests (user_id, title, description, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$user_id, $title, $description]);
            header('Location: requests.php?success=Request submitted successfully');
            exit;
        } catch (PDOException $e) {
            $error = "Error submitting request: " . $e->getMessage();
        }
    } else {
        $error = "Title and description are required.";
    }
}

// Fetch user's requests
try {
    $stmt = $pdo->prepare("SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $requests = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error fetching requests: " . $e->getMessage();
    exit;
}
?>

<main class="container mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-6">Item Requests</h1>

    <!-- Request Form -->
    <div class="max-w-md mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-8">
        <?php if (isset($error)): ?>
            <p class="text-red-500 mb-4"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif (isset($_GET['success'])): ?>
            <p class="text-green-500 mb-4"><?php echo htmlspecialchars($_GET['success']); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-4">
                <label for="title" class="block text-gray-700 dark:text-gray-300 mb-2">Request Title</label>
                <input type="text" name="title" id="title" class="w-full p-2 border-2 border-accent rounded-lg bg-neutral text-primary focus:outline-none focus:ring-2 focus:ring-accent" required>
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700 dark:text-gray-300 mb-2">Description</label>
                <textarea name="description" id="description" class="w-full p-2 border-2 border-accent rounded-lg bg-neutral text-primary focus:outline-none focus:ring-2 focus:ring-accent" rows="4" required></textarea>
            </div>
            <button type="submit" name="submit_request" class="btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-4 py-2 rounded-lg">Submit Request</button>
        </form>
    </div>

    <!-- Requests List -->
    <?php if (empty($requests)): ?>
        <div class="text-center py-10">
            <p class="text-lg text-muted">No requests yet. Submit one above!</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($requests as $request): ?>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($request['title']); ?></h3>
                    <p class="text-gray-700 dark:text-gray-300 mt-2"><?php echo nl2br(htmlspecialchars($request['description'])); ?></p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2"><?php echo date('F d, Y', strtotime($request['created_at'])); ?></p>
                    <form method="POST" action="" class="mt-4" onsubmit="return confirm('Are you sure you want to delete this request?');">
                        <input type="hidden" name="action" value="delete_request">
                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                        <button type="submit" class="btn btn-outline border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-4 py-2 rounded-lg">Delete Request</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>