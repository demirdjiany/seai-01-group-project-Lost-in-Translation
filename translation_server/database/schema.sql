-- Test schema for local development.
-- Reconstructed from the columns the API files already query against
-- (create_round.php, get_round.php, add_guess.php, use_hint.php, add_vote.php,
-- get_entries.php, add_user.php, get_user.php). Not the final authoritative
-- schema - if the real one differs, use that one instead.
--
-- To load it:
--   "C:\xampp\mysql\bin\mysql.exe" -u root < schema.sql

CREATE DATABASE IF NOT EXISTS lost_in_translation;
USE lost_in_translation;

CREATE TABLE sentences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content VARCHAR(255) NOT NULL
);

CREATE TABLE rounds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sentence_id INT NOT NULL,
    final_translation TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'open',
    started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ends_at DATETIME NOT NULL,
    score INT NOT NULL,
    FOREIGN KEY (sentence_id) REFERENCES sentences(id)
);

CREATE TABLE round_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    round_id INT NOT NULL,
    step_number INT NOT NULL,
    from_language VARCHAR(10) NOT NULL,
    to_language VARCHAR(10) NOT NULL,
    translated_text TEXT NOT NULL,
    FOREIGN KEY (round_id) REFERENCES rounds(id)
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    special_id INT NOT NULL UNIQUE,
    username VARCHAR(100) NOT NULL
);

CREATE TABLE guesses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    round_id INT NOT NULL,
    player_id INT NOT NULL,
    guess VARCHAR(255) NOT NULL,
    similarity_score DOUBLE NOT NULL,
    result VARCHAR(10) NOT NULL,
    final_score INT NOT NULL,
    hints_used INT NOT NULL,
    FOREIGN KEY (round_id) REFERENCES rounds(id)
);

CREATE TABLE hint_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    round_id INT NOT NULL,
    player_id INT NOT NULL,
    step_number INT NOT NULL,
    FOREIGN KEY (round_id) REFERENCES rounds(id)
);

CREATE TABLE hall_of_fame_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    round_id INT NOT NULL,
    FOREIGN KEY (round_id) REFERENCES rounds(id)
);

-- A few seeds so create_round.php has something to pick from.
-- The full 40-idiom seed table is Student B's to fill in.
INSERT INTO sentences (content) VALUES
    ("It's raining cats and dogs"),
    ("Break a leg"),
    ("He let the cat out of the bag"),
    ("The early bird catches the worm");
