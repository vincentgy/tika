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
-- Table structure for table `chat_messages`
--

DROP TABLE IF EXISTS `chat_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `chat_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text,
  `timestamp` int(11) NOT NULL,
  `updated` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_messages`
--

LOCK TABLES `chat_messages` WRITE;
/*!40000 ALTER TABLE `chat_messages` DISABLE KEYS */;
INSERT INTO `chat_messages` VALUES (1,1,9,'hi',1536293852,0),(2,1,9,'hello',1536293944,0),(3,1,9,'hi',1536299099,0),(4,6,9,'hello',1536311220,0),(5,6,9,'what is your name',1536311229,0),(6,6,9,'hi',1536329288,0),(7,6,10,'hi',1536331347,0),(8,6,9,'',1536332236,0),(9,6,9,'hello',1536332242,0),(10,6,10,'>',1536332395,0),(11,6,10,'',1536332396,0),(12,6,9,'what',1536332406,0),(13,6,10,'>>>',1536332428,0),(14,6,9,'hello',1536332992,0),(15,6,9,'nihao',1536333054,0),(16,6,10,'hi',1536333083,0),(17,6,10,'hi',1536333084,0),(18,6,10,'hi',1536333084,0),(19,6,10,'hi',1536333084,0),(20,6,10,'hi',1536333084,0),(21,6,10,'hi',1536333085,0),(22,6,9,'?',1536333145,0),(23,6,9,'it is crazy',1536333156,0),(24,6,10,'what is that',1536333171,0),(25,6,9,'what is this',1536333387,0),(26,6,10,'hmm',1536333396,0),(27,6,9,'hi',1536385400,0),(28,6,10,'hi',1536385417,0),(29,6,10,'how',1536385464,0),(30,6,9,'hello',1536385469,0),(31,6,9,'',1536385487,0),(32,6,9,'1',1536385489,0),(33,6,9,'2',1536385491,0),(34,6,10,'3',1536385499,0),(35,6,9,'>>>',1536385525,0),(36,6,10,'www',1536385532,0),(37,6,10,'111',1536385583,0),(38,6,10,'222',1536385600,0),(39,6,9,'111',1536385748,0),(40,6,10,'hello',1536385770,0),(41,6,10,'hi',1536386526,0),(42,6,9,'hi',1536415775,0),(43,6,9,'what ?',1536415783,0),(44,6,9,'morning',1536484934,0),(45,6,9,'',1536503262,0),(46,6,9,'hi',1536503265,0);
/*!40000 ALTER TABLE `chat_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_users`
--

DROP TABLE IF EXISTS `chat_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_users` (
  `chat_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_seen` bigint(20) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_users`
--

LOCK TABLES `chat_users` WRITE;
/*!40000 ALTER TABLE `chat_users` DISABLE KEYS */;
INSERT INTO `chat_users` VALUES (40,9,0),(40,10,0),(41,11,0),(41,11,0),(42,11,0),(42,7,0),(43,0,0),(43,11,0);
/*!40000 ALTER TABLE `chat_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chats`
--

DROP TABLE IF EXISTS `chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chats`
--

LOCK TABLES `chats` WRITE;
/*!40000 ALTER TABLE `chats` DISABLE KEYS */;
INSERT INTO `chats` VALUES (40,NULL,9,1538053240),(41,NULL,11,1538100615),(42,NULL,11,1538100663),(43,NULL,0,1538396022);
/*!40000 ALTER TABLE `chats` ENABLE KEYS */;
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
INSERT INTO `position_category` VALUES (2,6),(3,6),(4,1),(5,4),(2,5),(6,1),(7,1),(8,1),(9,1),(10,11),(11,1),(12,6),(13,3),(14,1),(15,1),(16,4),(16,6),(16,7);
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `positions`
--

