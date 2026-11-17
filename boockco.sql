-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-11-2026 a las 21:23:45
-- Versión del servidor: 10.11.16-MariaDB
-- Versión de PHP: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `boockco`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_buscar_productos` (IN `p_termino` VARCHAR(255), IN `p_id_usuario` INT)   BEGIN
    DECLARE total_res INT;
    
    SELECT COUNT(*) INTO total_res 
    FROM productos 
    WHERE activo = TRUE AND (nombre LIKE CONCAT('%', p_termino, '%') OR descripcion LIKE CONCAT('%', p_termino, '%'));

    INSERT INTO barra_busqueda_historial (id_usuario, termino_buscado, resultados_encontrados)
    VALUES (p_id_usuario, p_termino, total_res);

    SELECT p.*, c.nombre_visible AS categoria
    FROM productos p
    JOIN categorias c ON p.id_categoria = c.id_categoria
    WHERE p.activo = TRUE 
      AND (p.nombre LIKE CONCAT('%', p_termino, '%') OR p.descripcion LIKE CONCAT('%', p_termino, '%'));
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_login` (IN `p_id_usuario` INT, IN `p_token` VARCHAR(255), IN `p_ip` VARCHAR(45), IN `p_dispositivo` VARCHAR(255))   BEGIN
    INSERT INTO sesiones_usuario (id_usuario, token_sesion, ip_origen, dispositivo, estado_sesion)
    VALUES (p_id_usuario, p_token, p_ip, p_dispositivo, 'activa');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_logout` (IN `p_token` VARCHAR(255))   BEGIN
    UPDATE sesiones_usuario 
    SET fecha_logout = CURRENT_TIMESTAMP, estado_sesion = 'cerrada'
    WHERE token_sesion = p_token AND estado_sesion = 'activa';
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acciones_carrito`
--

