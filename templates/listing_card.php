<div
    class="content-card bg-neutral rounded-xl overflow-hidden transition-transform duration-300 hover:transform hover:scale-105 group">
    <div class="relative">
        <img src="<?php echo htmlspecialchars($listing['image'] ?: '../assets/images/placeholder.jpg'); ?>"
            alt="Listing Image" class="w-full h-56 object-cover">
        <div class="absolute top-4 right-4">
            <span
                class="bg-secondary text-primary text-xs font-bold px-3 py-1 rounded-full"><?php echo htmlspecialchars($listing['category_name']); ?></span>
        </div>
        <?php if ($listing['price'] > 0): ?>
            <div class="absolute bottom-4 left-4">
                <span
                    class="bg-accent text-light text-lg font-bold px-4 py-2 rounded-lg">₹<?php echo number_format($listing['price'], 2); ?></span>
            </div>
        <?php else: ?>
            <div class="absolute bottom-4 left-4">
                <span class="bg-accent text-light text-lg font-bold px-4 py-2 rounded-lg">Free</span>
            </div>
        <?php endif; ?>
    </div>
    <div class="p-6">
        <h3 class="text-xl font-bold text-primary mb-2 line-clamp-1"><?php echo htmlspecialchars($listing['title']); ?>
        </h3>
        <div class="flex items-center mb-4">
            <i class="fas fa-user-circle text-accent mr-2"></i>
            <p class="text-sm text-muted"><?php echo htmlspecialchars($listing['user_name']); ?></p>
        </div>
        <a href="./user/messages.php?listing_id=<?php echo $listing['id']; ?>"
            class="w-full inline-block bg-primary text-light text-center px-4 py-3 rounded-lg hover:bg-accent transition-colors duration-300 font-medium btn-glow">
            <i class="fas fa-envelope mr-2"></i> Contact Seller
        </a>
    </div>
</div>