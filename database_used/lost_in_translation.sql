-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 14, 2026 at 08:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lost_in_translation`
--

-- --------------------------------------------------------

--
-- Table structure for table `guesses`
--

CREATE TABLE `guesses` (
  `id` int(11) NOT NULL,
  `round_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `guess` varchar(255) NOT NULL,
  `similarity_score` decimal(5,4) NOT NULL,
  `result` varchar(255) NOT NULL,
  `final_score` int(11) NOT NULL,
  `hints_used` int(11) NOT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guesses`
--

INSERT INTO `guesses` (`id`, `round_id`, `player_id`, `guess`, `similarity_score`, `result`, `final_score`, `hints_used`, `submitted_at`) VALUES
(1, 3, 1, 'Hello, I\'m fine', 0.5625, 'wrong', 0, 0, '2026-08-14 20:40:53'),
(2, 3, 1, 'Hi, I\'m fine', 0.3125, 'wrong', 0, 0, '2026-08-14 20:40:58'),
(3, 3, 1, 'Hello, I\'m doing good', 0.4737, 'wrong', 0, 0, '2026-08-14 20:41:05'),
(4, 3, 1, 'Hello, I\'m good', 0.6250, 'close', 0, 0, '2026-08-14 20:41:09'),
(5, 3, 1, 'Hello, I\'m great', 0.8750, 'correct', 66, 0, '2026-08-14 20:41:12'),
(6, 4, 1, 'Hello I\'m great', 0.8750, 'correct', 90, 0, '2026-08-14 20:43:48'),
(7, 5, 1, 'Hi I am great', 0.7500, 'close', 0, 3, '2026-08-14 20:44:12'),
(8, 5, 1, 'Hello I am great', 1.0000, 'correct', 0, 3, '2026-08-14 20:44:19'),
(9, 6, 1, 'Hello I\'m great', 0.8750, 'correct', 93, 0, '2026-08-14 20:44:52'),
(10, 7, 1, 'Hello I am Great', 1.0000, 'correct', 94, 0, '2026-08-14 20:47:19'),
(11, 8, 1, 'Hello I am great', 1.0000, 'correct', 94, 0, '2026-08-14 20:47:38'),
(12, 9, 1, 'It\'s better than no nuclear power.', 0.4063, 'wrong', 0, 0, '2026-08-14 20:53:42'),
(13, 9, 1, 'It\'s is not better than nuclear power.', 0.3611, 'wrong', 0, 0, '2026-08-14 20:53:53'),
(14, 9, 1, 'It\'s not better than nuclear power.', 0.3939, 'wrong', 0, 0, '2026-08-14 20:53:57'),
(15, 9, 1, 'not better than nuclear power.', 0.4138, 'wrong', 0, 0, '2026-08-14 20:54:09'),
(16, 10, 1, 'Reveal the secret.', 0.2692, 'wrong', 0, 0, '2026-08-14 20:54:53'),
(17, 10, 1, 'secret', 0.1154, 'wrong', 0, 0, '2026-08-14 20:55:29'),
(18, 10, 1, 'reveal all your hidden truths', 0.2069, 'wrong', 0, 0, '2026-08-14 20:55:39'),
(19, 13, 1, 'Sit on the edge of the fence', 0.5714, 'wrong', 0, 1, '2026-08-14 21:03:44'),
(20, 13, 1, 'Sit on the fence', 1.0000, 'correct', 34, 1, '2026-08-14 21:04:05');

-- --------------------------------------------------------

--
-- Table structure for table `hall_of_fame_votes`
--

CREATE TABLE `hall_of_fame_votes` (
  `id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `round_id` int(11) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hall_of_fame_votes`
--

INSERT INTO `hall_of_fame_votes` (`id`, `player_id`, `round_id`, `created_at`) VALUES
(1, 1, 3, '2026-08-14'),
(20, 1, 10, '2026-08-14');

-- --------------------------------------------------------

--
-- Table structure for table `hint_usage`
--

CREATE TABLE `hint_usage` (
  `id` int(11) NOT NULL,
  `round_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `step_number` int(11) NOT NULL,
  `used_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hint_usage`
--

INSERT INTO `hint_usage` (`id`, `round_id`, `player_id`, `step_number`, `used_at`) VALUES
(1, 5, 1, 5, '2026-08-14 20:43:56'),
(2, 5, 1, 4, '2026-08-14 20:43:59'),
(3, 5, 1, 3, '2026-08-14 20:44:00'),
(4, 13, 1, 0, '2026-08-14 21:03:33');

-- --------------------------------------------------------

--
-- Table structure for table `rounds`
--

