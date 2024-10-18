<?php
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["website_url"])) {
    $website_url = $_GET["website_url"];
    $http_code = validateActiveURL($website_url);

    echo $http_code;
}

function validateActiveURL($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    // Execute the cURL request
    curl_exec($ch);

    // Now retrieve the HTTP response code
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return $http_code;
}
?>
