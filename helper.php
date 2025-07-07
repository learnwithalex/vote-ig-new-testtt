<?php
if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $value = getenv($key);

        if ($value === false && isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        return $value !== false ? $value : $default;
    }
}
