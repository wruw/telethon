<?php


$conn = new mysqli("localhost", "telethon","3H3aGL4PW!U*dCVH","telethon");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to retrieve the page ID based on the page path
$path = 'start-date';
$sql = "SELECT ID FROM wp_posts WHERE post_name = '$path' AND post_type = 'page'";
$result = $conn->query($sql);
$startDate = '';
$newshows = array();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $pageID = $row['ID'];

    // Query to retrieve the start date from custom fields
    $sql = "SELECT meta_value FROM wp_postmeta WHERE post_id = '".$pageID."' AND meta_key = 'start_date'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $startDate = $row['meta_value'];
        $startDate = date('Y/m/d', strtotime($startDate));
    }
}
// Query to retrieve the page IDs based on the template name
$allshows = array();
$sql = "SELECT post_id FROM wp_postmeta INNER JOIN wp_posts ON wp_posts.ID = post_id
WHERE meta_key = '_wp_page_template' AND meta_value = 'template-show.php' AND wp_posts.post_status = 'publish'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $showname = '';
        $slug = '';
        $sql = "select post_title, post_name from wp_posts where ID = '".$row['post_id']."'";
        $result2 = $conn->query($sql);
        if ($result2->num_rows > 0) {
            while( $row2 = $result2->fetch_assoc()) {
                $showname = $row2['post_title'];
                $slug = $row2['post_name'];
            }
        }
        if( !in_array($showname, $allshows) ) {
            array_push( $allshows, $showname );
            $goal = '';
            $program = '';
            $sql = "SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id = '".$row['post_id']."' AND meta_key in ('goal','program')";
            $result2 = $conn->query($sql);
            if ($result2->num_rows > 0) {
                while( $row2 = $result2->fetch_assoc()) {
                    if($row2['meta_key'] == 'goal'){
                        $goal = $row2['meta_value'];
                    }else{
                        $program = $row2['meta_value'];
                    }
                }

            }
            if($program == ''){
                continue;
            }
            $total = 0;
            $sql = "select sum(itemcost.meta_value) cost from wp_woocommerce_order_items 
            inner join wp_woocommerce_order_itemmeta productid on productid.order_item_id = wp_woocommerce_order_items.order_item_id
            inner join wp_woocommerce_order_itemmeta itemcost on itemcost.order_item_id = wp_woocommerce_order_items.order_item_id
            inner join wp_posts on wp_posts.ID = wp_woocommerce_order_items.order_id
            inner join wp_postmeta on wp_postmeta.post_id = productid.meta_value
            where productid.meta_key = '_product_id' and itemcost.meta_key = '_line_total'
            and wp_posts.post_status != 'wc-pending'
            and wp_posts.post_date > '".$startDate."'
            and wp_postmeta.meta_key = 'program' and wp_postmeta.meta_value = '" . $program . "';";
            $result2 = $conn->query($sql);
            if ($result2->num_rows > 0) {
                for($i = 0; $i < $result2->num_rows; $i++){
                    $row = $result2->fetch_assoc();
                    $total += $row['cost'];
                }
            }
            $sql = "select amount.meta_value amount, showname.meta_value showname, post.ID id from
                wp_posts post inner join wp_postmeta amount on post.id = amount.post_id
                inner join wp_postmeta showname on post.id = showname.post_id
                where post.post_status != 'wc-pending'
                and showname.meta_key = '_show'
                and amount.meta_key = '_order_total'
                AND post_date > '".$startDate."'
                and showname.meta_value like '%" . $program . "%';";
            $donations = $conn->query($sql);
            if ($donations->num_rows > 0) {
                while ($donation = $donations->fetch_assoc()) {
                    $shows = explode(",", $donation['showname']);
                    $thisordertotal = 0;
                    foreach ($shows as $show) {
                        if (strpos($show, $program) !== false) {
                            $thisordertotal = ($donation['amount']);
                            break;
                        }
                    }
                    if ($thisordertotal == 0) {
                        continue;
                    }
                    $sql = "select sum(itemcost.meta_value) cost from wp_woocommerce_order_items 
                inner join wp_woocommerce_order_itemmeta productid on productid.order_item_id = wp_woocommerce_order_items.order_item_id
                inner join wp_woocommerce_order_itemmeta itemcost on itemcost.order_item_id = wp_woocommerce_order_items.order_item_id
                inner join wp_postmeta on wp_postmeta.post_id = productid.meta_value
                inner join wp_posts on wp_posts.ID = wp_woocommerce_order_items.order_id
                where productid.meta_key = '_product_id' and itemcost.meta_key = '_line_total'
                and wp_posts.post_status != 'wc-pending'
                and wp_posts.post_date > '".$startDate."'
                and wp_postmeta.meta_key = 'program' and wp_postmeta.meta_value != ''
                and wp_woocommerce_order_items.order_id = " . $donation['id']. ";";
                $subtractby = $conn->query($sql);
                if ($subtractby->num_rows > 0) {
                    while ($subtract = $subtractby->fetch_assoc()) {
                        $thisordertotal -= $subtract['cost'];
                    }
                }
                    $total += $thisordertotal / count($shows);
                }
            }
            array_push($newshows, array("ShowName" => $showname, "slug" => $slug, "goal" => $goal, "total" => $total));
        }
    }
}
// Save $newshows in a text file
$file = '/var/www/telethon/showcache.json';
usort($newshows, function($a, $b) {
    return strcmp(strtolower($a['ShowName']), strtolower($b['ShowName']));
});
file_put_contents($file, json_encode($newshows));
echo json_encode($newshows);

// Close connection
$conn->close();
?>
