-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 06-02-2016 a las 00:58:04
-- Versión del servidor: 5.6.21
-- Versión de PHP: 5.6.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `aliados`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ACTIVIDADES_ECONOMICAS`
--

CREATE TABLE IF NOT EXISTS `ACTIVIDADES_ECONOMICAS` (
`ACT_ID` int(11) NOT NULL,
  `ACT_NOMBRE` varchar(120) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `ACTIVIDADES_ECONOMICAS`
--

INSERT INTO `ACTIVIDADES_ECONOMICAS` (`ACT_ID`, `ACT_NOMBRE`) VALUES
(1, 'Ventas por Catálogo'),
(2, 'Abarrotes'),
(3, 'Tienda Departamental'),
(4, 'Industria Alimentaria'),
(5, 'Programador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `GRUPOS`
--

CREATE TABLE IF NOT EXISTS `GRUPOS` (
`GRU_ID` int(11) NOT NULL,
  `GRU_FECHA` datetime NOT NULL,
  `GRU_PERSONAS` text NOT NULL COMMENT 'Personas en el grupo separadas por "|"',
  `GRU_MONTO` float NOT NULL COMMENT 'Monto individual solicitado',
  `GRU_PLAZO` int(11) NOT NULL COMMENT 'Semanas',
  `GRU_TASA` float NOT NULL COMMENT 'Tasa semanal',
  `GRU_FECHA_INICIAL` datetime NOT NULL COMMENT 'Fecha de Primer Pago',
  `GRU_AHORRO_P` float NOT NULL COMMENT 'Porcentaje de Ahorro',
  `GRU_AHORRO_D` float NOT NULL COMMENT 'Cantidad de Ahorro',
  `GRU_RECREDITO` tinyint(4) NOT NULL COMMENT '1 = Recredito; 0 = No Rec.',
  `GRU_COMISION_P` float NOT NULL COMMENT 'Porcentaje comision de apertura',
  `GRU_COMISION_D` float NOT NULL COMMENT 'Cantidad comision apertura',
  `GRU_MONTO_OTORGADO` float NOT NULL COMMENT 'Cantidad otorgada',
  `GRU_PAGO_CAPITAL` float NOT NULL COMMENT 'Pago a semanal SIN intereses',
  `GRU_PAGO_INTERES` float NOT NULL COMMENT 'Intereses del pago semanal (individual)',
  `GRU_PAGO_SEMANAL` float NOT NULL COMMENT 'Total a pagar (individual)',
  `GRU_DOMICILIO` text NOT NULL COMMENT 'Domicilio de juntas',
  `SIU_ID` int(11) NOT NULL,
  `GRU_VIGENTE` tinyint(11) NOT NULL DEFAULT '1' COMMENT '1 = Vigente; 0 = Terminado',
  `GRU_MONTO_TOTAL` float NOT NULL COMMENT 'Monto total solicitado',
  `GRU_MONTO_TOTAL_ENTREGAR` float NOT NULL COMMENT 'Monto total a entregar (ya se resta comision ap. y ahorro)',
  `PAGO_CAPITAL` float NOT NULL COMMENT 'Pago SIN intereses de todo el grupo',
  `PAGO_INTERES` float NOT NULL COMMENT 'Intereses de pago del Grupo',
  `PAGO_SEMANAL` float NOT NULL COMMENT 'Pago total del grupo semanal'
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `GRUPOS`
--

INSERT INTO `GRUPOS` (`GRU_ID`, `GRU_FECHA`, `GRU_PERSONAS`, `GRU_MONTO`, `GRU_PLAZO`, `GRU_TASA`, `GRU_FECHA_INICIAL`, `GRU_AHORRO_P`, `GRU_AHORRO_D`, `GRU_RECREDITO`, `GRU_COMISION_P`, `GRU_COMISION_D`, `GRU_MONTO_OTORGADO`, `GRU_PAGO_CAPITAL`, `GRU_PAGO_INTERES`, `GRU_PAGO_SEMANAL`, `GRU_DOMICILIO`, `SIU_ID`, `GRU_VIGENTE`, `GRU_MONTO_TOTAL`, `GRU_MONTO_TOTAL_ENTREGAR`, `PAGO_CAPITAL`, `PAGO_INTERES`, `PAGO_SEMANAL`) VALUES
(1, '2016-02-02 00:00:00', '1|2|3', 2000, 12, 0.0175, '2016-02-13 00:00:00', 0.1, 200, 0, 0.05, 100, 1700, 166.667, 2.91667, 169.583, 'Río Ramos 4572', 1, 1, 6000, 5100, 500, 8.75, 508.75),
(2, '2016-02-02 00:00:00', '1|2|3', 2000, 12, 0.0175, '2016-02-13 00:00:00', 0.1, 200, 0, 0.05, 100, 1700, 166.667, 2.91667, 169.583, 'Río Ramos 4572', 2, 1, 6000, 5100, 500, 8.75, 508.75);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `PERSONAS`
--

CREATE TABLE IF NOT EXISTS `PERSONAS` (
`PER_ID` int(11) NOT NULL,
  `PER_FECHA` date NOT NULL,
  `PER_NOMBRE` varchar(120) NOT NULL,
  `PER_DIRECCION` text NOT NULL,
  `PER_EMAIL` text NOT NULL,
  `PER_TELEFONO` varchar(20) NOT NULL,
  `PER_CELULAR` varchar(20) NOT NULL,
  `PER_FACEBOOK` varchar(120) NOT NULL,
  `MONTO_SOLICITADO` float NOT NULL,
  `VIVE_PADRES` tinyint(4) NOT NULL COMMENT '1 = VIVE CON PADRES; 0 = NO',
  `VIVE_CONYUGUE` tinyint(4) NOT NULL COMMENT '1 = VIVE CON CONYUGUE; 0 = NO',
  `VIVE_HIJOS` tinyint(4) NOT NULL COMMENT '1 = VIVE CON HIJOS; 0 = NO',
  `VIVE_HERMANOS` tinyint(4) NOT NULL COMMENT '1 = VIVE CON HERMANOS; 0 = NO',
  `VIVE_OTROS` varchar(120) NOT NULL,
  `DEPENDE_PADRES` tinyint(4) NOT NULL COMMENT '1 = PADRES DEPENDEN',
  `DEPENDE_PADRES_COMMENT` text NOT NULL,
  `DEPENDE_CONYUGUE` tinyint(4) NOT NULL COMMENT '1 = CONYUGUE DEPENDEN',
  `DEPENDE_CONYUGUE_COMMENT` text NOT NULL,
  `DEPENDE_HIJOS` tinyint(4) NOT NULL COMMENT '1 = HIJOS DEPENDEN',
  `DEPENDE_HIJOS_COMMENT` text NOT NULL,
  `DEPENDE_HERMANOS` tinyint(4) NOT NULL COMMENT '1 = HERMANOS DEPENDEN',
  `DEPENDE_HERMANOS_COMMENT` text NOT NULL,
  `DEPENDE_OTROS` tinyint(4) NOT NULL,
  `DEPENDE_OTROS_COMMENT` text NOT NULL,
  `ACT_ID` int(11) NOT NULL,
  `ACT_OTRO` varchar(120) NOT NULL,
  `ACT_ANTIGUEDAD` varchar(120) NOT NULL,
  `INGRESO_SEMANAL` float NOT NULL COMMENT 'INGRESO PROMEDIO SEMANAL PRIMARIO',
  `INGRESO_ADICIONAL_1` varchar(120) NOT NULL,
  `INGRESO_MONTO_1` float NOT NULL,
  `INGRESO_ADICIONAL_2` varchar(120) NOT NULL,
  `INGRESO_MONTO_2` float NOT NULL,
  `INGRESO_ADICIONAL_3` varchar(120) NOT NULL,
  `INGRESO_MONTO_3` float NOT NULL,
  `VIVIENDA` tinyint(4) NOT NULL COMMENT '0 = RENTADA; 1 = PROPIA',
  `VIVIENDA_GASTO` float NOT NULL,
  `PRESTAMO_OTRO_1` varchar(120) NOT NULL,
  `PRESTAMO_PAGO_1` float NOT NULL COMMENT 'PAGO SEMANAL',
  `PRESTAMO_OTRO_2` varchar(120) NOT NULL,
  `PRESTAMO_PAGO_2` float NOT NULL COMMENT 'SEMANAL',
  `PROYECTO_INVERSION` text NOT NULL,
  `REFERENCIA_NOMBRE_1` varchar(120) NOT NULL,
  `REFERENCIA_RELACION_1` varchar(60) NOT NULL,
  `REFERENCIA_TELEFONO_1` varchar(20) NOT NULL,
  `REFERENCIA_NOMBRE_2` varchar(120) NOT NULL,
  `REFERENCIA_RELACION_2` varchar(60) NOT NULL,
  `REFERENCIA_TELEFONO_2` varchar(20) NOT NULL,
  `REFERENCIA_NOMBRE_3` varchar(120) NOT NULL,
  `REFERENCIA_RELACION_3` varchar(60) NOT NULL,
  `REFERENCIA_TELEFONO_3` varchar(20) NOT NULL,
  `REFERENCIA_NOMBRE_4` varchar(120) NOT NULL,
  `REFERENCIA_RELACION_4` varchar(60) NOT NULL,
  `REFERENCIA_TELEFONO_4` varchar(20) NOT NULL,
  `GARANTIA_BIEN_1` varchar(100) NOT NULL,
  `GARANTIA_MODELO_1` varchar(60) NOT NULL,
  `GARANTIA_DESCRIPCION_1` text NOT NULL,
  `GARANTIA_BIEN_2` varchar(100) NOT NULL,
  `GARANTIA_MODELO_2` varchar(60) NOT NULL,
  `GARANTIA_DESCRIPCION_2` text NOT NULL,
  `GARANTIA_BIEN_3` varchar(100) NOT NULL,
  `GARANTIA_MODELO_3` varchar(60) NOT NULL,
  `GARANTIA_DESCRIPCION_3` text NOT NULL,
  `IFE` text NOT NULL,
  `COMPROBANTE_DOMICILIO` text NOT NULL,
  `COMENTARIOS` text NOT NULL,
  `STATUS` tinyint(4) NOT NULL COMMENT '-1 = Eliminado;0 = PENDIENTE; 1 = En Credito; 2 = RECHAZADO; 3 = Disponible para Recredito',
  `RAZON_RECHAZO` text NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `PERSONAS`
--

INSERT INTO `PERSONAS` (`PER_ID`, `PER_FECHA`, `PER_NOMBRE`, `PER_DIRECCION`, `PER_EMAIL`, `PER_TELEFONO`, `PER_CELULAR`, `PER_FACEBOOK`, `MONTO_SOLICITADO`, `VIVE_PADRES`, `VIVE_CONYUGUE`, `VIVE_HIJOS`, `VIVE_HERMANOS`, `VIVE_OTROS`, `DEPENDE_PADRES`, `DEPENDE_PADRES_COMMENT`, `DEPENDE_CONYUGUE`, `DEPENDE_CONYUGUE_COMMENT`, `DEPENDE_HIJOS`, `DEPENDE_HIJOS_COMMENT`, `DEPENDE_HERMANOS`, `DEPENDE_HERMANOS_COMMENT`, `DEPENDE_OTROS`, `DEPENDE_OTROS_COMMENT`, `ACT_ID`, `ACT_OTRO`, `ACT_ANTIGUEDAD`, `INGRESO_SEMANAL`, `INGRESO_ADICIONAL_1`, `INGRESO_MONTO_1`, `INGRESO_ADICIONAL_2`, `INGRESO_MONTO_2`, `INGRESO_ADICIONAL_3`, `INGRESO_MONTO_3`, `VIVIENDA`, `VIVIENDA_GASTO`, `PRESTAMO_OTRO_1`, `PRESTAMO_PAGO_1`, `PRESTAMO_OTRO_2`, `PRESTAMO_PAGO_2`, `PROYECTO_INVERSION`, `REFERENCIA_NOMBRE_1`, `REFERENCIA_RELACION_1`, `REFERENCIA_TELEFONO_1`, `REFERENCIA_NOMBRE_2`, `REFERENCIA_RELACION_2`, `REFERENCIA_TELEFONO_2`, `REFERENCIA_NOMBRE_3`, `REFERENCIA_RELACION_3`, `REFERENCIA_TELEFONO_3`, `REFERENCIA_NOMBRE_4`, `REFERENCIA_RELACION_4`, `REFERENCIA_TELEFONO_4`, `GARANTIA_BIEN_1`, `GARANTIA_MODELO_1`, `GARANTIA_DESCRIPCION_1`, `GARANTIA_BIEN_2`, `GARANTIA_MODELO_2`, `GARANTIA_DESCRIPCION_2`, `GARANTIA_BIEN_3`, `GARANTIA_MODELO_3`, `GARANTIA_DESCRIPCION_3`, `IFE`, `COMPROBANTE_DOMICILIO`, `COMENTARIOS`, `STATUS`, `RAZON_RECHAZO`) VALUES
(1, '2015-12-02', 'Juana Ma. Cruz Anguiano', 'Monte de los Olivos #117', '', '8119917629', '', 'Juana Cruz', 4000, 0, 1, 0, 1, '', 0, '', 0, '', 1, '', 0, '', 0, '', 0, 'Empresa', '1 año', 1400, 'Sueldo de mi esposo', 1500, 'venta de tortillas de harina, gorditas, champurrado', 1500, '', 0, 1, 0, '', 0, '', 0, 'Para invertir más', 'Brillante del Pilar Ongay', 'Familia', '8184747167', 'Beatriz Adriana', 'Trabajo', '8120116168', 'Damaso Esteban', 'Vecino', '8111830746', '', 'Familia', '', 'Plasma Polaroid', 'PTV2203 LED', 'Negra de 32''''', 'Estereo Panasonic', 'CA-AK 270', 'Negro de 2 bocinas grande', 'Refri LG', 'GM-32380', 'Blanco de 2 puertas', '1.png', '1.png', 'Muy cumplidora con el pago.', 1, ''),
(2, '2015-12-09', 'Nancy Elizabeth Cabriales S.', 'Bosques Otawa #144', '', '21652408', '8120160606', '', 5000, 0, 1, 0, 0, '', 0, '', 0, '', 0, '', 0, '', 0, '', 0, 'Venta Ropa', '3 años', 4000, 'Sueldo de Esposo', 2000, 'Venta de Tacos', 1200, 'Venta Ropa Paca', 1500, 1, 0, '', 0, '', 0, 'Invertir otro puesto comidas', 'Rosi Gomez', 'Familia', '8119633920', 'Elizabeth Castillo', 'Trabajo', '8120165202', 'Alicia Arrispe', 'Familia', '8117891692', '', 'Familia', '', 'Pasma', 'VSG132', '42 Pulgadas Negro', 'Bocina', '5321081324', 'RKG', 'Plasma', 'SM13254', '32 Pulgadas Gris', '2.jpg', '2.jpg', 'Referida', 1, ''),
(3, '2015-12-08', 'Araceli Francisco Almazán', 'Villa Suiza #104', '', '8121508951', '', '', 3000, 0, 1, 0, 0, '', 0, '', 0, '', 1, '', 0, '', 0, '', 2, '', '6 meses', 1500, 'Abarrotes', 1800, '', 0, '', 0, 1, 0, '', 0, '', 0, 'Invertir en mi tienda', 'Yazmín Almazán', 'Familia', '8180963473', 'Ana María Hernandez', 'Vecino', '8180860804', 'Areli Francisco', 'Familia', '8115284898', '', 'Familia', '', 'Clima LG', 'W05109912', 'Color Beige', 'Refrigerador IEM', '40608007', 'Color Crema', 'Tele Panasonic', '46080012', 'Color Gris', '3.png', '3.png', 'Referida', 1, ''),
(4, '2015-12-08', 'Araceli Francisco Almazán 2', 'Villa Suiza #104', '', '8121508951', '', '', 3000, 0, 1, 0, 0, '', 0, '', 0, '', 1, '', 0, '', 0, '', 2, '', '6 meses', 1500, 'Abarrotes', 1800, '', 0, '', 0, 1, 0, '', 0, '', 0, 'Invertir en mi tienda', 'Yazmín Almazán', 'Familia', '8180963473', 'Ana María Hernandez', 'Vecino', '8180860804', 'Areli Francisco', 'Familia', '8115284898', '', 'Familia', '', 'Clima LG', 'W05109912', 'Color Beige', 'Refrigerador IEM', '40608007', 'Color Crema', 'Tele Panasonic', '46080012', 'Color Gris', '3.png', '3.png', 'Referida', 2, 'No es buena paga'),
(5, '2015-12-09', 'Nancy Elizabeth Cabriales S. 2', 'Bosques Otawa #144', '', '21652408', '8120160606', '', 5000, 0, 1, 0, 0, '', 0, '', 0, '', 0, '', 0, '', 0, '', 0, 'Venta Ropa', '3 años', 4000, 'Sueldo de Esposo', 2000, 'Venta de Tacos', 1200, 'Venta Ropa Paca', 1500, 1, 0, '', 0, '', 0, 'Invertir otro puesto comidas', 'Rosi Gomez', 'Familia', '8119633920', 'Elizabeth Castillo', 'Trabajo', '8120165202', 'Alicia Arrispe', 'Familia', '8117891692', '', 'Familia', '', 'Pasma', 'VSG132', '42 Pulgadas Negro', 'Bocina', '5321081324', 'RKG', 'Plasma', 'SM13254', '32 Pulgadas Gris', '2.jpg', '2.jpg', 'Referida', 0, ''),
(7, '2015-12-02', 'Juana Ma. Cruz Anguiano 2', 'Monte de los Olivos #117', '', '8119917629', '', 'Juana Cruz', 4000, 0, 1, 0, 1, '', 0, '', 0, '', 1, '', 0, '', 0, '', 0, 'Empresa', '1 año', 1400, 'Sueldo de mi esposo', 1500, 'venta de tortillas de harina, gorditas, champurrado', 1500, '', 0, 1, 0, '', 0, '', 0, 'Para invertir más', 'Brillante del Pilar Ongay', 'Familia', '8184747167', 'Beatriz Adriana', 'Trabajo', '8120116168', 'Damaso Esteban', 'Vecino', '8111830746', '', 'Familia', '', 'Plasma Polaroid', 'PTV2203 LED', 'Negra de 32''''', 'Estereo Panasonic', 'CA-AK 270', 'Negro de 2 bocinas grande', 'Refri LG', 'GM-32380', 'Blanco de 2 puertas', '1.png', '1.png', 'Muy cumplidora con el pago.', 0, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `SISTEMA_MODULOS`
--

CREATE TABLE IF NOT EXISTS `SISTEMA_MODULOS` (
`SIM_ID` int(11) NOT NULL,
  `SIM_NOMBRE` varchar(100) DEFAULT NULL,
  `SIM_URL` text,
  `SIM_IMAGEN` varchar(100) DEFAULT NULL,
  `SIM_NIVEL` int(11) DEFAULT NULL,
  `SIM_ORDEN` varchar(45) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `SISTEMA_MODULOS`
--

INSERT INTO `SISTEMA_MODULOS` (`SIM_ID`, `SIM_NOMBRE`, `SIM_URL`, `SIM_IMAGEN`, `SIM_NIVEL`, `SIM_ORDEN`) VALUES
(1, 'Sistema', 'sistema', 'fa-desktop', 0, '1'),
(2, 'Usuarios', 'sistema/usuarios', 'fa-user', 1, '1'),
(3, 'Grupos', 'grupos', 'fa-group', 0, '3'),
(4, 'Catálogos', 'catalogos', 'fa-book', 0, '2'),
(5, 'Actividad Económica', 'catalogos/actividad', 'fa-dollar', 4, '1'),
(6, 'Prospectos', 'prospectos', 'fa-male', 0, '5'),
(7, 'Grupos', 'grupos-admin', 'fa-group', 0, '4'),
(8, 'Prospectos Rechazados', 'prospectos-rechazados', 'fa-user-times', 0, '6');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `SISTEMA_NOTIFICACIONES`
--

CREATE TABLE IF NOT EXISTS `SISTEMA_NOTIFICACIONES` (
`SIN_ID` int(11) NOT NULL,
  `SIN_MENSAJE` text,
  `SIN_LIGA` varchar(120) DEFAULT NULL,
  `SIN_USUARIOS` text,
  `SIN_ICONO` varchar(45) DEFAULT NULL,
  `SIN_COLOR` varchar(45) DEFAULT NULL,
  `SIN_DATE` timestamp NULL DEFAULT NULL,
  `SIN_ESTADO` tinyint(3) DEFAULT NULL COMMENT '0 = No leído; 1 = Leído'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `SISTEMA_USUARIO`
--

CREATE TABLE IF NOT EXISTS `SISTEMA_USUARIO` (
`SIU_ID` int(11) NOT NULL,
  `SIU_NOMBRE` varchar(120) DEFAULT NULL,
  `SIU_EMAIL` varchar(120) DEFAULT NULL,
  `SIU_PASSWORD` varchar(120) DEFAULT NULL,
  `SIU_RECUPERACION` tinyint(4) DEFAULT '0',
  `SUP_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `SISTEMA_USUARIO`
