<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Please login to send messages.");
    exit;
}

$listing_id = isset($_GET['listing_id']) ? (int)$_GET['listing_id'] : 0;
$errors = [];
$success = '';

if ($listing_id) {
    // Fetch listing details
    $stmt = $pdo->prepare("SELECT title, user_id FROM listings WHERE id = ? AND status = 'approved'");
    $stmt->execute([$listing_id]);
    $listing = $stmt->fetch();

    if (!$listing) {
        $errors[] = "Invalid or unapproved listing.";
    } else {
        $receiver_id = $listing['user_id'];

        // Handle message submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = trim($_POST['message']);
            if (empty($message)) {
                $errors[] = "Message cannot be empty.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO messages (listing_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$listing_id, $_SESSION['user_id'], $receiver_id, $message])) {
                    $success = "Message sent successfully!";
                } else {
                    $errors[] = "Failed to send message.";
                }
            }
        }

        // Fetch messages for this listing
        $stmt = $pdo->prepare("SELECT m.message, m.created_at, u.name AS sender_name 
                             FROM messages m 
                             JOIN users u ON m.sender_id = u.id 
                             WHERE m.listing_id = ? 
                             ORDER BY m.created_at ASC");
        $stmt->execute([$listing_id]);
        $messages = $stmt->fetchAll();
    }
}

define('PAGE_TITLE', 'Messages');
require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8 flex items-center justify-center min-h-screen animate-fade-in" style="animation-delay: 0.2s">
    <div class="content-card bg-light-cream p-8 rounded-lg shadow-md w-full max-w-2xl">
        <h2 class="text-3xl font-bold mb-6 text-deep-navy text-center"><i class="fas fa-envelope mr-2"></i> Messages</h2>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-900 text-off-white p-4 rounded mb-4">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-emerald-900 text-off-white p-4 rounded mb-4">
                <p><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($listing_id && $listing): ?>
            <h3 class="text-xl font-semibold text-deep-navy mb-4">Messages for: <?php echo htmlspecialchars($listing['title']); ?></h3>
            <div class="mb-6 bg-neutral p-4 rounded-lg max-h-64 overflow-y-auto">
                <?php if (empty($messages)): ?>
                    <p class="text-muted text-center">No messages yet.</p>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="mb-2 p-2 bg-light-gray rounded">
                            <p class="text-sm font-medium text-deep-navy"><?php echo htmlspecialchars($msg['sender_name']); ?>:</p>
                            <p class="text-sm text-muted"><?php echo htmlspecialchars($msg['message']); ?></p>
                            <p class="text-xs text-muted"><?php echo date('F d, Y H:i', strtotime($msg['created_at'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form method="POST" action="">
                <div class="mb-4">
                    <label for="message" class="block text-sm font-medium text-deep-navy">Your Message</label>
                    <textarea name="message" id="message" rows="4" class="mt-1 p-2 w-full border border-light-gray rounded bg-light-cream text-deep-navy focus:outline-none focus:ring-2 focus:ring-emerald-700" style="color: #18453B !important;" required></textarea>
                </div>
                <button type="submit" class="w-full bg-emerald-700 text-off-white p-2 rounded hover:bg-emerald-800 transition-colors duration-300"><i class="fas fa-paper-plane mr-2"></i> Send</button>
            </form>
        <?php else: ?>
            <p class="text-center text-muted">Select a listing to start messaging.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>