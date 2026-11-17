<?php
require_once __DIR__ . '/includes/funciones.php';
if (current_user()) redirect('tienda.php');
redirect('login.php');
