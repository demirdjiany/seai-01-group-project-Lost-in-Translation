<?php

    session_start();
    include(__DIR__ . "/../../database/connection.php");
    $player_id = isset($_SESSION["player_id"]) ? $_SESSION["player_id"] : -1;
    $round_id = -1;

    if (isset($_POST["round_id"])) {
        $round_id = $_POST["round_id"];
    }

    if ($player_id == -1 OR $round_id == -1) {
        echo json_encode(["success" => false,"message"=> "player or round not found"]);
        return;
    }

    // checking if there's a round and it's status

    $sql = "SELECT rounds.status, sentences.content AS original_sentence
            FROM rounds
            JOIN sentences ON sentences.id = rounds.sentence_id
            WHERE rounds.id = ?";
    $query = $mysql->prepare($sql);
    $query->bind_param("i", $round_id);
    $query->execute();
    $result = $query->get_result();
    $round = $result->fetch_assoc();
    
    if (!$round){
        echo json_encode(["success"=> false,"message"=> "round not found"]);
        return;
    }

    if ($round["status"] != "open"){
        echo json_encode(["success"=> false,"message"=> "round is not open"]);
        return;
    }

    // check the number of hints used

    $sql = "SELECT COUNT(*) AS hints_used FROM hint_usage WHERE round_id = ? AND player_id = ?";
    $query = $mysql->prepare($sql);
    $query->bind_param("ii", $round_id, $player_id);
    $query->execute();

    $result = $query->get_result();
    $hint_count = $result->fetch_assoc();
    $hints_used = $hint_count["hints_used"];

    if ($hints_used >= 1){
        echo json_encode(["success"=> false,"message"=> "no more hints left"]);
        return;
    }

    // reveal the first word of the original English sentence

    $words = explode(" ", trim($round["original_sentence"]));
    $first_word = $words[0];
    $step_number = 0;

    $hint = [
        "step_number" => $step_number,
        "to_language" => "en",
        "translated_text" => $first_word
    ];

    // records the players hint usage

    $sql = "INSERT INTO hint_usage (round_id, player_id, step_number) VALUES (?, ?, ?)";
    $query = $mysql->prepare($sql);    
    $query->bind_param("iii", $round_id, $player_id, $step_number);
    $query->execute();

    $hints_used++;

    echo json_encode(["success"=> true,"data"=> ["hint" => $hint, "hints_used" => $hints_used, "points_penalty" => $hints_used * 30]]);

?>
