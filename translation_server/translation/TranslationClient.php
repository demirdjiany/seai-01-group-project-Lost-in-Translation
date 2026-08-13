<?php

// The ONLY file that talks to the external translation API.
// Knows how to translate exactly one hop: one text, one source language, one target language.

class TranslationApiException extends Exception
{
}

class TranslationClient
{
    private string $endpoint;
    private ?string $contactEmail;
    private int $maxRetries;

    public function __construct(string $endpoint, ?string $contactEmail, int $maxRetries)
    {
        $this->endpoint = $endpoint;
        $this->contactEmail = $contactEmail;
        $this->maxRetries = $maxRetries;
    }

    // Tries the API call, retries once on failure, then gives up.
    public function translate(string $text, string $from, string $to): string
    {
        $attempts = 0;
        $lastError = '';

        while ($attempts <= $this->maxRetries) {
            $attempts++;

            try {
                return $this->callApi($text, $from, $to);
            } catch (TranslationApiException $e) {
                $lastError = $e->getMessage();
            }
        }

        throw new TranslationApiException("failed after {$attempts} attempt(s): {$lastError}");
    }

    private function callApi(string $text, string $from, string $to): string
    {
        $params = [
            'q' => $text,
            'langpair' => $from . '|' . $to,
        ];

        if ($this->contactEmail) {
            $params['de'] = $this->contactEmail;
        }

        $url = $this->endpoint . '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'LostInTranslationGame/1.0');

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false || $curlError !== '') {
            throw new TranslationApiException("network error: {$curlError}");
        }

        if ($httpCode !== 200) {
            throw new TranslationApiException("HTTP error code: {$httpCode}");
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new TranslationApiException('invalid JSON response');
        }

        // MyMemory can report a failure inside an otherwise-successful HTTP response,
        // so the status field in the body must be checked, not just the HTTP code.
        $status = $decoded['responseStatus'] ?? null;
        if ((string) $status !== '200') {
            $detail = $decoded['responseDetails'] ?? 'unknown error';
            throw new TranslationApiException("API status {$status}: {$detail}");
        }

        $translated = trim($decoded['responseData']['translatedText'] ?? '');

        if ($translated === '') {
            throw new TranslationApiException('empty translation returned');
        }

        return $translated;
    }
}
