<?php

    include(__DIR__ . "/../../database/connection.php");

    if (isset($_POST["special_id"])) {
        $special_id = $_POST["special_id"];
    }else{
        $special_id = -1;
        echo json_encode(["success" => false, "message"=> "user not found"]);
        return;
    }

    $first_name = ["Galaxy", "Planet", "Space", "Star"];
    $last_name = ["Voyager", "Ranger", "Explorer", "Pirate"];

    $f_name = $first_name[rand(0,3)];
    $l_name = $last_name[rand(0,3)];
    $number = rand(0, 999999);

    $username = $f_name . "_" . $l_name . "_" . $number;

    $sql = "INSERT INTO users (special_id, username) VALUES (?, ?)";
    $query = $mysql->prepare($sql);
    $query->bind_param("is",$special_id, $username);
    $query->execute();

    echo json_encode(["success"=> true,"message"=> "user added"]);
?>
