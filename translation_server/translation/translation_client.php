<?php

function translate_hop($text, $from, $to, $config) {
    $attempts = 0;
    $last_error = "";

    while ($attempts <= $config["max_retries_per_hop"]) {
        $attempts++;
        $result = call_mymemory($text, $from, $to, $config);

        if ($result["ok"]) {
            return $result;
        }

        $last_error = $result["error"];
    }

    return ["ok" => false, "error" => "hop $from->$to failed after $attempts attempt(s): $last_error"];
}

function call_mymemory($text, $from, $to, $config) {
    $params = [
        "q" => $text,
        "langpair" => $from . "|" . $to,
    ];

    if ($config["contact_email"]) {
        $params["de"] = $config["contact_email"];
    }

    $url = $config["api_endpoint"] . "?" . http_build_query($params);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($body === false || $curl_error !== "") {
        return ["ok" => false, "error" => "network error: $curl_error"];
    }

    if ($http_code !== 200) {
        return ["ok" => false, "error" => "HTTP error code: $http_code"];
    }

    $decoded = json_decode($body, true);
    if (!is_array($decoded)) {
        return ["ok" => false, "error" => "invalid JSON response"];
    }

    $status = $decoded["responseStatus"] ?? null;
    if ((string) $status !== "200") {
        $detail = $decoded["responseDetails"] ?? "unknown error";
        return ["ok" => false, "error" => "API status $status: $detail"];
    }

    $translated = trim($decoded["responseData"]["translatedText"] ?? "");
    if ($translated === "") {
        return ["ok" => false, "error" => "empty translation returned"];
    }

    return ["ok" => true, "text" => $translated];
}
