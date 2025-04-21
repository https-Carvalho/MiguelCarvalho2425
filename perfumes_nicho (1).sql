-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 21-Abr-2025 às 22:38
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.1.25

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
-- Estrutura da tabela `carrinho`
--

CREATE TABLE `carrinho` (
  `id_item` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 1,
  `adicionado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `carrinho`
--

INSERT INTO `carrinho` (`id_item`, `id_usuario`, `id_produto`, `quantidade`, `adicionado_em`) VALUES
(13, 1, 3, 3, '2025-04-08 08:39:16');

-- --------------------------------------------------------

--
-- Estrutura da tabela `familias_olfativas`
--

CREATE TABLE `familias_olfativas` (
  `id_familia` int(11) NOT NULL,
  `nome_familia` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `familias_olfativas`
--

INSERT INTO `familias_olfativas` (`id_familia`, `nome_familia`, `descricao`) VALUES
(1, 'Cítricos', 'Notas frescas e efervescentes derivadas de frutas cítricas, como limão e laranja.'),
(2, 'Frutas e Nozes', 'Notas doces e suculentas de frutas, além de elementos vegetais e de nozes, como abacaxi e amêndoa.'),
(3, 'Flores', 'Uma ampla categoria que inclui flores leves, frescas e intensas, como rosa e magnólia.'),
(4, 'Flores Brancas', 'Focada em flores brancas conhecidas por seu aroma marcante e opulento, como jasmim sambac e gardênia.'),
(5, 'Verdes e Aromáticas', 'Notas herbais, verdes e aromáticas, com elementos frescos como lavanda e hortelã.'),
(6, 'Especiarias', 'Notas quentes e frias provenientes de especiarias, como canela e cardamomo.'),
(7, 'Doces & Gourmets', 'Notas inspiradas em alimentos doces e sobremesas, como baunilha, chocolate e caramelo.'),
(8, 'Madeiras & Musgos', 'Notas secas, terrosas e amadeiradas, incluindo musgos, como cedro e vetiver.'),
(9, 'Resinas & Bálsamos', 'Notas doces e resinosas, frequentemente usadas como base, como benjoim e âmbar.'),
(10, 'Almíscar & Animálicos', 'Notas sensuais e intensas, centradas no almíscar, âmbar e acordes animálicos.'),
(11, 'Bebidas', 'Notas inspiradas em bebidas alcoólicas e não alcoólicas, como rum, café e conhaque.'),
(12, 'Sintéticas & Estranhas', 'Notas modernas, sintéticas ou incomuns, como Iso E Super e Ambroxan.');

-- --------------------------------------------------------

--
-- Estrutura da tabela `familia_notas`
--

CREATE TABLE `familia_notas` (
  `id_familia_nota` int(11) NOT NULL,
  `id_familia` int(11) NOT NULL,
  `id_nota` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `familia_notas`
--

INSERT INTO `familia_notas` (`id_familia_nota`, `id_familia`, `id_nota`) VALUES
(1, 1, 2),
(2, 1, 3),
(3, 1, 25),
(4, 1, 26),
(5, 1, 27),
(6, 1, 30),
(7, 1, 31),
(8, 1, 32),
(9, 2, 1),
(10, 2, 14),
(11, 2, 20),
(12, 2, 28),
(13, 2, 39),
(14, 2, 40),
(15, 2, 44),
(16, 2, 54),
(17, 2, 55),
(18, 2, 57),
(19, 2, 49),
(20, 3, 15),
(21, 3, 21),
(22, 3, 22),
(23, 3, 16),
(24, 3, 59),
(25, 3, 6),
(26, 3, 12),
(27, 3, 60),
(28, 3, 58),
(29, 4, 4),
(30, 4, 50),
(31, 4, 61),
(32, 5, 33),
(33, 5, 48),
(34, 6, 56),
(35, 6, 13),
(36, 6, 23),
(37, 6, 35),
(38, 6, 36),
(39, 7, 9),
(40, 7, 19),
(41, 7, 29),
(42, 7, 37),
(43, 7, 47),
(44, 7, 51),
(45, 8, 53),
(46, 8, 10),
(47, 8, 11),
(48, 8, 17),
(49, 8, 18),
(50, 8, 24),
(51, 8, 34),
(52, 8, 42),
(53, 8, 62),
(54, 8, 63),
(55, 9, 41),
(56, 9, 52),
(57, 9, 5),
(58, 9, 38),
(59, 10, 8),
(60, 10, 7),
(61, 10, 34),
(62, 11, 43),
(90, 1, 81),
(91, 1, 86),
(92, 2, 84),
(93, 2, 64),
(94, 3, 73),
(95, 3, 83),
(96, 3, 85),
(97, 3, 74),
(98, 4, 83),
(99, 4, 75),
(100, 5, 68),
(101, 5, 90),
(102, 5, 89),
(103, 9, 65),
(104, 9, 66),
(105, 9, 67),
(106, 9, 69),
(107, 9, 80),
(108, 9, 77),
(109, 8, 88),
(110, 8, 69),
(111, 8, 71),
(112, 8, 72),
(113, 8, 78),
(114, 8, 79),
(115, 10, 76),
(116, 10, 70),
(117, 10, 86),
(118, 11, 82),
(119, 11, 87),
(120, 6, 91);

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
(4, 2, 'images/xerjoff_alexandria1.jpg'),
(5, 2, 'images/xerjoff_alexandria2.jpg'),
(6, 2, 'images/xerjoff_alexandria3.jpg');

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcas`
--

CREATE TABLE `marcas` (
  `id_marca` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `caminho_imagem` varchar(230) NOT NULL,
  `descricao` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `marcas`
--

INSERT INTO `marcas` (`id_marca`, `nome`, `caminho_imagem`, `descricao`) VALUES
(1, 'Sospiro', 'images/sospiro.jpg', 'Sospiro é uma marca de perfumaria de luxo que mescla tradição e inovação, oferecendo fragrâncias sofisticadas e exclusivas. Cada perfume é inspirado em emoções e momentos únicos, criando uma experiência olfativa que vai além dos sentidos. Com suas composições requintadas, a marca se destaca por sua originalidade e pela qualidade dos ingredientes, sendo uma verdadeira expressão de arte olfativa.'),
(2, 'Xerjoff', 'images/xerjoff.jpg', 'Xerjoff é uma marca de alta perfumaria que redefine o luxo, com perfumes criados a partir de ingredientes raros e preciosos. Cada fragrância é uma verdadeira obra-prima, que une criatividade e tradição. Com um design elegante e notas complexas, os perfumes Xerjoff são feitos para quem busca sofisticação e exclusividade. A marca se destaca por seu compromisso com a qualidade e o cuidado artesanal em cada criação.'),
(3, 'BDK', 'images/BDK.jpg', 'BDK Parfums é uma marca parisiense que traz perfumes modernos, ousados e cativantes, com uma identidade única e envolvente. Cada fragrância é uma verdadeira história contada através de notas olfativas, traduzindo sentimentos e memórias. A marca se destaca pela autenticidade e pela capacidade de criar composições sofisticadas e marcantes, que conquistam os amantes da perfumaria de nicho pela sua originalidade e estilo contemporâneo.'),
(4, 'Kajal', 'images/Kajal.jpg', 'Kajal é uma marca de perfumaria de nicho inspirada nas tradições e na cultura do Oriente Médio, que combina riqueza e autenticidade com uma abordagem moderna. Suas fragrâncias exclusivas são criadas com paixão, utilizando ingredientes de alta qualidade que evocam emoções profundas. Kajal mistura aromas orientais com toques ocidentais, criando perfumes sofisticados, luxuosos e que transmitem um sentido de mistério e elegância.'),
(5, 'Stephane Humbert Lucas', 'images/StephaneHumbertLucas.jpg', 'tephane Humbert Lucas é uma marca francesa de perfumaria de nicho que se destaca por sua criatividade e ousadia. Suas fragrâncias intensas e únicas são criadas sem limites, sempre com uma abordagem inovadora e artística. A marca é conhecida por explorar temas complexos e emocionais, oferecendo perfumes que capturam a essência de momentos especiais e histórias inesquecíveis. Cada perfume é uma expressão autêntica da visão olfativa do criador.'),
(6, 'Creed', 'images/creed.jpg', 'Creed é uma marca de luxo com tradição em criar fragrâncias icônicas e atemporais.'),
(7, 'Maison Margiela', 'images/maison_margiela.jpg', 'Maison Margiela é conhecida pela sua linha Replica, que recria momentos e memórias através de fragrâncias.');

-- --------------------------------------------------------

--
-- Estrutura da tabela `notas_geral`
--

CREATE TABLE `notas_geral` (
  `id_nota` int(11) NOT NULL,
  `nome_nota` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `notas_geral`
--

INSERT INTO `notas_geral` (`id_nota`, `nome_nota`) VALUES
(20, 'Abacaxi'),
(82, 'Absinto'),
(35, 'Açafrão'),
(17, 'Agarwood (Oud)'),
(7, 'Almíscar'),
(8, 'Âmbar'),
(70, 'Âmbar Cinzento'),
(44, 'Ameixa'),
(49, 'Amêndoa'),
(64, 'Amora'),
(53, 'Amyris'),
(68, 'Artemísia'),
(57, 'Bagas Vermelhas'),
(77, 'Bálsamo do Peru'),
(76, 'Bálsamo-de-Tolu'),
(19, 'Baunilha'),
(37, 'Baunilha de Bourbon'),
(29, 'Baunilha de Madagascar'),
(38, 'Benjoim'),
(3, 'Bergamota'),
(26, 'Bergamota da Calábria'),
(80, 'Cade Lubrificam'),
(13, 'Canela'),
(9, 'Caramelo'),
(58, 'Cardamomo'),
(10, 'Cedro'),
(71, 'Cedro Atlas'),
(47, 'Chocolate'),
(84, 'Coco'),
(56, 'Coentro'),
(34, 'Couro'),
(51, 'Fava Tonka'),
(91, 'Feijão de Baunilha'),
(50, 'Flor de Laranjeira'),
(90, 'Folha de Tabaco'),
(28, 'Frutas'),
(36, 'Gengibre'),
(85, 'Heliotrópio'),
(33, 'Hortelã'),
(65, 'Incenso'),
(22, 'Íris'),
(11, 'Jacarandá'),
(21, 'Jacinto'),
(4, 'Jasmim'),
(75, 'Jasmim Egípcio'),
(61, 'Jasmim Sambac'),
(41, 'Ládano'),
(25, 'Laranja Siciliana'),
(12, 'Lavanda'),
(2, 'Limão'),
(30, 'Limão de Amalfi'),
(45, 'Limão Italiano'),
(27, 'Limão Siciliano'),
(16, 'Lírio-do-Vale'),
(14, 'Maçã'),
(54, 'Maçã Granny Smith'),
(52, 'Madeira de Âmbar'),
(62, 'Madeira de Cashmere'),
(60, 'Magnólia'),
(55, 'Maracujá'),
(66, 'Mirra'),
(39, 'Morango'),
(63, 'Musgo'),
(78, 'Nagarmota'),
(86, 'Neroli'),
(5, 'Notas Balsâmicas'),
(1, 'Notas Frutadas'),
(79, 'Óleo de Cipriol'),
(88, 'Óleo de Vetiver Java'),
(67, 'Opoponax'),
(24, 'Patchouli'),
(40, 'Pêssego'),
(32, 'Petitgrain'),
(23, 'Pimenta Rosa'),
(6, 'Raiz de Orris'),
(15, 'Rosa'),
(59, 'Rosa Búlgara'),
(74, 'Rosa de Grasse'),
(73, 'Rosa Marroquina'),
(46, 'Rosa Turca'),
(87, 'Rum'),
(89, 'Sálvia Esclareia'),
(18, 'Sândalo'),
(69, 'Styrax'),
(48, 'Tabaco'),
(31, 'Tangerina'),
(43, 'Tinta Nanquim'),
(81, 'Toranja'),
(42, 'Vetiver'),
(72, 'Videoleiro'),
(83, 'Ylang Ylang');

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
  `id_marca` int(11) NOT NULL,
  `id_familia` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `perfumes`
--

INSERT INTO `perfumes` (`id_perfume`, `nome`, `descricao`, `preco`, `caminho_imagem`, `caminho_imagem_hover`, `id_marca`, `id_familia`, `stock`) VALUES
(1, 'Erba Pura Magica', 'Um perfume com notas orientais e frutadas.', 230.00, 'images/sospiro_erba_pura.jpg', 'images/sospiro_erbapura_hover.jpg', 1, 1, 10),
(2, 'Alexandria II', 'Uma fragrância clássica com notas orientais.', 340.00, 'images/alexandria_ii.jpg', 'images/alexandria_ii_hover.jpg', 2, 8, 0),
(3, 'Accento', 'Uma fragrância cítrica e amadeirada com notas de abacaxi e almíscar.', 300.00, 'images/accento.jpg', 'images/accento_hover.jpg', 2, 3, 12),
(4, 'Erba Pura', 'Uma fragrância oriental fresca e frutada com notas de âmbar.', 320.00, 'images/erba_pura.jpg', 'images/erba_pura_hover.jpg', 2, 1, 15),
(5, 'Renaissance', 'Uma fragrância fresca com toques cítricos e de menta.', 280.00, 'images/renaissance.jpg', 'images/renaissance_hover.jpg', 2, 1, 20),
(6, 'La Capitale', 'Uma fragrância gourmand com notas de morango e baunilha.', 350.00, 'images/la_capitale.jpg', 'images/la_capitale_hover.jpg', 2, 2, 10),
(7, 'Tabac Rose', 'Uma fragrância floral oriental com toques de tabaco e rosa.', 280.00, 'images/tabac_rose.jpg', 'images/tabac_rose_hover.jpg', 3, NULL, 15),
(8, 'Velvet Tonka', 'Um perfume gourmand com toques de fava tonka e baunilha.', 240.00, 'images/velvet_tonka.jpg', 'images/velvet_tonka_hover.jpg', 3, NULL, 8),
(9, 'Dahab', 'Uma fragrância frutada com maçã verde e frutas exóticas.', 270.00, 'images/dahab.jpg', 'images/dahab_hover.jpg', 4, NULL, 18),
(10, 'Lamar', 'Uma fragrância floral com notas de rosa e almíscar.', 300.00, 'images/lamar.jpg', 'images/lamar_hover.jpg', 4, NULL, 10),
(11, 'Mortal Skin', 'Uma fragrância amadeirada e misteriosa com notas de âmbar.', 380.00, 'images/mortal_skin.jpg', 'images/mortal_skin_hover.jpg', 5, NULL, 7),
(12, 'Oumma', 'Uma fragrância oriental com notas de oud e especiarias.', 420.00, 'images/oumma.jpg', 'images/oumma_hover.jpg', 5, NULL, 12),
(13, 'Himalaya', 'Uma fragrância fresca com toques de limão e almíscar.', 400.00, 'images/himalaya.jpg', 'images/himalaya_hover.jpg', 6, NULL, 15),
(14, 'Viking', 'Uma fragrância amadeirada com notas de especiarias.', 420.00, 'images/viking.jpg', 'images/viking_hover.jpg', 6, NULL, 12),
(15, 'Beach Walk', 'Uma fragrância fresca com toques de limão e coco.', 190.00, 'images/beach_walk.jpg', 'images/beach_walk_hover.jpg', 7, NULL, 25),
(16, 'Jazz Club', 'Uma fragrância amadeirada com toques de rum e tabaco.', 210.00, 'images/jazz_club.jpg', 'images/jazz_club_hover.jpg', 7, NULL, 18);

-- --------------------------------------------------------

--
-- Estrutura da tabela `perfume_notas`
--

CREATE TABLE `perfume_notas` (
  `id_perfume_nota` int(11) NOT NULL,
  `id_perfume` int(11) NOT NULL,
  `id_nota` int(11) NOT NULL,
  `tipo_nota` enum('topo','base','coracao') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `perfume_notas`
--

INSERT INTO `perfume_notas` (`id_perfume_nota`, `id_perfume`, `id_nota`, `tipo_nota`) VALUES
(1, 1, 1, 'topo'),
(2, 1, 2, 'topo'),
(3, 1, 3, 'topo'),
(4, 1, 4, 'topo'),
(5, 1, 5, 'coracao'),
(6, 1, 6, 'coracao'),
(7, 1, 7, 'base'),
(8, 1, 8, 'base'),
(9, 1, 9, 'base'),
(10, 1, 10, 'base'),
(11, 2, 11, 'topo'),
(12, 2, 12, 'topo'),
(13, 2, 13, 'topo'),
(14, 2, 14, 'topo'),
(15, 2, 15, 'coracao'),
(16, 2, 10, 'coracao'),
(17, 2, 16, 'coracao'),
(18, 2, 17, 'base'),
(19, 2, 18, 'base'),
(20, 2, 8, 'base'),
(21, 2, 19, 'base'),
(22, 2, 7, 'base'),
(23, 3, 20, 'topo'),
(24, 3, 21, 'topo'),
(25, 3, 22, 'coracao'),
(26, 3, 23, 'coracao'),
(27, 3, 4, 'coracao'),
(28, 3, 7, 'base'),
(29, 3, 8, 'base'),
(30, 3, 19, 'base'),
(31, 3, 24, 'base'),
(32, 4, 25, 'topo'),
(33, 4, 26, 'topo'),
(34, 4, 27, 'topo'),
(35, 4, 28, 'coracao'),
(36, 4, 29, 'base'),
(37, 4, 7, 'base'),
(38, 4, 8, 'base'),
(39, 5, 31, 'topo'),
(40, 5, 32, 'topo'),
(41, 5, 3, 'topo'),
(42, 5, 30, 'topo'),
(43, 5, 33, 'coracao'),
(44, 5, 16, 'coracao'),
(45, 5, 15, 'coracao'),
(46, 5, 7, 'base'),
(47, 5, 10, 'base'),
(48, 5, 24, 'base'),
(49, 5, 8, 'base'),
(50, 6, 40, 'topo'),
(51, 6, 9, 'topo'),
(52, 6, 41, 'topo'),
(53, 6, 39, 'topo'),
(54, 6, 35, 'coracao'),
(55, 6, 8, 'coracao'),
(56, 6, 36, 'coracao'),
(57, 6, 34, 'coracao'),
(58, 6, 15, 'coracao'),
(59, 6, 38, 'base'),
(60, 6, 37, 'base');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(220) NOT NULL,
  `email` varchar(150) NOT NULL,
  `tipo` enum('Admin','trabalhador','cliente') NOT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `tbl_user`
--

INSERT INTO `tbl_user` (`id_user`, `username`, `password`, `email`, `tipo`, `criado_em`) VALUES
(1, 'admin', 'admin', 'admin@example.com', 'Admin', '2025-01-26 22:50:33'),
(2, 'trabalhador1', 'trabalhador', 'trabalhador@example.com', 'trabalhador', '2025-01-26 22:50:33'),
(3, 'cliente1', 'cliente', 'cliente@example.com', 'cliente', '2025-01-26 22:50:33');

-- --------------------------------------------------------

--
-- Estrutura da tabela `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `data_adicionado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `wishlist`
--

INSERT INTO `wishlist` (`id`, `id_user`, `id_produto`, `data_adicionado`) VALUES
(36, 3, 1, '2025-02-19 14:42:42'),
(40, 3, 3, '2025-02-21 08:58:18');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `carrinho`
--
ALTER TABLE `carrinho`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_produto` (`id_produto`);

--
-- Índices para tabela `familias_olfativas`
--
ALTER TABLE `familias_olfativas`
  ADD PRIMARY KEY (`id_familia`);

--
-- Índices para tabela `familia_notas`
--
ALTER TABLE `familia_notas`
  ADD PRIMARY KEY (`id_familia_nota`),
  ADD KEY `id_familia` (`id_familia`),
  ADD KEY `id_nota` (`id_nota`);

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
-- Índices para tabela `notas_geral`
--
ALTER TABLE `notas_geral`
  ADD PRIMARY KEY (`id_nota`),
  ADD UNIQUE KEY `nome_nota` (`nome_nota`),
  ADD UNIQUE KEY `nome_nota_2` (`nome_nota`);

--
-- Índices para tabela `perfumes`
--
ALTER TABLE `perfumes`
  ADD PRIMARY KEY (`id_perfume`),
  ADD KEY `id_marca` (`id_marca`),
  ADD KEY `fk_perfumes_familias` (`id_familia`);

--
-- Índices para tabela `perfume_notas`
--
ALTER TABLE `perfume_notas`
  ADD PRIMARY KEY (`id_perfume_nota`),
  ADD KEY `fk_perfume` (`id_perfume`),
  ADD KEY `fk_nota` (`id_nota`);

--
-- Índices para tabela `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- Índices para tabela `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_produto` (`id_produto`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `carrinho`
--
ALTER TABLE `carrinho`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `familias_olfativas`
--
ALTER TABLE `familias_olfativas`
  MODIFY `id_familia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `familia_notas`
--
ALTER TABLE `familia_notas`
  MODIFY `id_familia_nota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT de tabela `imagens_perfume`
--
ALTER TABLE `imagens_perfume`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `notas_geral`
--
ALTER TABLE `notas_geral`
  MODIFY `id_nota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT de tabela `perfumes`
--
ALTER TABLE `perfumes`
  MODIFY `id_perfume` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `perfume_notas`
--
ALTER TABLE `perfume_notas`
  MODIFY `id_perfume_nota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de tabela `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `carrinho`
--
ALTER TABLE `carrinho`
  ADD CONSTRAINT `carrinho_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_user` (`id_user`),
  ADD CONSTRAINT `carrinho_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `perfumes` (`id_perfume`);

--
-- Limitadores para a tabela `familia_notas`
--
ALTER TABLE `familia_notas`
  ADD CONSTRAINT `familia_notas_ibfk_1` FOREIGN KEY (`id_familia`) REFERENCES `familias_olfativas` (`id_familia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `familia_notas_ibfk_2` FOREIGN KEY (`id_nota`) REFERENCES `notas_geral` (`id_nota`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `imagens_perfume`
--
ALTER TABLE `imagens_perfume`
  ADD CONSTRAINT `imagens_perfume_ibfk_1` FOREIGN KEY (`perfume_id`) REFERENCES `perfumes` (`id_perfume`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `perfumes`
--
ALTER TABLE `perfumes`
  ADD CONSTRAINT `fk_perfumes_familias` FOREIGN KEY (`id_familia`) REFERENCES `familias_olfativas` (`id_familia`) ON DELETE SET NULL,
  ADD CONSTRAINT `perfumes_ibfk_1` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `perfume_notas`
--
ALTER TABLE `perfume_notas`
  ADD CONSTRAINT `fk_nota` FOREIGN KEY (`id_nota`) REFERENCES `notas_geral` (`id_nota`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_perfume` FOREIGN KEY (`id_perfume`) REFERENCES `perfumes` (`id_perfume`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `tbl_user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `perfumes` (`id_perfume`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
