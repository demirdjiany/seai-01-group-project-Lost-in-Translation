<?php

    session_start();
    include(__DIR__ . "/../../database/connection.php");

    $email = isset($_POST["email"]) ? trim(strtolower($_POST["email"])) : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";

    if ($email == "" OR $password == "") {
        echo json_encode(["success" => false, "message" => "email or password missing"]);
        return;
    }

    $sql = "SELECT id, email, password_hash, username FROM users WHERE email = ?";
    $query = $mysql->prepare($sql);
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();
    $data = $result->fetch_assoc();

    if (!$data OR !password_verify($password, $data["password_hash"])) {
        echo json_encode(["success" => false, "message" => "incorrect email or password"]);
        return;
    }

    $_SESSION["player_id"] = $data["id"];

    echo json_encode([
        "success" => true,
        "data" => [
            "id" => $data["id"],
            "email" => $data["email"],
            "username" => $data["username"]
        ]
    ]);

?>
