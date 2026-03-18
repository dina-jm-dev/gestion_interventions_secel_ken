<?php
require 'config/db.php';
$stmt = $conn->query("SELECT * FROM utilisateurs");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($users);
