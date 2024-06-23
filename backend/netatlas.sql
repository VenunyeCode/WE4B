-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : jeu. 02 mai 2024 à 19:28
-- Version du serveur : 5.7.39
-- Version de PHP : 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;

--
-- Base de données : `netatlas`
--

-- --------------------------------------------------------

--
-- Structure de la table `bookmark`
--

CREATE TABLE `bookmark` (
    `id_bookmark` int(11) NOT NULL,
    `id_post` int(11) DEFAULT NULL,
    `id_user` int(11) DEFAULT NULL,
    `bookmarked_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `followship`
--

CREATE TABLE `followship` (
    `id_followship` int(11) NOT NULL,
    `id_user_following` int(11) DEFAULT NULL,
    `id_user_follower` int(11) DEFAULT NULL,
    `follow_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `interdiction`
--

CREATE TABLE `interdiction` (
    `id_interdiction` int(11) NOT NULL,
    `message` varchar(280) DEFAULT NULL,
    `actif` tinyint(1) DEFAULT NULL,
    `interdiction_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `code_interdiction_type` varchar(20) DEFAULT NULL,
    `id_user` int(11) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `interdiction_type`
--

CREATE TABLE `interdiction_type` (
    `code` varchar(20) NOT NULL,
    `libelle` varchar(20) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `media`
--

CREATE TABLE `media` (
    `id_media` int(11) NOT NULL,
    `libelle_media` varchar(50) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
    `id_message` int(11) NOT NULL,
    `content_message` varchar(255) NOT NULL,
    `message_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `is_message_read` tinyint(1) NOT NULL DEFAULT '0',
    `id_user_sender` int(11) DEFAULT NULL,
    `id_user_receiver` int(11) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE `notification` (
    `id_notification` int(11) NOT NULL,
    `content_notification` varchar(50) NOT NULL,
    `notification_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `is_notification_read` tinyint(1) NOT NULL DEFAULT '0',
    `id_post` int(11) DEFAULT NULL,
    `id_user` int(11) DEFAULT NULL,
    `id_interdiction` int(11) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `permission`
--

CREATE TABLE `permission` (
    `code_permission` varchar(10) NOT NULL,
    `label_permission` varchar(50) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `post`
--

CREATE TABLE `post` (
    `id_post` int(11) NOT NULL,
    `content_post` text,
    `post_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `likes` int(11) DEFAULT '0',
    `reposts` int(11) DEFAULT '0',
    `views` int(11) DEFAULT '0',
    `removed` tinyint(1) NOT NULL DEFAULT '0',
    `media_post` varchar(255) DEFAULT NULL,
    `id_post_comment` int(11) DEFAULT NULL,
    `id_post_repost` int(11) DEFAULT NULL,
    `id_user` int(11) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

--
-- Déchargement des données de la table `post`
--

INSERT INTO `post` (`id_post`, `content_post`, `post_date`, `likes`, `reposts`, `views`, `removed`, `media_post`, `id_post_comment`, `id_post_repost`, `id_user`) VALUES
(1, 'Premier post', '2024-04-18 12:45:16', 0, 0, 0, 0, NULL, NULL, NULL, 1),
(26, 'Tmt', '2024-04-18 16:17:12', 0, 0, 0, 0, 'uploads/1_041824141712.png', NULL, NULL, 1),
(27, 'TMP', '2024-04-18 16:19:24', 0, 0, 1, 0, '', NULL, NULL, 1),
(28, 'TMPIMG', '2024-04-18 16:20:12', 0, 0, 0, 0, 'uploads/1_041824142012.png', NULL, NULL, 1),
(29, 'KDB', '2024-04-18 16:23:32', 0, 0, 0, 0, 'uploads/1_041824142332.png', NULL, NULL, 1),
(30, 'testimg', '2024-04-18 16:28:52', 1, 0, 1, 0, 'uploads/1_041824142852.jpg', NULL, NULL, 1),
(31, 'Ce post est bien. Continuez comme ça.', '2024-04-18 20:49:05', 0, 0, 0, 0, NULL, 30, NULL, 1),
(33, 'Premier commentaire sur interface', '2024-04-25 03:10:12', 0, 0, 0, 0, NULL, 30, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `post_like`
--

CREATE TABLE `post_like` (
    `id_post_like` int(11) NOT NULL,
    `id_post` int(11) DEFAULT NULL,
    `id_user` int(11) DEFAULT NULL,
    `like_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

--
-- Déchargement des données de la table `post_like`
--

INSERT INTO
    `post_like` (
        `id_post_like`,
        `id_post`,
        `id_user`,
        `like_date`
    )
VALUES (
        7,
        30,
        1,
        '2024-04-19 05:11:50'
    );

-- --------------------------------------------------------

--
-- Structure de la table `post_media`
--

CREATE TABLE `post_media` (
    `id_post_media` int(11) NOT NULL,
    `post_media_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `id_media` int(11) DEFAULT NULL,
    `id_post` int(11) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `post_tag`
--

CREATE TABLE `post_tag` (
    `id_post` int(11) NOT NULL,
    `id_tag` int(11) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `post_view`
--

CREATE TABLE `post_view` (
  `id_post_view` int(11) NOT NULL,
  `id_post` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `view_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `post_view`
--

INSERT INTO `post_view` (`id_post_view`, `id_post`, `id_user`, `view_date`) VALUES
(4, 30, 1, '2024-04-29 15:13:54'),
(5, 27, 1, '2024-04-29 15:16:07');

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

CREATE TABLE `role` (
    `code_role` varchar(10) NOT NULL,
    `label_role` varchar(50) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

--
-- Déchargement des données de la table `role`
--

INSERT INTO
    `role` (`code_role`, `label_role`)
VALUES ('ADMIN', 'Administrateur'),
    ('USER', 'Utilisateur');

-- --------------------------------------------------------

--
-- Structure de la table `role_permission`
--

CREATE TABLE `role_permission` (
    `code_role` varchar(10) NOT NULL,
    `code_permission` varchar(10) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `sensitive_word`
--

CREATE TABLE `sensitive_word` (
    `id_sensitive_word` int(11) NOT NULL,
    `libelle_sensitive_word` varchar(50) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `system_info`
--

CREATE TABLE `system_info` (
    `id` int(30) NOT NULL,
    `meta_field` varchar(50) NOT NULL,
    `meta_value` varchar(50) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

--
-- Déchargement des données de la table `system_info`
--

INSERT INTO `system_info` (`id`, `meta_field`, `meta_value`) VALUES
(1, 'name', 'Réseau Social Universitaire Winku'),
(6, 'short_name', 'Winku'),
(11, 'logo', 'uploads/logo.png'),
(13, 'user_avatar', 'uploads/user_avatar.jpg'),
(14, 'cover', 'uploads/cover.png');

-- --------------------------------------------------------

--
-- Structure de la table `tag`
--

CREATE TABLE `tag` (
    `id_tag` int(11) NOT NULL,
    `name` varchar(30) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `phone` varchar(13) DEFAULT NULL,
  `username` varchar(10) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `password` varchar(50) NOT NULL,
  `self_intro` varchar(500) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `cover_photo` varchar(100) DEFAULT NULL,
  `banned_forever` tinyint(1) DEFAULT NULL,
  `banned_temporarly` tinyint(1) DEFAULT NULL,
  `connected` tinyint(1) NOT NULL DEFAULT '0',
  `last_login` datetime DEFAULT NULL,
  `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `address` varchar(50) DEFAULT NULL,
  `code_role` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id_user`, `firstname`, `lastname`, `phone`, `username`, `email`, `password`, `self_intro`, `avatar`, `cover_photo`, `banned_forever`, `banned_temporarly`, `connected`, `last_login`, `date_added`, `address`, `code_role`) VALUES
(1, 'Soklibou', 'KADABA', '+330768699042', 'Joelkdb', 'soklibou.kadaba@utbm.fr', '0192023a7bbd73250516f069df18b500', 'Je suis un futur ingénieur informaticien actuellement étudiant à l\'université de technologie de Belfort-Montbéliard.', 'uploads/member/3.png?v=1712256740', NULL, 0, 0, 1, NULL, '2024-04-11 18:35:53', NULL, 'USER');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `bookmark`
--
ALTER TABLE `bookmark`
ADD PRIMARY KEY (`id_bookmark`),
ADD KEY `fk_post_bookmark_id` (`id_post`),
ADD KEY `fk_user_bookmark_id` (`id_user`);

--
-- Index pour la table `followship`
--
ALTER TABLE `followship`
ADD PRIMARY KEY (`id_followship`),
ADD KEY `fk_followship_user_following_id` (`id_user_following`),
ADD KEY `fk_followship_user_follower_id` (`id_user_follower`);

--
-- Index pour la table `interdiction`
--
ALTER TABLE `interdiction`
ADD PRIMARY KEY (`id_interdiction`),
ADD KEY `fk_interdiction_code` (`code_interdiction_type`),
ADD KEY `fk_user_interdiction_id` (`id_user`);

--
-- Index pour la table `interdiction_type`
--
ALTER TABLE `interdiction_type` ADD PRIMARY KEY (`code`);

--
-- Index pour la table `media`
--
ALTER TABLE `media` ADD PRIMARY KEY (`id_media`);

--
-- Index pour la table `message`
--
ALTER TABLE `message`
ADD PRIMARY KEY (`id_message`),
ADD KEY `fk_messagge_user_sender_id` (`id_user_sender`),
ADD KEY `fk_messagge_user_receiver_id` (`id_user_receiver`);

--
-- Index pour la table `notification`
--
ALTER TABLE `notification`
ADD PRIMARY KEY (`id_notification`),
ADD KEY `fk_post_notification_id` (`id_post`),
ADD KEY `fk_user_notification_id` (`id_user`),
ADD KEY `fk_interdiction_notification_id` (`id_interdiction`);

--
-- Index pour la table `permission`
--
ALTER TABLE `permission` ADD PRIMARY KEY (`code_permission`);

--
-- Index pour la table `post`
--
ALTER TABLE `post`
ADD PRIMARY KEY (`id_post`),
ADD KEY `fk_post_comment_id` (`id_post_comment`),
ADD KEY `fk_post_repost_id` (`id_post_repost`),
ADD KEY `fk_post_author` (`id_user`);

--
-- Index pour la table `post_like`
--
ALTER TABLE `post_like`
ADD PRIMARY KEY (`id_post_like`),
ADD KEY `fk_post_like_id` (`id_post`),
ADD KEY `fk_user_like_id` (`id_user`);

--
-- Index pour la table `post_media`
--
ALTER TABLE `post_media`
ADD PRIMARY KEY (`id_post_media`),
ADD KEY `fk_media_id` (`id_media`),
ADD KEY `fk_post_id` (`id_post`);

--
-- Index pour la table `post_tag`
--
ALTER TABLE `post_tag`
ADD PRIMARY KEY (`id_post`, `id_tag`),
ADD KEY `fk_tag` (`id_tag`);

--
-- Index pour la table `post_view`
--
ALTER TABLE `post_view`
ADD PRIMARY KEY (`id_post_view`),
ADD KEY `fk_post_view_id` (`id_post`),
ADD KEY `fk_post_user_id` (`id_user`);

--
-- Index pour la table `role`
--
ALTER TABLE `role` ADD PRIMARY KEY (`code_role`);

--
-- Index pour la table `role_permission`
--
ALTER TABLE `role_permission`
ADD PRIMARY KEY (
    `code_role`,
    `code_permission`
),
ADD KEY `fk_permission_id` (`code_permission`);

--
-- Index pour la table `sensitive_word`
--
ALTER TABLE `sensitive_word` ADD PRIMARY KEY (`id_sensitive_word`);

--
-- Index pour la table `system_info`
--
ALTER TABLE `system_info` ADD PRIMARY KEY (`id`);

--
-- Index pour la table `tag`
--
ALTER TABLE `tag` ADD PRIMARY KEY (`id_tag`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
ADD PRIMARY KEY (`id_user`),
ADD UNIQUE KEY `phone` (`phone`),
ADD UNIQUE KEY `username` (`username`),
ADD UNIQUE KEY `email` (`email`),
ADD KEY `fk_user_role_code` (`code_role`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `bookmark`
--
ALTER TABLE `bookmark`
MODIFY `id_bookmark` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `followship`
--
ALTER TABLE `followship`
MODIFY `id_followship` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `interdiction`
--
ALTER TABLE `interdiction`
MODIFY `id_interdiction` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `media`
--
ALTER TABLE `media`
MODIFY `id_media` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `message`
--
ALTER TABLE `message`
MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notification`
--
ALTER TABLE `notification`
MODIFY `id_notification` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `post`
--
ALTER TABLE `post`
  MODIFY `id_post` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `post_like`
--
ALTER TABLE `post_like`
MODIFY `id_post_like` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 8;

--
-- AUTO_INCREMENT pour la table `post_media`
--
ALTER TABLE `post_media`
MODIFY `id_post_media` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `post_view`
--
ALTER TABLE `post_view`
  MODIFY `id_post_view` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `sensitive_word`
--
ALTER TABLE `sensitive_word`
MODIFY `id_sensitive_word` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `tag`
--
ALTER TABLE `tag` MODIFY `id_tag` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `bookmark`
--
ALTER TABLE `bookmark`
ADD CONSTRAINT `fk_post_bookmark_id` FOREIGN KEY (`id_post`) REFERENCES `post` (`id_post`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_user_bookmark_id` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `followship`
--
ALTER TABLE `followship`
ADD CONSTRAINT `fk_followship_user_follower_id` FOREIGN KEY (`id_user_follower`) REFERENCES `user` (`id_user`),
ADD CONSTRAINT `fk_followship_user_following_id` FOREIGN KEY (`id_user_following`) REFERENCES `user` (`id_user`);

--
-- Contraintes pour la table `interdiction`
--
ALTER TABLE `interdiction`
ADD CONSTRAINT `fk_interdiction_code` FOREIGN KEY (`code_interdiction_type`) REFERENCES `interdiction_type` (`code`),
ADD CONSTRAINT `fk_user_interdiction_id` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Contraintes pour la table `message`
--
ALTER TABLE `message`
ADD CONSTRAINT `fk_messagge_user_receiver_id` FOREIGN KEY (`id_user_receiver`) REFERENCES `user` (`id_user`),
ADD CONSTRAINT `fk_messagge_user_sender_id` FOREIGN KEY (`id_user_sender`) REFERENCES `user` (`id_user`);

--
-- Contraintes pour la table `notification`
--
ALTER TABLE `notification`
ADD CONSTRAINT `fk_interdiction_notification_id` FOREIGN KEY (`id_interdiction`) REFERENCES `interdiction` (`id_interdiction`),
ADD CONSTRAINT `fk_post_notification_id` FOREIGN KEY (`id_post`) REFERENCES `post` (`id_post`),
ADD CONSTRAINT `fk_user_notification_id` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Contraintes pour la table `post`
--
ALTER TABLE `post`
ADD CONSTRAINT `fk_post_author` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
ADD CONSTRAINT `fk_post_comment_id` FOREIGN KEY (`id_post_comment`) REFERENCES `post` (`id_post`),
ADD CONSTRAINT `fk_post_repost_id` FOREIGN KEY (`id_post_repost`) REFERENCES `post` (`id_post`);

--
-- Contraintes pour la table `post_like`
--
ALTER TABLE `post_like`
ADD CONSTRAINT `fk_post_like_id` FOREIGN KEY (`id_post`) REFERENCES `post` (`id_post`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_user_like_id` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `post_media`
--
ALTER TABLE `post_media`
ADD CONSTRAINT `fk_media_id` FOREIGN KEY (`id_media`) REFERENCES `media` (`id_media`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_post_id` FOREIGN KEY (`id_post`) REFERENCES `post` (`id_post`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `post_tag`
--
ALTER TABLE `post_tag`
ADD CONSTRAINT `fk_post` FOREIGN KEY (`id_post`) REFERENCES `post` (`id_post`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_tag` FOREIGN KEY (`id_tag`) REFERENCES `tag` (`id_tag`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `post_view`
--
ALTER TABLE `post_view`
ADD CONSTRAINT `fk_post_user_id` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_post_view_id` FOREIGN KEY (`id_post`) REFERENCES `post` (`id_post`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `role_permission`
--
ALTER TABLE `role_permission`
ADD CONSTRAINT `fk_permission_id` FOREIGN KEY (`code_permission`) REFERENCES `permission` (`code_permission`),
ADD CONSTRAINT `fk_role_id` FOREIGN KEY (`code_role`) REFERENCES `role` (`code_role`);

--
-- Contraintes pour la table `user`
--
ALTER TABLE `user`
ADD CONSTRAINT `fk_user_role_code` FOREIGN KEY (`code_role`) REFERENCES `role` (`code_role`);

  ALTER TABLE 'post'
  ADD COLUMN is_post_sensitive BOOLEAN DEFAULT FALSE;

INSERT INTO `interdiction_type` (`code`, `libelle`) VALUES
  ('AVERTIR', 'Avertissement'),
  ('BANNED_TMP', 'Exclusion temporaire'),
  ('SENSIBLE', 'Sensibilité');

ALTER TABLE interdiction
ADD DELAY INT;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;

