<?php
define('PAGE_TITLE', 'Notifications');
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=Please login to view notifications');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch notifications for the user
try {
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error fetching notifications: " . $e->getMessage();
    exit;
}

// Mark notification as read if requested
if (isset($_GET['mark_as_read']) && is_numeric($_GET['mark_as_read'])) {
    $notification_id = (int)$_GET['mark_as_read'];
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET read_status = 'read' WHERE id = ? AND user_id = ?");
        $stmt->execute([$notification_id, $user_id]);
        header('Location: notifications.php');
        exit;
    } catch (PDOException $e) {
        echo "Error updating notification: " . $e->getMessage();
        exit;
    }
}
?>

<main class="container mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-6">Notifications</h1>

    <?php if (empty($notifications)): ?>
        <div class="text-center py-10">
            <p class="text-lg text-muted">No notifications yet.</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($notifications as $notification): ?>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md <?php echo $notification['read_status'] === 'unread' ? 'border-l-4 border-accent' : ''; ?>">
                    <p class="text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($notification['message']); ?></p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2"><?php echo date('F d, Y H:i', strtotime($notification['created_at'])); ?></p>
                    <?php if ($notification['read_status'] === 'unread'): ?>
                        <a href="?mark_as_read=<?php echo $notification['id']; ?>" class="mt-2 inline-block text-accent hover:underline">Mark as Read</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>