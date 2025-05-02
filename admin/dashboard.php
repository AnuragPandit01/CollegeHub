<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php?error=Access denied. Admin login required.");
    exit;
}

$errors = [];
$success = '';

// Fetch stats
try {
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_listings = $pdo->query("SELECT COUNT(*) FROM listings WHERE status = 'approved'")->fetchColumn();
    $total_lost_found = $pdo->query("SELECT COUNT(*) FROM lost_found WHERE status = 'approved'")->fetchColumn();
    $pending_listings = $pdo->query("SELECT COUNT(*) FROM listings WHERE status = 'pending'")->fetchColumn();
    $pending_lost_found = $pdo->query("SELECT COUNT(*) FROM lost_found WHERE status = 'pending'")->fetchColumn();

    $stmt = $pdo->query("SELECT id, title, user_id FROM listings WHERE status = 'pending' LIMIT 5");
    $pending_listings_data = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT id, title, user_id FROM lost_found WHERE status = 'pending' LIMIT 5");
    $pending_lost_found_data = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Database error: " . $e->getMessage();
}

// Handle item approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $item_id = (int)$_POST['item_id'];
        $table = $_POST['table'];
        $action = $_POST['action'];

        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE $table SET status = 'approved' WHERE id = ?");
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE $table SET status = 'rejected' WHERE id = ?");
        }

        if (isset($stmt) && $stmt->execute([$item_id])) {
            $success = "Item " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully.";
        } else {
            $errors[] = "Failed to " . ($action === 'approve' ? 'approve' : 'reject') . " item.";
        }
    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
    }
}

define('PAGE_TITLE', 'Admin Dashboard');
require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8 flex items-center justify-center min-h-screen animate-fade-in" style="animation-delay: 0.2s">
    <div class="content-card bg-light-cream p-8 rounded-lg w-full max-w-4xl" style="box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15); border: 1px solid #e5e7eb;">
        <h2 class="text-3xl font-bold mb-6 text-deep-navy text-center"><i class="fas fa-tachometer-alt mr-2"></i> Admin Dashboard</h2>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-900 text-off-white p-4 rounded mb-4" style="box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); border: 1px solid #f87171;">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-emerald-900 text-off-white p-4 rounded mb-4" style="box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); border: 1px solid #6ee7b7;">
                <p><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <!-- Admin Navigation -->
        <div class="mb-6 flex justify-center space-x-4">
            <a href="dashboard.php" class="menu-item text-light bg-primary p-2 rounded-lg hover:bg-opacity-90">Dashboard</a>
            <a href="manage_listings.php" class="menu-item text-light bg-primary p-2 rounded-lg hover:bg-opacity-90"><i class="fas fa-list-alt mr-1"></i> Manage Listings</a>
            <a href="manage_lost_found.php" class="menu-item text-light bg-primary p-2 rounded-lg hover:bg-opacity-90"><i class="fas fa-search mr-1"></i> Manage Lost/Found</a>
            <a href="manage_users.php" class="menu-item text-light bg-primary p-2 rounded-lg hover:bg-opacity-90"><i class="fas fa-users-cog mr-1"></i> Manage Users</a>
        </div>

        <!-- Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-neutral p-4 rounded-lg text-center" style="box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb;">
                <h3 class="text-lg font-semibold text-light">Total Users</h3>
                <p class="text-2xl font-bold text-secondary"><?php echo htmlspecialchars($total_users); ?></p>
            </div>
            <div class="bg-neutral p-4 rounded-lg text-center" style="box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb;">
                <h3 class="text-lg font-semibold text-light">Total Listings</h3>
                <p class="text-2xl font-bold text-secondary"><?php echo htmlspecialchars($total_listings); ?></p>
            </div>
            <div class="bg-neutral p-4 rounded-lg text-center" style="box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb;">
                <h3 class="text-lg font-semibold text-light">Total Lost/Found</h3>
                <p class="text-2xl font-bold text-secondary"><?php echo htmlspecialchars($total_lost_found); ?></p>
            </div>
        </div>

        <!-- Pending Items Section -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-deep-navy mb-4">Pending Listings (<?php echo htmlspecialchars($pending_listings); ?>)</h3>
            <?php if ($pending_listings_data): ?>
                <div class="space-y-4 max-h-64 overflow-y-auto">
                    <?php foreach ($pending_listings_data as $item): ?>
                        <div class="bg-light-gray p-4 rounded flex justify-between items-center" style="box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb;">
                            <span class="text-sm text-deep-navy"><?php echo htmlspecialchars($item['title']); ?></span>
                            <div>
                                <form method="POST" style="display:inline-block" class="mr-2">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="table" value="listings">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="bg-emerald-700 text-off-white px-3 py-1 rounded hover:bg-emerald-800 transition-colors" style="box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">Approve</button>
                                </form>
                                <form method="POST" style="display:inline-block">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="table" value="listings">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="bg-red-900 text-off-white px-3 py-1 rounded hover:bg-red-800 transition-colors" style="box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">Reject</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">No pending listings.</p>
            <?php endif; ?>
        </div>

        <div class="mb-8">
            <h3 class="text-xl font-semibold text-deep-navy mb-4">Pending Lost/Found (<?php echo htmlspecialchars($pending_lost_found); ?>)</h3>
            <?php if ($pending_lost_found_data): ?>
                <div class="space-y-4 max-h-64 overflow-y-auto">
                    <?php foreach ($pending_lost_found_data as $item): ?>
                        <div class="bg-light-gray p-4 rounded flex justify-between items-center" style="box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb;">
                            <span class="text-sm text-deep-navy"><?php echo htmlspecialchars($item['title']); ?></span>
                            <div>
                                <form method="POST" style="display:inline-block" class="mr-2">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="table" value="lost_found">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="bg-emerald-700 text-off-white px-3 py-1 rounded hover:bg-emerald-800 transition-colors" style="box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">Approve</button>
                                </form>
                                <form method="POST" style="display:inline-block">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="table" value="lost_found">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="bg-red-900 text-off-white px-3 py-1 rounded hover:bg-red-800 transition-colors" style="box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">Reject</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">No pending lost/found items.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>