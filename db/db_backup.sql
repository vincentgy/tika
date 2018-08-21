-- MySQL dump 10.13  Distrib 5.7.23, for Linux (x86_64)
--
-- Host: localhost    Database: tikadb
-- ------------------------------------------------------
-- Server version	5.7.23-0ubuntu0.16.04.1

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
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applications`
--

LOCK TABLES `applications` WRITE;
/*!40000 ALTER TABLE `applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Accounting'),(2,'Administration'),(3,'Banking'),(4,'Customer Service'),(5,'Construction'),(6,'Design'),(7,'Education'),(8,'Engineering'),(9,'Healthcare'),(10,'Hospitality'),(11,'Information Technology'),(12,'Manufacturing'),(13,'Transportation'),(14,'Real Estate'),(15,'Retail'),(16,'Sales'),(17,'Others'),(18,'Farming');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `country_codes`
--

DROP TABLE IF EXISTS `country_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `country_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `country_codes`
--

LOCK TABLES `country_codes` WRITE;
/*!40000 ALTER TABLE `country_codes` DISABLE KEYS */;
INSERT INTO `country_codes` VALUES (1,'NZ');
/*!40000 ALTER TABLE `country_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `districts`
--

DROP TABLE IF EXISTS `districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `districts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `region_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `districts`
--

LOCK TABLES `districts` WRITE;
/*!40000 ALTER TABLE `districts` DISABLE KEYS */;
INSERT INTO `districts` VALUES (1,'Auckland City',1),(2,'Franklin',1),(3,'Hauraki Gulf Islands',1),(4,'Manukau City',1),(5,'North Shore City',1),(6,'Papakura',1),(7,'Rodney',1),(8,'Waiheke Island',1),(9,'Waitakere City',1),(10,'Kawerau',2),(11,'Opotiki',2),(12,'Rotorua',2),(13,'Tauranga',2),(14,'Western Bay Of Plenty',2),(15,'Whakatane',2),(16,'Ashburton',3),(17,'Banks Peninsula',3),(18,'Christchurch City',3),(19,'Hurunui',3),(20,'Mackenzie',3),(21,'Selwyn',3),(22,'Timaru',3),(23,'Waimakariri',3),(24,'Waimate',3),(25,'Gisborne',4),(26,'Central Hawke\'s Bay',5),(27,'Hastings',5),(28,'Napier',5),(29,'Wairoa',5),(30,'Horowhenua',6),(31,'Manawatu',6),(32,'Palmerston North',6),(33,'Rangitikei',6),(34,'Ruapehu',6),(35,'Tararua',6),(36,'Wanganui',6),(37,'Blenheim',7),(38,'Kaikoura',7),(39,'Marlborough',7),(40,'Nelson',8),(41,'Tasman',8),(42,'Far North',9),(43,'Kaipara',9),(44,'Whangarei',9),(45,'Central Otago',10),(46,'Clutha',10),(47,'Dunedin',10),(48,'Queenstown-Lakes',10),(49,'South Otago',10),(50,'Waitaki',10),(51,'Wanaka',10),(52,'Catlins',11),(53,'Gore',11),(54,'Invercargill',11),(55,'Southland',11),(56,'New Plymouth',12),(57,'South Taranaki',12),(58,'Stratford',12),(59,'Hamilton',13),(60,'Hauraki',13),(61,'Matamata-Piako',13),(62,'Otorohanga',13),(63,'South Waikato',13),(64,'Taupo',13),(65,'Thames-Coromandel',13),(66,'Waikato',13),(67,'Waipa',13),(68,'Waitomo',13),(69,'Carterton',14),(70,'Kapiti Coast',14),(71,'Lower Hutt',14),(72,'Masterton',14),(73,'Porirua',14),(74,'South Wairarapa',14),(75,'Upper Hutt',14),(76,'Wellington',14),(77,'Buller',15),(78,'Grey',15),(79,'Westland',15);
/*!40000 ALTER TABLE `districts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pay_types`
--

DROP TABLE IF EXISTS `pay_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pay_types`
--

LOCK TABLES `pay_types` WRITE;
/*!40000 ALTER TABLE `pay_types` DISABLE KEYS */;
INSERT INTO `pay_types` VALUES (1,'one-off'),(2,'annual'),(3,'hourly');
/*!40000 ALTER TABLE `pay_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `position_category`
--

DROP TABLE IF EXISTS `position_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `position_category` (
  `position_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `position_category`
--

LOCK TABLES `position_category` WRITE;
/*!40000 ALTER TABLE `position_category` DISABLE KEYS */;
INSERT INTO `position_category` VALUES (2,6),(3,6),(4,1),(5,4),(2,5),(6,1),(7,1);
/*!40000 ALTER TABLE `position_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `positions`
--

DROP TABLE IF EXISTS `positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `description` text,
  `user_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `pay_type` int(11) NOT NULL,
  `minimum_pay` int(11) NOT NULL,
  `maximum_pay` int(11) DEFAULT NULL,
  `numbers` int(11) NOT NULL DEFAULT '1',
  `region_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(12,9) DEFAULT NULL,
  `longitude` decimal(12,9) DEFAULT NULL,
  `timestamp` int(11) NOT NULL,
  `active` int(1) DEFAULT '1',
  `avatar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `positions`
--

LOCK TABLES `positions` WRITE;
/*!40000 ALTER TABLE `positions` DISABLE KEYS */;
INSERT INTO `positions` VALUES (1,'gopd','1678',NULL,3,1,2,10000,20000,1,2,12,'325 east coast road',NULL,NULL,1532960591,1,NULL),(2,'designer','soul machine',NULL,3,1,2,10000,20000,1,1,1,'57 customer street',NULL,NULL,1533052691,1,NULL),(3,'designer','soul machine',NULL,3,1,2,10000,20000,1,1,1,'85 wakefield street',-36.855520700,174.765643100,1533052794,1,NULL),(4,'weekend morning kitchen hand','edit',NULL,3,1,3,10000,20000,1,1,1,'edit',-36.847260400,174.767113500,1533106828,1,NULL),(5,'å‰ç«¯','æ–¹æ­£',NULL,3,1,2,10000,20000,1,1,1,'éšæ„',NULL,NULL,1533649531,1,NULL),(6,'åŽç«¯php','å¤©æ‰å…¬å¸','åŽç«¯å¼€å‘èŒä½\n',3,1,1,10000,20000,1,1,1,'325 east',NULL,NULL,1533803145,1,NULL),(7,'åŽç«¯php','å¤©æ‰å…¬å¸','åŽç«¯å¼€å‘èŒä½\n',3,1,1,10000,20000,1,1,1,'325 east',NULL,NULL,1533803158,1,NULL);
/*!40000 ALTER TABLE `positions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qualifications`
--

DROP TABLE IF EXISTS `qualifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qualifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `degree` varchar(50) NOT NULL,
  `school` varchar(255) NOT NULL,
  `major` varchar(255) DEFAULT NULL,
  `start` varchar(6) DEFAULT NULL,
  `end` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qualifications`
--

LOCK TABLES `qualifications` WRITE;
/*!40000 ALTER TABLE `qualifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `qualifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `country_code` varchar(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regions`
--

LOCK TABLES `regions` WRITE;
/*!40000 ALTER TABLE `regions` DISABLE KEYS */;
INSERT INTO `regions` VALUES (1,'Auckland','NZ'),(2,'Bay Of Plenty','NZ'),(3,'Canterbury','NZ'),(4,'Gisborne','NZ'),(5,'Hawke\'s Bay','NZ'),(6,'Manawatu / Wanganui','NZ'),(7,'Marlborough','NZ'),(8,'Nelson / Tasman','NZ'),(9,'Northland','NZ'),(10,'Otago','NZ'),(11,'Southland','NZ'),(12,'Taranaki','NZ'),(13,'Waikato','NZ'),(14,'Wellington','NZ'),(15,'West Coast','NZ');
/*!40000 ALTER TABLE `regions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `token` varchar(32) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  `expiry_time` int(11) NOT NULL,
  `ipaddress` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('gS5MW4dfdmuPPwcQBzySoYVo4BIW79DX',1,1534844616,1537436616,'101.81.248.160'),('4wkhZVoDXXGvtIxAl9l80bxZN3HX7xtM',1,1534845302,1537437302,'101.81.248.160'),('x8ePi0cWxf45ynH1Jq5npYuRlgXJOywz',1,1534845344,1537437344,'101.81.248.160'),('74wN9xuBj3vFdvGh4FdAtgwOuawk5hSD',1,1534845371,1537437371,'101.81.253.126'),('LDelOlUWHfSGTQpIAu9h4MdcVYIGhGGv',1,1534845503,1537437503,'101.81.253.126'),('d8cl1gFqutJWfY52Z9fkjNT0ZlARQqMO',1,1534845521,1537437521,'101.81.253.126'),('gcqV5q0XwADnmD9zsIKPHQO14aKsH8li',1,1534845535,1537437535,'101.81.248.160'),('zHW9tt55KzDEM62l491vlFxsjqaw9d2y',0,1534853401,1537445401,'180.165.193.180'),('PgbAnyZfbW14oOprMBe7y11dYm48BgpM',0,1534853434,1537445434,'180.165.193.180'),('V9yN2hArRqQSYB1C7CKPyPi6t1zmLR9y',0,1534853487,1537445487,'180.165.193.180'),('rvojVp5bfN45Qqse0bZsBeKLTDLCEnO2',0,1534853843,1537445843,'180.165.193.180'),('5Ffv2gFOUhQ2mPCFKyrfkWGWj7USmjKu',0,1534853893,1537445893,'180.165.193.180'),('TTAQSiaEmygtO8IVt4leW6dyCy34PBRN',0,1534853927,1537445927,'180.165.193.180'),('ASxaaMEelcpETBDokeTCupdhIUWL4b4Z',0,1534854682,1537446682,'180.165.193.180');
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `types`
--

DROP TABLE IF EXISTS `types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `types`
--

LOCK TABLES `types` WRITE;
/*!40000 ALTER TABLE `types` DISABLE KEYS */;
INSERT INTO `types` VALUES (1,'full-time'),(2,'contract'),(3,'part-time'),(4,'one-off');
/*!40000 ALTER TABLE `types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `lastlogin` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `skills` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,'vincent.y.guo@hotmail.com','e10adc3949ba59abbe56e057f20f883e',NULL,NULL,1530717389,NULL,NULL,NULL,NULL),(3,'215566435@qq.com','metal_gear2',NULL,1531149150,1531149150,NULL,NULL,NULL,NULL),(4,'snakegear@163.com','metal_gear2','zhengfang',1534823966,1534823966,NULL,NULL,NULL,NULL),(5,'1snakegear@163.com','metal_gear2','zhengfang',1534844601,1534844601,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `work_experience`
--

DROP TABLE IF EXISTS `work_experience`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `work_experience` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `place` varchar(255) DEFAULT NULL,
  `task` varchar(255) DEFAULT NULL,
  `start` varchar(6) DEFAULT NULL,
  `end` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `work_experience`
--

LOCK TABLES `work_experience` WRITE;
/*!40000 ALTER TABLE `work_experience` DISABLE KEYS */;
/*!40000 ALTER TABLE `work_experience` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-08-21 15:23:50
