<?php

session_start();

//Eliminar completamente la sesión
session_unset();
session_destroy();
session_write_close();

//Barrido de cookie
setcookie(session_name(), '', 0, '/');

//Evita que se reutilice
session_regenerate_id(true);


header('Location: menu.php');



?>