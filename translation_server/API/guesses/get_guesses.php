<?php

    include(__DIR__ . "/../../database/connection.php");

    if (isset($_GET["round_id"])) {
        $round_id = $_GET["round_id"];
    }else{
        $round_id = -1;
        echo json_encode(["success" => false,"message" => "round not found!"]);
        return;
    }

    $sql = "SELECT * FROM guesses WHERE round_id = ?";
    $query = $mysql->prepare($sql);
    $query->bind_param("i", $round_id);
    $query->execute();

    $result = $query->get_result();
    $data = [];
    while($guess = $result->fetch_assoc()) {
        $data[] = $guess;
    }

    echo json_encode(["success"=> true,"data"=> $data]);

?>
