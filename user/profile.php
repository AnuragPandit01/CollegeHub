<?php
// Start session and define constants
session_start();
define('PAGE_TITLE', 'My Profile');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Include database connection
require_once '../includes/db_connect.php';

// Handle form submissions (BEFORE any output)
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';
$errors = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        if ($action === 'update_profile') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $college = trim($_POST['college']);
            $profile_picture = null;

            // Fetch current user details to get existing profile picture
            $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            $profile_picture = $user['profile_picture'];

            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
                list($image_name, $upload_errors) = uploadImage($_FILES['profile_picture']);
                if (!empty($upload_errors)) {
                    $errors = array_merge($errors, $upload_errors);
                } else {
                    $profile_picture = $image_name;
                }
            }

            if (empty($name)) $errors[] = "Name is required.";
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
            if (empty($college)) $errors[] = "College is required.";
            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, college = ?, profile_picture = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $college, $profile_picture, $user_id]);
                    $message = "Profile updated successfully.";
                    header("Location: profile.php?tab=profile&message=" . urlencode($message));
                    exit;
                } catch (PDOException $e) {
                    $errors[] = "Error updating profile: " . $e->getMessage();
                }
            }
        } elseif ($action === 'list_item') {
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $category_id = (int)$_POST['category_id'];
            $price = !empty($_POST['price']) ? (float)$_POST['price'] : null;
            $image = null;

            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                list($image_name, $upload_errors) = uploadImage($_FILES['image']);
                if (!empty($upload_errors)) {
                    $errors = array_merge($errors, $upload_errors);
                } else {
                    $image = $image_name;
                }
            }

            if (empty($title)) $errors[] = "Title is required.";
            if (empty($description)) $errors[] = "Description is required.";
            if ($category_id <= 0) $errors[] = "Please select a category.";
            if (!is_null($price) && $price < 0) $errors[] = "Price cannot be negative.";
            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
                    $stmt->execute([$category_id]);
                    if (!$stmt->fetch()) {
                        $errors[] = "Selected category does not exist.";
                    }
                } catch (PDOException $e) {
                    $errors[] = "Error validating category: " . $e->getMessage();
                }
            }
            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO listings (user_id, title, description, category_id, price, image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
                    $stmt->execute([$user_id, $title, $description, $category_id, $price, $image]);
                    $message = "Listing submitted for approval.";
                    header("Location: profile.php?tab=listings&message=" . urlencode($message));
                    exit;
                } catch (PDOException $e) {
                    $errors[] = "Error listing item: " . $e->getMessage();
                }
            }
        } elseif ($action === 'list_lost_found') {
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $type = $_POST['type'] ?? '';
            $category_id = (int)$_POST['category_id'];
            $date = $_POST['date'] ?? '';
            $contact_info = trim($_POST['contact_info'] ?? '');
            $image = null;

            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                list($image_name, $upload_errors) = uploadImage($_FILES['image']);
                if (!empty($upload_errors)) {
                    $errors = array_merge($errors, $upload_errors);
                } else {
                    $image = $image_name;
                }
            }

            if (empty($title)) $errors[] = "Title is required.";
            if (empty($description)) $errors[] = "Description is required.";
            if (!in_array($type, ['lost', 'found'])) $errors[] = "Please select if the item is lost or found.";
            if ($category_id <= 0) $errors[] = "Please select a category.";
            if (empty($date)) $errors[] = "Date is required.";
            elseif (!strtotime($date)) $errors[] = "Invalid date format.";
            if (empty($contact_info)) $errors[] = "Contact information is required.";
            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
                    $stmt->execute([$category_id]);
                    if (!$stmt->fetch()) {
                        $errors[] = "Selected category does not exist.";
                    }
                } catch (PDOException $e) {
                    $errors[] = "Error validating category: " . $e->getMessage();
                }
            }
            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO lost_found (user_id, title, type, description, category_id, date, contact_info, image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
                    $stmt->execute([$user_id, $title, $type, $description, $category_id, $date, $contact_info, $image]);
                    $message = "Lost/Found item submitted for approval.";
                    header("Location: profile.php?tab=lost_found&message=" . urlencode($message));
                    exit;
                } catch (PDOException $e) {
                    $errors[] = "Error listing lost/found item: " . $e->getMessage();
                }
            }
        } elseif ($action === 'edit_item') {
            $listing_id = (int)$_POST['listing_id'];
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $category_id = (int)$_POST['category_id'];
            $price = !empty($_POST['price']) ? (float)$_POST['price'] : null;
            $image = $_POST['existing_image'] ?? null;

            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                list($image_name, $upload_errors) = uploadImage($_FILES['image']);
                if (!empty($upload_errors)) {
                    $errors = array_merge($errors, $upload_errors);
                } else {
                    $image = $image_name;
                }
            }

            if (empty($title)) $errors[] = "Title is required.";
            if (empty($description)) $errors[] = "Description is required.";
            if ($category_id <= 0) $errors[] = "Please select a category.";
            if (!is_null($price) && $price < 0) $errors[] = "Price cannot be negative.";
            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
                    $stmt->execute([$category_id]);
                    if (!$stmt->fetch()) {
                        $errors[] = "Selected category does not exist.";
                    }
                } catch (PDOException $e) {
                    $errors[] = "Error validating category: " . $e->getMessage();
                }
            }
            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare("UPDATE listings SET title = ?, description = ?, category_id = ?, price = ?, image = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
                    $stmt->execute([$title, $description, $category_id, $price, $image, $listing_id, $user_id]);
                    $message = "Listing updated successfully.";
                    header("Location: profile.php?tab=listings&message=" . urlencode($message));
                    exit;
                } catch (PDOException $e) {
                    $errors[] = "Error updating listing: " . $e->getMessage();
                }
            }
        } elseif ($action === 'delete_lost_found') {
            $item_id = (int)$_POST['item_id'];
            try {
                $stmt = $pdo->prepare("DELETE FROM lost_found WHERE id = ? AND user_id = ?");
                $stmt->execute([$item_id, $user_id]);
                $message = "Lost/Found item deleted successfully.";
                header("Location: profile.php?tab=lost_found&message=" . urlencode($message));
                exit;
            } catch (PDOException $e) {
                $errors[] = "Error deleting lost/found item: " . $e->getMessage();
            }
        } elseif ($action === 'send_message') {
            $recipient_email = trim($_POST['recipient']);
            $message_content = trim($_POST['content']);
            $listing_id = isset($_POST['listing_id']) ? (int)$_POST['listing_id'] : 0;

            if (empty($recipient_email) || empty($message_content)) {
                $errors[] = "Recipient email and message content are required.";
            } else {
                // Find the recipient user by email
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$recipient_email]);
                $recipient = $stmt->fetch();

                if (!$recipient) {
                    $errors[] = "Recipient email not found.";
                } else {
                    $receiver_id = $recipient['id'];
                    $stmt = $pdo->prepare("INSERT INTO messages (listing_id, sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
                    if ($stmt->execute([$listing_id, $user_id, $receiver_id, $message_content])) {
                        $message = "Message sent successfully!";
                        header("Location: profile.php?tab=messages&listing_id=$listing_id&message=" . urlencode($message));
                        exit;
                    } else {
                        $errors[] = "Failed to send message.";
                    }
                }
            }
        } elseif ($action === 'reply_message') {
            $listing_id = (int)$_POST['listing_id'];
            $receiver_id = (int)$_POST['receiver_id'];
            $message_content = trim($_POST['content']);

            if (empty($message_content)) {
                $errors[] = "Message content is required.";
            } else {
                // Verify the listing and receiver exist
                $stmt = $pdo->prepare("SELECT id FROM listings WHERE id = ? AND status = 'approved'");
                $stmt->execute([$listing_id]);
                if (!$stmt->fetch()) {
                    $errors[] = "Invalid listing.";
                } else {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
                    $stmt->execute([$receiver_id]);
                    if (!$stmt->fetch()) {
                        $errors[] = "Invalid recipient.";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO messages (listing_id, sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
                        if ($stmt->execute([$listing_id, $user_id, $receiver_id, $message_content])) {
                            $message = "Reply sent successfully!";
                            header("Location: profile.php?tab=messages&listing_id=$listing_id&message=" . urlencode($message));
                            exit;
                        } else {
                            $errors[] = "Failed to send reply.";
                        }
                    }
                }
            }
        }
    }
}

