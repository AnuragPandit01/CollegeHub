<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php?error=Access denied. Admin login required.");
    exit;
}

$errors = [];
$success = '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle listing updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['listing_id'])) {
        $listing_id = (int)$_POST['listing_id'];
        $action = $_POST['action'];

        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM listings WHERE id = ?");
            if ($stmt->execute([$listing_id])) {
                $success = "Listing deleted successfully.";
            } else {
                $errors[] = "Failed to delete listing.";
            }
        } elseif ($action === 'update_status' && isset($_POST['status'])) {
            $status = $_POST['status'];
            $stmt = $pdo->prepare("UPDATE listings SET status = ? WHERE id = ?");
            if ($stmt->execute([$status, $listing_id])) {
                $success = "Status updated successfully.";
            } else {
                $errors[] = "Failed to update status.";
            }
        }
    }
}

// Fetch listings with search
$sql = "SELECT id, title, user_id, status, created_at FROM listings WHERE 1=1";
if ($search) {
    $sql .= " AND (title LIKE :search OR user_id LIKE :search)";
}
$stmt = $pdo->prepare($sql);
if ($search) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->execute();
$listings = $stmt->fetchAll();

define('PAGE_TITLE', 'Manage Listings');
require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8 flex items-center justify-center min-h-screen animate-fade-in" style="animation-delay: 0.2s">
    <div class="content-card bg-light-cream p-8 rounded-lg w-full max-w-4xl" style="box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15); border: 1px solid #e5e7eb;">
        <h2 class="text-3xl font-bold mb-6 text-deep-navy text-center"><i class="fas fa-list-alt mr-2"></i> Manage Listings</h2>

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

        <!-- Search Bar -->
        <div class="mb-6">
            <form method="GET" action="" class="flex">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by title or user..." class="p-2 w-full border border-light-gray rounded-l bg-light-cream text-deep-navy focus:outline-none focus:ring-2 focus:ring-emerald-700" style="box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);">
                <button type="submit" class="bg-emerald-700 text-off-white p-2 rounded-r hover:bg-emerald-800 transition-colors" style="box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <!-- Listings Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-deep-navy border border-light-gray rounded-lg" style="box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);">
                <thead class="bg-primary text-light">
                    <tr>
                        <th class="px-4 py-2 border-b border-light-gray">Title</th>
                        <th class="px-4 py-2 border-b border-light-gray">User ID</th>
                        <th class="px-4 py-2 border-b border-light-gray">Status</th>
                        <th class="px-4 py-2 border-b border-light-gray">Date</th>
                        <th class="px-4 py-2 border-b border-light-gray">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listings as $listing): ?>
                        <tr class="bg-light-cream border-b border-light-gray hover:bg-neutral transition-colors" style="box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);">
                            <td class="px-4 py-2"><?php echo htmlspecialchars($listing['title']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($listing['user_id']); ?></td>
                            <td class="px-4 py-2">
                                <form method="POST" style="display:inline-block">
                                    <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                    <input type="hidden" name="action" value="update_status">
                                    <select name="status" onchange="this.form.submit()" class="bg-green-600 border border-light-gray rounded p-1 text-deep-navy focus:outline-none" style="box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);">
                                        <option value="pending" <?php echo $listing['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo $listing['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="rejected" <?php echo $listing['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($listing['created_at']))); ?></td>
                            <td class="px-4 py-2">
                                <form method="POST" style="display:inline-block" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                    <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="bg-red-900 text-off-white px-3 py-1 rounded hover:bg-red-800 transition-colors" style="box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($listings)): ?>
            <p class="text-center text-muted mt-4">No listings found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>