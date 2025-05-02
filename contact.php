<?php
define('PAGE_TITLE', 'Contact Us');
require_once 'includes/header.php';

// Handle form submission (simple placeholder for now)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $message && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Placeholder: In a real app, this would send an email or save to a database
        $success = "Thank you, $name! Your message has been received. We will get back to you soon.";
    } else {
        $error = "Please fill all fields with a valid email address.";
    }
}
?>

<main class="container mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-6">Contact Us</h1>

    <div class="max-w-md mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <?php if (isset($error)): ?>
            <p class="text-red-500 mb-4"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif (isset($success)): ?>
            <p class="text-green-500 mb-4"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label for="name" class="block text-gray-700 dark:text-gray-300 mb-2">Name</label>
                <input type="text" name="name" id="name" class="w-full p-2 border-2 border-accent rounded-lg bg-neutral text-primary focus:outline-none focus:ring-2 focus:ring-accent" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700 dark:text-gray-300 mb-2">Email</label>
                <input type="email" name="email" id="email" class="w-full p-2 border-2 border-accent rounded-lg bg-neutral text-primary focus:outline-none focus:ring-2 focus:ring-accent" required>
            </div>
            <div class="mb-4">
                <label for="message" class="block text-gray-700 dark:text-gray-300 mb-2">Message</label>
                <textarea name="message" id="message" class="w-full p-2 border-2 border-accent rounded-lg bg-neutral text-primary focus:outline-none focus:ring-2 focus:ring-accent" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-4 py-2 rounded-lg">Send Message</button>
        </form>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>