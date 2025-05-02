<?php
   define('PAGE_TITLE', 'Lost & Found Details');
   require_once 'includes/header.php';

   // Fetch item ID from URL
   if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
       header('Location: lost_found.php?error=Invalid item ID');
       exit;
   }

   $item_id = (int)$_GET['id'];

   // Fetch item details with category name
   try {
       $stmt = $pdo->prepare("
           SELECT lf.*, u.name AS user_name, c.name AS category_name 
           FROM lost_found lf 
           JOIN users u ON lf.user_id = u.id 
           LEFT JOIN categories c ON lf.category_id = c.id 
           WHERE lf.id = ? AND lf.status = 'approved'
       ");
       $stmt->execute([$item_id]);
       $item = $stmt->fetch();

       if (!$item) {
           header('Location: lost_found.php?error=Item not found or not approved');
           exit;
       }
   } catch (PDOException $e) {
       echo "Error fetching item details: " . $e->getMessage();
       exit;
   }
   ?>

   <main class="container mx-auto px-4 py-16">
       <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-6">Lost & Found Details</h1>

       <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
           <div class="flex items-start space-x-4">
               <?php if (isset($item['image']) && $item['image']): ?>
                   <img src="../assets/images/uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Item Image" class="w-32 h-32 object-cover rounded-lg">
               <?php else: ?>
                   <img src="../assets/images/placeholder.jpg" alt="Placeholder" class="w-32 h-32 object-cover rounded-lg">
               <?php endif; ?>
               <div class="flex-1">
                   <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($item['description']); ?></h2> <!-- Using description as title -->
                   <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">Posted by: <?php echo htmlspecialchars($item['user_name']); ?> - <?php echo date('F d, Y', strtotime($item['created_at'])); ?></p>
                   <p class="text-gray-700 dark:text-gray-300 mb-2"><strong>Type:</strong> <?php echo ucfirst($item['type']); ?></p>
                   <p class="text-gray-700 dark:text-gray-300 mb-2"><strong>Category:</strong> <?php echo htmlspecialchars($item['category_name'] ?: 'N/A'); ?></p>
                   <p class="text-gray-700 dark:text-gray-300 mb-2"><strong>Date:</strong> <?php echo date('F d, Y', strtotime($item['date'])); ?></p>
                   <p class="text-gray-700 dark:text-gray-300 mb-2"><strong>Contact:</strong> <?php echo htmlspecialchars($item['contact_info']); ?></p>
                   <p class="text-gray-700 dark:text-gray-300"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
               </div>
           </div>
           <a href="lost_found.php" class="mt-6 inline-block btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-4 py-2 rounded-lg">Back to Lost & Found</a>
           <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $item['user_id']): ?>
               <form method="POST" action="" class="mt-4" onsubmit="return confirm('Are you sure you want to delete this item?');">
                   <input type="hidden" name="action" value="delete_lost_found">
                   <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                   <button type="submit" class="btn btn-outline border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-4 py-2 rounded-lg">Delete Item</button>
               </form>
           <?php endif; ?>
       </div>
   </main>

   <?php require_once 'includes/footer.php'; ?>