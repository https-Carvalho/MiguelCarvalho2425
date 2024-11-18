-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 18-Nov-2024 às 23:01
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `perfumes_nicho`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `imagens_perfume`
--

CREATE TABLE `imagens_perfume` (
  `id` int(11) NOT NULL,
  `perfume_id` int(11) NOT NULL,
  `caminho_imagem` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `imagens_perfume`
--

INSERT INTO `imagens_perfume` (`id`, `perfume_id`, `caminho_imagem`) VALUES
(1, 1, 'images/sospiro_erbapura_1.jpg'),
(2, 1, 'images/sospiro_erbapura_2.jpg'),
(3, 1, 'images/sospiro_erbapura_3.jpg');

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcas`
--

CREATE TABLE `marcas` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `marcas`
--

INSERT INTO `marcas` (`id`, `nome`) VALUES
(1, 'Sospiro');

-- --------------------------------------------------------

--
-- Estrutura da tabela `notas_olfativas`
--

CREATE TABLE `notas_olfativas` (
  `id` int(11) NOT NULL,
  `descricao` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `notas_olfativas`
--

INSERT INTO `notas_olfativas` (`id`, `descricao`) VALUES
(1, 'Frutado'),
(2, 'Cítrico'),
(3, 'Amadeirado'),
(4, 'Oriental');

-- --------------------------------------------------------

--
-- Estrutura da tabela `perfumes`
--

CREATE TABLE `perfumes` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `caminho_imagem` varchar(255) NOT NULL,
  `caminho_imagem_hover` varchar(255) DEFAULT NULL,
  `id_marca` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `perfumes`
--

INSERT INTO `perfumes` (`id`, `nome`, `descricao`, `preco`, `caminho_imagem`, `caminho_imagem_hover`, `id_marca`) VALUES
(1, 'Erba Pura Magica', 'Uma fragrância mágica e vibrante com notas frutadas e um toque oriental.', 250.00, 'images/sospiro_erbapura.jpg', 'images/sospiro_erbapuramagica_hover.jpg', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `perfume_notas`
--

CREATE TABLE `perfume_notas` (
  `id_perfume` int(11) NOT NULL,
  `id_nota` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `perfume_notas`
--

INSERT INTO `perfume_notas` (`id_perfume`, `id_nota`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `imagens_perfume`
--
ALTER TABLE `imagens_perfume`
  ADD PRIMARY KEY (`id`),
  ADD KEY `perfume_id` (`perfume_id`);

--
-- Índices para tabela `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices para tabela `notas_olfativas`
--
ALTER TABLE `notas_olfativas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `perfumes`
--
ALTER TABLE `perfumes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_marca` (`id_marca`);

--
-- Índices para tabela `perfume_notas`
--
ALTER TABLE `perfume_notas`
  ADD PRIMARY KEY (`id_perfume`,`id_nota`),
  ADD KEY `id_nota` (`id_nota`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `imagens_perfume`
--
ALTER TABLE `imagens_perfume`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `notas_olfativas`
--
ALTER TABLE `notas_olfativas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `perfumes`
--
ALTER TABLE `perfumes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `imagens_perfume`
--
ALTER TABLE `imagens_perfume`
  ADD CONSTRAINT `imagens_perfume_ibfk_1` FOREIGN KEY (`perfume_id`) REFERENCES `perfumes` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `perfumes`
--
ALTER TABLE `perfumes`
  ADD CONSTRAINT `perfumes_ibfk_1` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id`);

--
-- Limitadores para a tabela `perfume_notas`
--
ALTER TABLE `perfume_notas`
  ADD CONSTRAINT `perfume_notas_ibfk_1` FOREIGN KEY (`id_perfume`) REFERENCES `perfumes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `perfume_notas_ibfk_2` FOREIGN KEY (`id_nota`) REFERENCES `notas_olfativas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
