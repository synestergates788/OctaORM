<?php
require_once('OctaORM.php');

/*database variables*/
$database = array(
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'octa_orm'
);

$db = new OctaORM($database);