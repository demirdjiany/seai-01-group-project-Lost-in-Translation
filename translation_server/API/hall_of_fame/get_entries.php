<?php

    include(__DIR__ . "/../../database/connection.php");
    $sort = "score";

    // decides what to order on, maybe based on the user choice(2 order by buttons) in the hall of fame

    if (isset($_GET["sort"])) {
        $sort = $_GET["sort"];
    }

    if ($sort == "votes") {
        $order_by = "votes DESC, rounds.score DESC";
    }else{
        $order_by = "rounds.score DESC, votes DESC";
    }

    $sql = "SELECT rounds.id AS round_id, sentences.content AS original_sentence, rounds.final_translation, rounds.score,
            (
                SELECT COUNT(*)
                FROM hall_of_fame_votes
                WHERE hall_of_fame_votes.round_id = rounds.id
            ) AS votes
        FROM rounds JOIN sentences ON sentences.id = rounds.sentence_id WHERE rounds.status = 'closed' ORDER BY $order_by";
    
    $query = $mysql->prepare($sql);
    $query->execute();

    $result = $query->get_result();

    $data = [];

    while ($entry = $result->fetch_assoc()) {
        $data[] = $entry;
    }

    echo json_encode(["success" => true, "data" => $data]);

?>
