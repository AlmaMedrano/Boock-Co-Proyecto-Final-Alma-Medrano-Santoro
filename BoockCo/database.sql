CREATE DATABASE IF NOT EXISTS boockco CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE boockco;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS carrito_items;
DROP TABLE IF EXISTS carritos;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE usuarios (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
    estado ENUM('activo','suspendido','pendiente') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultima_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB;

CREATE TABLE productos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    imagen VARCHAR(255) NULL,
    precio DECIMAL(12,2) NOT NULL DEFAULT 0,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    categoria_id INT UNSIGNED NOT NULL,
    estado ENUM('activo','archivado') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_productos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    INDEX idx_productos_categoria_estado (categoria_id, estado)
) ENGINE=InnoDB;

CREATE TABLE carritos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    estado ENUM('abierto','cerrado') NOT NULL DEFAULT 'abierto',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_carritos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_carritos_usuario_estado (usuario_id, estado)
) ENGINE=InnoDB;

CREATE TABLE carrito_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    carrito_id INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NOT NULL,
    cantidad INT UNSIGNED NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_items_carrito FOREIGN KEY (carrito_id) REFERENCES carritos(id) ON DELETE CASCADE,
    CONSTRAINT fk_items_producto FOREIGN KEY (producto_id) REFERENCES productos(id),
    UNIQUE KEY uq_carrito_producto (carrito_id, producto_id)
) ENGINE=InnoDB;

INSERT INTO categorias (nombre,slug,descripcion) VALUES
('Libros','libros','Libros para descubrir nuevas historias.'),
('Post-it y papelería','postit','Papelería para organizar ideas.'),
('Kindles','kindles','Lectores digitales.'),
('Fundas lectoras','fundas','Fundas y protección para lectores.'),
('Lámparas lectoras','lamparas','Lámparación para leer.'),
('Marcapáginas','marcapaginas','Detalles para no perder la página.');