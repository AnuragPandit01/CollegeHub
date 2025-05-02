<?php
// session_start(); // Start session if not already started
require_once 'includes/db_connect.php'; // Database connection

// Process form submission before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $college = trim($_POST['college']);
    $errors = [];

    if (empty($name) || empty($email) || empty($password) || empty($college)) {
        $errors[] = "All fields are required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email already registered.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, college, role) VALUES (?, ?, ?, ?, 'student')");
        if ($stmt->execute([$name, $email, $hashed_password, $college])) {
            header("Location: login.php?success=Registration successful! Please login.");
            exit;
        } else {
            $errors[] = "Registration failed. Try again.";
        }
    }
}

// If no redirect, then proceed with HTML output
define('PAGE_TITLE', 'Register');
require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8 flex items-center justify-center min-h-screen animate-fade-in" style="animation-delay: 0.2s">
    <div class="content-card bg-light-cream p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-3xl font-bold mb-6 text-deep-navy text-center"><i class="fas fa-user-plus mr-2"></i> Register for CollegeHub</h2>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-900 text-off-white p-4 rounded mb-4">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-deep-navy">Name</label>
                <input type="text" name="name" id="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" class="mt-1 p-2 w-full border border-light-gray rounded bg-light-cream text-deep-navy focus:outline-none focus:ring-2 focus:ring-emerald-700" style="color: #18453B !important;">
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-deep-navy">Email</label>
                <input type="email" name="email" id="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" class="mt-1 p-2 w-full border border-light-gray rounded bg-light-cream text-deep-navy focus:outline-none focus:ring-2 focus:ring-emerald-700" style="color: #18453B !important;">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-deep-navy">Password</label>
                <input type="password" name="password" id="password" class="mt-1 p-2 w-full border border-light-gray rounded bg-light-cream text-deep-navy focus:outline-none focus:ring-2 focus:ring-emerald-700" style="color: #18453B !important;">
            </div>
            <div class="mb-4">
                <label for="college" class="block text-sm font-medium text-deep-navy">College</label>
                <input type="text" name="college" id="college" value="<?php echo isset($college) ? htmlspecialchars($college) : ''; ?>" class="mt-1 p-2 w-full border border-light-gray rounded bg-light-cream text-deep-navy focus:outline-none focus:ring-2 focus:ring-emerald-700" style="color: #18453B !important;">
            </div>
            <button type="submit" class="w-full bg-emerald-700 text-off-white p-2 rounded hover:bg-emerald-800 transition-colors duration-200"><i class="fas fa-check mr-2"></i> Register</button>
        </form>
        <p class="mt-4 text-center text-deep-navy">Already have an account? <a href="login.php" class="text-coral hover:underline">Login</a></p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>