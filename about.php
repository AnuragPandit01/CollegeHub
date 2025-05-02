<?php
define('PAGE_TITLE', 'About Us');
require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-16">
    <!-- Hero Section -->
    <section class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-4">About CollegeHub</h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
            Your go-to platform for college students to connect, share, and exchange resources.
        </p>
    </section>

    <!-- Mission Section -->
    <section class="mb-12">
        <div class="flex flex-col md:flex-row items-center gap-8">
            <div class="md:w-1/2">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <i class="fas fa-bullseye text-accent mr-2"></i> Our Mission
                </h2>
                <p class="text-gray-700 dark:text-gray-300">
                    At CollegeHub, we aim to create a vibrant community where college students can easily buy, sell, and exchange notes, books, electronics, and more. We’re here to simplify your college life by connecting you with resources and peers in a secure and user-friendly environment.
                </p>
            </div>
            <div class="md:w-1/2">
                <img src="assets/images/community.jpg" alt="Community" class="w-full h-64 object-cover rounded-lg shadow-md">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6 text-center flex items-center justify-center">
            <i class="fas fa-star text-accent mr-2"></i> Key Features
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                <i class="fas fa-book text-3xl text-accent mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Buy & Sell Items</h3>
                <p class="text-gray-700 dark:text-gray-300">Trade textbooks, electronics, and more with ease.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                <i class="fas fa-search text-3xl text-accent mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Lost & Found</h3>
                <p class="text-gray-700 dark:text-gray-300">Report and find lost or found items on campus.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                <i class="fas fa-tags text-3xl text-accent mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Category Browsing</h3>
                <p class="text-gray-700 dark:text-gray-300">Browse listings by category for quick access.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                <i class="fas fa-envelope text-3xl text-accent mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Secure Messaging</h3>
                <p class="text-gray-700 dark:text-gray-300">Connect with other users safely.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                <i class="fas fa-shield-alt text-3xl text-accent mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Admin Tools</h3>
                <p class="text-gray-700 dark:text-gray-300">Manage listings and users efficiently.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                <i class="fas fa-heart text-3xl text-accent mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Community First</h3>
                <p class="text-gray-700 dark:text-gray-300">Built by students, for students.</p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="text-center mb-12">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center justify-center">
            <i class="fas fa-envelope text-accent mr-2"></i> Get in Touch
        </h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            Have questions or suggestions? We’d love to hear from you! Reach out via our 
            <a href="contact.php" class="text-accent hover:underline">Contact Form</a>.
        </p>
        <p class="text-gray-700 dark:text-gray-300">
            CollegeHub is developed with ❤️ by a team of passionate students. Stay tuned for more updates!
        </p>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>