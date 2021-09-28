<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hihome";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM `shop_brand`";
$result = $conn->query($sql);


  while($row = $result->fetch_assoc()) {
      $prev_id=$row['id'];
      $sql = "INSERT INTO shop_brand (`name`,`alias`,`slogan`,`image`) VALUES ('".$row['name']."', '".$row['alias']."', '".$row['slogan']."','".$row['image']."')";
    $last_id = $conn->insert_id;
    if ($conn->query($sql) === TRUE) {
        $last_id = $conn->insert_id;
      
        




        
    } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
    
  }




