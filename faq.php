<?php
define('PAGE_TITLE', 'FAQ');
require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-16">
    <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-6 text-center">Frequently Asked Questions</h1>
    <p class="text-center text-gray-600 dark:text-gray-400 mb-10 max-w-2xl mx-auto">
        Find answers to common questions about using CollegeHub.
    </p>

    <div class="max-w-3xl mx-auto space-y-4">
        <!-- FAQ 1 -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <button class="w-full text-left p-4 flex justify-between items-center text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none" type="button">
                <span class="flex items-center"><i class="fas fa-question-circle text-accent mr-2"></i> How do I list an item for sale?</span>
                <i class="fas fa-chevron-down text-gray-500 dark:text-gray-400"></i>
            </button>
            <div class="hidden">
                <p class="p-4 text-gray-700 dark:text-gray-300">
                    To list an item, go to your profile page, click 'Add Listing', fill in the details (title, description, price, etc.), and submit. Once approved by an admin, it will be visible to others.
                </p>
            </div>
        </div>

        <!-- FAQ 2 -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <button class="w-full text-left p-4 flex justify-between items-center text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none" type="button">
                <span class="flex items-center"><i class="fas fa-search text-accent mr-2"></i> How can I find a lost item?</span>
                <i class="fas fa-chevron-down text-gray-500 dark:text-gray-400"></i>
            </button>
            <div class="hidden">
                <p class="p-4 text-gray-700 dark:text-gray-300">
                    Visit the 'Lost & Found' page, use the search or category filter to check reported items, and contact the poster if you find a match.
                </p>
            </div>
        </div>

        <!-- FAQ 3 -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <button class="w-full text-left p-4 flex justify-between items-center text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none" type="button">
                <span class="flex items-center"><i class="fas fa-money-bill-wave text-accent mr-2"></i> How are payments handled?</span>
                <i class="fas fa-chevron-down text-gray-500 dark:text-gray-400"></i>
            </button>
            <div class="hidden">
                <p class="p-4 text-gray-700 dark:text-gray-300">
                    CollegeHub does not handle payments directly. Transactions are conducted offline between users. Ensure safety by meeting in public areas.
                </p>
            </div>
        </div>

        <!-- FAQ 4 -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <button class="w-full text-left p-4 flex justify-between items-center text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none" type="button">
                <span class="flex items-center"><i class="fas fa-user-shield text-accent mr-2"></i> How do I report a problem?</span>
                <i class="fas fa-chevron-down text-gray-500 dark:text-gray-400"></i>
            </button>
            <div class="hidden">
                <p class="p-4 text-gray-700 dark:text-gray-300">
                    Use the <a href="contact.php" class="text-accent hover:underline">Contact Form</a> to report any issues or concerns. Our team will respond promptly.
                </p>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.border-b button').forEach(button => {
            button.addEventListener('click', () => {
                const content = button.nextElementSibling;
                if (content.style.display === 'block') {
                    content.style.display = 'none';
                    button.querySelector('i').classList.remove('fa-chevron-up');
                    button.querySelector('i').classList.add('fa-chevron-down');
                } else {
                    content.style.display = 'block';
                    button.querySelector('i').classList.remove('fa-chevron-down');
                    button.querySelector('i').classList.add('fa-chevron-up');
                }
            });
        });
    </script>
</main>

<?php require_once 'includes/footer.php'; ?>