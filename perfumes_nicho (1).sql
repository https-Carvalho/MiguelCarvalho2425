-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 28-Nov-2024 às 00:13
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
(1, 1, 'images/sospiro_erbapura1.jpg'),
(2, 1, 'images/sospiro_erbapura2.jpg'),
(3, 1, 'images/sospiro_erbapura3.jpg'),
(4, 2, 'images/xerjoff_alexandria_ii_1.jpg'),
(5, 2, 'images/xerjoff_alexandria_ii_2.jpg'),
(6, 2, 'images/xerjoff_alexandria_ii_3.jpg');

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcas`
--

CREATE TABLE `marcas` (
  `id_marca` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `caminho_imagem` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `marcas`
--

INSERT INTO `marcas` (`id_marca`, `nome`, `caminho_imagem`) VALUES
(1, 'Sospiro', 'images/sospiro.jpg'),
(2, 'Xerjoff', 'images/xerjoff.jpg'),
(3, 'BDK', 'images/BDK.jpg'),
(4, 'Stephane Humbert Lucas', 'images/StephaneHumbertLucas.jpg'),
(5, 'Kajal', 'images/kajal.jpg');

-- --------------------------------------------------------

--
-- Estrutura da tabela `notas_olfativas`
--

CREATE TABLE `notas_olfativas` (
  `id_notes` int(11) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `tipo` enum('topo','coração','base') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `notas_olfativas`
--

INSERT INTO `notas_olfativas` (`id_notes`, `descricao`, `tipo`) VALUES
(1, 'Bergamota', 'topo'),
(2, 'Limão', 'topo'),
(3, 'Frutas Vermelhas', 'topo'),
(4, 'Jasmim', 'topo'),
(5, 'Rosa', 'coração'),
(6, 'Madeiras Nobres', 'coração'),
(7, 'Baunilha', 'base'),
(8, 'Âmbar', 'base'),
(9, 'Sândalo', 'base'),
(10, 'Notas Frutadas', 'topo'),
(11, 'Limão', 'topo'),
(12, 'Notas Balsâmicas', 'coração'),
(13, 'Raíz de Orris', 'coração'),
(14, 'Cedro', 'base'),
(15, 'Almíscar', 'base'),
(16, 'Caramelo', 'base');

-- --------------------------------------------------------

--
-- Estrutura da tabela `perfumes`
--

CREATE TABLE `perfumes` (
  `id_perfume` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `caminho_imagem` varchar(255) NOT NULL,
  `caminho_imagem_hover` varchar(255) NOT NULL,
  `id_marca` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `perfumes`
--

INSERT INTO `perfumes` (`id_perfume`, `nome`, `descricao`, `preco`, `caminho_imagem`, `caminho_imagem_hover`, `id_marca`) VALUES
(1, 'Sospiro Erba Pura', 'Uma fragrância mágica e vibrante com notas frutadas e orientais.', 230.00, 'images/sospiro_erba_pura.jpg', 'images/sospiro_erba_pura_hover.jpg', 1),
(2, 'Xerjoff Alexandria II', 'Uma composição clássica com notas orientais e especiadas, ideal para ocasiões especiais.', 340.00, 'images/xerjoff_alexandria_ii.jpg', 'images/xerjoff_alexandria_ii_hover.jpg', 2);

-- --------------------------------------------------------

--
-- Estrutura da tabela `perfume_notas`
--

CREATE TABLE `perfume_notas` (
  `id_perfume` int(11) NOT NULL,
  `id_notes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `perfume_notas`
--

INSERT INTO `perfume_notas` (`id_perfume`, `id_notes`) VALUES
(1, 1),
(1, 2),
(1, 4),
(1, 8),
(1, 10),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(2, 3),
(2, 5),
(2, 6),
(2, 9);

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
  ADD PRIMARY KEY (`id_marca`);

--
-- Índices para tabela `notas_olfativas`
--
ALTER TABLE `notas_olfativas`
  ADD PRIMARY KEY (`id_notes`);

--
-- Índices para tabela `perfumes`
--
ALTER TABLE `perfumes`
  ADD PRIMARY KEY (`id_perfume`),
  ADD KEY `id_marca` (`id_marca`);

--
-- Índices para tabela `perfume_notas`
--
ALTER TABLE `perfume_notas`
  ADD PRIMARY KEY (`id_perfume`,`id_notes`),
  ADD KEY `id_nota` (`id_notes`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `imagens_perfume`
--
ALTER TABLE `imagens_perfume`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `notas_olfativas`
--
ALTER TABLE `notas_olfativas`
  MODIFY `id_notes` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `perfumes`
--
ALTER TABLE `perfumes`
  MODIFY `id_perfume` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `imagens_perfume`
--
ALTER TABLE `imagens_perfume`
  ADD CONSTRAINT `imagens_perfume_ibfk_1` FOREIGN KEY (`perfume_id`) REFERENCES `perfumes` (`id_perfume`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `perfumes`
--
ALTER TABLE `perfumes`
  ADD CONSTRAINT `perfumes_ibfk_1` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `perfume_notas`
--
ALTER TABLE `perfume_notas`
  ADD CONSTRAINT `perfume_notas_ibfk_1` FOREIGN KEY (`id_perfume`) REFERENCES `perfumes` (`id_perfume`) ON DELETE CASCADE,
  ADD CONSTRAINT `perfume_notas_ibfk_2` FOREIGN KEY (`id_notes`) REFERENCES `notas_olfativas` (`id_notes`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
