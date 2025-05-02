<?php
define('PAGE_TITLE', 'Home');
require_once 'includes/header.php';

// Fetch categories for filter
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Fetch approved listings (with optional search, category filter, price range, and condition)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$price_min = isset($_GET['price_min']) ? (int)$_GET['price_min'] : 0;
$price_max = isset($_GET['price_max']) ? (int)$_GET['price_max'] : 10000;
$condition = isset($_GET['condition']) ? trim($_GET['condition']) : '';

$sql = "SELECT l.*, c.name AS category_name, u.name AS user_name 
        FROM listings l 
        JOIN categories c ON l.category_id = c.id 
        JOIN users u ON l.user_id = u.id 
        WHERE l.status = 'approved'";
$params = [];

if ($search) {
    $sql .= " AND (l.title LIKE ? OR l.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category_id) {
    $sql .= " AND l.category_id = ?";
    $params[] = $category_id;
}
if ($price_min > 0) {
    $sql .= " AND l.price >= ?";
    $params[] = $price_min;
}
if ($price_max < 10000) {
    $sql .= " AND l.price <= ?";
    $params[] = $price_max;
}
if ($condition) {
    $sql .= " AND l.condition = ?";
    $params[] = $condition;
}
$sql .= " ORDER BY l.created_at DESC LIMIT 6";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();

