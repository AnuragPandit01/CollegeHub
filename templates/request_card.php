<div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($request['title']); ?></h3>
    <p class="text-gray-700 dark:text-gray-300 mt-2"><?php echo nl2br(htmlspecialchars($request['description'])); ?></p>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2"><?php echo date('F d, Y', strtotime($request['created_at'])); ?></p>
    <form method="POST" action="" class="mt-4" onsubmit="return confirm('Are you sure you want to delete this request?');">
        <input type="hidden" name="action" value="delete_request">
        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
        <button type="submit" class="btn btn-outline border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-4 py-2 rounded-lg">Delete Request</button>
    </form>
</div>