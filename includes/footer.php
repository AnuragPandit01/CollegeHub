 <!-- Footer with Wave Effect -->
 <div class="footer-wave"></div>
    <footer class="footer text-light py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Logo and About -->
                <div class="text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start mb-4">
                        <i class="fas fa-graduation-cap text-3xl mr-2"></i>
                        <span class="text-2xl font-bold">CollegeHub</span>
                    </div>
                    <p class="text-light text-opacity-80 mb-4">
                        Connecting students, simplifying campus life, and creating a vibrant academic community.
                    </p>
                </div>
                
                <!-- Quick Links -->
                <div class="text-center">
                    <h3 class="text-xl font-semibold mb-4">Quick Links</h3>
                    <div class="flex flex-col space-y-2">
                        <a href="about.php" class="footer-link">About Us</a>
                        <a href="contact.php" class="footer-link">Contact</a>
                        <a href="faq.php" class="footer-link">FAQ</a>
                        <a href="privacy.php" class="footer-link">Privacy Policy</a>
                    </div>
                </div>
                
                <!-- Connect -->
                <div class="text-center md:text-right">
                    <h3 class="text-xl font-semibold mb-4">Connect With Us</h3>
                    <div class="flex justify-center md:justify-end space-x-4 mb-6">
                        <a href="https://facebook.com" target="_blank" class="social-icon">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com" target="_blank" class="social-icon">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" class="social-icon">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://linkedin.com" target="_blank" class="social-icon">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                    <p class="text-sm text-light text-opacity-70">
                        Subscribe to our newsletter for updates
                    </p>
                    <div class="mt-2 flex justify-center md:justify-end">
                        <input type="email" placeholder="Your email" class="bg-white bg-opacity-20 rounded-l-lg px-4 py-2 focus:outline-none" />
                        <button class="bg-secondary rounded-r-lg px-4 py-2 hover:bg-opacity-90 transition-colors">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-white border-opacity-20 mt-8 pt-6 text-center">
                <p>© <?php echo date('Y'); ?> CollegeHub. All rights reserved.</p>
                <p class="text-sm text-light text-opacity-70 mt-2">
                    Made with <i class="fas fa-heart text-accent"></i> for college students worldwide
                </p>
            </div>
        </div>
    </footer>
</body>
</html>