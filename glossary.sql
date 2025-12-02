-- MySQL dump 10.13  Distrib 9.5.0, for macos15.4 (arm64)
--
-- Host: localhost    Database: glossary
-- ------------------------------------------------------
-- Server version	9.5.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ 'c2abd7f2-bb14-11f0-b992-958bf7352416:1-136';

--
-- Table structure for table `terms`
--

DROP TABLE IF EXISTS `terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `terms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `french_term` varchar(255) NOT NULL,
  `english_term` varchar(255) NOT NULL,
  `french_definition` text,
  `english_definition` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_terms` (`french_term`,`english_term`)
) ENGINE=InnoDB AUTO_INCREMENT=1716 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `terms`
--

LOCK TABLES `terms` WRITE;
/*!40000 ALTER TABLE `terms` DISABLE KEYS */;
INSERT INTO `terms` VALUES (1,'bonjour','hello','Salutation utilisÃ©e pour dire bonjour.','A greeting used to say hello.'),(2,'fromage','cheese','Produit laitier obtenu par la coagulation du lait.','A dairy product made by coagulating milk.'),(3,'ordinateur','computer','Machine Ã©lectronique capable de traiter des donnÃ©es.','An electronic machine capable of processing data.'),(4,'formage','forming; shaping','Action ou processus de donner une forme spécifique à un matériau (métal, plastique, etc.) par déformation plastique, sans enlèvement de matière significatif. Il s\'agit d\'une technique de fabrication utilisée pour transformer une matière première en un produit fini ou semi-fini.','The action or process of giving a specific shape to a material (metal, plastic, etc.) through plastic deformation, without significant material removal. It is a manufacturing technique used to transform a raw material into a finished or semi-finished product.'),(5,'API','Application Programming Interface','Ensemble de règles et de protocoles permettant à des applications de communiquer entre elles.','Set of rules and protocols allowing applications to communicate with each other.'),(6,'Base de donnÃ©es','Database','Collection organisÃ©e de donnÃ©es structurÃ©es permettant le stockage, la rÃ©cupÃ©ration et la manipulation efficace des informations.','Organized collection of structured data allowing efficient storage, retrieval, and manipulation of information.'),(7,'SQL','Structured Query Language','Langage standardisé pour gérer les bases de données relationnelles.','Standardized language for managing relational databases.'),(8,'HTTP','HyperText Transfer Protocol','Protocole de communication utilisé pour transférer des données sur le web.','Communication protocol used to transfer data on the web.'),(9,'Encapsulation','Encapsulation','Principe de la POO qui consiste à cacher les détails d\'implémentation d\'une classe.','OOP principle that hides implementation details of a class.'),(10,'HÃ©ritage','Inheritance','MÃ©canisme de la POO permettant Ã  une classe d\'hÃ©riter des propriÃ©tÃ©s d\'une autre classe.','OOP mechanism allowing a class to inherit properties from another class.'),(11,'Polymorphisme','Polymorphism','Capacité d\'un objet à prendre plusieurs formes.','Ability of an object to take multiple forms.'),(12,'MVC','Model-View-Controller','Pattern architectural séparant la logique métier, l\'interface utilisateur et la gestion des interactions.','Architectural pattern separating business logic, user interface, and interaction management.'),(13,'Firewall','Firewall','Système de sécurité réseau contrôlant le trafic entrant et sortant.','Network security system controlling incoming and outgoing traffic.'),(14,'Algorithme','Algorithm','Ensemble d\'instructions finies et précises permettant de résoudre un problème.','Finite and precise set of instructions to solve a problem.'),(16,'Base de données','Database','Collection organisée de données structurées permettant le stockage, la récupération et la manipulation efficace des informations.','Organized collection of structured data allowing efficient storage, retrieval, and manipulation of information.'),(20,'Héritage','Inheritance','Mécanisme de la POO permettant à une classe d\'hériter des propriétés d\'une autre classe.','OOP mechanism allowing a class to inherit properties from another class.'),(85,'HTTP','HTTP','HTTP (Hypertext Transfer Protocol) est un protocole de la couche application destiné à la transmission de documents hypermédias, tels que le HTML, sur internet. Il est le fondement de la communication de données pour le World Wide Web, permettant aux navigateurs web de récupérer des pages web et d\'interagir avec des serveurs web.','HTTP (Hypertext Transfer Protocol) is an application-layer protocol for transmitting hypermedia documents, such as HTML, over the internet. It is the foundation of data communication for the World Wide Web, allowing web browsers to retrieve web pages and interact with web servers.');
/*!40000 ALTER TABLE `terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$12$BcICRAp7Kxj8BOLqf9h7MeoInNFBqmph0S0hghx7rQStw1f02XNsO','2025-12-02 00:54:42');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-01 20:18:09
