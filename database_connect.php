<?php
define('DB_DSN', 'mysql:host=172.18.0.1;dbname=myserverside;charset=utf8');
define('DB_USER', 'thisserveruser');
define('DB_PASS', 'password!');

try {
    $db = new PDO(DB_DSN, DB_USER, DB_PASS);

} catch (PDOException $e) {
    print "Error: " . $e->getMessage();
    die(); 
}
?>