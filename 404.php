<?php
$page_title = 'Page Not Found - 404 Error';
require 'config/database.php';

// Fetch 3 recent posts
$recent_query = "SELECT p.*, c.title as category_title FROM posts p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.date_time DESC LIMIT 3";
$recent_posts = mysqli_query($connection, $recent_query);

// Fetch categories
$categories_query = "SELECT * FROM categories LIMIT 8";
$categories = mysqli_query($connection, $categories_query);

include 'partials/header.php';
?>


<main class="container mx-auto px-4 py-8">
    <div class="grid md:grid-cols-2 gap-12 items-start">
        <!-- 404 Hero Section -->
        <div class="text-center md:text-left">
            <h1 class="text-8xl md:text-9xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent mb-6">404</h1>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Oops! Page Not Found</h2>
            <p class="text-xl text-gray-600 mb-8 leading-relaxed">The requested URL was not found on this server. It might have been moved, renamed, or doesn't exist.</p>
            
            <div class="flex flex-wrap gap-4 justify-center md:justify-start">
                <a href="<?php echo ROOT_URL; ?>" class="bg-primary text-white px-8 py-4 rounded-full font-semibold hover:bg-primary-dark transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    ← Go Home
                </a>
                <a href="<?php echo ROOT_URL; ?>search.php" class="border-2 border-primary text-primary px-8 py-4 rounded-full font-semibold hover:bg-primary hover:text-white transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    Search Site
                </a>
            </div>
        </div>

        <!-- Search & Quick Links -->
        <div class="space-y-6">
            <!-- Search Form -->
            <div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-100">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Still Looking for Something?</h3>
                <form action="search.php" method="GET" class="flex gap-3">
                    <input type="search" name="query" placeholder="Search Fantepedia..." class="flex-1 px-5 py-4 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all duration-300 text-lg" required>
                    <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-8 py-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 whitespace-nowrap">
                        Search
                    </button>
                </form>
            </div>

            <!-- Browse Categories -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-xl shadow-lg border border-blue-100">
                <h4 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="uil uil-th-large"></i> Browse Categories
                </h4>
                <div class="grid grid-cols-2 gap-3">
                    <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                    <a href="<?php echo ROOT_URL; ?>category.php?id=<?php echo $cat['id']; ?>" class="text-primary hover:text-primary-dark font-medium hover:underline transition-colors duration-200 text-sm">
                        <?php echo htmlspecialchars($cat['title']); ?>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Posts Section -->
    <?php if(mysqli_num_rows($recent_posts) > 0): ?>
    <div class="mt-20">
        <h3 class="text-3xl font-bold text-gray-800 mb-8 text-center">Recent Articles</h3>
        <div class="grid md:grid-cols-3 gap-8">
            <?php while($post = mysqli_fetch_assoc($recent_posts)): ?>
            <article class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden border border-gray-100 hover:border-primary">
                <div class="h-48 overflow-hidden">
                    <img src="images/<?php echo htmlspecialchars($post['thumbnail']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <div class="p-6">
                    <span class="inline-block bg-primary text-white text-xs px-3 py-1 rounded-full font-semibold mb-3">
                        <?php echo htmlspecialchars($post['category_title'] ?? 'Uncategorized'); ?>
                    </span>
                    <h4 class="text-xl font-bold text-gray-800 mb-3 line-clamp-2 group-hover:text-primary transition-colors">
                        <a href="<?php echo ROOT_URL; ?>post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                    </h4>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo substr(strip_tags($post['body']), 0, 120); ?>...</p>
                    <div class="flex items-center text-xs text-gray-500">
                        <i class="uil uil-calendar-alt mr-1"></i>
                        <?php echo date("M d, Y", strtotime($post['date_time'])); ?>
                    </div>
                </div>
            </article>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
</main>

<?php include 'partials/footer.php'; ?>
