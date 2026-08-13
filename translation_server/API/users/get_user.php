<?php

    include(__DIR__ . "/../../database/connection.php");

    if (isset($_GET["special_id"])){
        $special_id = $_GET["special_id"];
    }
    else{
        $special_id = -1;
        echo json_encode(["success" => false,"message"=> "user not found"]);
        return;
    }

    $sql = "SELECT * FROM users WHERE special_id = ?";
    $query = $mysql->prepare($sql);
    $query->bind_param("i", $special_id);
    $query->execute();
    $result = $query->get_result();
    $data = $result->fetch_assoc();

    echo json_encode(["success"=> true,"data"=> $data]);

?>
