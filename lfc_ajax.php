<?php
$conn = new mysqli("localhost", "telethon","3H3aGL4PW!U*dCVH","telethon");  
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$program = 'live-from-cleveland';
$total = 0;
$show_specificssql = $conn->prepare("select sum(itemcost.meta_value) cost from wp_woocommerce_order_items 
inner join wp_woocommerce_order_itemmeta productid on productid.order_item_id = wp_woocommerce_order_items.order_item_id
inner join wp_woocommerce_order_itemmeta itemcost on itemcost.order_item_id = wp_woocommerce_order_items.order_item_id
inner join wp_posts on wp_posts.ID = productid.meta_value
inner join wp_postmeta on wp_postmeta.post_id = productid.meta_value
where productid.meta_key = '_product_id' and itemcost.meta_key = '_line_total'
and wp_posts.post_status != 'wc-pending'
and wp_posts.post_date > '2023-03-26'
and wp_postmeta.meta_key = 'program' and wp_postmeta.meta_value = ?;");
$show_specificssql->bind_param("s", $program);
$show_specificssql->execute();
$show_specifics_result = $show_specificssql->get_result();
while($show_specifics_row = $show_specifics_result->fetch_assoc()){
    $total += $show_specifics_row['cost'];
  }
$donationssql = $conn->prepare("select amount.meta_value amount, showname.meta_value showname, post.ID id from
    wp_posts post inner join wp_postmeta amount on post.id = amount.post_id
  inner join wp_postmeta showname on post.id = showname.post_id
  where post.post_status != 'wc-pending'
  and showname.meta_key = '_show'
  and amount.meta_key = '_order_total'
  AND post_date > '2023-03-26'
  and showname.meta_value like ?;");
$programpercent = "%".$program."%";
$donationssql->bind_param("s", $programpercent);
$donationssql->execute();
$donations_result = $donationssql->get_result();
while($donations_row = $donations_result->fetch_assoc()){
  $shows = explode(",", $donations_row['showname']);
  foreach($shows as $show){
    if (strpos($show, $program) !== false) {
    $total += ($donations_row['amount'])/count($shows);
      break;
    }
  }
  $subtractby = $conn->prepare("select sum(itemcost.meta_value) cost from wp_woocommerce_order_items 
    inner join wp_woocommerce_order_itemmeta productid on productid.order_item_id = wp_woocommerce_order_items.order_item_id
    inner join wp_woocommerce_order_itemmeta itemcost on itemcost.order_item_id = wp_woocommerce_order_items.order_item_id
    inner join wp_postmeta on wp_postmeta.post_id = productid.meta_value
    inner join wp_posts on wp_posts.ID = productid.meta_value
    where productid.meta_key = '_product_id' and itemcost.meta_key = '_line_total'
    and wp_posts.post_status != 'wc-pending'
    and wp_posts.post_date > '2023-03-26'
    and wp_postmeta.meta_key = 'program' and wp_postmeta.meta_value != ''
    and wp_woocommerce_order_items.order_id = ?;");
  $subtractby->bind_param("i", $donations_row['id']);
  $subtractby->execute();
  $subtractby_result = $subtractby->get_result();
  while($subtractby_row = $subtractby_result->fetch_assoc()){
    $total -= $subtractby_row['cost'];
  }
}
echo(number_format((float)$total, 2, '.', ''));
?>
