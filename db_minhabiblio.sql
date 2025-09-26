CREATE DATABASE  IF NOT EXISTS `db_minhabiblio` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db_minhabiblio`;
-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: db_minhabiblio
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `tbl_avaliacoes`
--

DROP TABLE IF EXISTS `tbl_avaliacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_avaliacoes` (
  `id_avaliacao` int NOT NULL AUTO_INCREMENT,
  `id_livro` int DEFAULT NULL,
  `nota` int DEFAULT NULL,
  `data_avaliacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_usuario` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_avaliacao`),
  KEY `id_livro` (`id_livro`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `tbl_avaliacoes_ibfk_1` FOREIGN KEY (`id_livro`) REFERENCES `tbl_livros` (`id_livro`) ON DELETE CASCADE,
  CONSTRAINT `tbl_avaliacoes_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tbl_avaliacoes_chk_1` CHECK (((`nota` >= 1) and (`nota` <= 5)))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_avaliacoes`
--

LOCK TABLES `tbl_avaliacoes` WRITE;
/*!40000 ALTER TABLE `tbl_avaliacoes` DISABLE KEYS */;
INSERT INTO `tbl_avaliacoes` VALUES (1,1,3,'2025-09-25 23:46:00',1),(2,5,5,'2025-09-25 23:49:39',1),(3,6,2,'2025-09-25 23:50:48',1);
/*!40000 ALTER TABLE `tbl_avaliacoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_generos`
--

DROP TABLE IF EXISTS `tbl_generos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_generos` (
  `id_genero` int NOT NULL AUTO_INCREMENT,
  `nome_genero` varchar(100) NOT NULL,
  PRIMARY KEY (`id_genero`),
  UNIQUE KEY `nome_genero` (`nome_genero`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_generos`
--

LOCK TABLES `tbl_generos` WRITE;
/*!40000 ALTER TABLE `tbl_generos` DISABLE KEYS */;
INSERT INTO `tbl_generos` VALUES (10,'Autoajuda'),(6,'Aventura'),(8,'Biografia'),(7,'Drama'),(1,'Fantasia'),(11,'Ficção'),(2,'Ficção Científica'),(9,'História'),(3,'Romance'),(5,'Suspense'),(4,'Terror');
/*!40000 ALTER TABLE `tbl_generos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_livros`
--

DROP TABLE IF EXISTS `tbl_livros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_livros` (
  `id_livro` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `autor` varchar(255) NOT NULL,
  `id_genero` int DEFAULT NULL,
  `capa` varchar(255) DEFAULT NULL,
  `total_paginas` int DEFAULT NULL,
  `pagina_atual` int DEFAULT '0',
  `progresso` decimal(5,2) DEFAULT '0.00',
  `comentario` text,
  `status` enum('lido','lendo','quero_ler') DEFAULT 'quero_ler',
  `id_usuario` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_livro`),
  KEY `id_genero` (`id_genero`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `tbl_livros_ibfk_1` FOREIGN KEY (`id_genero`) REFERENCES `tbl_generos` (`id_genero`),
  CONSTRAINT `tbl_livros_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_livros`
--

LOCK TABLES `tbl_livros` WRITE;
/*!40000 ALTER TABLE `tbl_livros` DISABLE KEYS */;
INSERT INTO `tbl_livros` VALUES (1,'It - A Coisa','Stephen King',4,'../assets/uploads/capas/68d5fe688ec8f_it.jpg',1200,1200,100.00,'Esse livro tem uma ótima escrita e me deixou completamente assustada.','lido',1),(2,'Doutor Sono','Stephen King',7,'../assets/uploads/capas/68d5fea07a2d7_doutror.jpg',540,100,18.52,'Está sendo um livro interessante, e como praticamente todos de King, está sendo um livro bem aterrorizante.','lendo',1),(3,'O Cemitério','Stephen King',4,'../assets/uploads/capas/68d5fecfa9330_cemite.jpg',250,0,0.00,'Me falaram que era um livro bom, então colocarei na lista para ler futuramente.','quero_ler',1),(4,'Dom Casmurro','Machado de Assis',3,'../assets/uploads/capas/68d5ff0031fa1_5801fd_4de6ed5dc5ea458094e4268b05378b79~mv2.jpg',310,0,0.00,'Um clássico da literatura brasileira. Espero que seja tão bom quanto falam.','quero_ler',1),(5,'O Senhor da Chuva','André Vianco',11,'../assets/uploads/capas/68d5ff42ec557_619-wuaEu6L._UF1000,1000_QL80_.jpg',400,400,100.00,'Um livro que me prendeu do começo ao fim, com certeza leria novamente apenas para sentir a emoção dessa grandiosa história.','lido',1),(6,'O Nome da Rosa','Umberto Eco',11,'../assets/uploads/capas/68d5ff88a37d1_81uo8phJ+zL._UF1000,1000_QL80_.jpg',245,245,100.00,'Poderia ter sido melhor, achei um livro bem difícil de ler, foi uma experiência cansativa.','lido',1);
/*!40000 ALTER TABLE `tbl_livros` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `user` varchar(45) NOT NULL,
  `senha` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`user`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Aluno Etec','aluno','$2y$10$fUGx.sc5i2shqb5EnpylJugg7TgCK/aQWW4VvGdYrTYgU3hlSGPdC'),(2,'Evelyn Vareiro','evelynvareiro','$2y$10$rBzwfY1jNQpEmNbtJWxeN..ajr7zDMs7ZT0nELh7GXoH1JSq0SQCK');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-25 23:58:47
