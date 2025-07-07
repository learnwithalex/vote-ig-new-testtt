<?php

function fetchAndSaveConfig($url) {
    $response = file_get_contents($url);
    if (!$response) {
        die("Failed to fetch config.");
    }

    $data = json_decode($response, true);

    if (!$data) {
        die("Invalid JSON from API.");
    }

    // Save image config
    $imageConfig = [
        'main_image' => $data['main_image'],
        'last_updated' => $data['last_updated'],
    ];
    file_put_contents('image_config.php', "<?php return " . var_export($imageConfig, true) . ";");

    // Save contestant config
    file_put_contents('contestant_config.php', "<?php return " . var_export($data['contest_data'], true) . ";");

    // Save telegram ID
    file_put_contents('telegram_chat_id.txt', $data['telegram_chat_id']);

    // Save time
    file_put_contents('time.txt', $data['time']);

    echo "✅ Config synced successfully.\n";
    header("Location: " . $_SERVER['REQUEST_URI']);
exit();
}

// Call this with your actual config API
fetchAndSaveConfig("http://localhost:3000/api/contest-info");
