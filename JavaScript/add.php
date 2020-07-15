<?php

require_once "pdo.php";
session_start();
if(!isset($_SESSION['user_id'])){
  die("ACCESS DENIED");
  return;
}
if(isset($_POST['cancel'])){
  header('Location: index.php');
  return;
}
if(isset($_POST['add'])){
  if(strlen($_POST['first_name']) == 0 || strlen($_POST['last_name']) == 0 ||
  strlen($_POST['email']) == 0 || strlen($_POST['headline']) == 0 ||
  strlen($_POST['summary']) == 0){
    $_SESSION['error'] = "All fields are required";
    header('Location: add.php');
    return;
  }
  elseif(strpos($_POST['email'],'@') === false) {
    $_SESSION['error'] = "Email address must contain @";
    header('Location: add.php');
    return;
  } else {
    for($i=1; $i<=9; $i++){
      if( ! isset($_POST['year'.$i])) continue;
      if( ! isset($_POST['desc'.$i])) continue;
      $year = $_POST['year'.$i];
      $desc = $_POST['desc'.$i];
      if( strlen($year) == 0 || strlen($desc) == 0){
        $_SESSION['error'] = "All fields are required";
        header('Location: add.php');
        return;
      }
      if(! is_numeric($year)){
        $_SESSION['error'] = "Position year must be numeric";
        header('Location: add.php');
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
        header('Location: add.php');
        return;
      }
      if(! is_numeric($edu_year)){
        $_SESSION['error'] = "Education year must be numeric";
        header('Location: add.php');
        return;
      }
    }
    $stmt = $pdo->prepare('INSERT INTO Profile (user_id, first_name, last_name, email, headline, summary) VALUES (:user_id, :first_name, :last_name, :email, :headline,:summary)');
          $stmt->execute(array(
                  ':user_id' => $_SESSION['user_id'],
                  ':first_name' => $_POST['first_name'],
                  ':last_name' => $_POST['last_name'],
                  ':email' => $_POST['email'],
                  ':headline' => $_POST['headline'],
                  ':summary' => $_POST['summary']));
    $p_id = $pdo->lastInsertId();
    $rank = 1;
    for($i=1;$i<=9;$i++){
      if( ! isset($_POST['year'.$i])) continue;
      if( ! isset($_POST['desc'.$i])) continue;
      $year = $_POST['year'.$i];
      $desc = $_POST['desc'.$i];
      $stmt = $pdo->prepare("INSERT INTO position (profile_id,rank,year,description)
                             VALUES(:pid, :rank, :year, :desc)");
      $stmt->execute(array(':pid' => $p_id,
                           ':rank' => $rank,
                           ':year' => $year,
                           ':desc' => $desc));
      $rank++;
    }
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
      $stmt->execute(array(':pid' => $p_id,
                           ':rank' => $rank,
                           ':year' => $edu_year,
                           ':iid' => $institution_id));
      $rank++;
    }
    $_SESSION['success']  = 'Profile added';
    header('Location: index.php');
    return;
  }
}
?>

 <!DOCTYPE html>
 <head>
   <title>Arnav Singh's Profile Add</title>
   <?php require_once "bootstrap.php";
   ?>
 </head>
 <body>
   <div class="container">
     <h1>Adding Profile for UMSI</h1>
     <?php if(isset($_SESSION['error'])){
       echo('<p style="color:red;">'.$_SESSION['error']."</p>\n");
       unset($_SESSION['error']);
     }
     ?>
     <form method="POST">
       <p>First Name:<input type="text" name="first_name" size="60"/></p>
       <p>Last Name:<input type="text" name="last_name" size="60"/></p>
       <p>Email:<input type="text" name="email" size="30"/></p>
       <p>Headline:<br><input type="text" name="headline" size="80"/></p>
       <p>Summary:<br><textarea name="summary" rows="8" cols="80"></textarea></p>
       <p>Education:<input type="submit" id="addEdu" value="+">
         <div id="edu_fields">
         </div></p>
       <p>Postion:<input type="submit" id="addPos" value="+">
         <div id="position_fields">
         </div></p>
       <p><input type="submit" name="add" value="Add">
          <input type="submit" name="cancel" value="Cancel"></p>
      </form>
      <script>
      countPos = 0;
      countEdu = 0;
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
