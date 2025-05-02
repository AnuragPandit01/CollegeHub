<?php
ob_start(); // Start output buffering to avoid header errors
define('PAGE_TITLE', 'Login');
session_start();

require_once 'includes/db_connect.php';

$errors = [];
$success = '';
$email = '';
$password = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        if (empty($email) || empty($password)) {
            $errors[] = "Email and password are required.";
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $success = "You have been logged in successfully!";
                header("Location: " . ($_SESSION['user_role'] === 'admin' ? 'admin/dashboard.php' : 'index.php') . "?success=" . urlencode($success));
                exit;
            } else {
                $errors[] = "Invalid email or password.";
            }
        }
    }
} catch (PDOException $e) {
    $errors[] = "Database error: " . $e->getMessage();
}

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8 flex items-center justify-center min-h-screen animate-fade-in" style="animation-delay: 0.2s">
    <div class="content-card bg-light-cream p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-3xl font-bold mb-6 text-deep-navy text-center"><i class="fas fa-sign-in-alt mr-2"></i> Login to CollegeHub</h2>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-900 text-off-white p-4 rounded mb-4">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="bg-emerald-900 text-off-white p-4 rounded mb-4">
                <p><?php echo htmlspecialchars($_GET['success']); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-deep-navy">Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" class="mt-1 p-2 w-full border border-light-gray rounded bg-light-cream text-deep-navy focus:outline-none focus:ring-2 focus:ring-emerald-700" style="color: #18453B !important;">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-deep-navy">Password</label>
                <input type="password" name="password" id="password" class="mt-1 p-2 w-full border border-light-gray rounded bg-light-cream text-deep-navy focus:outline-none focus:ring-2 focus:ring-emerald-700" style="color: #18453B !important;">
            </div>
            <button type="submit" class="w-full bg-emerald-700 text-off-white p-2 rounded hover:bg-emerald-800 transition-colors duration-200"><i class="fas fa-lock mr-2"></i> Login</button>
        </form>
        <p class="mt-4 text-center text-deep-navy">Don't have an account? <a href="register.php" class="text-secondary hover:underline">Register</a></p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
<?php ob_end_flush(); // End buffering and flush output ?>