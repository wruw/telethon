<?php
$conn = new mysqli("localhost", "telethon","3H3aGL4PW!U*dCVH","telethon");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = $conn->prepare("SELECT SUM(meta_value) AS sum FROM wp_postmeta INNER JOIN wp_posts ON wp_postmeta.post_id = wp_posts.ID WHERE meta_key = '_order_total' AND post_date > '2023-03-26' AND post_status != 'wc-pending'");
$sql->execute();
$result = $sql->get_result();
while($row = $result->fetch_assoc()){
    echo $row['sum'];
  }

?>
