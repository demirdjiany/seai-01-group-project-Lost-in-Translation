<?php

    include(__DIR__ . "/../../database/connection.php");

    if (isset($_GET["round_id"])) {
        $round_id = $_GET["round_id"];
    }else{
        echo json_encode(["success" => false,"message"=> "round not found"]);
        return;
    }

    $sql = "SELECT *, TIMESTAMPDIFF(SECOND, NOW(), ends_at) AS remaining_seconds FROM rounds WHERE id = ?";
    $query = $mysql->prepare($sql);
    $query->bind_param("i", $round_id);
    $query->execute();

    $result = $query->get_result();
    $round = $result->fetch_assoc();

    if (!$round) {
        echo json_encode(["success"=> false,"message"=> "round not found"]);
        return;
    }

    if ($round["remaining_seconds"] <= 0 && $round["status"] == "open") {
        $sql = "UPDATE rounds SET status = 'closed' WHERE id = ?";
        $query = $mysql->prepare($sql);
        $query->bind_param("i", $round_id);
        $query->execute();

        $round["status"] = "closed";
        $round["remaining_seconds"] = 0;
    }

    $data = [
        "id" => $round["id"],
        "final_translation" => $round["final_translation"],
        "status" => $round["status"],
        "remaining_seconds" => $round["remaining_seconds"]
    ];

    if ($round["status"] == "closed") {
        $data["remaining_seconds"] = 0;

        $sentence_id = $round["sentence_id"];

        $sql = "SELECT content FROM sentences WHERE id = ?";
        $query = $mysql->prepare($sql);
        $query->bind_param("i", $sentence_id);
        $query->execute();

        $result = $query->get_result();
        $sentence = $result->fetch_assoc();

        $data["original_sentence"] = $sentence["content"];
        $data["score"] = $round["score"];
        $data["steps"] = [];

        $sql = "SELECT step_number, from_language, to_language, translated_text FROM round_steps WHERE round_id = ? ORDER BY step_number ASC";
        $query = $mysql->prepare($sql);
        $query->bind_param("i", $round_id);
        $query->execute();

        $result = $query->get_result();

        while ($step = $result->fetch_assoc()) {
            $data["steps"][] = $step;
        }
    }

    echo json_encode(["success" => true, "data" => $data]);
?>