--

INSERT INTO `SISTEMA_USUARIO` (`SIU_ID`, `SIU_NOMBRE`, `SIU_EMAIL`, `SIU_PASSWORD`, `SIU_RECUPERACION`, `SUP_ID`) VALUES
(1, 'Cynthia Castillo', 'c.castillo@technoweb.mx', '$2y$05$op4yhdui30oplkadfetgwuaQbdDxLuyMx0U5STMfQbpFqhq8McQsi', 0, 1),
(2, 'Usuario Promotor', 'user@promotor.com', '$2y$05$op4yhdui30oplkadfetgwukf7MXvRq7ZRajl5RM6nLYBuzc7IHTnC', 0, 3),
(3, 'Usuario Admin', 'user@admin.com', '$2y$05$op4yhdui30oplkadfetgwukf7MXvRq7ZRajl5RM6nLYBuzc7IHTnC', 0, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `SISTEMA_USUARIO_PERFIL`
--

CREATE TABLE IF NOT EXISTS `SISTEMA_USUARIO_PERFIL` (
`SUP_ID` int(11) NOT NULL,
  `SUP_NOMBRE` varchar(45) DEFAULT NULL,
  `SUP_PERMISO` text
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `SISTEMA_USUARIO_PERFIL`
--

INSERT INTO `SISTEMA_USUARIO_PERFIL` (`SUP_ID`, `SUP_NOMBRE`, `SUP_PERMISO`) VALUES
(1, 'daemon', '1-1111|2-1111|3-1111|4-1111|5-1111|6-1111|7-1111|8-1111'),
(2, 'Administrador', '1-1111|2-1111|4-1111|5-1111|6-1111|7-1111|8-1111'),
(3, 'Promotor', '3-1111|4-1111|5-1111|6-1111');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `SISTEMA_VARIABLE`
--

CREATE TABLE IF NOT EXISTS `SISTEMA_VARIABLE` (
`SIV_ID` int(11) NOT NULL,
  `SIV_VARIABLE` varchar(45) DEFAULT NULL,
  `SIV_VALOR` text
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `SISTEMA_VARIABLE`
--

INSERT INTO `SISTEMA_VARIABLE` (`SIV_ID`, `SIV_VARIABLE`, `SIV_VALOR`) VALUES
(1, 'baseurl', 'http://aliados.com.mx'),
(2, 'nuevo usuario email cuerpo', 'Se ha generado una cuenta de usuario para la Plataforma Financiera de <i>Aliados</i><br><br>Las credenciales de acceso son:<br><br>Url: [baseurl]<br><br>Email: [email]<br><br>Password: [password]'),
(3, 'nuevo usuario email titulo', 'Tu nueva cuenta en la Plataforma Financiera de "Aliados"'),
(4, 'cambio de contraseña email cuerpo', 'Se ha hecho un cambio en la contraseña de su cuenta de usuario para la Plataforma Financiera de <i>Aliados</i> <br><br>Las credenciales de acceso son:<br><br>Url: [baseurl]<br>Email: [email]<br>Password: [password]'),
(5, 'cambio de contraseña email titulo', 'Cambio de contraseña en la Plataforma Financiera de "Aliados"');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ACTIVIDADES_ECONOMICAS`
--
ALTER TABLE `ACTIVIDADES_ECONOMICAS`
 ADD PRIMARY KEY (`ACT_ID`);

--
-- Indices de la tabla `GRUPOS`
--
ALTER TABLE `GRUPOS`
 ADD PRIMARY KEY (`GRU_ID`);

--
-- Indices de la tabla `PERSONAS`
--
ALTER TABLE `PERSONAS`
 ADD PRIMARY KEY (`PER_ID`);

--
-- Indices de la tabla `SISTEMA_MODULOS`
--
ALTER TABLE `SISTEMA_MODULOS`
 ADD PRIMARY KEY (`SIM_ID`);

--
-- Indices de la tabla `SISTEMA_NOTIFICACIONES`
--
ALTER TABLE `SISTEMA_NOTIFICACIONES`
 ADD PRIMARY KEY (`SIN_ID`);

--
-- Indices de la tabla `SISTEMA_USUARIO`
--
ALTER TABLE `SISTEMA_USUARIO`
 ADD PRIMARY KEY (`SIU_ID`), ADD KEY `FK_SIU_SUP_idx` (`SUP_ID`);

--
-- Indices de la tabla `SISTEMA_USUARIO_PERFIL`
--
ALTER TABLE `SISTEMA_USUARIO_PERFIL`
 ADD PRIMARY KEY (`SUP_ID`);

--
-- Indices de la tabla `SISTEMA_VARIABLE`
--
ALTER TABLE `SISTEMA_VARIABLE`
 ADD PRIMARY KEY (`SIV_ID`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ACTIVIDADES_ECONOMICAS`
--
ALTER TABLE `ACTIVIDADES_ECONOMICAS`
MODIFY `ACT_ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT de la tabla `GRUPOS`
--
ALTER TABLE `GRUPOS`
MODIFY `GRU_ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT de la tabla `PERSONAS`
--
ALTER TABLE `PERSONAS`
MODIFY `PER_ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT de la tabla `SISTEMA_MODULOS`
--
ALTER TABLE `SISTEMA_MODULOS`
MODIFY `SIM_ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT de la tabla `SISTEMA_NOTIFICACIONES`
--
ALTER TABLE `SISTEMA_NOTIFICACIONES`
MODIFY `SIN_ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `SISTEMA_USUARIO`
--
ALTER TABLE `SISTEMA_USUARIO`
MODIFY `SIU_ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT de la tabla `SISTEMA_USUARIO_PERFIL`
--
ALTER TABLE `SISTEMA_USUARIO_PERFIL`
MODIFY `SUP_ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT de la tabla `SISTEMA_VARIABLE`
--
ALTER TABLE `SISTEMA_VARIABLE`
MODIFY `SIV_ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
