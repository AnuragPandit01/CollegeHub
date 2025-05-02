<div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md <?php echo $notification['read_status'] === 'unread' ? 'border-l-4 border-accent' : ''; ?>">
    <p class="text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($notification['message']); ?></p>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2"><?php echo date('F d, Y H:i', strtotime($notification['created_at'])); ?></p>
    <?php if ($notification['read_status'] === 'unread'): ?>
        <a href="?mark_as_read=<?php echo $notification['id']; ?>" class="mt-2 inline-block text-accent hover:underline">Mark as Read</a>
    <?php endif; ?>
</div>