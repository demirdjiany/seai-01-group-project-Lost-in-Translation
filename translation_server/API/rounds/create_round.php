<?php

    include(__DIR__ . "/../../database/connection.php");
    include(__DIR__ . "/../../functions/similarity.php");

    $sql = "SELECT * FROM sentences ORDER BY RAND() LIMIT 1";
    $query = $mysql->prepare($sql);
    $query->execute();

    $result = $query->get_result();
    $sentence = $result->fetch_assoc();

    if (!$sentence){
        echo json_encode(["success" =>false,"message"=> "sentence not found"]);
        return;
    }

    $sentence_id = $sentence["id"];
    $sentence_content = $sentence["content"];

    $translation_result = [
        "success" => true,
        "final_translation" => "Temporary mangled English sentence",
        "steps" => [
            ["from" => "en", "to" => "ja", "text" => "Temporary Japanese result"],
            ["from" => "ja", "to" => "ar", "text" => "Temporary Arabic result"],
            ["from" => "ar", "to" => "fi", "text" => "Temporary Finnish result"],
            ["from" => "fi", "to" => "sw", "text" => "Temporary Swahili result"],
            ["from" => "sw", "to" => "hu", "text" => "Temporary Hungarian result"],
            ["from" => "hu", "to" => "ko", "text" => "Temporary Korean result"],
            ["from" => "ko", "to" => "en", "text" => "Temporary final English result"]
        ]
    ];

    if (!$translation_result["success"]){
        echo json_encode(["success"=>false,"message"=> "translation failed"]);
        return;
    }

    $final_translation = $translation_result["final_translation"];
    $status = "open";
    $score = calculate_mangle_score($sentence_content, $final_translation);

    $sql = "INSERT INTO rounds (sentence_id, final_translation, status, ends_at, score) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 60 SECOND), ?)";
    $query = $mysql->prepare($sql);
    $query->bind_param("issi", $sentence_id, $final_translation, $status, $score);
    $query->execute();

    $round_id = $mysql->insert_id;

    $step_number = 1;

    $sql = "INSERT INTO round_steps (round_id, step_number, from_language, to_language, translated_text) VALUES (?, ?, ?, ?, ?)";
    $query = $mysql->prepare($sql);

    foreach ($translation_result["steps"] as $step) {
        $from_language = $step["from"];
        $to_language = $step["to"];
        $translated_text = $step["text"];

        $query->bind_param("iisss", $round_id, $step_number, $from_language, $to_language, $translated_text);
        $query->execute();
        $step_number++;
    }

    echo json_encode(["success" => true, "round_id" => $round_id]);
?>
