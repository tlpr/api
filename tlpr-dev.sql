
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tlpr-dev`
--

-- --------------------------------------------------------

--
-- Table structure for table `invitations`
--

CREATE TABLE `invitations` (
  `id` int(11) NOT NULL COMMENT 'Unique invitation ID',
  `issued_date` int(11) NOT NULL COMMENT 'UNIX timestamp of date when the code has been generated',
  `issuer` int(11) NOT NULL COMMENT 'User''s ID who has issued invite code',
  `code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique invite code',
  `is_used` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Boolean if the invite code has been already used to create a new user',
  `new_user` int(11) DEFAULT NULL COMMENT 'New user''s ID, NULL if code is not used yet'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL COMMENT 'Status ID',
  `song_id` int(11) NOT NULL COMMENT 'Song ID status is related to',
  `user_id` int(11) NOT NULL COMMENT 'User ID status is related to',
  `status` int(11) NOT NULL COMMENT 'Status, 0 - dislike, 1 - like'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `songs`
--

CREATE TABLE `songs` (
  `id` int(11) NOT NULL COMMENT 'Unique song''s ID',
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Full song title including artist name',
  `album` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Album title',
  `cover_image_url` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Album cover image URL'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL COMMENT 'User''s unique ID',
  `nickname` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Public unique username representing the user',
  `email` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Optional e-mail address used for account recovery',
  `password` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hashed user password',
  `permissions` int(11) NOT NULL DEFAULT 1 COMMENT 'Permissions level of user, 0 is banned, 1 is regular, 2 is special, 3 is full access',
  `register_ip` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'IP address used during the registration',
  `last_login_ip` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'IP address used during last log-in',
  `last_login_date` int(11) NOT NULL COMMENT 'UNIX timestamp when last log-in occured',
  `avatar_url` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL address to the user''s profile picture',
  `totp_key` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'User''s TOTP secret key, empty if 2FA disabled',
  `oauth2` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'User''s generated oAuth2 key to access the REST API, empty if didn''t requested one'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `invitations`
--
ALTER TABLE `invitations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nickname` (`nickname`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `invitations`
--
ALTER TABLE `invitations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique invitation ID';

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Status ID';

--
-- AUTO_INCREMENT for table `songs`
--
ALTER TABLE `songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique song''s ID';

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'User''s unique ID';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