CREATE TABLE `acciones_carrito` (
  `id_accion` int(11) NOT NULL,
  `id_carrito` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `tipo_accion` enum('agregar_producto','eliminar_producto','actualizar_cantidad','vaciar_carrito','aplicar_cupon','iniciar_checkout') NOT NULL,
  `detalles` varchar(255) DEFAULT NULL,
  `fecha_accion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `barra_busqueda_historial`
--

CREATE TABLE `barra_busqueda_historial` (
  `id_busqueda` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `termino_buscado` varchar(255) NOT NULL,
  `categoria_filtrada` varchar(50) DEFAULT NULL,
  `resultados_encontrados` int(11) DEFAULT 0,
  `fecha_busqueda` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carritos`
--

CREATE TABLE `carritos` (
  `id_carrito` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `session_invitado` varchar(255) DEFAULT NULL,
  `estado` enum('abierto','procesado','abandonado') DEFAULT 'abierto',
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carritos`
--

INSERT INTO `carritos` (`id_carrito`, `id_usuario`, `session_invitado`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 2, NULL, '', '2026-11-15 20:17:42', '2026-11-15 21:29:09'),
(2, 2, NULL, '', '2026-11-15 21:29:09', '2026-11-15 21:29:18'),
(3, 2, NULL, 'abierto', '2026-11-15 21:29:18', '2026-11-15 21:29:18'),
(4, 6, NULL, 'abierto', '2026-11-15 23:09:08', '2026-11-15 23:09:08'),
(5, 8, NULL, '', '2026-11-16 12:37:28', '2026-11-16 12:39:28'),
(6, 8, NULL, 'abierto', '2026-11-16 12:39:28', '2026-11-16 12:39:28'),
(7, 4, NULL, '', '2026-11-16 12:42:56', '2026-11-17 19:54:37'),
(8, 10, NULL, '', '2026-11-17 15:11:28', '2026-11-17 17:02:53'),
(9, 10, NULL, 'abierto', '2026-11-17 17:02:54', '2026-11-17 17:02:54'),
(10, 4, NULL, 'abierto', '2026-11-17 19:54:37', '2026-11-17 19:54:37'),
(11, 11, NULL, '', '2026-11-17 19:56:58', '2026-11-17 19:59:07'),
(12, 11, NULL, 'abierto', '2026-11-17 19:59:07', '2026-11-17 19:59:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito_items`
--

CREATE TABLE `carrito_items` (
  `id_item` int(11) NOT NULL,
  `id_carrito` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `fecha_agregado` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito_items`
--

INSERT INTO `carrito_items` (`id_item`, `id_carrito`, `id_producto`, `cantidad`, `precio_unitario`, `fecha_agregado`) VALUES
(1, 1, 18, 1, 34000.00, '2026-11-15 21:27:44'),
(2, 1, 27, 1, 25800.00, '2026-11-15 21:27:51'),
(3, 2, 26, 1, 22500.00, '2026-11-15 21:29:16'),
(4, 4, 19, 1, 34500.00, '2026-11-15 23:09:08'),
(5, 5, 17, 1, 33500.00, '2026-11-16 12:37:28'),
(6, 5, 14, 1, 27500.00, '2026-11-16 12:37:48'),
(7, 5, 28, 1, 29000.00, '2026-11-16 12:38:23'),
(8, 5, 36, 1, 13500.00, '2026-11-16 12:38:30'),
(9, 5, 42, 1, 315000.00, '2026-11-16 12:38:41'),
(10, 5, 55, 1, 2000.00, '2026-11-16 12:38:47'),
(14, 8, 10, 1, 37000.00, '2026-11-17 17:02:38'),
(15, 8, 23, 1, 12000.00, '2026-11-17 17:02:48'),
(22, 7, 7, 1, 35900.00, '2026-11-17 19:46:10'),
(23, 7, 8, 1, 37500.00, '2026-11-17 19:54:26'),
(24, 7, 55, 1, 2000.00, '2026-11-17 19:54:30'),
(25, 11, 1, 1, 28500.00, '2026-11-17 19:56:58'),
(26, 11, 28, 1, 29000.00, '2026-11-17 19:58:13'),
(27, 11, 55, 1, 2000.00, '2026-11-17 19:58:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre_clave` varchar(50) NOT NULL,
  `nombre_visible` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre_clave`, `nombre_visible`, `descripcion`, `activa`) VALUES
(1, 'categoria_libros', 'Libros', 'Libros físicos de diversos géneros y editoriales', 1),
(2, 'categoria_postits', 'Post-its y Notas', 'Notas adhesivas, señaladores y papelería', 1),
(3, 'categoria_kindles', 'Kindles y E-readers', 'Lectores de libros electrónicos y dispositivos', 1),
(4, 'categoria_lamparas', 'Lámparas de Lectura', 'Iluminación portátil, de clip y de escritorio', 1),
(5, 'categoria_fundas', 'Fundas y Protectores', 'Fundas para Kindle, tablets y libros', 1),
(6, 'categoria_marcapaginas', 'Marcapáginas', 'Separadores magnéticos, metálicos y de papel', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `panel_administracion_logs`
--

CREATE TABLE `panel_administracion_logs` (
  `id_log` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `modulo_afectado` enum('productos','categorias','usuarios','pedidos','configuracion') NOT NULL,
  `accion_ejecutada` enum('crear','editar','eliminar','cambiar_estado','exportar_datos') NOT NULL,
  `id_registro_afectado` int(11) DEFAULT NULL,
  `descripcion_cambio` text NOT NULL,
  `fecha_ejecucion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `panel_administracion_logs`
--

INSERT INTO `panel_administracion_logs` (`id_log`, `id_admin`, `modulo_afectado`, `accion_ejecutada`, `id_registro_afectado`, `descripcion_cambio`, `fecha_ejecucion`) VALUES
(1, 4, 'usuarios', 'cambiar_estado', 1, 'Usuario archivado: sol@gmail.com', '2026-11-17 15:04:54'),
(2, 4, 'usuarios', 'cambiar_estado', 1, 'Usuario desarchivado: sol@gmail.com', '2026-11-17 15:05:07'),
(3, 4, 'productos', 'crear', 66, 'Producto creado: resaltador rosa pastel', '2026-11-17 15:08:09'),
(4, 4, 'productos', 'editar', 66, 'Producto marcado sin stock: resaltador rosa pastel', '2026-11-17 15:08:21'),
(5, 4, 'productos', 'eliminar', 66, 'Producto dado de baja: resaltador rosa pastel', '2026-11-17 15:09:37'),
(6, 4, 'usuarios', 'cambiar_estado', 10, 'Usuario archivado: a@gmail.com', '2026-11-17 17:04:00'),
(7, 4, 'usuarios', 'cambiar_estado', 10, 'Usuario desarchivado: a@gmail.com', '2026-11-17 17:04:08'),
(8, 4, 'productos', 'cambiar_estado', 66, 'Producto desarchivado: resaltador rosa pastel', '2026-11-17 17:06:57'),
(9, 4, 'productos', 'eliminar', 66, 'Producto dado de baja: resaltador rosa pastel', '2026-11-17 17:07:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `imagen_url` varchar(255) DEFAULT NULL,
  `atributos_extra` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`atributos_extra`)),
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `id_categoria`, `sku`, `nombre`, `descripcion`, `precio`, `stock`, `imagen_url`, `atributos_extra`, `activo`, `fecha_creacion`) VALUES
(1, 1, 'LIB-001', 'Hasta quedarnos sin estrellas', 'Novela romántica juvenil escrita por Inma Rubiales.', 28500.00, 25, 'libro1.jpg', '{\"autor\": \"Inma Rubiales\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(2, 1, 'LIB-002', 'El arte de ser nosotros', 'Novela romántica contemporánea escrita por Inma Rubiales.', 29900.00, 30, 'libro2.jpg', '{\"autor\": \"Inma Rubiales\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(3, 1, 'LIB-003', 'Todos los lugares que mantuvimos en secreto', 'Historia de amor y autodescubrimiento escrita por Inma Rubiales.', 29500.00, 20, 'libro3.jpg', '{\"autor\": \"Inma Rubiales\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(4, 1, 'LIB-004', 'Nuestro lugar en el mundo', 'Novela juvenil de romance escrita por Inma Rubiales.', 31000.00, 25, 'libro4.jpg', '{\"autor\": \"Inma Rubiales\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(5, 1, 'LIB-005', 'Binding 13', 'Primera entrega de la serie Los chicos de Tommen por Chloe Walsh.', 36500.00, 40, 'libro5.jpg', '{\"autor\": \"Chloe Walsh\", \"serie\": \"Los chicos de Tommen\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(6, 1, 'LIB-006', 'Keeping 13', 'Segunda entrega de la serie Los chicos de Tommen por Chloe Walsh.', 38000.00, 35, 'libro6.jpg', '{\"autor\": \"Chloe Walsh\", \"serie\": \"Los chicos de Tommen\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(7, 1, 'LIB-007', 'Saving 6', 'Tercera entrega de la serie Los chicos de Tommen por Chloe Walsh.', 35900.00, 30, 'libro7.jpg', '{\"autor\": \"Chloe Walsh\", \"serie\": \"Los chicos de Tommen\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(8, 1, 'LIB-008', 'Redeeming 6', 'Cuarta entrega de la serie Los chicos de Tommen por Chloe Walsh.', 37500.00, 30, 'libro8.jpg', '{\"autor\": \"Chloe Walsh\", \"serie\": \"Los chicos de Tommen\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(9, 1, 'LIB-009', 'Taming 7', 'Quinta entrega de la serie Los chicos de Tommen por Chloe Walsh.', 36000.00, 35, 'libro9.jpg', '{\"autor\": \"Chloe Walsh\", \"serie\": \"Los chicos de Tommen\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(10, 1, 'LIB-010', 'Releasing 10', 'Sexta entrega de la serie Los chicos de Tommen por Chloe Walsh.', 37000.00, 25, 'libro10.jpg', '{\"autor\": \"Chloe Walsh\", \"serie\": \"Los chicos de Tommen\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(11, 1, 'LIB-011', 'El color de las cosas invisibles', 'Novela romántica contemporánea escrita por Andrea Longarela.', 27900.00, 20, 'libro11.jpg', '{\"autor\": \"Andrea Longarela\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(12, 1, 'LIB-012', 'Mejor que en las películas', 'Comedia romántica juvenil escrita por Lynn Painter.', 26500.00, 40, 'libro12.jpg', '{\"autor\": \"Lynn Painter\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(13, 1, 'LIB-013', 'Nada como en las películas', 'Secuela de Mejor que en las películas escrita por Lynn Painter.', 28000.00, 35, 'libro13.jpg', '{\"autor\": \"Lynn Painter\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(14, 1, 'LIB-014', 'Farsa sobre hielo', 'Comedia romántica ambientada en el mundo del hockey por Lynn Painter.', 27500.00, 25, 'libro14.jpg', '{\"autor\": \"Lynn Painter\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(15, 1, 'LIB-015', 'El amor no es un juego', 'Comedia romántica contemporánea escrita por Sarah Adams.', 26900.00, 20, 'libro15.jpg', '{\"autor\": \"Sarah Adams\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(16, 1, 'LIB-016', 'Rozando el cielo', 'Primer libro de la serie Windy City escrita por Liz Tomforde.', 32500.00, 30, 'libro16.jpg', '{\"autor\": \"Liz Tomforde\", \"serie\": \"Windy City\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(17, 1, 'LIB-017', 'Jugando fuerte', 'Segundo libro de la serie Windy City escrita por Liz Tomforde.', 33500.00, 30, 'libro17.jpg', '{\"autor\": \"Liz Tomforde\", \"serie\": \"Windy City\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(18, 1, 'LIB-018', 'Perdiendo el control', 'Tercer libro de la serie Windy City escrita por Liz Tomforde.', 34000.00, 25, 'libro18.jpg', '{\"autor\": \"Liz Tomforde\", \"serie\": \"Windy City\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(19, 1, 'LIB-019', 'Siguiendo el juego', 'Cuarto libro de la serie Windy City escrita por Liz Tomforde.', 34500.00, 25, 'libro19.jpg', '{\"autor\": \"Liz Tomforde\", \"serie\": \"Windy City\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(20, 1, 'LIB-020', 'Volviendo a empezar', 'Quinto libro de la serie Windy City escrita por Liz Tomforde.', 35000.00, 20, 'libro20.jpg', '{\"autor\": \"Liz Tomforde\", \"serie\": \"Windy City\", \"formato\": \"Tapa blanda\"}', 1, '2026-11-15 17:52:06'),
(21, 2, 'POS-001', 'Pack de Post-its por 5 unidades', 'Notas adhesivas de colores variados, pack x5.', 4500.00, 50, 'postit1.jpg', '{\"cantidad\": 5, \"tipo\": \"Notas adhesivas\"}', 1, '2026-11-15 17:55:27'),
(22, 2, 'POS-002', 'Pack de Post-its por 10 unidades', 'Notas adhesivas de colores variados, pack x10.', 8500.00, 45, 'postit2.jpg', '{\"cantidad\": 10, \"tipo\": \"Notas adhesivas\"}', 1, '2026-11-15 17:55:27'),
(23, 2, 'POS-003', 'Pack de Post-its por 15 unidades', 'Notas adhesivas de colores variados, pack x15.', 12000.00, 40, 'postit3.jpg', '{\"cantidad\": 15, \"tipo\": \"Notas adhesivas\"}', 1, '2026-11-15 17:55:27'),
(24, 2, 'POS-004', 'Pack de Post-its por 20 unidades', 'Notas adhesivas de colores variados, pack x20.', 15500.00, 35, 'postit4.jpg', '{\"cantidad\": 20, \"tipo\": \"Notas adhesivas\"}', 1, '2026-11-15 17:55:27'),
(25, 2, 'POS-005', 'Pack de Post-its por 25 unidades', 'Notas adhesivas de colores variados, pack x25.', 19000.00, 30, 'postit5.jpg', '{\"cantidad\": 25, \"tipo\": \"Notas adhesivas\"}', 1, '2026-11-15 17:55:27'),
(26, 2, 'POS-006', 'Pack de Post-its por 30 unidades', 'Notas adhesivas de colores variados, pack x30.', 22500.00, 25, 'postit6.jpg', '{\"cantidad\": 30, \"tipo\": \"Notas adhesivas\"}', 1, '2026-11-15 17:55:27'),
(27, 2, 'POS-007', 'Pack de Post-its por 35 unidades', 'Notas adhesivas de colores variados, pack x35.', 25800.00, 20, 'postit7.jpg', '{\"cantidad\": 35, \"tipo\": \"Notas adhesivas\"}', 1, '2026-11-15 17:55:27'),
(28, 2, 'POS-008', 'Pack de Post-its por 40 unidades', 'Notas adhesivas de colores variados, pack x40.', 29000.00, 20, 'postit8.jpg', '{\"cantidad\": 40, \"tipo\": \"Notas adhesivas\"}', 1, '2026-11-15 17:55:27'),
(29, 5, 'FUN-001', 'Funda acolchada para Kindle Paperwhite - Diseño Minimalista', 'Funda protectora de tela acolchada con cierre, ideal para proteger tu Kindle contra golpes y rayas.', 12500.00, 30, 'fundas1.jpg', '{\"compatibilidad\": \"Kindle Paperwhite\", \"material\": \"Tela acolchada\"}', 1, '2026-11-15 17:57:02'),
(30, 5, 'FUN-002', 'Funda de cuerina impermeable para Kindle Basic', 'Funda elegante con interior suave y cierre magnético para lectura segura en cualquier lugar.', 14200.00, 25, 'fundas2.jpg', '{\"compatibilidad\": \"Kindle Basic\", \"material\": \"Cuerina PU\"}', 1, '2026-11-15 17:57:02'),
(31, 5, 'FUN-003', 'Funda sleeve con bolsillo para libros de bolsillo (Tapa Blanda)', 'Protector de tela estampa botánica con bolsillo frontal para marcadores y lapiceras.', 13000.00, 20, 'fundas3.jpg', '{\"compatibilidad\": \"Libros estándar de bolsillo\", \"material\": \"Lona de algodón\"}', 1, '2026-11-15 17:57:02'),
(32, 5, 'FUN-004', 'Funda acolchada grande para libros de Tapa Dura', 'Diseñada especialmente para novelas grandes y ediciones coleccionista. Gran protección.', 15500.00, 18, 'fundas4.jpg', '{\"compatibilidad\": \"Libros de tapa dura grandes\", \"material\": \"Cordura acolchada\"}', 1, '2026-11-15 17:57:02'),
(33, 5, 'FUN-005', 'Funda temática estilo vintage para Kindle Oasis', 'Funda protectora con diseño clásico de biblioteca antigua y cierre seguro.', 16000.00, 15, 'fundas5.jpg', '{\"compatibilidad\": \"Kindle Oasis\", \"material\": \"Textil y cuero sintético\"}', 1, '2026-11-15 17:57:02'),
(34, 5, 'FUN-006', 'Funda universal con cremallera para tablets de lectura y E-readers', 'Funda con interior de felpa anti-rayas y bolsillo organizador de accesorios.', 14900.00, 22, 'fundas6.jpg', '{\"compatibilidad\": \"Universal E-readers 6 a 7 pulgadas\", \"material\": \"Neopreno\"}', 1, '2026-11-15 17:57:02'),
(35, 5, 'FUN-007', 'Funda protectora impermeable estilo estuche para libros de viaje con consteaciones verde', 'Perfecta para llevar tus libros a la playa o pileta sin miedo a salpicaduras.', 13800.00, 20, 'fundas7.jpg', '{\"compatibilidad\": \"Libros medianos\", \"material\": \"Plástico impermeable y lona\"}', 1, '2026-11-15 17:57:02'),
(36, 5, 'FUN-008', 'Funda acolchada con diseño estético de constelaciones para Kindle', 'Protección con diseño astronómico y acolchado de alta densidad contra impactos.', 13500.00, 28, 'fundas8.jpg', '{\"compatibilidad\": \"Kindle Paperwhite / Basic\", \"material\": \"Algodón estampado\"}', 1, '2026-11-15 17:57:02'),
(37, 5, 'FUN-009', 'Funda portatodo literaria con manija para libros y cuadernos', 'Funda tipo bolso de mano acolchado con manija corta para transportar tus lecturas con estilo.', 16500.00, 15, 'fundas9.jpg', '{\"compatibilidad\": \"Libros tamaño estándar y grandes\", \"material\": \"Lona reforzada\"}', 1, '2026-11-15 17:57:02'),
(38, 3, 'KIN-001', 'Kindle Basic (11ª Generación) - 16GB - Negro', 'El Kindle más ligero y compacto, ahora con pantalla de alta resolución de 300 ppp y luz frontal ajustable.', 115000.00, 15, 'kindle1.jpg', '{\"almacenamiento\": \"16GB\", \"pantalla\": \"6 pulgadas 300 ppp\", \"luz\": \"Sí\"}', 1, '2026-11-15 17:57:47'),
(39, 3, 'KIN-002', 'Kindle Paperwhite (11ª Gen) - 8GB - Negro', 'Pantalla de 6.8 pulgadas sin reflejos, luz cálida ajustable, batería de semanas y resistencia al agua IPX8.', 145000.00, 12, 'kindle2.jpg', '{\"almacenamiento\": \"8GB\", \"pantalla\": \"6.8 pulgadas\", \"resistencia_agua\": \"IPX8\"}', 1, '2026-11-15 17:57:47'),
(40, 3, 'KIN-003', 'Kindle Paperwhite Signature Edition - 32GB - Negro Salvia', 'Incluye carga inalámbrica, luz frontal con autorregulación y 32 GB de almacenamiento.', 178000.00, 10, 'kindle3.jpg', '{\"almacenamiento\": \"32GB\", \"carga\": \"Inalámbrica\", \"luz\": \"Autorregulable\"}', 1, '2026-11-15 17:57:47'),
(41, 3, 'KIN-004', 'Kindle Oasis - 8GB - Grafito con botones de cambio de página', 'Diseño ergonómico con botones dedicados para pasar página y pantalla de alta resolución de 7 pulgadas.', 210000.00, 8, 'kindle4.jpg', '{\"almacenamiento\": \"8GB\", \"pantalla\": \"7 pulgadas\", \"botones\": \"Sí\"}', 1, '2026-11-15 17:57:47'),
(42, 3, 'KIN-005', 'Kindle Scribe - 16GB con Lápiz Básico', 'El primer Kindle para leer y escribir, con pantalla Paperwhite de 10.2 pulgadas a 300 ppp y lápiz incluido.', 315000.00, 6, 'kindle5.jpg', '{\"almacenamiento\": \"16GB\", \"pantalla\": \"10.2 pulgadas\", \"incluye\": \"Lápiz básico\"}', 1, '2026-11-15 17:57:47'),
(43, 3, 'KIN-006', 'Kindle Paperwhite Kids - 8GB - Diseño Planeta Espacial', 'Diseñado para niños: incluye una funda protectora infantil, 2 años de garantía sin preocupaciones y suscripción a Kids+.', 155000.00, 10, 'kindle6.jpg', '{\"edicion\": \"Kids\", \"almacenamiento\": \"8GB\", \"incluye\": \"Funda y garantía\"}', 1, '2026-11-15 17:57:47'),
(44, 3, 'KIN-007', 'Kindle Oasis - 32GB con Conexión Celular Gratuita (Wi-Fi + 4G)', 'Máxima capacidad de almacenamiento y conectividad móvil integrada para descargar libros en cualquier lugar.', 250000.00, 5, 'kindle7.jpg', '{\"almacenamiento\": \"32GB\", \"conectividad\": \"Wi-Fi + 4G gratuito\"}', 1, '2026-11-15 17:57:47'),
(45, 3, 'KIN-008', 'Kindle Scribe - 32GB con Lápiz Premium', 'Incluye el lápiz Premium con borrador dedicado y botón de acceso directo personalizable.', 345000.00, 7, 'kindle8.jpg', '{\"almacenamiento\": \"32GB\", \"pantalla\": \"10.2 pulgadas\", \"incluye\": \"Lápiz premium\"}', 1, '2026-11-15 17:57:47'),
(46, 3, 'KIN-009', 'Kindle Basic (10ª Generación) - 8GB - Blanco', 'Modelo clásico reacondicionado certificado, con luz frontal integrada y pantalla de 6 pulgadas.', 95000.00, 14, 'kindle9.jpg', '{\"almacenamiento\": \"8GB\", \"condicion\": \"Reacondicionado\", \"color\": \"Blanco\"}', 1, '2026-11-15 17:57:47'),
(47, 6, 'MAR-001', 'Marcapáginas magnético diseño constelaciones', 'Separador de páginas magnético que se adhiere firmemente a la hoja sin dañarla.', 1500.00, 50, 'marcapagina1.jpg', '{\"material\": \"Papel imantado\", \"tipo\": \"Magnético\"}', 1, '2026-11-15 17:58:30'),
(48, 6, 'MAR-002', 'Marcapáginas metálico espada medieval de fantasía', 'Separador de metal con detalles grabados ideal para libros de lectura juvenil y fantasía.', 2800.00, 40, 'marcapagina2.jpg', '{\"material\": \"Aleación metálica\", \"tipo\": \"Colgante\"}', 1, '2026-11-15 17:58:30'),
(49, 6, 'MAR-003', 'Pack de marcapáginas de cuero sintético con frase', 'Marcapáginas esquinero de cuero ecológico grabado con frases motivadoras para lectores.', 2200.00, 35, 'marcapagina3.jpg', '{\"material\": \"Cuero sintético\", \"tipo\": \"Esquinero\"}', 1, '2026-11-15 17:58:30'),
(50, 6, 'MAR-004', 'Marcapáginas de acetato transparente con diseño floral', 'Lámina transparente resistente al agua con ilustraciones botánicas y detalles dorados.', 1800.00, 45, 'marcapagina4.jpg', '{\"material\": \"Acetato\", \"tipo\": \"Lámina\"}', 1, '2026-11-15 17:58:30'),
(51, 6, 'MAR-005', 'Marcapáginas con borla de hilo de seda colorido', 'Clásico marcapáginas de cartulina encauchada de alta densidad con borla decorativa.', 1200.00, 60, 'marcapagina5.jpg', '{\"material\": \"Cartulina ilustración y seda\", \"tipo\": \"Clásico con borla\"}', 1, '2026-11-15 17:58:30'),
(52, 6, 'MAR-006', 'Marcapáginas de madera grabado láser con diseño de libros', 'Madera fina calada con láser y cinta de tela superior.', 3100.00, 30, 'marcapagina6.jpg', '{\"material\": \"Madera multilaminada\", \"tipo\": \"Calado láser\"}', 1, '2026-11-15 17:58:30'),
(53, 6, 'MAR-007', 'Marcapàginas de madera', 'Pequeña lámpara marcapáginas ultra fina con luz LED para leer de noche en la cama.', 4500.00, 25, 'marcapagina7.jpg', '{\"material\": \"Plástico ABS y LED\", \"tipo\": \"Iluminado\"}', 1, '2026-11-15 17:58:30'),
(54, 6, 'MAR-008', 'Marcapáginas bordado a mano estilo vintage', 'Delicado diseño en tela con bordados florales y terminación reforzada.', 3500.00, 20, 'marcapagina8.jpg', '{\"material\": \"Tela y hilo de bordar\", \"tipo\": \"Bordado artesanal\"}', 1, '2026-11-15 17:58:30'),
(55, 6, 'MAR-009', 'Marcapáginas 3D con diseño de animalitos asomándose', 'Divertido marcapáginas tridimensional de PVC flexible que simula un animal apoyado en el renglón.', 2000.00, 40, 'marcapagina9.jpg', '{\"material\": \"PVC flexible 3D\", \"tipo\": \"Tridimensional\"}', 1, '2026-11-15 17:58:30'),
(56, 4, 'LAM-001', 'Lámpara de lectura LED con clip flexible - Recargable USB', 'Luz de lectura portátil con cuello flexible de 360 grados y 3 niveles de temperatura de color.', 8900.00, 30, 'lamparas1.jpg', '{\"alimentación\": \"Batería recargable USB\", \"niveles_luz\": 3, \"tipo\": \"Clip\"}', 1, '2026-11-15 18:01:32'),
(57, 4, 'LAM-002', 'Lámpara de cuello para lectura manos libres', 'Diseño ergonómico que se cuelga del cuello con doble cabezal LED ajustable para no molestar a nadie.', 12500.00, 25, 'lamparas2.jpg', '{\"alimentación\": \"Batería recargable\", \"diseño\": \"Colgante de cuello\"}', 1, '2026-11-15 18:01:32'),
(58, 4, 'LAM-003', 'Lámpara de escritorio LED táctil regulable', 'Lámpara de mesa moderna con control táctil de intensidad y protección ocular anti-parpadeo.', 16800.00, 20, 'lamparas3.jpg', '{\"alimentación\": \"Enchufe 220V\", \"control\": \"Táctil\"}', 1, '2026-11-15 18:01:32'),
(59, 4, 'LAM-004', 'Mini luz LED de lectura con USB directo', 'Pequeña lámpara flexible ideal para conectar directamente a un puerto USB o power bank.', 4200.00, 50, 'lamparas4.jpg', '{\"alimentación\": \"Conector USB directo\", \"portabilidad\": \"Alta\"}', 1, '2026-11-15 18:01:32'),
(60, 4, 'LAM-005', 'Lámpara de lectura con pinza y filtro de luz azul', 'Protege tus ojos por la noche reduciendo la emisión de luz azul para mejorar el descanso.', 10500.00, 28, 'lamparas5.jpg', '{\"característica\": \"Filtro de luz azul\", \"tipo\": \"Clip\"}', 1, '2026-11-15 18:01:32'),
(61, 4, 'LAM-006', 'Lámpara de lectura recargable con indicador de batería', 'Modelo ultra delgado con batería de larga duración y visor digital del nivel de carga restante.', 13400.00, 22, 'lamparas6.jpg', '{\"autonomía\": \"Hasta 10 horas\", \"indicador\": \"Digital\"}', 1, '2026-11-15 18:01:32'),
(62, 4, 'LAM-007', 'Lámpara de libro estilo marcapáginas con luz integrada', 'Lámina transparente iluminada que se coloca directamente sobre la página del libro.', 7500.00, 35, 'lamparas7.jpg', '{\"tipo\": \"Placa acrílica iluminada\", \"diseño\": \"Marcapáginas\"}', 1, '2026-11-15 18:01:32'),
(63, 4, 'LAM-008', 'Lámpara de escritorio plegable con batería y soporte para celular', 'Ideal para estudiar o leer, incluye base organizadora y puerto de carga para dispositivos.', 18900.00, 15, 'lamparas8.jpg', '{\"extras\": \"Soporte para celular\", \"plegable\": \"Sí\"}', 1, '2026-11-15 18:01:32'),
(64, 4, 'LAM-009', 'Lámpara magnética de pared para cabecera de cama', 'Luz de lectura inalámbrica con base magnética desmontable para fijar en cualquier superficie.', 14200.00, 18, 'lamparas9.jpg', '{\"fijación\": \"Magnética adhesiva\", \"inalámbrica\": \"Sí\"}', 1, '2026-11-15 18:01:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_usuario`
--

CREATE TABLE `sesiones_usuario` (
  `id_sesion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `token_sesion` varchar(255) NOT NULL,
  `ip_origen` varchar(45) DEFAULT NULL,
  `dispositivo` varchar(255) DEFAULT NULL,
  `fecha_login` timestamp NULL DEFAULT current_timestamp(),
  `fecha_logout` timestamp NULL DEFAULT NULL,
  `estado_sesion` enum('activa','cerrada','expirada') DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('cliente','admin') DEFAULT 'cliente',
  `estado` enum('activo','suspendido','pendiente') DEFAULT 'activo',
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `ultima_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `email`, `password_hash`, `rol`, `estado`, `fecha_creacion`, `ultima_actualizacion`) VALUES
(1, 'Sol', 'Rodriguez', 'sol@gmail.com', '$2y$10$0G4ORD1HxCNyoMfOyj.22OLkptbGpqkymmRuEsyb2c4onCp5Pxdvy', 'cliente', 'activo', '2026-11-15 19:47:04', '2026-11-17 15:05:07'),
(2, 'maria', 'hunt', 'lulu@gmail.com', '$2y$10$kSntitYtvX/Jv7.1zVkAPuohpDjSx/1Tz6JL6YjaNICSi6iRqagwe', 'cliente', 'activo', '2026-11-15 20:04:35', '2026-11-15 20:44:09'),
(4, 'Alma', 'Medrano Santoro', 'alma@gmail.com', '$2y$10$5m/WZC26PylvfPP5bRVtI.mpbvXdtwRjj/cObgzndJyUffg/.wXQW', 'admin', 'activo', '2026-11-15 21:45:57', '2026-11-16 12:41:48'),
(6, 'll', 'uu', 'uu@gmail.com', '$2y$10$9XoAj4yN4IkdPZo5KSGqWutj80/JqvvCDbp0HL96CYqz5CVMAzeA.', 'cliente', 'activo', '2026-11-15 22:45:34', '2026-11-15 22:45:34'),
(7, 'jaz', 'p', 'jajajaja123@gmail.com', '$2y$10$LZzg0Y/H0zkThTC2/YETUeZ7SuJOEvWU6iNWlujfdiza6XB.307L.', 'cliente', 'activo', '2026-11-16 12:35:38', '2026-11-16 12:35:38'),
(8, 'Jaz', 'Hola', 'jajaja1@gmail.com', '$2y$10$WtFekBNHYu2O3dw.VJZcG.nMIN.tBYk3oXYoavoKhwjZA6IDoW87i', 'cliente', 'activo', '2026-11-16 12:36:39', '2026-11-16 12:36:39'),
(10, 'iysdgiiyd', 'tgdsy', 'a@gmail.com', '$2y$10$sPTNgSl1h548N6Or1LUk7O.w10dcqpfidjs.n/gkIbfEyHNRKVa4W', 'cliente', 'activo', '2026-11-17 15:10:44', '2026-11-17 17:04:08'),
(11, 'Veronica', 'Santoro', 'vekaese.1970@gmail.com', '$2y$10$ow/0ZyirQpo9zvnzlvr7q.RO5SyccqWuZrpFM6O3IYHzu1fXy.oZ.', 'cliente', 'activo', '2026-11-17 19:55:50', '2026-11-17 19:55:50');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_categoria_fundas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_categoria_fundas` (
`id_producto` int(11)
,`id_categoria` int(11)
,`sku` varchar(50)
,`nombre` varchar(200)
,`descripcion` text
,`precio` decimal(10,2)
,`stock` int(11)
,`imagen_url` varchar(255)
,`atributos_extra` longtext
,`activo` tinyint(1)
,`fecha_creacion` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_categoria_kindles`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_categoria_kindles` (
`id_producto` int(11)
,`id_categoria` int(11)
,`sku` varchar(50)
,`nombre` varchar(200)
,`descripcion` text
,`precio` decimal(10,2)
,`stock` int(11)
,`imagen_url` varchar(255)
,`atributos_extra` longtext
,`activo` tinyint(1)
,`fecha_creacion` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_categoria_lamparas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_categoria_lamparas` (
`id_producto` int(11)
,`id_categoria` int(11)
,`sku` varchar(50)
,`nombre` varchar(200)
,`descripcion` text
,`precio` decimal(10,2)
,`stock` int(11)
,`imagen_url` varchar(255)
,`atributos_extra` longtext
,`activo` tinyint(1)
,`fecha_creacion` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_categoria_libros`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_categoria_libros` (
`id_producto` int(11)
,`id_categoria` int(11)
,`sku` varchar(50)
,`nombre` varchar(200)
,`descripcion` text
,`precio` decimal(10,2)
,`stock` int(11)
,`imagen_url` varchar(255)
,`atributos_extra` longtext
,`activo` tinyint(1)
,`fecha_creacion` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_categoria_marcapaginas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_categoria_marcapaginas` (
`id_producto` int(11)
,`id_categoria` int(11)
,`sku` varchar(50)
,`nombre` varchar(200)
,`descripcion` text
,`precio` decimal(10,2)
,`stock` int(11)
,`imagen_url` varchar(255)
,`atributos_extra` longtext
,`activo` tinyint(1)
,`fecha_creacion` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_categoria_postits`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_categoria_postits` (
`id_producto` int(11)
,`id_categoria` int(11)
,`sku` varchar(50)
,`nombre` varchar(200)
,`descripcion` text
,`precio` decimal(10,2)
,`stock` int(11)
,`imagen_url` varchar(255)
,`atributos_extra` longtext
,`activo` tinyint(1)
,`fecha_creacion` timestamp
);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `acciones_carrito`
--
ALTER TABLE `acciones_carrito`
  ADD PRIMARY KEY (`id_accion`),
  ADD KEY `id_carrito` (`id_carrito`);

--
-- Indices de la tabla `barra_busqueda_historial`
--
ALTER TABLE `barra_busqueda_historial`
  ADD PRIMARY KEY (`id_busqueda`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `carritos`
--
ALTER TABLE `carritos`
  ADD PRIMARY KEY (`id_carrito`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `carrito_items`
--
ALTER TABLE `carrito_items`
  ADD PRIMARY KEY (`id_item`),
  ADD UNIQUE KEY `unique_producto_carrito` (`id_carrito`,`id_producto`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre_clave` (`nombre_clave`);

--
-- Indices de la tabla `panel_administracion_logs`
--
ALTER TABLE `panel_administracion_logs`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `id_categoria` (`id_categoria`);
ALTER TABLE `productos` ADD FULLTEXT KEY `idx_busqueda_productos` (`nombre`,`descripcion`);

--
-- Indices de la tabla `sesiones_usuario`
--
ALTER TABLE `sesiones_usuario`
  ADD PRIMARY KEY (`id_sesion`),
  ADD UNIQUE KEY `token_sesion` (`token_sesion`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `acciones_carrito`
--
ALTER TABLE `acciones_carrito`
  MODIFY `id_accion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `barra_busqueda_historial`
--
ALTER TABLE `barra_busqueda_historial`
  MODIFY `id_busqueda` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carritos`
--
ALTER TABLE `carritos`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `carrito_items`
--
ALTER TABLE `carrito_items`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `panel_administracion_logs`
--
ALTER TABLE `panel_administracion_logs`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT de la tabla `sesiones_usuario`
--
ALTER TABLE `sesiones_usuario`
  MODIFY `id_sesion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_categoria_fundas`
--
DROP TABLE IF EXISTS `vista_categoria_fundas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_categoria_fundas`  AS SELECT `p`.`id_producto` AS `id_producto`, `p`.`id_categoria` AS `id_categoria`, `p`.`sku` AS `sku`, `p`.`nombre` AS `nombre`, `p`.`descripcion` AS `descripcion`, `p`.`precio` AS `precio`, `p`.`stock` AS `stock`, `p`.`imagen_url` AS `imagen_url`, `p`.`atributos_extra` AS `atributos_extra`, `p`.`activo` AS `activo`, `p`.`fecha_creacion` AS `fecha_creacion` FROM (`productos` `p` join `categorias` `c` on(`p`.`id_categoria` = `c`.`id_categoria`)) WHERE `c`.`nombre_clave` = 'categoria_fundas' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_categoria_kindles`
--
DROP TABLE IF EXISTS `vista_categoria_kindles`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_categoria_kindles`  AS SELECT `p`.`id_producto` AS `id_producto`, `p`.`id_categoria` AS `id_categoria`, `p`.`sku` AS `sku`, `p`.`nombre` AS `nombre`, `p`.`descripcion` AS `descripcion`, `p`.`precio` AS `precio`, `p`.`stock` AS `stock`, `p`.`imagen_url` AS `imagen_url`, `p`.`atributos_extra` AS `atributos_extra`, `p`.`activo` AS `activo`, `p`.`fecha_creacion` AS `fecha_creacion` FROM (`productos` `p` join `categorias` `c` on(`p`.`id_categoria` = `c`.`id_categoria`)) WHERE `c`.`nombre_clave` = 'categoria_kindles' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_categoria_lamparas`
--
DROP TABLE IF EXISTS `vista_categoria_lamparas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_categoria_lamparas`  AS SELECT `p`.`id_producto` AS `id_producto`, `p`.`id_categoria` AS `id_categoria`, `p`.`sku` AS `sku`, `p`.`nombre` AS `nombre`, `p`.`descripcion` AS `descripcion`, `p`.`precio` AS `precio`, `p`.`stock` AS `stock`, `p`.`imagen_url` AS `imagen_url`, `p`.`atributos_extra` AS `atributos_extra`, `p`.`activo` AS `activo`, `p`.`fecha_creacion` AS `fecha_creacion` FROM (`productos` `p` join `categorias` `c` on(`p`.`id_categoria` = `c`.`id_categoria`)) WHERE `c`.`nombre_clave` = 'categoria_lamparas' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_categoria_libros`
--
DROP TABLE IF EXISTS `vista_categoria_libros`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_categoria_libros`  AS SELECT `p`.`id_producto` AS `id_producto`, `p`.`id_categoria` AS `id_categoria`, `p`.`sku` AS `sku`, `p`.`nombre` AS `nombre`, `p`.`descripcion` AS `descripcion`, `p`.`precio` AS `precio`, `p`.`stock` AS `stock`, `p`.`imagen_url` AS `imagen_url`, `p`.`atributos_extra` AS `atributos_extra`, `p`.`activo` AS `activo`, `p`.`fecha_creacion` AS `fecha_creacion` FROM (`productos` `p` join `categorias` `c` on(`p`.`id_categoria` = `c`.`id_categoria`)) WHERE `c`.`nombre_clave` = 'categoria_libros' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_categoria_marcapaginas`
--
DROP TABLE IF EXISTS `vista_categoria_marcapaginas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_categoria_marcapaginas`  AS SELECT `p`.`id_producto` AS `id_producto`, `p`.`id_categoria` AS `id_categoria`, `p`.`sku` AS `sku`, `p`.`nombre` AS `nombre`, `p`.`descripcion` AS `descripcion`, `p`.`precio` AS `precio`, `p`.`stock` AS `stock`, `p`.`imagen_url` AS `imagen_url`, `p`.`atributos_extra` AS `atributos_extra`, `p`.`activo` AS `activo`, `p`.`fecha_creacion` AS `fecha_creacion` FROM (`productos` `p` join `categorias` `c` on(`p`.`id_categoria` = `c`.`id_categoria`)) WHERE `c`.`nombre_clave` = 'categoria_marcapaginas' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_categoria_postits`
--
DROP TABLE IF EXISTS `vista_categoria_postits`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_categoria_postits`  AS SELECT `p`.`id_producto` AS `id_producto`, `p`.`id_categoria` AS `id_categoria`, `p`.`sku` AS `sku`, `p`.`nombre` AS `nombre`, `p`.`descripcion` AS `descripcion`, `p`.`precio` AS `precio`, `p`.`stock` AS `stock`, `p`.`imagen_url` AS `imagen_url`, `p`.`atributos_extra` AS `atributos_extra`, `p`.`activo` AS `activo`, `p`.`fecha_creacion` AS `fecha_creacion` FROM (`productos` `p` join `categorias` `c` on(`p`.`id_categoria` = `c`.`id_categoria`)) WHERE `c`.`nombre_clave` = 'categoria_postits' ;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `acciones_carrito`
--
ALTER TABLE `acciones_carrito`
  ADD CONSTRAINT `acciones_carrito_ibfk_1` FOREIGN KEY (`id_carrito`) REFERENCES `carritos` (`id_carrito`) ON DELETE CASCADE;

--
-- Filtros para la tabla `barra_busqueda_historial`
--
ALTER TABLE `barra_busqueda_historial`
  ADD CONSTRAINT `barra_busqueda_historial_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `carritos`
--
ALTER TABLE `carritos`
  ADD CONSTRAINT `carritos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `carrito_items`
--
ALTER TABLE `carrito_items`
  ADD CONSTRAINT `carrito_items_ibfk_1` FOREIGN KEY (`id_carrito`) REFERENCES `carritos` (`id_carrito`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_items_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `panel_administracion_logs`
--
ALTER TABLE `panel_administracion_logs`
  ADD CONSTRAINT `panel_administracion_logs_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);

--
-- Filtros para la tabla `sesiones_usuario`
--
ALTER TABLE `sesiones_usuario`
  ADD CONSTRAINT `sesiones_usuario_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