CREATE TABLE `rounds` (
  `id` int(11) NOT NULL,
  `sentence_id` int(11) NOT NULL,
  `final_translation` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'in_progress',
  `started_at` datetime NOT NULL DEFAULT current_timestamp(),
  `ends_at` datetime NOT NULL,
  `score` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rounds`
--

INSERT INTO `rounds` (`id`, `sentence_id`, `final_translation`, `status`, `started_at`, `ends_at`, `score`) VALUES
(1, 1, 'Temporary mangled English sentence', 'open', '2026-08-13 14:32:16', '2026-08-13 14:33:16', 0),
(2, 1, 'Hi, I\'m fine.', 'open', '2026-08-14 20:08:35', '2026-08-14 20:09:35', 69),
(3, 1, 'Hi, I\'m fine.', 'closed', '2026-08-14 20:40:38', '2026-08-14 20:41:38', 69),
(4, 1, 'Hi, I\'m fine.', 'closed', '2026-08-14 20:43:38', '2026-08-14 20:44:38', 69),
(5, 1, 'Hi, I\'m fine.', 'closed', '2026-08-14 20:43:54', '2026-08-14 20:44:54', 69),
(6, 1, 'Hi, I\'m fine.', 'closed', '2026-08-14 20:44:45', '2026-08-14 20:45:45', 69),
(7, 1, 'Hi, I\'m fine.', 'closed', '2026-08-14 20:47:13', '2026-08-14 20:48:13', 69),
(8, 1, 'Hi, I\'m fine.', 'closed', '2026-08-14 20:47:32', '2026-08-14 20:48:32', 69),
(9, 5, 'It\'s better than no nuclear power.', 'closed', '2026-08-14 20:53:13', '2026-08-14 20:54:13', 59),
(10, 11, 'Reveal the secret.', 'closed', '2026-08-14 20:54:41', '2026-08-14 20:55:41', 73),
(11, 38, '- You\'re right.', 'open', '2026-08-14 20:59:55', '2026-08-14 21:00:55', 73),
(12, 26, 'Things are getting more complicated.', 'open', '2026-08-14 21:00:03', '2026-08-14 21:01:03', 74),
(13, 32, 'Stay on the fence', 'closed', '2026-08-14 21:03:29', '2026-08-14 21:04:29', 18);

-- --------------------------------------------------------

--
-- Table structure for table `round_steps`
--

CREATE TABLE `round_steps` (
  `id` int(11) NOT NULL,
  `round_id` int(11) NOT NULL,
  `step_number` int(11) NOT NULL,
  `from_language` varchar(255) NOT NULL,
  `to_language` varchar(255) NOT NULL,
  `translated_text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `round_steps`
--

INSERT INTO `round_steps` (`id`, `round_id`, `step_number`, `from_language`, `to_language`, `translated_text`) VALUES
(1, 1, 1, 'en', 'ja', 'Temporary Japanese result'),
(2, 1, 2, 'ja', 'ar', 'Temporary Arabic result'),
(3, 1, 3, 'ar', 'fi', 'Temporary Finnish result'),
(4, 1, 4, 'fi', 'sw', 'Temporary Swahili result'),
(5, 1, 5, 'sw', 'hu', 'Temporary Hungarian result'),
(6, 1, 6, 'hu', 'ko', 'Temporary Korean result'),
(7, 1, 7, 'ko', 'en', 'Temporary final English result'),
(8, 2, 1, 'en', 'ja', 'こんにちは、私は素晴らしいです'),
(9, 2, 2, 'ja', 'ar', 'مرحباً، أنا رائعة'),
(10, 2, 3, 'ar', 'fi', 'Hei, olen kunnossa.'),
(11, 2, 4, 'fi', 'sw', 'Habari, Niko sawa.'),
(12, 2, 5, 'sw', 'hu', 'Szia, jól vagyok.'),
(13, 2, 6, 'hu', 'ko', '안녕하세요, 저는 괜찮습니다.'),
(14, 2, 7, 'ko', 'en', 'Hi, I\'m fine.'),
(15, 3, 1, 'en', 'ja', 'こんにちは、私は素晴らしいです'),
(16, 3, 2, 'ja', 'ar', 'مرحباً، أنا رائعة'),
(17, 3, 3, 'ar', 'fi', 'Hei, olen kunnossa.'),
(18, 3, 4, 'fi', 'sw', 'Habari, Niko sawa.'),
(19, 3, 5, 'sw', 'hu', 'Szia, jól vagyok.'),
(20, 3, 6, 'hu', 'ko', '안녕하세요, 저는 괜찮습니다.'),
(21, 3, 7, 'ko', 'en', 'Hi, I\'m fine.'),
(22, 4, 1, 'en', 'ja', 'こんにちは、私は素晴らしいです'),
(23, 4, 2, 'ja', 'ar', 'مرحباً، أنا رائعة'),
(24, 4, 3, 'ar', 'fi', 'Hei, olen kunnossa.'),
(25, 4, 4, 'fi', 'sw', 'Habari, Niko sawa.'),
(26, 4, 5, 'sw', 'hu', 'Szia, jól vagyok.'),
(27, 4, 6, 'hu', 'ko', '안녕하세요, 저는 괜찮습니다.'),
(28, 4, 7, 'ko', 'en', 'Hi, I\'m fine.'),
(29, 5, 1, 'en', 'ja', 'こんにちは、私は素晴らしいです'),
(30, 5, 2, 'ja', 'ar', 'مرحباً، أنا رائعة'),
(31, 5, 3, 'ar', 'fi', 'Hei, olen kunnossa.'),
(32, 5, 4, 'fi', 'sw', 'Habari, Niko sawa.'),
(33, 5, 5, 'sw', 'hu', 'Szia, jól vagyok.'),
(34, 5, 6, 'hu', 'ko', '안녕하세요, 저는 괜찮습니다.'),
(35, 5, 7, 'ko', 'en', 'Hi, I\'m fine.'),
(36, 6, 1, 'en', 'ja', 'こんにちは、私は素晴らしいです'),
(37, 6, 2, 'ja', 'ar', 'مرحباً، أنا رائعة'),
(38, 6, 3, 'ar', 'fi', 'Hei, olen kunnossa.'),
(39, 6, 4, 'fi', 'sw', 'Habari, Niko sawa.'),
(40, 6, 5, 'sw', 'hu', 'Szia, jól vagyok.'),
(41, 6, 6, 'hu', 'ko', '안녕하세요, 저는 괜찮습니다.'),
(42, 6, 7, 'ko', 'en', 'Hi, I\'m fine.'),
(43, 7, 1, 'en', 'ja', 'こんにちは、私は素晴らしいです'),
(44, 7, 2, 'ja', 'ar', 'مرحباً، أنا رائعة'),
(45, 7, 3, 'ar', 'fi', 'Hei, olen kunnossa.'),
(46, 7, 4, 'fi', 'sw', 'Habari, Niko sawa.'),
(47, 7, 5, 'sw', 'hu', 'Szia, jól vagyok.'),
(48, 7, 6, 'hu', 'ko', '안녕하세요, 저는 괜찮습니다.'),
(49, 7, 7, 'ko', 'en', 'Hi, I\'m fine.'),
(50, 8, 1, 'en', 'ja', 'こんにちは、私は素晴らしいです'),
(51, 8, 2, 'ja', 'ar', 'مرحباً، أنا رائعة'),
(52, 8, 3, 'ar', 'fi', 'Hei, olen kunnossa.'),
(53, 8, 4, 'fi', 'sw', 'Habari, Niko sawa.'),
(54, 8, 5, 'sw', 'hu', 'Szia, jól vagyok.'),
(55, 8, 6, 'hu', 'ko', '안녕하세요, 저는 괜찮습니다.'),
(56, 8, 7, 'ko', 'en', 'Hi, I\'m fine.'),
(57, 9, 1, 'en', 'ja', '原子力発電もないよりマシ'),
(58, 9, 2, 'ja', 'ar', 'إنها أفضل من عدم وجود طاقة نووية.'),
(59, 9, 3, 'ar', 'fi', 'Se on parempi kuin ei ydinvoimaa.'),
(60, 9, 4, 'fi', 'sw', 'Ni bora kuliko hakuna nishati ya nyuklia.'),
(61, 9, 5, 'sw', 'hu', 'Jobb, mintha nem lenne atomenergia.'),
(62, 9, 6, 'hu', 'ko', '원자력이 없는 것보다는 낫다.'),
(63, 9, 7, 'ko', 'en', 'It\'s better than no nuclear power.'),
(64, 10, 1, 'en', 'ja', '秘密を漏らす。'),
(65, 10, 2, 'ja', 'ar', 'أفصح عن السر.'),
(66, 10, 3, 'ar', 'fi', 'Paljasta salaisuus.'),
(67, 10, 4, 'fi', 'sw', 'Funua siri.'),
(68, 10, 5, 'sw', 'hu', 'Fedje fel a titkot.'),
(69, 10, 6, 'hu', 'ko', '비밀을 밝혀라.'),
(70, 10, 7, 'ko', 'en', 'Reveal the secret.'),
(71, 11, 1, 'en', 'ja', 'あなたのいう通りですよ。'),
(72, 11, 2, 'ja', 'ar', 'أنت على حق.'),
(73, 11, 3, 'ar', 'fi', '- Oikeassa olet.'),
(74, 11, 4, 'fi', 'sw', '- Uko sahihi.'),
(75, 11, 5, 'sw', 'hu', '- Igazad van.'),
(76, 11, 6, 'hu', 'ko', '- 당신은 맞다.'),
(77, 11, 7, 'ko', 'en', '- You\'re right.'),
(78, 12, 1, 'en', 'ja', '手に負えなくなる'),
(79, 12, 2, 'ja', 'ar', 'يخرج الأمر عن السيطرة'),
(80, 12, 3, 'ar', 'fi', 'Tilanne riistäytyy käsistä'),
(81, 12, 4, 'fi', 'sw', 'Mambo yanazidi kuwa magumu'),
(82, 12, 5, 'sw', 'hu', 'A dolgok egyre bonyolultabbá válnak'),
(83, 12, 6, 'hu', 'ko', '상황이 점점 더 복잡해지고 있습니다.'),
(84, 12, 7, 'ko', 'en', 'Things are getting more complicated.'),
(85, 13, 1, 'en', 'ja', 'フェンスの上に座る'),
(86, 13, 2, 'ja', 'ar', 'الجلوس على السياج'),
(87, 13, 3, 'ar', 'fi', 'Aidalla istuminen'),
(88, 13, 4, 'fi', 'sw', 'Kukaa kwenye uzio'),
(89, 13, 5, 'sw', 'hu', 'Maradok a kerítésen'),
(90, 13, 6, 'hu', 'ko', '울타리에 머물러라'),
(91, 13, 7, 'ko', 'en', 'Stay on the fence');

-- --------------------------------------------------------

--
-- Table structure for table `sentences`
--

CREATE TABLE `sentences` (
  `id` int(11) NOT NULL,
  `content` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sentences`
--

INSERT INTO `sentences` (`id`, `content`) VALUES
(1, 'hello I am great'),
(2, 'Break a leg'),
(3, 'It\'s raining cats and dogs'),
(4, 'The early bird catches the worm'),
(5, 'Better late than never'),
(6, 'Actions speak louder than words'),
(7, 'A picture is worth a thousand words'),
(8, 'Don\'t judge a book by its cover'),
(9, 'The best of both worlds'),
(10, 'Hit the nail on the head'),
(11, 'Let the cat out of the bag'),
(12, 'Bite the bullet'),
(13, 'Call it a day'),
(14, 'Get your act together'),
(15, 'Go back to the drawing board'),
(16, 'Hang in there'),
(17, 'Miss the boat'),
(18, 'Pull yourself together'),
(19, 'Speak of the devil'),
(20, 'Under the weather'),
(21, 'Your guess is as good as mine'),
(22, 'A blessing in disguise'),
(23, 'Beat around the bush'),
(24, 'Burn the midnight oil'),
(25, 'Cutting corners'),
(26, 'Get out of hand'),
(27, 'Make a long story short'),
(28, 'No pain, no gain'),
(29, 'On the same page'),
(30, 'Pull someone\'s leg'),
(31, 'See eye to eye'),
(32, 'Sit on the fence'),
(33, 'Take it with a grain of salt'),
(34, 'The ball is in your court'),
(35, 'Through thick and thin'),
(36, 'Time flies when you\'re having fun'),
(37, 'Wrap your head around it'),
(38, 'You can say that again'),
(39, 'Every cloud has a silver lining'),
(40, 'When pigs fly'),
(41, 'Once in a blue moon');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `username`, `created_at`) VALUES
(1, 'minecraft@gmail.com', '$2y$10$RVsCk5Jm7F22Tq7Ud8NIleRHpFpk2erHPxmDL0k1rOhXZEH96RJT2', 'Terraria', '2026-08-14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guesses`
--
ALTER TABLE `guesses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hall_of_fame_votes`
--
ALTER TABLE `hall_of_fame_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `player_id` (`player_id`,`round_id`);

--
-- Indexes for table `hint_usage`
--
ALTER TABLE `hint_usage`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `round_id` (`round_id`,`player_id`,`step_number`);

--
-- Indexes for table `rounds`
--
ALTER TABLE `rounds`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `round_steps`
--
ALTER TABLE `round_steps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sentences`
--
ALTER TABLE `sentences`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guesses`
--
ALTER TABLE `guesses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `hall_of_fame_votes`
--
ALTER TABLE `hall_of_fame_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `hint_usage`
--
ALTER TABLE `hint_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rounds`
--
ALTER TABLE `rounds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `round_steps`
--
ALTER TABLE `round_steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `sentences`
--
ALTER TABLE `sentences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
