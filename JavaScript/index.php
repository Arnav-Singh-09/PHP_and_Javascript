<?php

  require_once "pdo.php";
  session_start();
  $stmt = $pdo->query("SELECT profile_id, first_name, last_name, headline FROM users JOIN Profile on users.user_id = Profile.user_id");
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
  <head>
    <title>Arnav Singh's Resume Registry</title>
    <?php require_once "bootstrap.php"; ?>
  </head>
  <body>
    <div class="container">
      <h1>Arnav Singh's Resume Repository</h1>
      <?php if(isset($_SESSION['error'])){
        echo('<p style="color:red;">'.$_SESSION['error']."</p>\n");
        unset($_SESSION['error']);
      }
      if(isset($_SESSION['success'])){
        echo('<p style="color:green;">' . $_SESSION['success'] . "</p>\n");
        unset($_SESSION['success']);
      }
      if(!isset($_SESSION['name'])){
        echo('<p><a href="login.php">Please log in</a></p>');
      }
      else{
        echo('<p><a href="logout.php">Logout</a></p>');
      }
      if(empty($rows)){
        echo('<p>No Rows Found</p>');
      }
      else{
        echo('<table border="1">');
        echo('<thead><tr><th>Name</th>');
        echo('<th>Headline</th>');
        if(isset($_SESSION['name'])){
          echo('<th>Action</th>');
        }
        echo('</tr></thead>');
        foreach($rows as $row){
          echo('<tr><td>');
          echo('<a href="view.php?profile_id=' .($row['profile_id']) . '">' . htmlentities($row['first_name']) . ' ' . htmlentities($row['last_name']) . '</a>');
          echo('</td><td>');
          echo(htmlentities($row['headline']) . '</td>');
          if(isset($_SESSION['name'])){
            echo('<td>');
            echo('<a href="edit.php?profile_id=' .($row['profile_id']) . '">Edit</a>');
            echo(' / ');
            echo('<a href="delete.php?profile_id=' .($row['profile_id']) . '">Delete</a>');
            echo('</td>');
          }
        }
        echo('</tr></table>');
      }
      ?>
      <p><a href='add.php'>Add New Entry</a></p>
    </div>
  </body>
</html>
