<nav class="bg-blue-900 text-white shadow-lg">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <div class="flex items-center space-x-4">
                <img src="https://marina.gov.ph/wp-content/uploads/2019/06/marina-logo.png" alt="Marina Logo" class="h-10">
                <div>
                    <h1 class="text-xl font-bold">Marina SES Tester</h1>
                    <p class="text-sm text-blue-200">Maritime Industry Authority</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-6">
                <nav class="hidden md:flex space-x-6">
                    <a href="dashboard.php" class="hover:text-blue-200 transition-colors">Dashboard</a>
                    <a href="basic-test.php" class="hover:text-blue-200 transition-colors">Basic Test</a>
                    <a href="template-test.php" class="hover:text-blue-200 transition-colors">Templates</a>
                    <a href="bulk-test.php" class="hover:text-blue-200 transition-colors">Bulk Test</a>
                    <a href="logs.php" class="hover:text-blue-200 transition-colors">Logs</a>
                    <a href="settings.php" class="hover:text-blue-200 transition-colors">Settings</a>
                </nav>
                
                <div class="flex items-center space-x-4">
                    <span class="text-sm">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm transition-colors">Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>