// Fetch user details
try {
    $stmt = $pdo->prepare("
        SELECT name, email, created_at, profile_picture, college, role 
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: ../index.php?message=User+not+found');
        exit;
    }
} catch (PDOException $e) {
    $errors[] = "Error fetching user details: " . $e->getMessage();
}

// Fetch user stats (listings and lost/found items)
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM listings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_listings = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM lost_found WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_lost_found = $stmt->fetchColumn();
} catch (PDOException $e) {
    $errors[] = "Error fetching user stats: " . $e->getMessage();
}

// Fetch user's listings (all statuses for "My Posts")
try {
    $stmt = $pdo->prepare("
        SELECT l.id, l.title, l.image, l.price, l.created_at, l.status, c.name AS category_name 
        FROM listings l 
        JOIN categories c ON l.category_id = c.id 
        WHERE l.user_id = ?
        ORDER BY l.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $all_listings = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Error fetching listings: " . $e->getMessage();
}

// Fetch approved listings (for "My Listings" tab)
try {
    $stmt = $pdo->prepare("
        SELECT l.id, l.title, l.image, l.price, l.description, l.created_at, c.name AS category_name 
        FROM listings l 
        JOIN categories c ON l.category_id = c.id 
        WHERE l.user_id = ? AND l.status = 'approved'
        ORDER BY l.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $approved_listings = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Error fetching approved listings: " . $e->getMessage();
}

// Fetch user's lost/found items (all statuses for "My Posts")
try {
    $stmt = $pdo->prepare("
        SELECT lf.id, lf.title, lf.image, lf.created_at, lf.status 
        FROM lost_found lf 
        WHERE lf.user_id = ? 
        ORDER BY lf.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $all_lost_found = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Error fetching lost/found items: " . $e->getMessage();
}

// Fetch categories for forms
try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Error fetching categories: " . $e->getMessage();
}

// Fetch notifications for the user
try {
    $stmt = $pdo->prepare("
        SELECT id, message, read_status, created_at
        FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();

    // Mark notifications as read when viewed
    if ($active_tab === 'notifications') {
        $stmt = $pdo->prepare("UPDATE notifications SET read_status = 'read' WHERE user_id = ? AND read_status = 'unread'");
        $stmt->execute([$user_id]);
    }
} catch (PDOException $e) {
    $errors[] = "Error fetching notifications: " . $e->getMessage();
}

// Fetch user's approved listings with messages
try {
    $stmt = $pdo->prepare("
        SELECT l.id, l.title, 
               (SELECT COUNT(*) FROM messages m WHERE m.listing_id = l.id) as message_count
        FROM listings l 
        WHERE l.user_id = ? AND l.status = 'approved'
        ORDER BY l.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $user_listings_with_messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Error fetching your listings: " . $e->getMessage();
}

// Handle message display for the Messages tab
$selected_listing_id = isset($_GET['listing_id']) ? (int)$_GET['listing_id'] : 0;
$messages = [];
$listing = null;

if ($selected_listing_id && $active_tab === 'messages') {
    // Fetch listing details
    $stmt = $pdo->prepare("SELECT id, title, user_id FROM listings WHERE id = ? AND status = 'approved'");
    $stmt->execute([$selected_listing_id]);
    $listing = $stmt->fetch();

    if (!$listing) {
        $errors[] = "Invalid or unapproved listing.";
    } else {
        // Fetch messages for this listing
        $stmt = $pdo->prepare("
            SELECT m.message, m.created_at, u.name AS sender_name, m.sender_id
            FROM messages m 
            JOIN users u ON m.sender_id = u.id 
            WHERE m.listing_id = ? 
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$selected_listing_id]);
        $messages = $stmt->fetchAll();
    }
}

// Function to calculate time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $current = time();
    $seconds = $current - $time;
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);

    if ($seconds <= 60) return "just now";
    elseif ($minutes <= 60) return $minutes . " minute" . ($minutes == 1 ? "" : "s") . " ago";
    elseif ($hours <= 24) return $hours . " hour" . ($hours == 1 ? "" : "s") . " ago";
    else return $days . " day" . ($days == 1 ? "" : "s") . " ago";
}

// Function to handle image upload
function uploadImage($file, $upload_dir = '../assets/images/uploads/') {
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    $errors = [];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error uploading file.";
        return [null, $errors];
    }

    if (!in_array($file['type'], $allowed_types)) {
        $errors[] = "Only JPEG, PNG, and GIF images are allowed.";
        return [null, $errors];
    }

    if ($file['size'] > $max_size) {
        $errors[] = "Image size must be less than 5MB.";
        return [null, $errors];
    }

    $file_name = uniqid() . '_' . basename($file['name']);
    $file_path = $upload_dir . $file_name;

    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        return [$file_name, []];
    } else {
        $errors[] = "Failed to upload image.";
        return [null, $errors];
    }
}

