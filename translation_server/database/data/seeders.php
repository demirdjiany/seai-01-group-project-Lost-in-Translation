<?php

    header("Content-Type: application/json");

    include(__DIR__ . "/../connection.php");
    include(__DIR__ . "/factories.php");

    $sentences = factorySentences();
    $sentences_added = 0;
    $sentences_skipped = 0;

    $check_sql = "SELECT id FROM sentences WHERE content = ?";
    $check_query = $mysql->prepare($check_sql);

    $insert_sql = "INSERT INTO sentences (content) VALUES (?)";
    $insert_query = $mysql->prepare($insert_sql);

    foreach ($sentences as $sentence) {
        $check_query->bind_param("s", $sentence);
        $check_query->execute();
        $result = $check_query->get_result();

        if ($result->fetch_assoc()) {
            $sentences_skipped++;
            continue;
        }

        $insert_query->bind_param("s", $sentence);
        $insert_query->execute();
        $sentences_added++;
    }

    echo json_encode([
        "success" => true,
        "message" => "sentences seeded successfully",
        "data" => [
            "sentences_added" => $sentences_added,
            "sentences_skipped" => $sentences_skipped
        ]
    ]);

?>
