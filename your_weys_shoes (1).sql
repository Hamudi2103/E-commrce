-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-07-2025 a las 03:56:52
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `your_weys_shoes`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `talla_seleccionada` varchar(10) NOT NULL,
  `fecha_añadido` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contrasena_hash`
--

CREATE TABLE `contrasena_hash` (
  `contrasena_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_orden`
--

CREATE TABLE `detalles_orden` (
  `id` int(11) NOT NULL,
  `orden_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `talla_seleccionada` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_orden`
--

INSERT INTO `detalles_orden` (`id`, `orden_id`, `producto_id`, `cantidad`, `precio_unitario`, `talla_seleccionada`) VALUES
(1, 1, 11, 1, 90.00, '35'),
(2, 2, 14, 1, 75.00, '42'),
(3, 2, 12, 1, 120.00, '35'),
(4, 3, 12, 5, 120.00, '42'),
(5, 3, 11, 3, 90.00, '44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_factura` datetime NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `detalle_productos` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodos_pago`
--

CREATE TABLE `metodos_pago` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_tarjeta` varchar(50) NOT NULL,
  `ultimos_cuatro_digitos` varchar(4) NOT NULL,
  `mes_expiracion` int(2) NOT NULL,
  `año_expiracion` int(4) NOT NULL,
  `token_pago` varchar(255) DEFAULT NULL,
  `predeterminado` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `metodos_pago`
--

INSERT INTO `metodos_pago` (`id`, `usuario_id`, `tipo_tarjeta`, `ultimos_cuatro_digitos`, `mes_expiracion`, `año_expiracion`, `token_pago`, `predeterminado`, `fecha_creacion`) VALUES
(1, 5, 'MasterCard', '2256', 12, 2027, 'simulated_token_68709ea3523f7', 1, '2025-07-11 05:18:27'),
(3, 5, 'Visa', '4242', 12, 2095, 'simulated_token_68709fc74fcaa', 0, '2025-07-11 05:23:19'),
(4, 6, 'American Express', '9852', 12, 2034, 'simulated_token_6870b4084d8a8', 0, '2025-07-11 06:49:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes`
--

CREATE TABLE `ordenes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `metodo_pago_id` int(11) DEFAULT NULL,
  `fecha_orden` datetime DEFAULT current_timestamp(),
  `total_orden` decimal(10,2) NOT NULL,
  `estado_orden` varchar(50) DEFAULT 'Completado',
  `direccion_envio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ordenes`
--

INSERT INTO `ordenes` (`id`, `usuario_id`, `metodo_pago_id`, `fecha_orden`, `total_orden`, `estado_orden`, `direccion_envio`) VALUES
(1, 5, 1, '2025-07-11 02:22:25', 90.00, 'Completado', 'Dirección de envío del usuario (ej. desde el perfil)'),
(2, 5, 3, '2025-07-11 02:46:35', 195.00, 'Completado', 'Dirección de envío del usuario (ej. desde el perfil)'),
(3, 6, 4, '2025-07-11 02:49:48', 870.00, 'Completado', 'Dirección de envío del usuario (ej. desde el perfil)');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) UNSIGNED NOT NULL COMMENT 'Clave primaria',
  `nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL COMMENT 'Precio con 2 decimales',
  `stock` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) UNSIGNED NOT NULL COMMENT 'Clave primaria',
  `nombre_usuario` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `contrasena_hash` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `rol` varchar(50) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL COMMENT '''cliente'', ''admin'''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `email`, `contrasena_hash`, `rol`) VALUES
(5, 'Hamudi', 'Mchansedine@gmail.com', '$2y$10$bCn/L2VmdquFMVmI5inO6u3uektcgp4/c8WgSzUg2RYG0It5eot1.', 'admin'),
(6, 'Luis', 'Luisguillarte@gmail.com', '$2y$10$NjFOSMyJQnOwmNfgPXb.E..RXH5eHu/xUzdoeGxWx4apsE7VZi1sK', 'usuario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zapatos`
--

CREATE TABLE `zapatos` (
  `id` bigint(11) NOT NULL COMMENT '	Clave primaria',
  `nombre` varchar(255) NOT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `Imagen` varchar(255) NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `talla` varchar(50) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `zapatos`
--

INSERT INTO `zapatos` (`id`, `nombre`, `marca`, `precio`, `Imagen`, `categoria`, `talla`, `descripcion`, `fecha_creacion`) VALUES
(1, 'Nike', 'AirForce 1', 110.00, 'https://img01.ztat.net/article/spp-media-p1/3e0146f35cfd4df1a2618cba8a58ceaa/a7281513c85740dfa227ba197605d20f.jpg?imwidth=1800', 'deportivo', '35,36,37,38,39,40,41,42,43,44,45', 'Modelo Basico', '2025-07-08 05:27:55'),
(11, 'Tenis Duramo SL 2.0', 'Adidas', 90.00, 'https://assets.adidas.com/images/w_1880,f_auto,q_auto/897b2eca9ca74aa2af1299a41e14cfba_9366/ID2709_HM5.jpg', 'deportivo', '35,36,37,38,39,40,41,42,43,44,45', 'Hombre • Running', '2025-07-09 00:41:09'),
(12, 'Nike Air Force 1 GS »Kobe Bryant»', 'Nike', 120.00, 'https://trapx.shop/wp-content/uploads/2024/09/Nike-Air-Force-1-GS-Kobe-Bryant.png', 'tenis', '35,36,37,38,39,40,41,42,43,44,45', 'articulo exclusivo\r\n Nike Modelo AirForce 1 version Koby Bryant', '2025-07-11 01:19:36'),
(14, 'Formal Flexi de Piel con Cintas 90718', 'flexi', 75.00, 'https://plazapar.com/cdn/shop/products/031777_1.jpg?v=1686715809', 'formal', '35,36,37,38,39,40,41,42,43,44,45', 'zapáto de Piel con Cintas 90718', '2025-07-11 06:44:26');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalles_orden`
--
ALTER TABLE `detalles_orden`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `zapatos`
--
ALTER TABLE `zapatos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `detalles_orden`
--
ALTER TABLE `detalles_orden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Clave primaria';

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Clave primaria', AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `zapatos`
--
ALTER TABLE `zapatos`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT COMMENT '	Clave primaria', AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