// Now include the header (AFTER all redirects)
require_once '../includes/header.php';
?>

<main class="container mx-auto px-4 py-16 min-h-screen bg-gray-50 dark:bg-gray-900">
    <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-8 text-center animate-fade-in">My Profile</h1>

    <!-- Messages -->
    <?php if (!empty($message) || (isset($_GET['message']) && !empty($_GET['message']))): ?>
        <div class="alert alert-success mb-6 bg-green-100 border-green-400 text-green-800 dark:bg-green-900 dark:border-green-600 dark:text-green-100 p-4 rounded-lg shadow-md animate-fade-in">
            <p><?php echo htmlspecialchars($message ?: $_GET['message']); ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger mb-6 bg-red-100 border-red-400 text-red-800 dark:bg-red-900 dark:border-red-600 dark:text-red-100 p-4 rounded-lg shadow-md animate-fade-in">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="mb-8 flex flex-wrap gap-3 justify-center border-b border-gray-200 dark:border-gray-700">
        <button class="tab-btn px-5 py-3 text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-800 rounded-t-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-300 transform hover:scale-105 <?php echo $active_tab === 'profile' ? 'bg-secondary text-white' : ''; ?>" onclick="showTab('profile')"><i class="fas fa-user mr-2"></i> Profile</button>
        <button class="tab-btn px-5 py-3 text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-800 rounded-t-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-300 transform hover:scale-105 <?php echo $active_tab === 'posts' ? 'bg-secondary text-white' : ''; ?>" onclick="showTab('posts')"><i class="fas fa-clipboard-list mr-2"></i> My Posts</button>
        <button class="tab-btn px-5 py-3 text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-800 rounded-t-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-300 transform hover:scale-105 <?php echo $active_tab === 'listings' ? 'bg-secondary text-white' : ''; ?>" onclick="showTab('listings')"><i class="fas fa-list mr-2"></i> My Listings</button>
        <button class="tab-btn px-5 py-3 text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-800 rounded-t-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-300 transform hover:scale-105 <?php echo $active_tab === 'lost_found' ? 'bg-secondary text-white' : ''; ?>" onclick="showTab('lost_found')"><i class="fas fa-search mr-2"></i> Lost/Found</button>
        <button class="tab-btn px-5 py-3 text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-800 rounded-t-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-300 transform hover:scale-105 <?php echo $active_tab === 'messages' ? 'bg-secondary text-white' : ''; ?>" onclick="showTab('messages')"><i class="fas fa-envelope mr-2"></i> Messages</button>
        <button class="tab-btn px-5 py-3 text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-800 rounded-t-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-300 transform hover:scale-105 <?php echo $active_tab === 'notifications' ? 'bg-secondary text-white' : ''; ?>" onclick="showTab('notifications')"><i class="fas fa-bell mr-2"></i> Notifications</button>
        <button class="tab-btn px-5 py-3 text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-800 rounded-t-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-300 transform hover:scale-105 <?php echo $active_tab === 'requests' ? 'bg-secondary text-white' : ''; ?>" onclick="showTab('requests')"><i class="fas fa-hand-holding-heart mr-2"></i> Requests</button>
    </div>

    <!-- Profile Tab -->
    <div id="profile-tab" class="tab-content <?php echo $active_tab === 'profile' ? 'block' : 'hidden'; ?> animate-fade-in">
        <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
            <div class="flex items-center space-x-6 mb-6">
                <div class="relative">
                    <img src="<?php echo $user['profile_picture'] ? '../assets/images/uploads/' . htmlspecialchars($user['profile_picture']) : '../assets/images/placeholder.jpg'; ?>" alt="Profile Picture" class="w-24 h-24 rounded-full object-cover border-4 border-secondary">
                    <button onclick="openModal('updateProfileModal')" class="absolute bottom-0 right-0 bg-secondary text-white p-2 rounded-full hover:bg-opacity-90 transition-all duration-300">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p class="text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="text-gray-600 dark:text-gray-400">College: <?php echo htmlspecialchars($user['college']); ?></p>
                    <p class="text-gray-600 dark:text-gray-400">Role: <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full <?php echo $user['role'] === 'admin' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'; ?>"><?php echo ucfirst($user['role']); ?></span></p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Member since <?php echo date('F d, Y', strtotime($user['created_at'])); ?> (<?php echo timeAgo($user['created_at']); ?>)</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="text-center p-4 bg-gray-100 dark:bg-gray-700 rounded-lg shadow">
                    <p class="text-2xl font-bold text-secondary"><?php echo $total_listings; ?></p>
                    <p class="text-gray-600 dark:text-gray-400">Total Listings</p>
                </div>
                <div class="text-center p-4 bg-gray-100 dark:bg-gray-700 rounded-lg shadow">
                    <p class="text-2xl font-bold text-secondary"><?php echo $total_lost_found; ?></p>
                    <p class="text-gray-600 dark:text-gray-400">Lost/Found Posts</p>
                </div>
            </div>
        </div>
    </div>

    <!-- My Posts Tab -->
    <div id="posts-tab" class="tab-content <?php echo $active_tab === 'posts' ? 'block' : 'hidden'; ?> animate-fade-in">
        <div class="space-y-8">
            <div>
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <i class="fas fa-list mr-2 text-accent"></i> Listings
                </h3>
                <?php if (empty($all_listings)): ?>
                    <p class="text-gray-600 dark:text-gray-400 text-center">No listings found.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($all_listings as $listing): ?>
                            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                                <?php if ($listing['image']): ?>
                                    <img src="../assets/images/uploads/<?php echo htmlspecialchars($listing['image']); ?>" alt="Listing Image" class="w-full h-40 object-cover rounded-t-lg mb-4">
                                <?php else: ?>
                                    <img src="../assets/images/placeholder.jpg" alt="Placeholder" class="w-full h-40 object-cover rounded-t-lg mb-4">
                                <?php endif; ?>
                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                    <a href="../listing_details.php?id=<?php echo $listing['id']; ?>" class="text-secondary hover:text-accent"><?php echo htmlspecialchars($listing['title']); ?></a>
                                </h4>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">
                                    Category: <?php echo htmlspecialchars($listing['category_name']); ?>
                                </p>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">
                                    Price: <?php echo $listing['price'] ? number_format($listing['price'], 2) : 'Free'; ?>
                                </p>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">
                                    Status: <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full <?php echo $listing['status'] === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'; ?>"><?php echo ucfirst($listing['status']); ?></span>
                                </p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm"><?php echo timeAgo($listing['created_at']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <i class="fas fa-search mr-2 text-accent"></i> Lost/Found Items
                </h3>
                <?php if (empty($all_lost_found)): ?>
                    <p class="text-gray-600 dark:text-gray-400 text-center">No lost/found items found.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($all_lost_found as $item): ?>
                            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                                <?php if ($item['image']): ?>
                                    <img src="../assets/images/uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Item Image" class="w-full h-40 object-cover rounded-t-lg mb-4">
                                <?php else: ?>
                                    <img src="../assets/images/placeholder.jpg" alt="Placeholder" class="w-full h-40 object-cover rounded-t-lg mb-4">
                                <?php endif; ?>
                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2"><?php echo htmlspecialchars($item['title']); ?></h4>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">
                                    Status: <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full <?php echo $item['status'] === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'; ?>"><?php echo ucfirst($item['status']); ?></span>
                                </p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm"><?php echo timeAgo($item['created_at']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- My Listings Tab -->
    <div id="listings-tab" class="tab-content <?php echo $active_tab === 'listings' ? 'block' : 'hidden'; ?> animate-fade-in">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                <i class="fas fa-list mr-2 text-accent"></i> My Listings
            </h2>
            <button onclick="openModal('addListingModal')" class="btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i> Add Listing
            </button>
        </div>
        <?php if (empty($approved_listings)): ?>
            <p class="text-gray-600 dark:text-gray-400 text-center">You have no approved listings yet.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($approved_listings as $listing): ?>
                    <div class="relative p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                        <?php if ($listing['image']): ?>
                            <img src="../assets/images/uploads/<?php echo htmlspecialchars($listing['image']); ?>" alt="Listing Image" class="w-full h-40 object-cover rounded-t-lg mb-4">
                        <?php else: ?>
                            <img src="../assets/images/placeholder.jpg" alt="Placeholder" class="w-full h-40 object-cover rounded-t-lg mb-4">
                        <?php endif; ?>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                            <a href="../listing_details.php?id=<?php echo $listing['id']; ?>" class="text-secondary hover:text-accent"><?php echo htmlspecialchars($listing['title']); ?></a>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">Category: <?php echo htmlspecialchars($listing['category_name']); ?></p>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">Price: <?php echo $listing['price'] ? number_format($listing['price'], 2) : 'Free'; ?></p>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-4"><?php echo timeAgo($listing['created_at']); ?></p>
                        <div class="flex space-x-2">
                            <button onclick="openModal('editListingModal<?php echo $listing['id']; ?>')" class="btn btn-outline border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-3 py-1 rounded-lg">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                <input type="hidden" name="action" value="edit_item">
                                <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                <button type="submit" class="btn btn-outline border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1 rounded-lg">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                        <!-- Edit Listing Modal -->
                        <div id="editListingModal<?php echo $listing['id']; ?>" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
                            <div class="modal-overlay fixed inset-0 bg-black bg-opacity-70" onclick="closeModal('editListingModal<?php echo $listing['id']; ?>')"></div>
                            <div class="modal-content bg-white dark:bg-gray-800 p-6 rounded-lg shadow-2xl max-w-md w-full relative max-h-[80vh] overflow-y-auto">
                                <button onclick="closeModal('editListingModal<?php echo $listing['id']; ?>')" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-2xl focus:outline-none transition-colors duration-200">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div class="mb-6">
                                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Edit Listing</h3>
                                </div>
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="edit_item">
                                    <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($listing['image'] ?? ''); ?>">
                                    <div class="mb-5">
                                        <label for="edit_title_<?php echo $listing['id']; ?>" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Title</label>
                                        <input type="text" id="edit_title_<?php echo $listing['id']; ?>" name="title" value="<?php echo htmlspecialchars($listing['title']); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition duration-200">
                                    </div>
                                    <div class="mb-5">
                                        <label for="edit_description_<?php echo $listing['id']; ?>" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Description</label>
                                        <textarea id="edit_description_<?php echo $listing['id']; ?>" name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary resize-none transition duration-200"><?php echo htmlspecialchars($listing['description']); ?></textarea>
                                    </div>
                                    <div class="mb-5">
                                        <label for="edit_category_<?php echo $listing['id']; ?>" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Category</label>
                                        <select id="edit_category_<?php echo $listing['id']; ?>" name="category_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition duration-200">
                                            <option value="0">Select a category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" <?php echo $listing['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-5">
                                        <label for="edit_price_<?php echo $listing['id']; ?>" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Price (optional)</label>
                                        <input type="number" id="edit_price_<?php echo $listing['id']; ?>" name="price" step="0.01" value="<?php echo $listing['price'] ? number_format($listing['price'], 2) : ''; ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition duration-200">
                                    </div>
                                    <div class="mb-5">
                                        <label for="edit_image_<?php echo $listing['id']; ?>" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Update Image (optional)</label>
                                        <input type="file" id="edit_image_<?php echo $listing['id']; ?>" name="image" accept="image/*" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-secondary file:text-white hover:file:bg-opacity-90 transition duration-200">
                                    </div>
                                    <button type="submit" class="btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-6 py-3 rounded-lg w-full font-medium transition duration-200">Update Listing</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <!-- Add Listing Modal -->
        <div id="addListingModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg max-w-lg w-full">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Add New Listing</h3>
                    <button onclick="closeModal('addListingModal')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="list_item">
                    <div class="mb-4">
                        <label for="list_title" class="block text-gray-700 dark:text-gray-300 mb-2">Title</label>
                        <input type="text" id="list_title" name="title" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div class="mb-4">
                        <label for="list_description" class="block text-gray-700 dark:text-gray-300 mb-2">Description</label>
                        <textarea id="list_description" name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="list_category" class="block text-gray-700 dark:text-gray-300 mb-2">Category</label>
                        <select id="list_category" name="category_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary">
                            <option value="0">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="list_price" class="block text-gray-700 dark:text-gray-300 mb-2">Price (optional)</label>
                        <input type="number" id="list_price" name="price" step="0.01" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div class="mb-4">
                        <label for="list_image" class="block text-gray-700 dark:text-gray-300 mb-2">Image (optional)</label>
                        <input type="file" id="list_image" name="image" accept="image/*" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg">
                    </div>
                    <button type="submit" class="btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-4 py-2 rounded-lg w-full">Add Listing</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Lost/Found Tab -->
    <div id="lost_found-tab" class="tab-content <?php echo $active_tab === 'lost_found' ? 'block' : 'hidden'; ?> animate-fade-in">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                <i class="fas fa-search mr-2 text-accent"></i> My Lost/Found Items
            </h2>
            <button onclick="openModal('addLostFoundModal')" class="btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i> Add Lost/Found Item
            </button>
        </div>
        <?php if (empty($all_lost_found)): ?>
            <p class="text-gray-600 dark:text-gray-400 text-center">You have no lost/found items yet.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($all_lost_found as $item): ?>
                    <div class="relative p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                        <?php if ($item['image']): ?>
                            <img src="../assets/images/uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Item Image" class="w-full h-40 object-cover rounded-t-lg mb-4">
                        <?php else: ?>
                            <img src="../assets/images/placeholder.jpg" alt="Placeholder" class="w-full h-40 object-cover rounded-t-lg mb-4">
                        <?php endif; ?>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2"><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">
                            Status: <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full <?php echo $item['status'] === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'; ?>"><?php echo ucfirst($item['status']); ?></span>
                        </p>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-4"><?php echo timeAgo($item['created_at']); ?></p>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this item?');">
                            <input type="hidden" name="action" value="delete_lost_found">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn btn-outline border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1 rounded-lg w-full">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <!-- Add Lost/Found Modal -->
        <div id="addLostFoundModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
            <div class="modal-overlay fixed inset-0 bg-black bg-opacity-70" onclick="closeModal('addLostFoundModal')"></div>
            <div class="modal-content bg-white dark:bg-gray-800 p-6 rounded-lg shadow-2xl max-w-md w-full relative max-h-[80vh] overflow-y-auto">
                <button onclick="closeModal('addLostFoundModal')" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-2xl focus:outline-none transition-colors duration-200">
                    <i class="fas fa-times"></i>
                </button>
                <div class="mb-6">
                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Add Lost/Found Item</h3>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="list_lost_found">
                    <div class="mb-5">
                        <label for="lost_title" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Title</label>
                        <input type="text" id="lost_title" name="title" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition duration-200">
                    </div>
                    <div class="mb-5">
                        <label for="lost_description" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Description</label>
                        <textarea id="lost_description" name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary resize-none transition duration-200"></textarea>
                    </div>
                    <div class="mb-5">
                        <label for="lost_type" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Item Type</label>
                        <select id="lost_type" name="type" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition duration-200">
                            <option value="" disabled selected>Select type</option>
                            <option value="lost">Lost</option>
                            <option value="found">Found</option>
                        </select>
                    </div>
                    <div class="mb-5">
                        <label for="lost_category" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Category</label>
                        <select id="lost_category" name="category_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition duration-200">
                            <option value="0">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-5">
                        <label for="lost_date" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Date of Loss/Find</label>
                        <input type="date" id="lost_date" name="date" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition duration-200">
                    </div>
                    <div class="mb-5">
                        <label for="lost_contact_info" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Contact Information</label>
                        <input type="text" id="lost_contact_info" name="contact_info" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition duration-200" placeholder="e.g., Phone number or email">
                    </div>
                    <div class="mb-5">
                        <label for="lost_image" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Image (optional)</label>
                        <input type="file" id="lost_image" name="image" accept="image/*" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-secondary file:text-white hover:file:bg-opacity-90 transition duration-200">
                    </div>
                    <button type="submit" class="btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-6 py-3 rounded-lg w-full font-medium transition duration-200">Add Lost/Found Item</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Messages Tab -->
    <div id="messages-tab" class="tab-content <?php echo $active_tab === 'messages' ? 'block' : 'hidden'; ?> animate-fade-in">
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                    <i class="fas fa-envelope mr-2 text-accent"></i> Messages
                </h2>
                <button onclick="openModal('sendMessageModal')" class="btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Send Message
                </button>
            </div>

            <!-- Display Errors -->
            <?php if (!empty($errors) && $active_tab === 'messages'): ?>
                <div class="alert alert-danger mb-6 bg-red-100 border-red-400 text-red-800 dark:bg-red-900 dark:border-red-600 dark:text-red-100 p-4 rounded-lg shadow-md animate-fade-in">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Display Success Message -->
            <?php if (!empty($message) && $active_tab === 'messages'): ?>
                <div class="alert alert-success mb-6 bg-green-100 border-green-400 text-green-800 dark:bg-green-900 dark:border-green-600 dark:text-green-100 p-4 rounded-lg shadow-md animate-fade-in">
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>

            <!-- Listings with Messages -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <?php if (empty($user_listings_with_messages)): ?>
                    <p class="text-gray-600 dark:text-gray-400 text-center col-span-3">You have no listings with messages.</p>
                <?php else: ?>
                    <?php foreach ($user_listings_with_messages as $user_listing): ?>
                        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105 <?php echo $selected_listing_id == $user_listing['id'] ? 'border-2 border-secondary' : ''; ?>">
                            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                <?php echo htmlspecialchars($user_listing['title']); ?>
                            </h4>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                                Messages: <?php echo $user_listing['message_count']; ?>
                            </p>
                            <a href="?tab=messages&listing_id=<?php echo $user_listing['id']; ?>" class="btn btn-outline border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-3 py-1 rounded-lg flex items-center">
                                <i class "fas fa-envelope-open mr-2"></i> Open Messages
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Messages Display for Selected Listing -->
            <?php if ($selected_listing_id && $listing): ?>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-16">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Messages for: <?php echo htmlspecialchars($listing['title']); ?>
                    </h3>
                    <div class="max-h-96 overflow-y-auto space-y-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <?php if (empty($messages)): ?>
                            <p class="text-gray-600 dark:text-gray-400 text-center">No messages for this listing yet.</p>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <div class="flex flex-col <?php echo $msg['sender_id'] == $user_id ? 'items-end' : 'items-start'; ?> mb-4">
                                    <div class="max-w-[75%] p-3 rounded-lg <?php echo $msg['sender_id'] == $user_id ? 'bg-blue-100 dark:bg-blue-900 text-gray-900 dark:text-gray-100' : 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-gray-200'; ?>">
                                        <p class="text-sm font-medium"><?php echo htmlspecialchars($msg['sender_name']); ?>:</p>
                                        <p class="text-sm"><?php echo htmlspecialchars($msg['message']); ?></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?php echo date('F d, Y H:i', strtotime($msg['created_at'])); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Reply Form -->
                    <div class="mt-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Reply to Sender</h4>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="reply_message">
                            <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                            <input type="hidden" name="receiver_id" value="<?php echo $messages ? $messages[count($messages) - 1]['sender_id'] : 0; ?>">
                            <div class="mb-4">
                                <textarea name="content" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary resize-none transition duration-200" placeholder="Type your reply..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-6 py-2 rounded-lg flex items-center">
                                <i class="fas fa-paper-plane mr-2"></i> Send Reply
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Send Message Modal -->
        <div id="sendMessageModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
            <div class="modal-overlay fixed inset-0 bg-black bg-opacity-70" onclick="closeModal('sendMessageModal')"></div>
            <div class="modal-content bg-white dark:bg-gray-800 p-6 rounded-lg shadow-2xl max-w-md w-full relative max-h-[80vh] overflow-y-auto">
                <button onclick="closeModal('sendMessageModal')" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-2xl focus:outline-none transition-colors duration-200">
                    <i class="fas fa-times"></i>
                </button>
                <div class="mb-6">
                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Send Message</h3>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="send_message">
                    <input type="hidden" name="listing_id" value="0">
                    <div class="mb-5">
                        <label for="message_recipient" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Recipient Email</label>
                        <input type="email" id="message_recipient" name="recipient" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition duration-200" placeholder="Enter user email" required>
                    </div>
                    <div class="mb-5">
                        <label for="message_content" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Message</label>
                        <textarea id="message_content" name="content" rows="4" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary resize-none transition duration-200" placeholder="Type your message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-6 py-3 rounded-lg w-full font-medium transition duration-200">Send Message</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Notifications Tab -->
    <div id="notifications-tab" class="tab-content <?php echo $active_tab === 'notifications' ? 'block' : 'hidden'; ?> animate-fade-in">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
            <i class="fas fa-bell mr-2 text-accent"></i> Notifications
        </h2>
        <?php if (empty($notifications)): ?>
            <p class="text-gray-600 dark:text-gray-400 text-center">No notifications found.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($notifications as $notification): ?>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 <?php echo $notification['read_status'] === 'unread' ? 'border-l-4 border-accent' : ''; ?>">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 rounded-full bg-<?php echo $notification['read_status'] === 'unread' ? 'secondary' : 'gray-300'; ?> flex items-center justify-center text-white">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class "flex-1">
                                <p class="text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($notification['message']); ?></p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm"><?php echo date('F d, Y H:i', strtotime($notification['created_at'])); ?> (<?php echo timeAgo($notification['created_at']); ?>)</p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Requests Tab -->
    <div id="requests-tab" class="tab-content <?php echo $active_tab === 'requests' ? 'block' : 'hidden'; ?> animate-fade-in">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                <i class="fas fa-hand-holding-heart mr-2 text-accent"></i> Requests
            </h2>
            <button onclick="openModal('submitRequestModal')" class="btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i> Submit Request
            </button>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-center">Request system to be implemented (placeholder).</p>
        <!-- Submit Request Modal -->
        <div id="submitRequestModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
            <div class="modal-overlay fixed inset-0 bg-black bg-opacity-70" onclick="closeModal('submitRequestModal')"></div>
            <div class="modal-content bg-white dark:bg-gray-800 p-6 rounded-lg shadow-2xl max-w-md w-full relative max-h-[80vh] overflow-y-auto">
                <button onclick="closeModal('submitRequestModal')" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-2xl focus:outline-none transition-colors duration-200">
                    <i class="fas fa-times"></i>
                </button>
                <div class="mb-6">
                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Submit Request</h3>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="submit_request">
                    <div class="mb-5">
                        <label for="request_title" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Request Title</label>
                        <input type="text" id="request_title" name="title" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition duration-200">
                    </div>
                    <div class="mb-5">
                        <label for="request_description" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Description</label>
                        <textarea id="request_description" name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary resize-none transition duration-200"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-6 py-3 rounded-lg w-full font-medium transition duration-200">Submit Request</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Profile Modal -->
    <div id="updateProfileModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="modal-overlay fixed inset-0 bg-black bg-opacity-70" onclick="closeModal('updateProfileModal')"></div>
        <div class="modal-content bg-white dark:bg-gray-800 p-6 rounded-lg shadow-2xl max-w-md w-full relative max-h-[80vh] overflow-y-auto">
            <button onclick="closeModal('updateProfileModal')" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-2xl focus:outline-none transition-colors duration-200">
                <i class="fas fa-times"></i>
            </button>
            <div class="mb-6">
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Update Profile</h3>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">
                <div class="mb-5">
                    <label for="name" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition duration-200">
                </div>
                <div class="mb-5">
                    <label for="email" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition duration-200">
                </div>
                <div class="mb-5">
                    <label for="college" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">College</label>
                    <input type="text" id="college" name="college" value="<?php echo htmlspecialchars($user['college']); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary transition duration-200">
                </div>
                <div class="mb-5">
                    <label for="profile_picture" class="block text-gray-700 dark:text-gray-300 mb-2 font-medium">Profile Picture (optional)</label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-secondary file:text-white hover:file:bg-opacity-90 transition duration-200">
                </div>
                <button type="submit" class="btn btn-primary bg-secondary text-white hover:bg-opacity-90 px-6 py-3 rounded-lg w-full font-medium transition duration-200">Update Profile</button>
            </form>
        </div>
    </div>
</main>

<script>
function showTab(tabName) {
    const tabs = ['profile', 'posts', 'listings', 'lost_found', 'messages', 'notifications', 'requests'];
    tabs.forEach(t => {
        document.getElementById(`${t}-tab`).classList.add('hidden');
        document.querySelector(`.tab-btn[onclick="showTab('${t}')"]`).classList.remove('bg-secondary', 'text-white');
        document.querySelector(`.tab-btn[onclick="showTab('${t}')"]`).classList.add('bg-gray-100', 'dark:bg-gray-800');
    });
    document.getElementById(`${tabName}-tab`).classList.remove('hidden');
    document.querySelector(`.tab-btn[onclick="showTab('${tabName}')"]`).classList.remove('bg-gray-100', 'dark:bg-gray-800');
    document.querySelector(`.tab-btn[onclick="showTab('${tabName}')"]`).classList.add('bg-secondary', 'text-white');
    window.history.pushState({}, '', `?tab=${tabName}`);
}

function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    showTab('<?php echo $active_tab; ?>');
});
</script>

<?php require_once '../includes/footer.php'; ?>

</body>
</html>

<?php
// Close any open database connections (optional, PDO closes automatically at script end)
$pdo = null;
?>