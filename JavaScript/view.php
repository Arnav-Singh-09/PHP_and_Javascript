<?php
session_start();
require_once "pdo.php";

if(!isset($_GET['profile_id'])){
  $_SESSION['error'] = "Could not load profile";
  header('Location: index.php');
  return;
}
$stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id = :xyz ORDER BY profile_id");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt = $pdo->prepare("SELECT * FROM Position WHERE profile_id = :zip ORDER BY rank");
$stmt->execute(array(":zip" => $_GET['profile_id']));
$position = [];
while($pos = $stmt->fetch(PDO::FETCH_OBJ)){
  $position[] = $pos;
}
$positionLen = count($position);
$stmt = $pdo->prepare("SELECT * FROM education LEFT JOIN institution ON
                       education.institution_id = institution.institution_id
                       WHERE profile_id = :zip ORDER BY rank");
$stmt->execute(array(':zip' => $_GET['profile_id']));
$res = [];
while($edu = $stmt->fetch(PDO::FETCH_OBJ)){
  $res[] = $edu;
}
$eduLen = count($res);
?>

<!DOCTYPE html>
<head>
  <title>Arnav Singh's Profile View</title>
  <?php require_once "bootstrap.php"; ?>
</head>
<body>
  <div class="container">
    <h1>Profile information</h1>
    <p>First Name: <?php echo htmlentities($row['first_name']); ?></p>
    <p>Last Name: <?php echo htmlentities($row['last_name']); ?></p>
    <p>Email: <?php echo htmlentities($row['email']); ?></p>
    <p>Headline: <?php echo htmlentities($row['headline']); ?></p>
    <p>Summary: <?php echo htmlentities($row['summary']); ?></p>
    <p>Education
      <ul>
        <?php
        if($eduLen==0){
          echo('<li>Nil</li>');
        }else{
          for($i=1;$i<=$eduLen;$i++){
            echo('<li>'.$res[$i-1]->year.': '.$res[$i-1]->name.'</li>');
          }
        }
        ?>
      </ul></p>
    <p>Position
      <ul>
        <?php
        if($positionLen==0){
          echo('<li>Nil</li>');
        }else{
          for($i=1;$i<=$positionLen;$i++){
            echo('<li>'.$position[$i-1]->year.': '.$position[$i-1]->description.'</li>');
          }
        }
        ?>
      </ul></p>
    <a href='index.php'>Done</a>
  </div>
</body>
</html>