// Fetch latest lost/found items
$stmt = $pdo->query("SELECT lf.*, c.name AS category_name 
                     FROM lost_found lf 
                     JOIN categories c ON lf.category_id = c.id 
                     WHERE lf.status = 'approved' 
                     ORDER BY lf.created_at DESC 
                     LIMIT 3");
$lost_found_items = $stmt->fetchAll();
?>

<main class="relative">
    <!-- Hero Section -->
    <section class="relative min-h-[80vh] flex items-center justify-center text-center overflow-hidden bg-gray-900">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('assets/images/hero-bg.jpg'); opacity: 0.5;"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-blue-800 opacity-80"></div>
        <div class="container mx-auto px-4 relative z-10">
            <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 tracking-tight animate-pulse">
                Welcome to <span class="gradient-text">CollegeHub</span>
            </h1>
            <p class="text-xl text-gray-200 mb-8 max-w-3xl mx-auto drop-shadow-sm">
                Discover, Share, and Connect—Your Ultimate College Marketplace
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#listings" class="btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-6 py-3 rounded-lg flex items-center transform hover:scale-105 transition-transform">
                    <i class="fas fa-search mr-2"></i> Explore Listings
                </a>
                <a href="lost_found.php" class="btn btn-outline border-white text-white hover:bg-white hover:text-gray-900 px-6 py-3 rounded-lg flex items-center transform hover:scale-105 transition-transform">
                    <i class="fas fa-search mr-2"></i> Lost & Found
                </a>
            </div>
        </div>
        <div class="wave-bottom absolute bottom-0 left-0 w-full overflow-hidden">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="relative block w-full h-16">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="fill-white dark:fill-gray-900"></path>
            </svg>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                    Why Choose <span class="text-blue-600 dark:text-accent">CollegeHub</span>?
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Empowering students with the tools to connect and thrive
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="content-card p-6 text-center bg-gradient-to-br from-blue-50 to-blue-100 dark:from-gray-800 dark:to-gray-700 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-blue-500">
                        <i class="fas fa-book text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Share Resources</h3>
                    <p class="text-gray-600 dark:text-gray-400">Exchange notes, textbooks, and study materials with peers.</p>
                </div>
                <div class="content-card p-6 text-center bg-gradient-to-br from-blue-50 to-blue-100 dark:from-gray-800 dark:to-gray-700 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-secondary">
                        <i class="fas fa-search text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Lost & Found</h3>
                    <p class="text-gray-600 dark:text-gray-400">Report and find lost items across campus quickly.</p>
                </div>
                <div class="content-card p-6 text-center bg-gradient-to-br from-blue-50 to-blue-100 dark:from-gray-800 dark:to-gray-700 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-accent">
                        <i class="fas fa-users text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Community Building</h3>
                    <p class="text-gray-600 dark:text-gray-400">Connect with peers who share your academic goals.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Category Carousel -->
    <section class="py-16 bg-gray-100 dark:bg-gray-800">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-8 text-center flex items-center justify-center">
                <i class="fas fa-tags text-accent mr-2"></i> Browse Categories
            </h2>
            <div class="overflow-x-auto">
                <div class="flex space-x-4">
                    <?php foreach ($categories as $category): ?>
                        <a href="categories.php?category=<?php echo $category['id']; ?>" class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105 flex-shrink-0 w-48 text-center">
                            <i class="fas fa-folder text-2xl text-accent mb-2"></i>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($category['name']); ?></h3>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Listings Section -->
    <section id="listings" class="py-16 relative bg-gray-900">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-blue-800 opacity-90"></div>
        <div class="wave-top absolute top-0 left-0 w-full overflow-hidden rotate-180">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="relative block w-full h-16">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="fill-white dark:fill-gray-900"></path>
            </svg>
        </div>
        <div class="container mx-auto px-4 relative z-10">
            <h2 class="text-4xl font-bold text-white mb-8 text-center drop-shadow-md">
                Latest Listings
            </h2>
            <form action="index.php" method="GET" class="flex flex-col md:flex-row justify-between items-center mb-10 gap-4">
                <div class="w-full md:w-1/3 flex">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search listings..." class="w-full p-3 rounded-l-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-secondary">
                    <button type="submit" class="bg-secondary text-white p-3 rounded-r-lg hover:bg-opacity-90 transition-colors duration-200">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <select name="category" onchange="this.form.submit()" class="w-full md:w-1/4 p-3 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-secondary">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="w-full md:w-1/4 flex gap-2">
                    <input type="number" name="price_min" value="<?php echo htmlspecialchars($price_min); ?>" placeholder="Min Price" class="w-1/2 p-3 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-secondary">
                    <input type="number" name="price_max" value="<?php echo htmlspecialchars($price_max); ?>" placeholder="Max Price" class="w-1/2 p-3 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-secondary">
                </div>
                <select name="condition" onchange="this.form.submit()" class="w-full md:w-1/4 p-3 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-secondary">
                    <option value="">All Conditions</option>
                    <option value="new" <?php echo $condition == 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="used" <?php echo $condition == 'used' ? 'selected' : ''; ?>>Used</option>
                </select>
            </form>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (empty($listings)): ?>
                    <div class="col-span-full text-center py-10">
                        <i class="fas fa-search text-4xl text-secondary mb-4"></i>
                        <p class="text-lg text-gray-200 dark:text-gray-300">
                            No listings found. Try adjusting your search criteria.
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($listings as $listing): ?>
                        <div class="relative bg-white dark:bg-gray-800 p-4 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                            <?php if (strtotime($listing['created_at']) > strtotime('-3 days')): ?>
                                <span class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full z-50">New</span>
                            <?php endif; ?>
                            <?php include 'templates/listing_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="text-center mt-10">
                <a href="./user/list_item.php" class="inline-flex items-center text-secondary hover:text-accent font-medium transition-colors duration-200">
                    View All Listings <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
        <div class="wave-bottom absolute bottom-0 left-0 w-full overflow-hidden">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="relative block w-full h-16">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="fill-white dark:fill-gray-900"></path>
            </svg>
        </div>
    </section>

    <!-- Lost & Found Section -->
    <section class="py-16 bg-white dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-8 text-center flex items-center justify-center">
                <i class="fas fa-search text-accent mr-2"></i> Recent Lost & Found
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (empty($lost_found_items)): ?>
                    <div class="col-span-full text-center py-10">
                        <i class="fas fa-search text-4xl text-secondary mb-4"></i>
                        <p class="text-lg text-gray-600 dark:text-gray-400">
                            No lost & found items found.
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($lost_found_items as $item): ?>
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                            <span class="inline-block px-3 py-1 text-sm font-semibold text-white bg-<?php echo $item['type'] == 'lost' ? 'red' : 'green'; ?>-500 rounded-full mb-2">
                                <?php echo ucfirst($item['type']); ?>
                            </span>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2"><?php echo htmlspecialchars($item['description']); ?></h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Category: <?php echo htmlspecialchars($item['category_name']); ?></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo date('F d, Y', strtotime($item['created_at'])); ?></p>
                            <a href="lost_found_details.php?id=<?php echo $item['id']; ?>" class="mt-2 inline-block text-accent hover:underline">View Details</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="text-center mt-10">
                <a href="lost_found.php" class="inline-flex items-center text-secondary hover:text-accent font-medium transition-colors duration-200">
                    View All Lost & Found <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-16 bg-gray-100 dark:bg-gray-800">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-8 text-center flex items-center justify-center">
                <i class="fas fa-quote-left text-accent mr-2"></i> What Our Users Say
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-lg">
                    <p class="text-gray-600 dark:text-gray-400 mb-4">"CollegeHub made it so easy to find a textbook I needed for my semester. Highly recommend!"</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Anurag, Student</p>
                </div>
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-lg">
                    <p class="text-gray-600 dark:text-gray-400 mb-4">"I found my lost calculator within a day thanks to the Lost & Found section. Amazing platform!"</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Priya, Student</p>
                </div>
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-lg">
                    <p class="text-gray-600 dark:text-gray-400 mb-4">"The community here is so helpful. Sold my old laptop in just a few hours!"</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Rahul, Student</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-white dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-center">
                <div class="content-card p-6">
                    <i class="fas fa-users text-4xl text-accent mb-4"></i>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2 counter" data-count="500">0</h3>
                    <p class="text-gray-600 dark:text-gray-400">Active Users</p>
                </div>
                <div class="content-card p-6">
                    <i class="fas fa-list-alt text-4xl text-secondary mb-4"></i>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2 counter" data-count="1200">0</h3>
                    <p class="text-gray-600 dark:text-gray-400">Total Listings</p>
                </div>
                <div class="content-card p-6">
                    <i class="fas fa-handshake text-4xl text-accent mb-4"></i>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2 counter" data-count="850">0</h3>
                    <p class="text-gray-600 dark:text-gray-400">Successful Exchanges</p>
                </div>
                <div class="content-card p-6">
                    <i class="fas fa-university text-4xl text-secondary mb-4"></i>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2 counter" data-count="25">0</h3>
                    <p class="text-gray-600 dark:text-gray-400">College Campuses</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative py-20">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-accent opacity-90"></div>
        <div class="absolute inset-0 bg-pattern opacity-20"></div>
        <div class="container mx-auto px-4 text-center relative z-10">
            <h2 class="text-4xl font-bold text-white mb-6 drop-shadow-md">
                Ready to Join the Community?
            </h2>
            <p class="text-lg text-gray-200 mb-8 max-w-2xl mx-auto drop-shadow-sm">
                Sign up today and start connecting with your campus—buy, sell, share, and discover!
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="signup.php" class="btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-6 py-3 rounded-lg flex items-center transform hover:scale-105 transition-transform">
                    <i class="fas fa-user-plus mr-2"></i> Sign Up Free
                </a>
                <a href="contact.php" class="btn btn-outline border-white text-white hover:bg-white hover:text-gray-900 px-6 py-3 rounded-lg flex items-center transform hover:scale-105 transition-transform">
                    <i class="fas fa-paper-plane mr-2"></i> Contact Us
                </a>
            </div>
        </div>
    </section>

    <!-- To Top Button -->
    <button id="to-top" class="fixed bottom-6 right-6 bg-secondary text-white p-4 rounded-full hover:bg-opacity-90 transition-all duration-200 shadow-lg transform hover:scale-110" title="Back to Top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // To Top Button
        document.getElementById('to-top').addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Fade-in animation
        const animateElements = document.querySelectorAll('.animate-fade-in');
        animateElements.forEach((el, index) => {
            el.style.opacity = '0';
            setTimeout(() => {
                el.style.transition = 'opacity 0.5s ease-in-out';
                el.style.opacity = '1';
            }, index * 200);
        });

        // Counter animation
        const counters = document.querySelectorAll('.counter');
        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-count');
                const count = +counter.innerText.replace(/,/g, '');
                const increment = target / 300;

                if (count < target) {
                    counter.innerText = Math.ceil(count + increment).toLocaleString();
                    setTimeout(updateCount, 15);
                } else {
                    counter.innerText = target.toLocaleString();
                }
            };
            updateCount();
        });

        // Background pattern
        const styleSheet = document.createElement("style");
        styleSheet.textContent = `
            .bg-pattern {
                background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%231F2937' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            }
            .dark .bg-pattern {
                background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23E5E7EB' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            }
            .gradient-text {
                background: linear-gradient(to right, #3B82F6, #10B981);
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
            }
        `;
        document.head.appendChild(styleSheet);
    </script>
</main>

<?php require_once 'includes/footer.php'; ?>