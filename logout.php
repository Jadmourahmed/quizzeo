<?php
session_start();
session_destroy();
header('Location: /projetweb_php/login.php');
exit();