LOCK TABLES `positions` WRITE;
/*!40000 ALTER TABLE `positions` DISABLE KEYS */;
INSERT INTO `positions` VALUES (1,'gopd','1678',NULL,3,1,2,10000,20000,1,2,12,'325 east coast road',NULL,NULL,1532960591,1,NULL),(2,'designer','soul machine',NULL,3,1,2,10000,20000,1,1,1,'57 customer street',NULL,NULL,1533052691,1,NULL),(3,'designer','soul machine',NULL,3,1,2,10000,20000,1,1,1,'85 wakefield street',-36.855520700,174.765643100,1533052794,1,NULL),(4,'weekend morning kitchen hand','edit',NULL,3,1,3,10000,20000,1,1,1,'edit',-36.847260400,174.767113500,1533106828,1,NULL),(5,'å‰ç«¯','æ–¹æ­£',NULL,3,1,2,10000,20000,1,1,1,'éšæ„',NULL,NULL,1533649531,1,NULL),(6,'åŽç«¯php','å¤©æ‰å…¬å¸','åŽç«¯å¼€å‘èŒä½\n',3,1,1,10000,20000,1,1,1,'325 east',NULL,NULL,1533803145,1,NULL),(7,'åŽç«¯php','å¤©æ‰å…¬å¸','åŽç«¯å¼€å‘èŒä½\n',3,1,1,10000,20000,1,1,1,'325 east',NULL,NULL,1533803158,1,NULL),(8,'web developer','Timix','we are chinese',7,1,2,15000,80000,1,1,4,'325 east coast road',-36.994101700,174.872660000,1537193462,1,NULL),(9,'web developer','Timix','we are chinese\n\n',7,1,2,20000,75000,1,1,1,'325 east coast road',NULL,NULL,1537193886,1,NULL),(10,'web developer','Timix','we are chinese',7,1,2,20000,50000,2,1,5,'325 east coast road',NULL,NULL,1537362307,1,NULL),(11,'UI dev','Timix','we are chinese',7,1,2,45000,55000,1,1,5,'325 east coast road',NULL,NULL,1537364510,1,NULL),(12,'designer UI','Timix','we are chinese\n',7,1,2,50000,80000,1,1,5,'325 east coast road',NULL,NULL,1537365092,1,NULL),(13,'just peopel','Timix','we are chinese\n\n\n\n',7,1,2,20000,40000,1,1,5,'325 east coast road',-36.745022200,174.739743800,1537365980,1,NULL),(14,'ä¼šè®¡','ä¸­å›½é‚®æ”¿','we are chinese',11,1,2,40000,50000,2,1,5,'185 east coast road',-36.848459700,174.763331500,1538099603,1,NULL),(15,'web developer55','Timixrr','we are chinese',7,3,2,25,0,1,1,1,'47 mt albert',-36.884101700,174.715591900,1538122552,1,NULL),(16,'contractor','Timix','we are chinese',7,4,2,200,0,1,1,1,'74 holly street mt albert',-36.886691600,174.692204800,1538122740,1,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qualifications`
--

LOCK TABLES `qualifications` WRITE;
/*!40000 ALTER TABLE `qualifications` DISABLE KEYS */;
INSERT INTO `qualifications` VALUES (9,7,'Bachelor','AUT','Information techonology','012017','012018'),(18,7,'Bachelors','auxkland','it of computing','012013','012017');
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
INSERT INTO `sessions` VALUES ('gS5MW4dfdmuPPwcQBzySoYVo4BIW79DX',1,1534844616,1537436616,'101.81.248.160'),('4wkhZVoDXXGvtIxAl9l80bxZN3HX7xtM',1,1534845302,1537437302,'101.81.248.160'),('x8ePi0cWxf45ynH1Jq5npYuRlgXJOywz',1,1534845344,1537437344,'101.81.248.160'),('74wN9xuBj3vFdvGh4FdAtgwOuawk5hSD',1,1534845371,1537437371,'101.81.253.126'),('LDelOlUWHfSGTQpIAu9h4MdcVYIGhGGv',1,1534845503,1537437503,'101.81.253.126'),('d8cl1gFqutJWfY52Z9fkjNT0ZlARQqMO',1,1534845521,1537437521,'101.81.253.126'),('gcqV5q0XwADnmD9zsIKPHQO14aKsH8li',1,1534845535,1537437535,'101.81.248.160'),('zHW9tt55KzDEM62l491vlFxsjqaw9d2y',0,1534853401,1537445401,'180.165.193.180'),('PgbAnyZfbW14oOprMBe7y11dYm48BgpM',0,1534853434,1537445434,'180.165.193.180'),('V9yN2hArRqQSYB1C7CKPyPi6t1zmLR9y',0,1534853487,1537445487,'180.165.193.180'),('rvojVp5bfN45Qqse0bZsBeKLTDLCEnO2',0,1534853843,1537445843,'180.165.193.180'),('5Ffv2gFOUhQ2mPCFKyrfkWGWj7USmjKu',0,1534853893,1537445893,'180.165.193.180'),('TTAQSiaEmygtO8IVt4leW6dyCy34PBRN',0,1534853927,1537445927,'180.165.193.180'),('ASxaaMEelcpETBDokeTCupdhIUWL4b4Z',0,1534854682,1537446682,'180.165.193.180'),('0sgX697K3lWhHgEqFKg5HSyhK9maPWje',6,1534905341,1537497341,'101.81.248.160'),('AOV6DfUrqkpRRDhyeYeV5JhklH2zmFJ7',6,1534905348,1537497348,'101.81.248.160'),('Lfw5HtliKZShKd6VAPGQitHzwvczf1U8',6,1534905428,1537497428,'101.81.248.160'),('YRpBTGz9g530b6B566Ce4PDIt499GRSN',6,1534905953,1537497953,'101.81.253.126'),('Of9SVMR5XDAJ7lrHE7WWRHHXqMKjcjvf',6,1534913913,1537505913,'101.81.253.126'),('eZsL4m1aw5df0HXoP4Hy7DU0i8snaOOT',6,1534921229,1537519669,'101.81.253.126'),('AImyYTeErORdcdahPSBZ2HNkHdeOm3jY',7,1535165883,1537759808,'180.165.193.180'),('MbJMRBVCBwCzGtiDF3jC8XJs9gbKhNiX',7,1535167921,1537787683,'180.165.193.180'),('pdoWPPN70cDiFfm8RgozDo4wmuc7Dnwo',7,1535195991,1538477357,'180.165.193.180'),('OhyG8ALfFDeEBQnIcgOp0q3wgS9k68UE',7,1535373698,1537965742,'180.165.193.180'),('oN0JlGHOpkFrruZsRhAtIE02J51zXr5C',7,1535374005,1537966010,'180.165.193.180'),('tggN165LWgLZcAraWQeAbFvfzDONMXao',7,1535380137,1537972219,'180.165.193.180'),('6EWbTiX2aMk4G1XMhx979PRteDYQjwNG',9,1535423132,1538015613,'118.149.97.42'),('iiI4B1AvDUcu86CLSEQXCt3jdTJtSXvI',9,1535432413,1538206417,'118.149.97.42'),('lx61jUXHgfpXOuAAS52tevcyojm2KoCX',9,1535617016,1538216242,'118.148.144.133'),('OF9XMlj484Gjo6nYaNISRk0JYv4Qc1Jv',7,1535630770,1538223507,'180.165.193.180'),('nyM0hd7JBk6HhAsDBkMWf0KY1LwrNvdz',9,1535632991,1538294831,'118.149.97.42'),('80qBBnNkTM1Jn7vFhPqPjTCC9JHN3QXo',7,1535636474,1538228474,'180.165.193.180'),('8xHioHa30NpdsEabIjwR9vGk24aEZOAK',7,1535807389,1538399799,'180.165.193.180'),('A83ZrIidABXJO61IMClPapHGfrsNXH1T',7,1535809024,1538402077,'180.165.193.180'),('qQ3rktjzEv4RMoYQnE68cdC6nnwfG8bp',7,1535810462,1538402465,'180.165.193.180'),('6qUuEngXro07hAAFoI6Kpy71BMvEr7hy',7,1535810751,1538402889,'180.165.193.180'),('eDg6dgI9Zm0p1L2Bx84rzhr3BkGPt9xo',7,1535811194,1538491024,'180.165.193.180'),('3ZlwuisY1KS80Pug8EhkYAtxec6MYmox',7,1535900136,1538492587,'180.165.193.180'),('dbY62phUhWj3I4YPauOgk8SnQIZsSC23',7,1535942937,1538534969,'101.81.253.126'),('tyVFYvxVTpLBEZzG2O0heNVH05QttlSM',7,1535985990,1538581967,'180.165.193.180'),('rRQ8QXT84AtH2nxc2XwQveza9zMX5qAs',7,1536024714,1538726735,'101.81.253.126'),('LaUGChDuqFu3SFHFgA8Vf9Zry0ceRYu2',7,1536079120,1538742905,'180.165.193.180'),('nLeCvd797TMsxsVA6U3erAby2v1ZRSkE',9,1536107662,1538894442,'117.136.8.224'),('rUGpiym6cMykvjZYyBZUY8ArqEKL4YVv',7,1536153861,1539148430,'180.165.193.180'),('pfCwIXoxRUtQjkf7MgwEJhIfcC0vGRSH',7,1536238040,1538830155,'180.165.193.180'),('TbRZFplCKIs54zPM5ygMYStQ3gWOA7vM',10,1536302503,1538979988,'58.38.45.204'),('LOsuWwPIryKx3v7wbuuRacl4KPwYFost',9,1536544323,1539350274,'223.104.213.109'),('xdJU9dHZPKzmRGf3p0AwR1tC6VvYU5Su',7,1536557472,1539273315,'101.81.248.160'),('4FCOihWVHclB4MatuhQjVDDfdG7mnFQv',7,1536715022,1539411809,'101.81.248.160'),('X143u6RFuFB3KcY2g9rDU0bXsrcwRRTA',7,1536765338,1539357363,'180.165.193.180'),('5YOq68AiN3A8Vb4k2tEwUlPpmcCW0Z30',7,1536765739,1539430471,'180.165.193.180'),('Po1FFKIlh1GLGGIRSYHfqlpcLPOaji0p',9,1536795561,1539604018,'117.136.8.79'),('MFvH2fsCdxo4jHZOXz6EIES7h3ENTkq6',7,1536839619,1539431619,'180.165.193.180'),('nofuZv4C2mHZa0yuOyhwu8Vse6Lv0eBF',7,1536844312,1539508313,'180.165.193.180'),('GgRHoo9ysW0pftUq0lZrPpdAcm1P19Uz',7,1536938246,1539530247,'47.88.53.71'),('zgsBHm2MxGAvFv6YP3fGpanibFBt6PXT',7,1536938368,1539530369,'47.88.53.71'),('iuhZjspY6uIunsLi2gobyxN5o6L5LoVE',7,1536938685,1539530686,'47.88.53.71'),('yIvUBb2IF31JfhU1ObkOGx4pI4wzywID',7,1536938807,1539530807,'47.88.53.71'),('KbbuVL8c6mYsTs0Mo9PRs6aiL8dwb97Y',7,1536939500,1539531502,'47.88.53.71'),('yNkjv1gLROAdbUN1fiZtQpYhx341xIt0',7,1536939614,1539531614,'47.88.53.71'),('Sp3uawxEXVqZkJx56mvHhNC86ZfeO4Qi',7,1536940065,1539532065,'47.88.53.71'),('OtNLy3Cc1GGC2AdnHP5pYo924hG4l1YI',7,1536940177,1539532178,'47.88.53.71'),('jocJX3DtdLH4ZUMBRuwcxUkX6qs0OfNp',7,1536940563,1539532563,'47.88.53.71'),('x1KEFawSKvgQy1nxzbyoebOdUb6gNz6F',7,1536940665,1539532666,'47.88.53.71'),('RyooyppUfm6NiDtvxHkwqTpEdfngmXOd',7,1536940747,1539532748,'47.88.53.71'),('knZwuHL9BfXTVkpRFWACwbaq43gGIWU4',7,1536941067,1539533068,'47.88.53.71'),('uzIpi33nlnLARbG6ey3VaLziQdAnFPwo',7,1536941262,1539533262,'47.88.53.71'),('XCg391cK4iRuZE5syviT7uddl0i5BFgF',7,1536981379,1539855616,'180.165.193.180'),('BJ5ZZS2jgB8MBFi9a58uKAa6237COSJF',7,1536981905,1539619788,'180.165.193.180'),('U1QZwTtoKfExKQV0RlMLlTDeP8sASpqi',9,1537027546,1540984289,'58.38.45.204'),('yJms1CRH7Cp2YjbyZlrz7brSi3kAxxfM',7,1537197951,1539790019,'180.165.193.180'),('2tcNlB4okwPfZTPuyT01ZXbdAjy7mJ8Y',7,1537198213,1539872971,'180.165.193.180'),('sNCySP7nqxkx5A4uKbA4ZXFxV5SclAsK',7,1537338847,1539944181,'101.81.253.126'),('BoRp2nJc1mlleMBRvmUvxyOpH01UzjU6',7,1537362119,1539956588,'112.65.61.33'),('qKDgNc6tUKM8C8Y3A5BQNBiQrXLQvOXr',7,1537365050,1540305542,'180.165.193.180'),('mVwlof9ow9pGSJzuFA17MfniyXdUsnj1',7,1537368633,1539960645,'180.165.193.180'),('OMXMrKJlRxVmqc4TzkW1g6SC61pbKT6Z',7,1537370737,1539962822,'180.165.193.180'),('Et1nAkT6qwWNowTYr8OIMzuJINkez1Fy',7,1537371054,1540036853,'180.165.193.180'),('uU8FLV7tjRR9lHEdVr5hVJiua15LubGE',7,1537446928,1540038928,'180.165.193.180'),('VJRaPjLgyzeahq8M0ALASNloc92h9EWn',7,1537714062,1540631428,'180.165.193.180'),('CAibbMZCumJ2lfvmic5ylQ9Ke3svJY70',11,1538099292,1540693948,'101.81.251.241'),('u7t9lR6mcWJ8R9v22wymt4qUEwImJhxY',7,1538102095,1540961285,'101.81.251.241'),('eyytBIKQylT2Uh4pP2lxZkFFKpH64051',11,1538230703,1540822765,'180.165.193.180'),('tGjlOPN1p3CgBKnvLpOy6ggUUgobBa8H',11,1538231019,1540823252,'180.165.193.180'),('x7wzq3ZwG4rHr0bzKuyX14lYZeScZ7DP',7,1538369307,1540993650,'211.69.161.93'),('3SJ27YTxDxux2KirZCgJt8z7BOxlwbaO',11,1538372003,1540987643,'211.69.161.50');
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
  `title` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,'vincent.y.guo@hotmail.com','e10adc3949ba59abbe56e057f20f883e',NULL,NULL,1530717389,NULL,NULL,NULL,NULL,NULL,NULL),(3,'215566435@qq.com','metal_gear2',NULL,1531149150,1531149150,NULL,NULL,NULL,'https://timix.s3.us-east-2.amazonaws.com/users/3.',NULL,NULL),(4,'snakegear@163.com','metal_gear2','zhengfang',1534823966,1534823966,'','','Array',NULL,NULL,NULL),(5,'1snakegear@163.com','metal_gear2','zhengfang',1534844601,1534844601,NULL,NULL,NULL,NULL,NULL,NULL),(6,'215@qq.com','metal_gear2','zhengfang1',1534905327,1534905327,NULL,NULL,NULL,NULL,NULL,NULL),(7,'snakegear2@163.com','2fe423bbf913e107320fc153bacc7076','æ­£æ–¹',1535165869,1535165869,'ç†Ÿæ‚‰ react å†…æ ¸ä»£ç ï¼Œç†Ÿæ‚‰åŽå°é€»è¾‘ï¼Œéžå¸¸ç‰›é€¼çš„ï¼','','js','https://timix.s3.us-east-2.amazonaws.com/users/7_15381194102',NULL,NULL),(8,'shg@163.com','2fe423bbf913e107320fc153bacc7076','æ­£124',1535167861,1535167861,NULL,NULL,NULL,NULL,NULL,NULL),(9,'vg@163.com','d24ee668d06a6804e9696e868502f767','Vincent',1535423118,1535423118,NULL,NULL,NULL,NULL,NULL,NULL),(10,'vg@qq.com','d24ee668d06a6804e9696e868502f767','simon',1536302499,1536302499,NULL,NULL,NULL,NULL,NULL,NULL),(11,'qlylovefz@163.com','2fe423bbf913e107320fc153bacc7076','qlylovefz',1538099267,1538099267,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `watchlist`
--

DROP TABLE IF EXISTS `watchlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `watchlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `watchlist`
--

LOCK TABLES `watchlist` WRITE;
/*!40000 ALTER TABLE `watchlist` DISABLE KEYS */;
INSERT INTO `watchlist` VALUES (1,13,7,1537757720),(2,13,7,1537757741),(3,13,7,1537757745),(4,13,7,1537939742),(5,13,7,1537939756),(6,13,7,1538027167),(7,13,7,1538027439),(8,13,7,1538027529),(9,13,7,1538027560),(10,13,7,1538027593),(11,13,7,1538027940),(12,13,7,1538038605);
/*!40000 ALTER TABLE `watchlist` ENABLE KEYS */;
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
  `title` varchar(255) DEFAULT NULL,
  `task` varchar(255) DEFAULT NULL,
  `start` varchar(6) DEFAULT NULL,
  `end` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `work_experience`
--

LOCK TABLES `work_experience` WRITE;
/*!40000 ALTER TABLE `work_experience` DISABLE KEYS */;
INSERT INTO `work_experience` VALUES (25,7,'timix','rnï¼Œreact','rnï¼Œreact','012016','012017'),(26,7,'google','react','react','012017','012017');
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

-- Dump completed on 2018-10-01 14:05:45
