<?php
$conn = new mysqli("localhost", "telethon","3H3aGL4PW!U*dCVH","telethon");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = $conn->prepare("SELECT name.meta_value AS name, post_date as time, name.post_id AS post_id, city.meta_value AS city, state.meta_value AS state, onair.meta_value AS onair, amount.meta_value AS amount, showname.meta_value AS showname FROM wp_postmeta AS name INNER JOIN wp_posts ON name.post_id = wp_posts.ID INNER JOIN wp_postmeta AS city ON name.post_id = city.post_id INNER JOIN wp_postmeta AS state ON name.post_id = state.post_id INNER JOIN wp_postmeta AS onair ON name.post_id = onair.post_id INNER JOIN wp_postmeta AS amount ON name.post_id = amount.post_id LEFT JOIN wp_postmeta AS showname ON name.post_id = showname.post_id AND showname.meta_key = '_show' WHERE name.meta_key = '_billing_first_name' AND city.meta_key = '_billing_city' AND state.meta_key = '_billing_state' AND onair.meta_key = '_can_we_say_your_name' AND amount.meta_key = '_order_total' AND post_date >= DATE_SUB(NOW(),INTERVAL 24 HOUR) AND post_status != 'wc-pending' ORDER BY time desc LIMIT 30");
$sql->execute();
$result = $sql->get_result();
$orders = [];
while($row = $result->fetch_assoc()){
    $order = array('items'=>array(),'shows'=>array());
    foreach($row as $key => $val){
        $order[$key] = $val;
    }
    $sql = $conn->prepare("SELECT order_item_name FROM wp_woocommerce_order_items WHERE order_id = ".$row['post_id']." AND order_item_type = 'line_item'");
    $sql->execute();
    $result2 = $sql->get_result();
    while($row2 = $result2->fetch_assoc()){
        array_push($order['items'],$row2['order_item_name']);
    }
    array_push($orders, $order);
  }
$data = array('orders'=>$orders);
$sql = $conn->prepare("SELECT SUM(meta_value) AS sum FROM wp_postmeta INNER JOIN wp_posts ON wp_postmeta.post_id = wp_posts.ID WHERE meta_key = '_order_total' AND post_date > '2024-03-01' AND post_status != 'wc-pending'");
$sql->execute();
$result = $sql->get_result();
while($row = $result->fetch_assoc()){
    $data['total'] = $row['sum'];
}
$sql = $conn->prepare("SELECT SUM(meta_value) AS sum FROM wp_postmeta INNER JOIN wp_posts ON wp_postmeta.post_id = wp_posts.ID WHERE meta_key = '_order_total' AND post_date >= DATE_SUB(NOW(),INTERVAL 5 HOUR) AND post_status != 'wc-pending'");
$sql->execute();
$result = $sql->get_result();
while($row = $result->fetch_assoc()){
    $data['hour'] = $row['sum'];
}
$sql = $conn->prepare("SELECT SUM(meta_value) AS sum FROM wp_postmeta INNER JOIN wp_posts ON wp_postmeta.post_id = wp_posts.ID WHERE meta_key = '_order_total' AND post_date >= DATE_SUB(NOW(),INTERVAL 6 HOUR) AND post_status != 'wc-pending'");
$sql->execute();
$result = $sql->get_result();
while($row = $result->fetch_assoc()){
    $data['two'] = $row['sum'];
}
$sql = $conn->prepare("SELECT SUM(meta_value) AS sum FROM wp_postmeta INNER JOIN wp_posts ON wp_postmeta.post_id = wp_posts.ID WHERE meta_key = '_order_total' AND post_date >= DATE_SUB(NOW(),INTERVAL 28 HOUR) AND post_status != 'wc-pending'");
$sql->execute();
$result = $sql->get_result();
while($row = $result->fetch_assoc()){
    $data['twentyfour'] = $row['sum'];
}


echo json_encode($data);
?>
