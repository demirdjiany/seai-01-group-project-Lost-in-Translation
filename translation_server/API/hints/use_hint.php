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
        echo json_encode(["success" => false,"message"=> "player or round not found"]);
        return;
    }

    // checking if there's a round and it's status

    $sql = "SELECT status FROM rounds WHERE id = ?";
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

    if ($hints_used >= 3){
        echo json_encode(["success"=> false,"message"=> "no more hints left"]);
        return;
    }

    // check which step to reveal and reveal it

    $next_step = 5 - $hints_used;
    $sql = "SELECT * FROM round_steps WHERE round_id = ? AND step_number = ?";
    $query = $mysql->prepare($sql);
    $query->bind_param("ii", $round_id, $next_step);
    $query->execute();

    $result = $query->get_result();
    $hint = $result->fetch_assoc();
    
    if (!$hint){
        echo json_encode(["success"=> false,"message"=> "no hint found"]);
        return;
    }

    // records the players hint usage

    $sql = "INSERT INTO hint_usage (round_id, player_id, step_number) VALUES (?, ?, ?)";
    $query = $mysql->prepare($sql);    
    $query->bind_param("iii", $round_id, $player_id, $next_step);
    $query->execute();

    $hints_used++;

    echo json_encode(["success"=> true,"data"=> ["hint" => $hint, "hints_used" => $hints_used, "points_penalty" => $hints_used * 30]]);

?>
