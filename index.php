<?php
require_once('database_config.php');

/*$database = array(
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'octa_orm'
);

$db = new OctaORM($database);
$data = array(
    "first_name"=>"john",
    "last_name"=>"doe",
    "email"=>"jdoe@gmail.com",
    "password"=>"admin"
);*/

$db->get('user');
$res = $db->result();

echo '<pre>';
print_r($res);
echo '</pre>';