<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/includes/db_connect.php'; // Root se relative path

// Check user preference or default to light mode
$mode = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo $mode === 'dark' ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CollegeHub - <?php echo defined('PAGE_TITLE') ? PAGE_TITLE : 'Home'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('menu-btn');
            const menu = document.getElementById('menu');
            const modeToggle = document.getElementById('mode-toggle');
            const html = document.documentElement;
            const hamburger = document.querySelector('.hamburger');

            // Initialize mode from localStorage or cookie
            const savedMode = localStorage.getItem('theme') || '<?php echo $mode; ?>';
            if (savedMode === 'dark') {
                html.classList.add('dark');
                modeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            }

            // Toggle menu (with fallback if btn not found)
            if (btn && menu && hamburger) {
                btn.addEventListener('click', () => {
                    menu.classList.toggle('hidden');
                    hamburger.classList.toggle('open');
                });
            }

            // Toggle mode (with fallback if modeToggle not found)
            if (modeToggle) {
                modeToggle.addEventListener('click', () => {
                    html.classList.toggle('dark');
                    const isDark = html.classList.contains('dark');
                    modeToggle.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                    document.cookie = `theme=${isDark ? 'dark' : 'light'}; path=/`;
                });
            }
        });
    </script>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            @apply bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300;
            line-height: 1.6;
        }

        /* Animation and Hover Effects */
        .card {
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        .dark .card:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        /* Fixed hamburger menu */
        #menu-btn {
            cursor: pointer;
            width: 24px;
            height: 24px;
            position: relative;
            z-index: 20;
        }
        .hamburger {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 18px;
            width: 24px;
            position: relative;
        }
        .hamburger span {
            display: block;
            height: 2px;
            width: 100%;
            background-color: #FFFFFF;
            transition: all 0.3s ease;
            position: absolute;
        }
        .hamburger span:nth-child(1) {
            top: 0;
        }
        .hamburger span:nth-child(2) {
            top: 8px;
        }
        .hamburger span:nth-child(3) {
            top: 16px;
        }
        .hamburger.open span:nth-child(1) {
            top: 8px;
            transform: rotate(45deg);
        }
        .hamburger.open span:nth-child(2) {
            opacity: 0;
        }
        .hamburger.open span:nth-child(3) {
            top: 8px;
            transform: rotate(-45deg);
        }

        /* Responsive Mobile Menu */
        @media (max-width: 768px) {
            #menu {
                position: absolute;
                top: 60px;
                right: 0;
                left: 0;
                background-color: rgba(31, 41, 55, 0.95);
                padding: 1rem;
                border-radius: 0 0 12px 12px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                z-index: 10;
                backdrop-filter: blur(10px);
            }
            #menu a {
                padding: 0.75rem 1rem;
                display: block;
                margin: 0.5rem 0;
                border-radius: 8px;
                font-weight: 500;
            }
            #menu a:hover {
                background-color: rgba(255, 255, 255, 0.1);
            }
            .dark #menu {
                background-color: rgba(17, 24, 39, 0.95);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            }
        }

        /* Navigation enhancements */
        .menu-item {
            position: relative;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            @apply text-gray-200;
        }
        .menu-item:hover {
            @apply bg-gray-200/20 text-gray-100;
        }
        .menu-item:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background-color: #FF8C42;
            transition: all 0.3s ease;
        }
        .menu-item:hover:after {
            width: 80%;
            left: 10%;
        }

        /* Custom Colors - Modern Palette */
        .bg-primary { background-color: #1F2937; } /* Slate Gray */
        .bg-secondary { background-color: #FF8C42; } /* Warm Orange */
        .bg-accent { background-color: #36B37E; } /* Vibrant Green */
        .bg-neutral { background-color: #F9FAFB; } /* Soft White */
        .bg-dark { background-color: #111827; } /* Deep Gray */
        .bg-footer { background-color: #111827; } /* Deep Gray Footer */

        .text-primary { color: #1F2937; }
        .text-secondary { color: #FF8C42; }
        .text-accent { color: #36B37E; }
        .text-light { color: #FFFFFF; }
        .text-muted { color: #6B7280; }

        /* Dark Mode Variants */
        .dark .bg-neutral {
            background-color: #111827; /* Deep Gray for dark mode */
        }
        .dark .bg-primary {
            background-color: #111827; /* Deep Gray */
        }
        .dark .bg-footer {
            background-color: #0F172A; /* Slightly darker gray for footer */
        }
        .dark .text-primary {
            color: #E5E7EB; /* Light gray for dark mode */
        }
        .dark .text-muted {
            color: #9CA3AF; /* Lighter muted text */
        }

        /* Button Styles - Modernized */
        .btn {
            @apply px-4 py-2 rounded-lg font-medium transition-all duration-300;
        }
        .btn-primary {
            @apply bg-secondary text-white hover:bg-opacity-90 transform hover:-translate-y-1 shadow-md;
        }
        .btn-outline {
            @apply border-2 border-secondary text-secondary hover:bg-secondary hover:text-white;
        }

        /* Form Controls - Modernized */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="search"],
        input[type="date"],
        input[type="time"],
        textarea,
        select {
            @apply px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all duration-200 shadow-sm;
        }
        
        .dark input[type="text"],
        .dark input[type="email"],
        .dark input[type="password"],
        .dark input[type="search"],
        .dark input[type="date"],
        .dark input[type="time"],
        .dark textarea,
        .dark select {
            @apply bg-gray-800 border-gray-700 text-gray-200 focus:ring-secondary focus:border-secondary;
        }

        /* Label styles */
        label {
            @apply block text-sm font-medium mb-2 text-gray-700;
        }
        .dark label {
            @apply text-gray-300;
        }

        /* Logo Animation */
        .logo-icon {
            display: inline-block;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
            100% { transform: translateY(0px); }
        }

        /* Improved card design - Neumorphic */
        .content-card {
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            @apply bg-white text-gray-900;
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.05), -5px -5px 15px rgba(255, 255, 255, 0.8);
        }
        .content-card:hover {
            transform: translateY(-3px);
            box-shadow: 5px 5px 20px rgba(0, 0, 0, 0.1), -5px -5px 20px rgba(255, 255, 255, 0.9);
        }
        .dark .content-card {
            @apply bg-gray-800 text-gray-200;
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.3), -5px -5px 15px rgba(255, 255, 255, 0.05);
        }
        .dark .content-card:hover {
            box-shadow: 5px 5px 20px rgba(0, 0, 0, 0.4), -5px -5px 20px rgba(255, 255, 255, 0.1);
        }

        /* Table styles - Modernized */
        table {
            @apply w-full border-collapse rounded-lg overflow-hidden;
        }
        table th {
            @apply py-3 px-4 text-left bg-gray-100 text-gray-800 font-semibold border-b border-gray-200;
        }
        table td {
            @apply py-3 px-4 border-b border-gray-200 text-gray-700;
        }
        table tr:hover {
            @apply bg-gray-50;
        }
        .dark table th {
            @apply bg-gray-700 text-gray-100 border-gray-600;
        }
        .dark table td {
            @apply border-gray-600 text-gray-200;
        }
        .dark table tr:hover {
            @apply bg-gray-600;
        }

        /* Improved alerts/notifications */
        .alert {
            @apply p-4 mb-4 rounded-lg border-l-4 shadow-md;
        }
        .alert-success {
            @apply bg-green-50 border-green-400 text-green-800;
        }
        .alert-danger {
            @apply bg-red-50 border-red-400 text-red-800;
        }
        .alert-warning {
            @apply bg-yellow-50 border-yellow-400 text-yellow-800;
        }
        .alert-info {
            @apply bg-blue-50 border-blue-400 text-blue-800;
        }
        .dark .alert-success {
            @apply bg-green-900 border-green-400 text-green-100;
        }
        .dark .alert-danger {
            @apply bg-red-900 border-red-400 text-red-100;
        }
        .dark .alert-warning {
            @apply bg-yellow-900 border-yellow-400 text-yellow-100;
        }
        .dark .alert-info {
            @apply bg-blue-900 border-blue-400 text-blue-100;
        }

        /* Footer enhancements */
        .footer {
            background: linear-gradient(135deg, #111827 0%, #1F2937 100%);
        }
        .footer-wave {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120' preserveAspectRatio='none'%3E%3Cpath d='M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z' opacity='.25' class='shape-fill'%3E%3C/path%3E%3Cpath d='M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z' opacity='.5' class='shape-fill'%3E%3C/path%3E%3Cpath d='M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z' class='shape-fill'%3E%3C/path%3E%3C/svg%3E");
            height: 60px;
            width: 100%;
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            margin-top: -60px;
        }
        .social-icon {
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .social-icon:hover {
            transform: scale(1.1);
            background-color: #FF8C42;
            box-shadow: 0 5px 15px rgba(255, 140, 66, 0.4);
        }
        .footer-link {
            position: relative;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            @apply text-gray-400;
        }
        .footer-link:before {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #FF8C42;
            transition: all 0.3s ease;
        }
        .footer-link:hover:before {
            width: 100%;
        }
        .footer-link:hover {
            @apply text-secondary;
        }
        .dark .footer-link {
            @apply text-gray-300;
        }
        .dark .footer-link:hover {
            @apply text-secondary;
        }

        /* Gradient text effect */
        .gradient-text {
            background: linear-gradient(90deg, #FF8C42, #36B37E);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline;
        }

        /* Nav glassy effect for modern look */
        .nav-glass {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            background-color: rgba(31, 41, 55, 0.8);
        }
        .dark .nav-glass {
            background-color: rgba(17, 24, 39, 0.8);
        }

        /* Custom button and glowing effect */
        .btn-glow {
            position: relative;
            overflow: hidden;
            transition: all 0.5s ease;
        }
        .btn-glow:after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(30deg);
            transition: all 0.5s ease;
            opacity: 0;
        }
        .btn-glow:hover:after {
            opacity: 1;
            transform: rotate(30deg) translate(-30%, -30%);
        }

        /* Paragraph styling for better readability */
        p {
            @apply text-gray-800 leading-relaxed;
        }
        .dark p {
            @apply text-gray-200;
        }

        /* Headings styling */
        h1, h2, h3, h4, h5, h6 {
            @apply text-gray-900 font-bold;
        }
        h1 { @apply text-4xl; }
        h2 { @apply text-3xl; }
        h3 { @apply text-2xl; }
        .dark h1, 
        .dark h2, 
        .dark h3, 
        .dark h4, 
        .dark h5, 
        .dark h6 {
            @apply text-gray-100;
        }

        /* Links styling */
        a {
            @apply text-secondary transition-colors duration-200;
        }
        a:hover {
            @apply text-accent;
        }

        /* Code blocks styling */
        code {
            @apply bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm;
        }
        .dark code {
            @apply bg-gray-700 text-gray-200;
        }

        /* Lists styling */
        ul, ol {
            @apply pl-5 text-gray-800;
        }
        .dark ul, 
        .dark ol {
            @apply text-gray-200;
        }

        /* Icons styling */
        i {
            @apply text-gray-700 mr-2;
        }
        .dark i {
            @apply text-gray-300;
        }
    </style>
</head>
<body class="bg-neutral font-sans text-gray-900 dark:text-gray-200 transition-colors duration-300">
    <nav class="bg-primary sticky top-0 z-10 shadow-lg nav-glass">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="/collegehub/index.php" class="text-2xl font-bold text-light flex items-center">
                <span class="logo-icon text-secondary"><i class="fas fa-graduation-cap mr-2"></i></span> College<span class="gradient-text">Hub</span>
            </a>
            <div class="flex items-center space-x-6">
                <button id="mode-toggle" class="text-light hover:text-secondary focus:outline-none transition-colors duration-200">
                    <i class="fas fa-moon text-xl"></i>
                </button>
                <div class="md:hidden">
                    <button id="menu-btn" class="focus:outline-none">
                        <div class="hamburger">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </button>
                </div>
                <div id="menu" class="hidden md:flex md:items-center md:space-x-4">
                    <a href="/collegehub/lost_found.php" class="menu-item block md:inline-block">
                        <i class="fas fa-search"></i> Lost & Found
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/collegehub/user/profile.php" class="menu-item block md:inline-block">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <a href="/collegehub/logout.php" class="menu-item block md:inline-block">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <a href="/collegehub/admin/dashboard.php" class="menu-item block md:inline-block">
                                <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="/collegehub/login.php" class="menu-item block md:inline-block">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="/collegehub/register.php" class="menu-item block md:inline-block bg-secondary rounded-lg px-4 py-2 shadow-md hover:shadow-lg hover:transform hover:scale-105 btn-glow">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Rest of your body content (e.g., main content) will go here -->
</body>
</html>