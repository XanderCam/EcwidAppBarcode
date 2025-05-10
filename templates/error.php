<!DOCTYPE html>
<html>
<head>
    <title>Error - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-red-600 mb-4">Error</h1>
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
                    <p class="text-red-700">
                        <?php echo htmlspecialchars($e->getMessage()); ?>
                    </p>
                </div>
                <p class="text-gray-600 mb-6">
                    An error occurred while processing your request. Please try again later or contact support if the problem persists.
                </p>
                <a href="<?php echo APP_URL; ?>" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Return to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
