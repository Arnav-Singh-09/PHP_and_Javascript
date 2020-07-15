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
if(!isset($_GET['profile_id'])){
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}
$x = $_GET['profile_id'];

if(isset($_POST['save'])){
  if(strlen($_POST['first_name']) == 0 || strlen($_POST['last_name']) == 0 ||
  strlen($_POST['email']) == 0 || strlen($_POST['headline']) == 0 ||
  strlen($_POST['summary']) == 0){
    $_SESSION['error'] = "All fields are required";
    header("Location: edit.php?profile_id=$x");
    return;
  }
  elseif(strpos($_POST['email'],'@') === false) {
    $_SESSION['error'] = "Email address must contain @";
    header("Location: edit.php?profile_id=$x");
    return;
  } else {
    for($i=1; $i<=9; $i++){
      if( ! isset($_POST['year'.$i])) continue;
      if( ! isset($_POST['desc'.$i])) continue;
      $year = $_POST['year'.$i];
      $desc = $_POST['desc'.$i];
      if( strlen($year) == 0 || strlen($desc) == 0){
        $_SESSION['error'] = "All fields are required";
        header("Location: edit.php?profile_id=$x");
        return;
      }
      if(!is_numeric($year)){
        $_SESSION['error'] = "Position year must be numeric";
        header("Location: edit.php?profile_id=$x");
        return;
      }
    }
    for($i=1; $i<=9; $i++){
      if( ! isset($_POST['edu_year'.$i])) continue;
      if( ! isset($_POST['edu_school'.$i])) continue;
      $edu_year = $_POST['edu_year'.$i];
      $edu_school = $_POST['edu_school'.$i];
      if( strlen($edu_year) == 0 || strlen($edu_school) == 0){
        $_SESSION['error'] = "All fields are required";
        header("Location: edit.php?profile_id=$x");
        return;
      }
      if(! is_numeric($edu_year)){
        $_SESSION['error'] = "Education year must be numeric";
        header("Location: edit.php?profile_id=$x");
        return;
      }
    }
    $sql = "UPDATE Profile SET first_name = :fn, last_name = :ln, email = :em,
    headline = :he, summary = :su WHERE profile_id = :pid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array( ':fn' => $_POST['first_name'],
                         ':ln' => $_POST['last_name'],
                         ':em' => $_POST['email'],
                         ':he' => $_POST['headline'],
                         ':su' => $_POST['summary'],
                         ':pid' => $x));
    $stmt = $pdo->prepare('DELETE FROM position WHERE profile_id=:pid');
    $stmt->execute(array(':pid' => $x));
    $rank = 1;
    for($i=1;$i<=9;$i++){
      if( ! isset($_POST['year'.$i])) continue;
      if( ! isset($_POST['desc'.$i])) continue;
      $year = $_POST['year'.$i];
      $desc = $_POST['desc'.$i];
      $stmt = $pdo->prepare("INSERT INTO position (profile_id,rank,year,description)
                             VALUES (:pid, :rank, :year, :desc)");
      $stmt->execute(array(':pid' => $x,
                           ':rank' => $rank,
                           ':year' => $year,
                           ':desc' => $desc));
      $rank++;
    }
    $stmt = $pdo->prepare('DELETE FROM education WHERE profile_id=:pid');
    $stmt->execute(array(':pid' => $x));
    $rank = 1;
    for($i=1;$i<=9;$i++){
      if( ! isset($_POST['edu_year'.$i])) continue;
      if( ! isset($_POST['edu_school'.$i])) continue;
      $edu_year = $_POST['edu_year'.$i];
      $edu_school = $_POST['edu_school'.$i];
      $institution_id = false;
      $stmt = $pdo->prepare("SELECT institution_id FROM institution WHERE name = :name");
      $stmt->execute(array(':name' => $edu_school));
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if($row !== false){
        $institution_id = $row['institution_id'];
      } else {
        $stmt = $pdo->prepare("INSERT INTO Institution (name) VALUES (:name)");
        $stmt->execute(array(':name' => $edu_school));
        $institution_id = $pdo->lastInsertId();
      }
      $stmt = $pdo->prepare("INSERT INTO education (profile_id, rank, year, institution_id) VALUES (:pid, :rank, :year, :iid)");
      $stmt->execute(array(':pid' => $x,
                           ':rank' => $rank,
                           ':year' => $edu_year,
                           ':iid' => $institution_id));
      $rank++;
    }
    $_SESSION['success']  = 'Profile updated';
    header('Location: index.php');
    return;
  }
}
$stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id = :xyz");
$stmt->execute(array(':xyz' => $_GET['profile_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ($profile === false) {
    $_SESSION['error'] = 'Bad value for user_id';
    header('Location: index.php');
    return;
}
$stmt = $pdo->prepare("SELECT * FROM Position WHERE profile_id = :zip ORDER BY rank");
$stmt->execute(array(':zip' => $x));
$position = [];
while($row = $stmt->fetch(PDO::FETCH_OBJ)){
  $position[] = $row;
}
$positionLen = count($position);
$stmt = $pdo->prepare("SELECT * FROM education LEFT JOIN institution ON
                       education.institution_id = institution.institution_id
                       WHERE profile_id = :zip ORDER BY rank");
$stmt->execute(array(':zip' => $x));
$res = [];
while($row = $stmt->fetch(PDO::FETCH_OBJ)){
  $res[] = $row;
}
$eduLen = count($res);
?>

<!DOCTYPE html>
<head>
  <title>Arnav Singh's profile Edit</title>
  <?php require_once "bootstrap.php"; ?>
</head>
<body>
  <div class="container">
    <h1>Editing Profile for UMSI</h1>
    <?php if(isset($_SESSION['error'])){
      echo('<p style="color:red;">'.$_SESSION['error']."</p>\n");
      unset($_SESSION['error']);
    }
    ?>
    <form method="POST" >
      <p>First Name:<input type="text" name="first_name" size="60" value="<?php echo htmlentities($profile['first_name']);?>"/></p>
      <p>Last Name:<input type="text" name="last_name" size="60" value="<?php echo htmlentities($profile['last_name']);?>"/></p>
      <p>Email:<input type="text" name="email" size="30" value="<?php echo htmlentities($profile['email']);?>"/></p>
      <p>Headline:<br><input type="text" name="headline" size="80" value="<?php echo htmlentities($profile['headline']);?>"/></p>
      <p>Summary:<br><textarea name="summary" rows="8" cols="80"><?php echo htmlentities($profile['summary']);?></textarea></p>
      <p>Education:<input type="submit" id="addEdu" value="+">
        <div id="edu_fields">
          <?php
            if($eduLen > 0){
              for($i=1;$i<=$eduLen;$i++){
                echo('<div id="edu'.$i.'">'."\n");
                echo('<p>Year: <input type="text" name="edu_year'.$i.'" value="'.$res[$i-1]->year.'" />'."\n");
                echo('<input type="button" value="-" onclick="$('."'#edu".$i."'".').remove();return false;"></p>'."\n");
                echo('<p>School: <input type="text" name="edu_school'.$i.'"value="'.$res[$i-1]->name.'"/>'."\n");
                echo('</p></div>'."\n");
              }
            }
          ?>
        </div></p>
       <p>Postion:<input type="submit" id="addPos" value="+">
        <div id="position_fields">
          <?php
            if($positionLen > 0){
              for($i=1;$i<=$positionLen;$i++){
                echo('<div id="position'.$i.'">'."\n");
                echo('<p>Year: <input type="text" name="year'.$i.'" value="'.$position[$i-1]->year.'" />'."\n");
                echo('<input type="button" value="-" onclick="$('."'#position".$i."'".').remove();return false;"></p>'."\n");
                echo('<textarea name="desc'.$i.'" rows="8" cols="80">'."\n");
                echo($position[$i-1]->description."\n");
                echo('</textarea>'."\n");
                echo('</div>'."\n");
              }
            }
          ?>
        </div></p>
      <p><input type="submit" name="save" value="Save"/>
         <input type="submit" name="cancel" value="Cancel"/>
      </p>
    </form>
    <script>
    countPos = <?php echo($positionLen."\n")?>;
    countEdu = <?php echo($eduLen."\n")?>;
    $(document).ready(function(){
    window.console && console.log('Document ready called');
      $('#addPos').click(function(event){
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);
        $('#position_fields').append(
          '<div id="position'+countPos+'"> \
          <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
          <input type="button" value="-" \
              onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
          <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
          </div>');
         });
         $('#addEdu').click(function(event){
         event.preventDefault();
         if( countEdu >= 9){
           alert("Maximum of nine education entries exceeded");
           return;
         }
         countEdu++;
         window.cosole && console.log("Adding education "+countEdu);
         $('#edu_fields').append(
           '<div id="edu'+countEdu+'"> \
           <p>Year: <input type="text" name="edu_year'+countEdu+'" value="" /> \
           <input type="button" value="-" onclick="$(\'#edu'+countEdu+'\').remove();return false;"></p> \
           <p>School: <input type="text" size="80" name="edu_school'+countEdu+'" class="school" value="" />\
           </p></div>'
         );
         $('.school').autocomplete({
          source: "school.php"
        });
      });
    });
  </script>
  </div>
</body>
</html>
