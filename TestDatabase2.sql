-- MySQL dump 10.16  Distrib 10.3.5-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: gps
-- ------------------------------------------------------
-- Server version	10.3.5-MariaDB-10.3.5+maria~stretch

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `lat` double NOT NULL,
  `lng` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,'La Boule',46.814466481077766,-71.15206897258759,'2018-03-07 05:25:31','2018-03-07 05:25:31'),(2,'Timmy #1',46.81426918950083,-71.15283071994781,'2018-03-07 05:25:49','2018-03-07 05:28:37'),(3,'Normandin',46.86543612345275,-71.18459343910217,'2018-03-07 05:27:27','2018-03-07 05:27:27'),(4,'Timmy #2',46.53827388692131,-71.63761854171753,'2018-03-07 05:28:28','2018-03-07 05:28:28');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `command_items`
--

DROP TABLE IF EXISTS `command_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `command_items` (
  `commandId` int(10) unsigned NOT NULL,
  `itemId` int(10) unsigned NOT NULL,
  `amount` int(11) NOT NULL,
  PRIMARY KEY (`commandId`,`itemId`),
  KEY `command_items_itemid_foreign` (`itemId`),
  CONSTRAINT `command_items_commandid_foreign` FOREIGN KEY (`commandId`) REFERENCES `commands` (`id`),
  CONSTRAINT `command_items_itemid_foreign` FOREIGN KEY (`itemId`) REFERENCES `items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `command_items`
--

