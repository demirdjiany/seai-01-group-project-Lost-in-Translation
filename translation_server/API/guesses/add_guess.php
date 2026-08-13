<?php

    include(__DIR__ . "/../../database/connection.php");
    include(__DIR__ . "/../../functions/similarity.php");

    $player_id = -1;
    $round_id = -1;
    $guess = "";

    if (isset($_POST["player_id"])) {
        $player_id = $_POST["player_id"];
    }

    if (isset($_POST["round_id"])) {
        $round_id = $_POST["round_id"];
    }

    if (isset($_POST["guess"])) {
        $guess = trim($_POST["guess"]);
    }

    if ($player_id == -1 OR $round_id == -1 OR $guess == "") {
        echo json_encode(["success"=> false,"message"=> "player, round, or guess content missing"]);
        return;
    }

    // getting the round information and orginal sentence

    $sql = "SELECT rounds.status, TIMESTAMPDIFF(SECOND, rounds.started_at, NOW()) AS elapsed_seconds, sentences.content AS original_sentence
            FROM rounds
            JOIN sentences ON sentences.id = rounds.sentence_id
            WHERE rounds.id = ?";
    $query = $mysql->prepare($sql);
    $query->bind_param("i", $round_id);
    $query->execute();

    $result = $query->get_result();
    $round = $result->fetch_assoc();

    if (!$round) {
        echo json_encode(["success"=> false,"message"=> "round not found or is over"]);
        return;
    }

    if ($round["status"] != "open"){
        echo json_encode(["success"=> false,"message"=> "round is not open"]);
        return;
    }

    // calls two functions defined in similarity.php and calculates the similarity score between the guess and the original sentence

    $similarity = calculate_similarity($round["original_sentence"], $guess);
    $guess_result = get_guess_result($similarity);

    // checks how many hints the player used

    $sql = "SELECT COUNT(*) AS hints_used FROM hint_usage WHERE round_id = ? AND player_id = ?";
    $query = $mysql->prepare($sql);
    $query->bind_param("ii", $round_id, $player_id);
    $query->execute();
    $result = $query->get_result();
    $hint_count = $result->fetch_assoc();

    $hints_used = $hint_count["hints_used"];

    // calculate the final score

    $final_score = 0;
    if ($guess_result == "correct") {
        $final_score = 100 - $round["elapsed_seconds"] - (30 * $hints_used);

        if ($final_score < 0) {
            $final_score = 0;
        }
    }

    // add the guess into the guesses table

    $sql = "INSERT INTO guesses (round_id, player_id, guess, similarity_score, result, final_score, hints_used) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $query = $mysql->prepare($sql);
    $query->bind_param("iisdsii", $round_id, $player_id, $guess, $similarity, $guess_result, $final_score, $hints_used);

    if (!$query->execute()) {
        echo json_encode(["success" => false, "message" => "guess could not be saved"]);
        return;
    }

    echo json_encode([
        "success" => true,
        "data" => [
            "similarity" => $similarity,
            "result" => $guess_result,
            "final_score" => $final_score,
            "hints_used" => $hints_used
        ]
    ]);
?>
