-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
-- Host: localhost    Database: fs
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Fante Alphabets','The Fante alphabet (Mfantse Akyerewamba) consists of 22 letters based on the Latin script, excluding c, j, q, v, and x. It includes special characters ɛ (eh) and ɔ (oh) to represent specific vowel sounds. The alphabet is: A, B, D, E, Ɛ, F, G, H, I, K, L, M, N, O, Ɔ, P, R, S, T, U, W, Y, Z.'),(2,'Fante Dictionary','Fante, a major dialect of the Akan language spoken in Ghana, features a distinct vocabulary. Key words include greetings like Ewo ho? (How are you?), nouns such as nsu (water), eda (day), abofra (child), and verbs like kɔ (go), ba (come), and didi (eat). Understanding these words aids basic communication and language learning. '),(3,'Fante Phonetics','Fante is an Akan language dialect characterized by specific phonemic mappings (e.g., E/e represents /e/ or /ɪ/, Ɛ/ɛ is /ɛ/, Ɔ/ɔ is /ɔ/) and unique digraphs like hw (wh), hy (sh), dw (j), and ts (ts). Basic Fante vocabulary frequently uses vowel-initial structures and consistent consonant mapping. '),(4,'Fante History','The Fante people are a major Akan subgroup in Ghana\'s coastal Central Region, tracing their origins to a 13th-century migration from the Bono state led by three warriors: Obrumankoma, Odapagyan, and Oson. Known as "Fa-atsew" ("the half that left"), they established a powerful 17th-century confederacy at Mankessim, acting as key middlemen in coastal trade. '),(5,'Fante States','The Fante States are a group of Akan sub-groups in Ghana that historically organized into a powerful, decentralized alliance (the Fante Confederacy) centered around Mankessim. Founded by migrants from the Bono state, they included traditional states like Abora, Ekumfi, Enyan, Nkusukum, and Gomoa, which acted as intermediaries in coastal trade. '),(6,'Fante Artifacts ','Fante artifacts, originating from the coastal Akan people of Ghana, are characterized by vibrant, narrative-driven art, most notably Asafo flags, fertility dolls (abaaba) with distinct triangular heads, wood carvings, and brass weights. These items often serve functional, spiritual, and military purposes, reflecting community history, proverbs, and social status. '),(7,'Virtual Museums ','Asafo Flags: Known for their vibrant appliquéd and embroidery, these flags represent Fante military companies (Asafo) and are often displayed in museums to showcase artistic traditions and historical resistance'),(8,'Fante Ceremonies','Fante ceremonies are vibrant, communal, and deeply rooted in Akan customs, focusing on life-cycle transitions, ancestral veneration, and community unity. Key practices include the "knocking" and "Tri-nsa" marriage rites, 8-day baby naming ceremonies, elaborate funerals, and the annual Fetu Afahye festival. These events feature traditional drumming, the energetic Adzewa dance, and, for priestesses, unique "Akom" music. ');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fante_artifacts`
--

DROP TABLE IF EXISTS `fante_artifacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fante_artifacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved') DEFAULT 'pending',
  `user_id` int(11) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(255) DEFAULT 'General',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fante_artifacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fante_artifacts`
--

LOCK TABLES `fante_artifacts` WRITE;
/*!40000 ALTER TABLE `fante_artifacts` DISABLE KEYS */;
INSERT INTO `fante_artifacts` VALUES (5,'Test Fante Artifact','test_artifact.jpg','This is a test artifact for category linking verification.',NULL,'approved',13,'2026-02-12 13:21:19','General');
/*!40000 ALTER TABLE `fante_artifacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fante_ceremonies`
--

DROP TABLE IF EXISTS `fante_ceremonies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fante_ceremonies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved') DEFAULT 'pending',
  `user_id` int(11) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fante_ceremonies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fante_ceremonies`
--

LOCK TABLES `fante_ceremonies` WRITE;
/*!40000 ALTER TABLE `fante_ceremonies` DISABLE KEYS */;
INSERT INTO `fante_ceremonies` VALUES (2,'Mankessim Festival','1770842581_3warriors.jpg','The Mankessim Festival, known as the Borbor Mfantse Amansi Festival (formerly Akwambo), is a vibrant annual celebration held in the second week of September in Mankessim, Central Region, Ghana. It highlights Fante culture, featuring a grand durbar, the "Lemon Friday" street carnival, and traditional processions led by the paramount chief. Key Aspects of the Mankessim Festival: Significance: It serves to honor the heritage of the Borbor Mfantse people and reinforces the traditional paramountcy of Mankessim. Lemon Friday: A unique, energetic street carnival that typically starts early in the morning, serving as a major highlight of the festival. Cultural Display: The festival includes a grand durbar where chiefs, including the Omanhene (Paramount Chief) Osagyefo Amanfo Edu VI, display rich Fante culture, traditional attire, and customs. Timing: The festivities, including the Akwambo, are generally held during the second week of September. Asafo Companies: The event features performances and parades by Asafo companies (traditional warrior groups). The festival brings together residents and visitors to celebrate the history and unity of the Mankessim traditional area. ','1770842581_SigThe Mankessim Borbor.mp4','approved',13,'2026-02-11 20:43:01');
/*!40000 ALTER TABLE `fante_ceremonies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fante_dictionary`
--

DROP TABLE IF EXISTS `fante_dictionary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fante_dictionary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `meaning` text NOT NULL,
  `origin` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `pronunciation` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=274 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fante_dictionary`
-- (Only approved entries included)
--

LOCK TABLES `fante_dictionary` WRITE;
/*!40000 ALTER TABLE `fante_dictionary` DISABLE KEYS */;
INSERT INTO `fante_dictionary` VALUES (233,'Kyiwnam','Fried fish','Sea','1770030105_food8.jpg','1770030105_KyewNam.mp3','approved',14,13,'2026-02-02 11:01:45','2026-02-03 21:04:06');
/*!40000 ALTER TABLE `fante_dictionary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fante_history`
--

DROP TABLE IF EXISTS `fante_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fante_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `details` text NOT NULL,
  `video` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fante_history`
-- (No approved entries - table is empty)
--

LOCK TABLES `fante_history` WRITE;
/*!40000 ALTER TABLE `fante_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `fante_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fante_phonetics`
--

