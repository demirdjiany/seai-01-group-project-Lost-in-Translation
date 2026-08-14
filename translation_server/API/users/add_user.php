<?php

    session_start();
    include(__DIR__ . "/../../database/connection.php");

    $username = isset($_POST["name"]) ? trim($_POST["name"]) : "";
    $email = isset($_POST["email"]) ? trim(strtolower($_POST["email"])) : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";

    if ($username == "" OR $email == "" OR $password == "") {
        echo json_encode(["success" => false, "message" => "name, email, or password missing"]);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "invalid email"]);
        return;
    }

    if (strlen($password) < 6) {
        echo json_encode(["success" => false, "message" => "password must be at least 6 characters"]);
        return;
    }

    $sql = "SELECT id FROM users WHERE email = ?";
    $query = $mysql->prepare($sql);
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();

    if ($result->fetch_assoc()) {
        echo json_encode(["success" => false, "message" => "an account with this email already exists"]);
        return;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (email, password_hash, username) VALUES (?, ?, ?)";
    $query = $mysql->prepare($sql);
    $query->bind_param("sss", $email, $password_hash, $username);

    if (!$query->execute()) {
        echo json_encode(["success" => false, "message" => "user could not be added"]);
        return;
    }

    $player_id = $mysql->insert_id;
    $_SESSION["player_id"] = $player_id;

    echo json_encode([
        "success" => true,
        "message" => "user added",
        "data" => [
            "id" => $player_id,
            "email" => $email,
            "username" => $username
        ]
    ]);
?>
