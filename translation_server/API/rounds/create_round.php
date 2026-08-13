<?php

    include(__DIR__ . "/../../database/connection.php");
    include(__DIR__ . "/../../functions/similarity.php");
    include(__DIR__ . "/../../translation/translation_service.php");

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

    $chain_result = run_translation_chain($sentence_content);

    if (!$chain_result["ok"]){
        echo json_encode(["success"=>false,"message"=> "translation failed"]);
        return;
    }

    // run_translation_chain() returns one entry per language, starting with the
    // seed (steps[0]). round_steps needs one entry per hop (from -> to), so pair
    // each step up with the one before it.
    $chain_steps = $chain_result["steps"];
    $steps = [];

    for ($i = 1; $i < count($chain_steps); $i++) {
        $steps[] = [
            "from" => $chain_steps[$i - 1]["lang"],
            "to" => $chain_steps[$i]["lang"],
            "text" => $chain_steps[$i]["text"],
        ];
    }

    $translation_result = [
        "success" => true,
        "final_translation" => end($chain_steps)["text"],
        "steps" => $steps,
    ];

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
