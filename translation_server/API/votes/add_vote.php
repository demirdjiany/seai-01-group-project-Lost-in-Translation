<?php

    include(__DIR__ . "/../../database/connection.php");

    $player_id = -1;
    $round_id = -1;

    if (isset($_POST["player_id"])) {
        $player_id = $_POST["player_id"];
    }

    if (isset($_POST["round_id"])) {
        $round_id = $_POST["round_id"];
    }

    if ($player_id == -1 OR $round_id == -1) {
        echo json_encode(array("success"=> false, "message"=> "no user or round found!"));
        return;
    }

    $sql = "INSERT INTO hall_of_fame_votes (player_id, round_id) VALUES (?, ?)";
    $query = $mysql->prepare($sql);
    $query->bind_param("ii", $player_id, $round_id);
    $query->execute();

    echo json_encode(array("success"=> true,"message"=> "voted successfully"));

?>
