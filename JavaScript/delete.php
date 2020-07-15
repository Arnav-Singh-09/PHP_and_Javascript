<?php
session_start();
require_once "pdo.php";
if(!isset($_SESSION['name'])){
  die("Not logged in");
}
if(isset($_POST['cancel'])){
  header('Location: index.php');
  return;
}
if(isset($_POST['delete']) && isset($_POST['profile_id'])){
  $sql = "DELETE FROM Profile WHERE profile_id = :zip";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(':zip' => $_POST['profile_id']));
  $_SESSION['success'] = 'Profile deleted';
  header('Location: index.php');
  return;
}

if(!isset($_GET['profile_id'])){
  $_SESSION['error'] = "Missing user_id";
  header('Location: index.php');
  return;
}

$stmt = $pdo->prepare("SELECT first_name, last_name FROM Profile where profile_id = :zip");
$stmt->execute(array(':zip' => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if($row===false){
  $_SESSION['error'] = "Bad value for profile_id";
  header('Location: index.php');
  return;
}
?>

<!DOCTYPE html>
<head>
  <title>Arnav Singh's Profile Delete</title>
  <?php require_once "bootstrap.php";?>
</head>
<body>
  <div class="container">
    <p>First Name: <?php echo htmlentities($row['first_name']);?></p>
    <p>Last Name: <?php echo htmlentities($row['last_name']);?></p>
    <form method="POST">
      <input type="hidden" name="profile_id" value="<?php echo ($_GET['profile_id']) ?>"/>
      <input type="submit" name="delete" value="Delete" onclick="return confirmDelete();"/>
      <input type="submit" name="cancel" value="Cancel"/>
    </form>
    <script>
    function confirmDelete(){
      var delProfile = confirm('Are you sure you want to delete this profile?');
      if(delProfile){
        return true;
      }
      return false;
    }
    </script>
  </div>
</body>
</html>
