<?php

    // normalizes both texts, removing all white spaces, extra spaces, punctuations and turning everything lowercase
    function normalize_text($text){
        $text = strtolower($text);
        $text = preg_replace("/[^a-z0-9\s]/", "", $text);
        $text = preg_replace("/\s+/", " ", $text);
        $text = trim($text);

        return $text;
    }

    // 
    function calculate_similarity($translated_text, $user_text){
        $translated_text = normalize_text($translated_text);
        $user_text = normalize_text($user_text);

        $translated_length = strlen($translated_text);
        $user_length = strlen($user_text);
        $longest_length = max($translated_length, $user_length);

        if ($longest_length == 0) {
            return 1;
        }

        $distance = levenshtein($translated_text, $user_text);
        $similarity = 1 - ($distance / $longest_length);

        return $similarity;
    }

    function get_guess_result($similarity) {
        if ($similarity >= 0.85) {
            return "correct";
        }

        if ($similarity >= 0.60) {
            return "close";
        }

        return "wrong";
    }

    function calculate_mangle_score($original_text, $final_translation) {
        $similarity = calculate_similarity($original_text, $final_translation);
        $mangle_score = (1 - $similarity) * 100;

        return round($mangle_score);
    }

?>
