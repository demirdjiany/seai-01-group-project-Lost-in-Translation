<?php

// DEV ONLY - lets the dev test page peek at a round's answer while testing.
// Not part of the game and not used by any real endpoint. Safe to delete
// (along with dev_test.html) before handing anything in.

include(__DIR__ . "/database/connection.php");

$round_id = $_GET["round_id"] ?? -1;

$sql = "SELECT sentences.content FROM rounds JOIN sentences ON sentences.id = rounds.sentence_id WHERE rounds.id = ?";
$query = $mysql->prepare($sql);
$query->bind_param("i", $round_id);
$query->execute();

$result = $query->get_result();
$row = $result->fetch_assoc();

echo json_encode(["success" => true, "seed" => $row["content"] ?? null]);
