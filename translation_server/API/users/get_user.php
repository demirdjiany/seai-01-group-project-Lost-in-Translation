<?php

    include(__DIR__ . "/../../database/connection.php");

    if (isset($_GET["id"])){
        $id = $_GET["id"];
    }
    else{
        $id = -1;
        echo json_encode(["success" => false,"message"=> "user not found"]);
        return;
    }

    $sql = "SELECT * FROM users WHERE id = ?";
    $query = $mysql->prepare($sql);
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    $data = $result->fetch_assoc();

    echo json_encode(["success"=> true,"data"=> $data]);

?>
