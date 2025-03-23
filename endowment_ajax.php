<?php
$conn = new mysqli("localhost", "telethon","3H3aGL4PW!U*dCVH","telethon");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = $conn->prepare("SELECT SUM(meta_value) AS sum from wp_woocommerce_order_items inner join wp_woocommerce_order_itemmeta on wp_woocommerce_order_itemmeta.order_item_id = wp_woocommerce_order_items.order_item_id where order_item_name = 'Endowment' and meta_key = '_line_total'");
$sql->execute();
$result = $sql->get_result();
while($row = $result->fetch_assoc()){
    echo $row['sum'];
  }

?>