DROP TABLE IF EXISTS `fante_phonetics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fante_phonetics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `audio` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fante_phonetics`
-- (Table is empty)
--

LOCK TABLES `fante_phonetics` WRITE;
/*!40000 ALTER TABLE `fante_phonetics` DISABLE KEYS */;
/*!40000 ALTER TABLE `fante_phonetics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fante_states`
--

DROP TABLE IF EXISTS `fante_states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fante_states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state_name` varchar(255) NOT NULL,
  `details` text NOT NULL,
  `video` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fante_states`
-- (Table is empty)
--

LOCK TABLES `fante_states` WRITE;
/*!40000 ALTER TABLE `fante_states` DISABLE KEYS */;
/*!40000 ALTER TABLE `fante_states` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
-- (Table is empty)
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pending_contributions`
--

DROP TABLE IF EXISTS `pending_contributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pending_contributions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `contact_info` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pending_contributions`
-- (Keeping pending entry for reference)
--

LOCK TABLES `pending_contributions` WRITE;
/*!40000 ALTER TABLE `pending_contributions` DISABLE KEYS */;
INSERT INTO `pending_contributions` VALUES (1,1,1,'Test Contribution','This is a test contribution content.','Test excerpt',NULL,'test@example.com','pending','2026-02-05 16:22:21');
/*!40000 ALTER TABLE `pending_contributions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `excerpt` text NOT NULL,
  `thumbnail` varchar(255) NOT NULL,
  `post_image` varchar(255) DEFAULT NULL,
  `date_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) unsigned DEFAULT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `is_featured` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_posts_category` (`category_id`),
  CONSTRAINT `FK_fs_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_posts_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (4,'Fante Letters','Fante letter writing follows specific structures for formal (Asipim krataa) and informal (Asipim foa krataa) correspondence, typically featuring the sender\'s address, date, salutation, body, closing, and signature. The language utilizes 22 letters, excluding c, j, q, v, x, and includes Ɛ/ɛ and Ɔ/ɔ. Formal letters require formal greetings and direct language, while informal letters are conversational. Key Components of a Fante Letter: Sender\'s Address (Ekyir kwan): Placed at the top right corner. Date: Included below the address. Salutation (Nkyia): Examples include Mepawokyirw (Dear/Please), Me nua pa (My good sibling), or Egya/Maame (Father/Mother). Body (Asɛm no ankasa): The main message. Closing (Nkradzi): Examples include Okyerɛwfo (Writer), Me nua pa (My good sibling). Common Fante Letter Phrases: Opening: Mawɔ ɔdɔfo, (To my beloved,) Inquiring about health: Mebɔ wo amandze dɛ medzi nkwa mu... (I am informing you that I am alive/well...) Closing: Wɔ enyimnyam (With honor), Mebɔ wo amandze (I send you news). Fante Writing Conventions: Uses a 22-letter alphabet: Aa Bb Dd Ee Ɛɛ Ff Gg Hh Ii Kk Ll Mm Nn Oo Ɔɔ Pp Rr Ss Tt Uu Ww Yy Zz. Numbers: Ekor (1), Ebein (2), Ebaasa (3), Anan (4), Enum (5), Esia (6), Eson (7), Awotwe (8). ','The language utilizes 22 letters, excluding c, j, q, v, x, and includes Ɛ/ɛ and Ɔ/ɔ.','17696788451769674886FanteLetters.jpg','17696788451769675528BabyBart.jpg','2026-01-29 08:32:08',1,13,1,'2026-01-29 08:32:08'),(6,'Mankessim Festival','The Mankessim Festival, known as the Borbor Mfantse Amansi Festival (formerly Akwambo), is a vibrant annual celebration held in the second week of September in Mankessim, Central Region, Ghana. It highlights Fante culture, featuring a grand durbar, the "Lemon Friday" street carnival, and traditional processions led by the paramount chief. Key Aspects of the Mankessim Festival: Significance: It serves to honor the heritage of the Borbor Mfantse people and reinforces the traditional paramountcy of Mankessim. Lemon Friday: A unique, energetic street carnival that typically starts early in the morning, serving as a major highlight of the festival. Cultural Display: The festival includes a grand durbar where chiefs, including the Omanhene (Paramount Chief) Osagyefo Amanfo Edu VI, display rich Fante culture, traditional attire, and customs. Timing: The festivities, including the Akwambo, are generally held during the second week of September. Asafo Companies: The event features performances and parades by Asafo companies (traditional warrior groups). The festival brings together residents and visitors to celebrate the history and unity of the Mankessim traditional area. ','','1770842581_3warriors.jpg',NULL,'2026-02-11 20:43:01',8,13,0,'2026-02-11 20:43:01');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimonies`
--

DROP TABLE IF EXISTS `testimonies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testimonies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date_time` datetime NOT NULL,
  `avatar` varchar(255) DEFAULT 'default-avatar.png',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonies`
-- (Table is empty)
--

LOCK TABLES `testimonies` WRITE;
/*!40000 ALTER TABLE `testimonies` DISABLE KEYS */;
/*!40000 ALTER TABLE `testimonies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
-- (Admin and user accounts)
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (13,'nana','arko','kwamena','nanaarko@gmail.com','$2y$10$8r8esGh1M4DMs1JcAUP0iuC9zyC85BS784GXqzT37AweZLwxCxM0O','1768139475pp.jpg',1,NULL),(14,'bato','melchy','pp','naa@gmail.com','$2y$10$sazMAuwP5KkeNFnjiToDceG.NI7zZvqjyWaOOy6d/R7ofsTy4T7N.','1769537337pp.jpg',0,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-12
