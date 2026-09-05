<?php
$page_title = 'Server Error';
include 'partials/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto text-center">
        <h1 class="text-6xl font-bold text-red-600 mb-4">500</h1>
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Internal Server Error</h2>
        <p class="text-gray-600 mb-6">Something went wrong on our end. Please try again later.</p>
        <a href="<?php echo ROOT_URL; ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-300">
            Go Home
        </a>
    </div>
</main>

<?php include 'partials/footer.php'; ?>
