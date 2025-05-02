<div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg transition-transform transform hover:scale-105">
    <div class="flex items-center space-x-4">
        <?php if ($item['image']): ?>
            <img src="../assets/images/uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Lost/Found Image" class="w-24 h-24 object-cover rounded-lg">
        <?php else: ?>
            <img src="../assets/images/placeholder.jpg" alt="Placeholder" class="w-24 h-24 object-cover rounded-lg">
        <?php endif; ?>
        <div class="flex-1">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($item['title']); ?></h3>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Type: <?php echo ucfirst($item['type']); ?></p>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Category: <?php echo htmlspecialchars($item['category_name'] ?: 'N/A'); ?></p>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Posted: <?php echo date('F d, Y', strtotime($item['created_at'])); ?></p>
        </div>
    </div>
    <p class="mt-4 text-gray-700 dark:text-gray-300"><?php echo nl2br(htmlspecialchars(substr($item['description'], 0, 100) . (strlen($item['description']) > 100 ? '...' : ''))); ?></p>
    <a href="lost_found_details.php?id=<?php echo $item['id']; ?>" class="mt-4 inline-block btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-4 py-2 rounded-lg">View Details</a>
</div>