<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php?error=Access denied. Admin login required.");
    exit;
}

$errors = [];
$success = '';

// Handle user updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];
        $action = $_POST['action'];

        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND id != ?");
            if ($stmt->execute([$user_id, $_SESSION['user_id']])) {
                $success = "User deleted successfully.";
            } else {
                $errors[] = "Failed to delete user.";
            }
        } elseif ($action === 'update_role' && isset($_POST['role'])) {
            $role = $_POST['role'];
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ? AND id != ?");
            if ($stmt->execute([$role, $user_id, $_SESSION['user_id']])) {
                $success = "Role updated successfully.";
            } else {
                $errors[] = "Failed to update role.";
            }
        }
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT id, name, email, role FROM users");
$users = $stmt->fetchAll();

define('PAGE_TITLE', 'Manage Users');
require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8 flex items-center justify-center min-h-screen animate-fade-in" style="animation-delay: 0.2s">
    <div class="content-card bg-light-cream p-8 rounded-lg shadow-md w-full max-w-4xl">
        <h2 class="text-3xl font-bold mb-6 text-deep-navy text-center"><i class="fas fa-users-cog mr-2"></i> Manage Users</h2>

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

        <!-- Users Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-deep-navy">
                <thead class="bg-neutral text-white">
                    <tr>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Role</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="bg-light-gray border-b hover:bg-neutral transition-colors">
                            <td class="px-4 py-2"><?php echo htmlspecialchars($user['name']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="px-4 py-2">
                                <form method="POST" style="display:inline-block">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="update_role">
                                    <select name="role" onchange="this.form.submit()" class="bg-light-cream border border-light-gray rounded p-1 text-deep-navy" style="color: #18453B !important;">
                                        <option value="student" <?php echo $user['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-2">
                                <form method="POST" style="display:inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="bg-red-900 text-off-white px-3 py-1 rounded hover:bg-red-800 transition-colors">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($users)): ?>
            <p class="text-center text-muted mt-4">No users found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>