LOCK TABLES `command_items` WRITE;
/*!40000 ALTER TABLE `command_items` DISABLE KEYS */;
INSERT INTO `command_items` VALUES (1,1,500),(1,2,200),(1,3,200),(1,5,1200),(2,1,60),(2,2,300),(2,3,500),(2,4,1200),(2,5,2400),(3,1,420),(3,2,420),(3,3,420),(3,4,1200),(3,5,2400),(3,6,800);
/*!40000 ALTER TABLE `command_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commands`
--

DROP TABLE IF EXISTS `commands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `commands` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clientId` int(10) unsigned NOT NULL,
  `sessionId` int(10) unsigned DEFAULT NULL,
  `complete` tinyint(1) NOT NULL DEFAULT 0,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `commands_clientid_foreign` (`clientId`),
  KEY `commands_sessionid_foreign` (`sessionId`),
  CONSTRAINT `commands_clientid_foreign` FOREIGN KEY (`clientId`) REFERENCES `clients` (`id`),
  CONSTRAINT `commands_sessionid_foreign` FOREIGN KEY (`sessionId`) REFERENCES `sessions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commands`
--

LOCK TABLES `commands` WRITE;
/*!40000 ALTER TABLE `commands` DISABLE KEYS */;
INSERT INTO `commands` VALUES (1,2,NULL,0,'2018-03-07 00:50:52'),(2,4,NULL,0,'2018-03-07 00:51:51'),(3,3,NULL,0,'2018-03-07 00:52:33');
/*!40000 ALTER TABLE `commands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `drivers`
--

DROP TABLE IF EXISTS `drivers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drivers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `lastName` varchar(64) NOT NULL,
  `licence` tinyint(4) NOT NULL,
  `phoneNumber` bigint(20) unsigned NOT NULL DEFAULT 0,
  `isAdmin` tinyint(1) NOT NULL DEFAULT 0,
  `email` varchar(128) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `drivers_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drivers`
--

LOCK TABLES `drivers` WRITE;
/*!40000 ALTER TABLE `drivers` DISABLE KEYS */;
INSERT INTO `drivers` VALUES (1,'123','123',1,9999999999,1,'geged6@hotmail.com','$2y$10$GKBKryCuCzIYV7V6ZCnLc.tGc2dTRlNlJt3C/c2MHWLrd5FmSi0fe',NULL,'2018-03-07 05:23:34','2018-03-07 05:23:34');
/*!40000 ALTER TABLE `drivers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geolocation`
--

DROP TABLE IF EXISTS `geolocation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `geolocation` (
  `clientId` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`clientId`),
  UNIQUE KEY `clientId` (`clientId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geolocation`
--

LOCK TABLES `geolocation` WRITE;
/*!40000 ALTER TABLE `geolocation` DISABLE KEYS */;
/*!40000 ALTER TABLE `geolocation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `supplierId` int(10) unsigned NOT NULL,
  `cost` double NOT NULL,
  `conditioning` tinyint(1) NOT NULL,
  `amountPerPackaging` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `items_name_unique` (`name`),
  KEY `items_supplierid_foreign` (`supplierId`),
  CONSTRAINT `items_supplierid_foreign` FOREIGN KEY (`supplierId`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (1,'Café Noir',1,4,0,120,'2018-03-07 05:34:00','2018-03-07 05:34:00'),(2,'Café Moka',1,4.2,0,120,'2018-03-07 05:34:38','2018-03-07 05:34:38'),(3,'Café d\'Orléan',1,5.4,0,120,'2018-03-07 05:35:02','2018-03-07 05:35:02'),(4,'Brown Sugar',2,0.42,0,3000,'2018-03-07 05:35:26','2018-03-07 05:35:26'),(5,'White Sugar',2,0.42,0,3000,'2018-03-07 05:35:40','2018-03-07 05:35:40'),(6,'Chocolate Mix',2,2.42,1,420,'2018-03-07 05:36:11','2018-03-07 05:36:11');
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (13,'2014_10_12_000000_create_users_table',1),(14,'2014_10_12_100000_create_password_resets_table',1),(15,'2018_02_10_060001_create_vehicles_table',1),(16,'2018_02_10_060029_create_clients_table',1),(17,'2018_02_10_060031_create_fournisseurs_table',1),(18,'2018_02_10_060038_create_items_table',1),(19,'2018_02_10_061200_create_sessions_table',1),(20,'2018_02_10_061201_create_commands_table',1),(21,'2018_02_10_061250_create_vehicle_items_table',1),(22,'2018_02_10_074038_create_command_items_table',1),(23,'2018_02_10_074100_create_session_pos_table',1),(24,'2018_02_14_215739_session_messages',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifies`
--

DROP TABLE IF EXISTS `notifies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifies` (
  `sessionId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`sessionId`),
  CONSTRAINT `notifies_sessionid_foreign` FOREIGN KEY (`sessionId`) REFERENCES `sessions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifies`
--

LOCK TABLES `notifies` WRITE;
/*!40000 ALTER TABLE `notifies` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session_pos`
--

DROP TABLE IF EXISTS `session_pos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_pos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sessionId` int(10) unsigned NOT NULL,
  `lat` double NOT NULL,
  `lng` double NOT NULL,
  `moment` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `session_pos_sessionid_foreign` (`sessionId`),
  CONSTRAINT `session_pos_sessionid_foreign` FOREIGN KEY (`sessionId`) REFERENCES `sessions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session_pos`
--

LOCK TABLES `session_pos` WRITE;
/*!40000 ALTER TABLE `session_pos` DISABLE KEYS */;
/*!40000 ALTER TABLE `session_pos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `driverId` int(10) unsigned DEFAULT NULL,
  `vehicleId` int(10) unsigned NOT NULL,
  `start` timestamp NULL DEFAULT NULL,
  `end` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_driverid_foreign` (`driverId`),
  KEY `sessions_vehicleid_foreign` (`vehicleId`),
  CONSTRAINT `sessions_driverid_foreign` FOREIGN KEY (`driverId`) REFERENCES `drivers` (`id`),
  CONSTRAINT `sessions_vehicleid_foreign` FOREIGN KEY (`vehicleId`) REFERENCES `vehicles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES (1,NULL,3,NULL,NULL);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `bill` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'Supplier #1',0),(2,'Supplier #2',0);
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicle_items`
--

DROP TABLE IF EXISTS `vehicle_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vehicle_items` (
  `vehicleId` int(10) unsigned NOT NULL,
  `itemId` int(10) unsigned NOT NULL,
  `amount` int(11) NOT NULL,
  `trueAmount` int(11) NOT NULL,
  PRIMARY KEY (`vehicleId`,`itemId`),
  KEY `vehicle_items_itemid_foreign` (`itemId`),
  CONSTRAINT `vehicle_items_itemid_foreign` FOREIGN KEY (`itemId`) REFERENCES `items` (`id`),
  CONSTRAINT `vehicle_items_vehicleid_foreign` FOREIGN KEY (`vehicleId`) REFERENCES `vehicles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicle_items`
--

LOCK TABLES `vehicle_items` WRITE;
/*!40000 ALTER TABLE `vehicle_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `vehicle_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vehicles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `licence` tinyint(4) NOT NULL,
  `name` varchar(32) NOT NULL,
  `conditioning` tinyint(1) NOT NULL,
  `capacity` int(10) unsigned NOT NULL,
  `usedCapacity` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicles`
--

LOCK TABLES `vehicles` WRITE;
/*!40000 ALTER TABLE `vehicles` DISABLE KEYS */;
INSERT INTO `vehicles` VALUES (1,5,'Ginette',1,42,0,'2018-03-07 05:49:39','2018-03-07 05:49:54'),(2,1,'Roberta',0,112,0,'2018-03-07 05:49:49','2018-03-07 05:49:49'),(3,1,'Semi-remorque #1',1,4200,0,'2018-03-07 05:50:15','2018-03-07 05:50:15');
/*!40000 ALTER TABLE `vehicles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-03-06 20:15:31
