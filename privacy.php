<?php
define('PAGE_TITLE', 'Privacy Policy');
require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-16">
    <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-6 text-center">Privacy Policy</h1>
    <p class="text-center text-gray-600 dark:text-gray-400 mb-10 max-w-2xl mx-auto">
        Last Updated: April 30, 2025
    </p>

    <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md space-y-6">
        <!-- Introduction -->
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                <i class="fas fa-info-circle text-accent mr-2"></i> Introduction
            </h2>
            <p class="text-gray-700 dark:text-gray-300">
                At CollegeHub, we value your privacy and are committed to protecting your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your data when you use our platform.
            </p>
        </section>

        <!-- Information We Collect -->
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                <i class="fas fa-database text-accent mr-2"></i> Information We Collect
            </h2>
            <p class="text-gray-700 dark:text-gray-300 mb-2">
                We may collect the following types of information:
            </p>
            <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-1">
                <li>Personal Information: Name, email address, contact details, and other information you provide during registration or while using our services.</li>
                <li>Listing Information: Details about items you list, including titles, descriptions, and images.</li>
                <li>Usage Data: Information about how you interact with our platform, such as IP address, browser type, and pages visited.</li>
            </ul>
        </section>

        <!-- How We Use Your Information -->
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                <i class="fas fa-cogs text-accent mr-2"></i> How We Use Your Information
            </h2>
            <p class="text-gray-700 dark:text-gray-300">
                We use your information to:
            </p>
            <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-1">
                <li>Provide and improve our services, such as facilitating transactions and communication.</li>
                <li>Send notifications about your listings or account activities.</li>
                <li>Ensure the security and integrity of our platform.</li>
                <li>Respond to your inquiries and provide customer support.</li>
            </ul>
        </section>

        <!-- Data Sharing -->
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                <i class="fas fa-share-alt text-accent mr-2"></i> Data Sharing
            </h2>
            <p class="text-gray-700 dark:text-gray-300">
                We do not sell your personal information. We may share your data with:
            </p>
            <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-1">
                <li>Other users: Contact information may be shared with users you interact with (e.g., for transactions).</li>
                <li>Service Providers: Third-party services that help us operate, such as email services for notifications.</li>
                <li>Legal Authorities: If required by law or to protect our rights and safety.</li>
            </ul>
        </section>

        <!-- Security -->
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                <i class="fas fa-shield-alt text-accent mr-2"></i> Security
            </h2>
            <p class="text-gray-700 dark:text-gray-300">
                We implement industry-standard security measures to protect your data. However, no method of transmission over the internet is 100% secure, and we cannot guarantee absolute security.
            </p>
        </section>

        <!-- Your Rights -->
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                <i class="fas fa-user-check text-accent mr-2"></i> Your Rights
            </h2>
            <p class="text-gray-700 dark:text-gray-300">
                You have the right to:
            </p>
            <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 space-y-1">
                <li>Access, update, or delete your personal information.</li>
                <li>Opt out of non-essential communications.</li>
                <li>Contact us for any privacy-related concerns via our <a href="contact.php" class="text-accent hover:underline">Contact Form</a>.</li>
            </ul>
        </section>

        <!-- Contact Us -->
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                <i class="fas fa-envelope text-accent mr-2"></i> Contact Us
            </h2>
            <p class="text-gray-700 dark:text-gray-300">
                If you have any questions about this Privacy Policy, please reach out to us via our <a href="contact.php" class="text-accent hover:underline">Contact Form</a>.
            </p>
        </section>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>