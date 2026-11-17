<?php

if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Acceso denegado. Ejecutá este archivo desde la terminal.'); }
require_once __DIR__ . '/includes/funciones.php';
echo "CREAR ADMINISTRADOR BOOCK&CO\n";
$read = static function(string $label): string { echo $label; return trim((string)fgets(STDIN)); };
$nombre=$read("Nombre: "); $apellido=$read("Apellido: "); $dni=preg_replace('/\D/','',$read("DNI: "));
$email=strtolower($read("Correo: ")); $password=$read("Contraseña (mínimo 8 caracteres): ");
if($nombre===''||$apellido===''||strlen($dni)<7||strlen($dni)>8||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<8){exit("Datos inválidos.\n");}
$q=db()->prepare("SELECT id FROM usuarios WHERE dni=? OR email=?");$q->execute([$dni,$email]);
if($q->fetch()){exit("Ese DNI o correo ya está registrado.\n");}
$q=db()->prepare("INSERT INTO usuarios(nombre,apellido,dni,email,password,rol,estado) VALUES(?,?,?,?,?,'admin','activo')");
$q->execute([$nombre,$apellido,$dni,$email,password_hash($password,PASSWORD_DEFAULT)]);
echo "Administrador creado correctamente.\n";
