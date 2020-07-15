<?php
require_once "pdo.php";
$stmt = $pdo->prepare("SELECT name FROM institution WHERE name LIKE :zip");
$stmt->execute(array(':zip' => $_GET['term']."%"));
$retval = array();
while( $row = $stmt->fetch(PDO::FETCH_ASSOC)){
  $retval[] = $row['name'];
}
echo(json_encode($retval, JSON_PRETTY_PRINT));
?>
