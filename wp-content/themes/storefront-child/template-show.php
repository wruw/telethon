<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 *
 * Template name: show
 * @package storefront
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<?php
			$startpage = get_page_by_path("start-date");
			$startpageid = $startpage->ID;
			$startdate = get_field("start_date", $startpageid);
			while ( have_posts() ) :
				the_post();
				do_action( 'storefront_page_before' );

				get_template_part( 'content', 'page' );
				/**
				 * Functions hooked in to storefront_page_after action
				 *
				 * @hooked storefront_display_comments - 10
				 */
	 			$goal = get_field("goal");
				$program = get_field("program");
	 			global $post;
	       $post_slug=$post->post_name;
		   $total = 0;
		   $show_specifics = $wpdb->get_results("select sum(itemcost.meta_value) cost from wp_woocommerce_order_items 
		   inner join wp_woocommerce_order_itemmeta productid on productid.order_item_id = wp_woocommerce_order_items.order_item_id
		   inner join wp_woocommerce_order_itemmeta itemcost on itemcost.order_item_id = wp_woocommerce_order_items.order_item_id
		   inner join $wpdb->posts on $wpdb->posts.ID = wp_woocommerce_order_items.order_id
		   inner join $wpdb->postmeta on $wpdb->postmeta.post_id = productid.meta_value
		   where productid.meta_key = '_product_id' and itemcost.meta_key = '_line_total'
		   and $wpdb->posts.post_status != 'wc-pending'
		   and $wpdb->posts.post_date > '".$startdate."'
		   and $wpdb->postmeta.meta_key = 'program' and $wpdb->postmeta.meta_value = '".$program."';");
			foreach($show_specifics as $show){
				$total += $show->cost;
			}
		   $donations = $wpdb->get_results("select amount.meta_value amount, showname.meta_value showname, post.ID id from
		   		$wpdb->posts post inner join $wpdb->postmeta amount on post.id = amount.post_id
				inner join $wpdb->postmeta showname on post.id = showname.post_id
				where post.post_status != 'wc-pending'
				and showname.meta_key = '_show'
				and amount.meta_key = '_order_total'
				AND post_date > '".$startdate."'
				and showname.meta_value like '%".$program."%';");
			foreach($donations as $donation){
				$thisordertotal = 0;
				$shows = explode(",", $donation->showname);
				foreach($shows as $show){
					if (strpos($show, $program) !== false) {
					$thisordertotal = $donation->amount;
					}
				}
				if($thisordertotal == 0){
					continue;
				}
				$subtractby = $wpdb->get_results("select sum(itemcost.meta_value) cost from wp_woocommerce_order_items 
					inner join wp_woocommerce_order_itemmeta productid on productid.order_item_id = wp_woocommerce_order_items.order_item_id
					inner join wp_woocommerce_order_itemmeta itemcost on itemcost.order_item_id = wp_woocommerce_order_items.order_item_id
					inner join $wpdb->postmeta on $wpdb->postmeta.post_id = productid.meta_value
					inner join $wpdb->posts on $wpdb->posts.ID = wp_woocommerce_order_items.order_id
					where productid.meta_key = '_product_id' and itemcost.meta_key = '_line_total'
					and $wpdb->posts.post_status != 'wc-pending'
					and $wpdb->posts.post_date > '".$startdate."'
					and $wpdb->postmeta.meta_key = 'program' and $wpdb->postmeta.meta_value != ''
					and wp_woocommerce_order_items.order_id = ".$donation->id.";");
				foreach($subtractby as $subtract){
					$thisordertotal -= $subtract->cost;
				}
				$total += $thisordertotal/count($shows);
			}
			   if ($goal && $goal!='0'){?>
	 				<div id="thermo" class="thermometer horizontal">

	         <div class="track">
	             <div class="goal">
	                 <div class="amount"> </div>
	             </div>
	             <div class="progress">
	                 <div class="amount"></div>
	             </div>
	         </div>

	     </div>
	 		<script
	 		  src="https://code.jquery.com/jquery-3.3.1.min.js"
	 		  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
	 		  crossorigin="anonymous"></script>
	 		<script>

	 function formatCurrency(n, c, d, t) {
	     "use strict";

	     var s, i, j;

	     c = isNaN(c = Math.abs(c)) ? 2 : c;
	     d = d === undefined ? "." : d;
	     t = t === undefined ? "," : t;

	     s = n < 0 ? "-" : "";
	     i = parseInt(n = Math.abs(+n || 0).toFixed(c), 10) + "";
	     j = (j = i.length) > 3 ? j % 3 : 0;

	     return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
	 }


	 function thermometer(id, goalAmount, progressAmount, animate) {
	     "use strict";

	     var $thermo = $("#"+id),
	         $progress = $(".progress", $thermo),
	         $goal = $(".goal", $thermo),
	         percentageAmount,
	         isHorizontal = $thermo.hasClass("horizontal"),
	         newCSS = {};

	     goalAmount = goalAmount || parseFloat( $goal.text() ),
	     progressAmount = progressAmount || parseFloat( $progress.text() ),
	     percentageAmount =  Math.min( Math.round(progressAmount / goalAmount * 1000) / 10, 100); //make sure we have 1 decimal point

	     //let's format the numbers and put them back in the DOM
	     $goal.find(".amount").text( "$" + formatCurrency( goalAmount ) );
	     $progress.find(".amount").text( "$" + formatCurrency( progressAmount ) );


	     //let's set the progress indicator
	     $progress.find(".amount").hide();

	     newCSS[ isHorizontal ? "width" : "height" ] = percentageAmount + "%";

	     if (animate !== false) {
	         $progress.animate( newCSS, 1200, function(){
	             $(this).find(".amount").fadeIn(500);
	         });
	     }
	     else {
	         $progress.css( newCSS );
	         $progress.find(".amount").fadeIn(500);
	     }
	 }
	 $(document).ready(function(){
	     thermometer("thermo", <?php echo $goal?>, <?php echo $total ?>);
	 });
	 d = new Date();
	 d.setTime(d.getTime() + 15*60*1000);
	 time = d.toUTCString();
	 document.cookie = 'program=<?php echo $program?>;expires='+time+';path=/';
	 </script>
	 <?php
	 }else{
	 	?>
	 	<div>
	 		<p>
	 			Currently raised $<?php echo $total ?>
	 		</p>


	 	</div>
	 			<?php

	 		}echo do_shortcode('[product_table category="2024-premiums"]');?>
			<!-- AddToAny BEGIN -->
	 <div class="a2a_kit a2a_kit_size_32 a2a_default_style">
	 <a class="a2a_dd" href="https://www.addtoany.com/share"></a>
	 <a class="a2a_button_facebook"></a>
	 <a class="a2a_button_twitter"></a>
	 <a class="a2a_button_email"></a>
	 </div>
	 <script async src="https://static.addtoany.com/menu/page.js"></script>
	 <?php if( current_user_can('editor') || current_user_can('administrator') ) {
		 ?>
		 <h3>Donors</h3>
		 <table>
			<tr>
				<th>
					ID
				</th>
				<th>
					Name
				</th>
				<th>
					City
				</th>
				<th>
					Say Name
				</th>
				<th>
					Email
				</th>
				<th>
					Amount
				</th>
				<th>
					View order
				</th>
			</tr>
				<?php
				$ids = array();
				$sql = $wpdb->get_results("SELECT posts.ID AS id
					FROM $wpdb->posts AS posts
					INNER JOIN $wpdb->postmeta AS showname ON posts.ID = showname.post_id WHERE
					showname.meta_key = '_show'	and showname.meta_value like '%".$program."%'
					AND post_date > '".$startdate."'  AND post_status != 'wc-pending'");		
				foreach($sql as $row){
					if(!in_array($row->id, $ids)){
						array_push($ids, $row->id);
					}
				}
				$sql = $wpdb->get_results("SELECT order_id FROM wp_woocommerce_order_items 
				INNER JOIN wp_woocommerce_order_itemmeta productid ON productid.order_item_id = wp_woocommerce_order_items.order_item_id
				INNER JOIN $wpdb->posts on $wpdb->posts.ID = wp_woocommerce_order_items.order_id
				INNER JOIN $wpdb->postmeta on $wpdb->postmeta.post_id = productid.meta_value
				WHERE productid.meta_key = '_product_id'
				AND $wpdb->posts.post_status != 'wc-pending'
				AND $wpdb->posts.post_date > '".$startdate."'
				AND $wpdb->postmeta.meta_key = 'program' and $wpdb->postmeta.meta_value = '".$program."'");
				foreach($sql as $row){
					if(!in_array($row->order_id, $ids)){
						array_push($ids, $row->order_id);
					}
				}
				sort($ids);
				foreach ($ids as $id){
					$total = 0;
					$show_specifics = $wpdb->get_results("select sum(itemcost.meta_value) cost from wp_woocommerce_order_items 
						inner join wp_woocommerce_order_itemmeta productid on productid.order_item_id = wp_woocommerce_order_items.order_item_id
						inner join wp_woocommerce_order_itemmeta itemcost on itemcost.order_item_id = wp_woocommerce_order_items.order_item_id
						inner join $wpdb->posts on $wpdb->posts.ID = wp_woocommerce_order_items.order_id
						inner join $wpdb->postmeta on $wpdb->postmeta.post_id = productid.meta_value
						where productid.meta_key = '_product_id' and itemcost.meta_key = '_line_total'
						and $wpdb->posts.post_status != 'wc-pending'
						and $wpdb->posts.post_date > '".$startdate."'
						and $wpdb->postmeta.meta_key = 'program' and $wpdb->postmeta.meta_value = '".$program."'
						AND wp_woocommerce_order_items.order_id = '".$id."'");
					foreach($show_specifics as $show){
						$total += $show->cost;
					}
					$donations = $wpdb->get_results("select amount.meta_value amount, showname.meta_value showname, post.ID id from
						$wpdb->posts post inner join $wpdb->postmeta amount on post.id = amount.post_id
						inner join $wpdb->postmeta showname on post.id = showname.post_id
						where post.post_status != 'wc-pending'
						and showname.meta_key = '_show'
						and amount.meta_key = '_order_total'
						AND post_date > '".$startdate."'
						and showname.meta_value like '%".$program."%'
						and post.id = '".$id."'");
					foreach($donations as $donation){
						$thisordertotal = 0;
						$shows = explode(",", $donation->showname);
						foreach($shows as $show){
							if (strpos($show, $program) !== false) {
								$thisordertotal = $donation->amount;
								break;
							}
						}
						$subtractby = $wpdb->get_results("select sum(itemcost.meta_value) cost from wp_woocommerce_order_items 
							inner join wp_woocommerce_order_itemmeta productid on productid.order_item_id = wp_woocommerce_order_items.order_item_id
							inner join wp_woocommerce_order_itemmeta itemcost on itemcost.order_item_id = wp_woocommerce_order_items.order_item_id
							inner join $wpdb->postmeta on $wpdb->postmeta.post_id = productid.meta_value
							inner join $wpdb->posts on $wpdb->posts.ID = wp_woocommerce_order_items.order_id
							where productid.meta_key = '_product_id' and itemcost.meta_key = '_line_total'
							and $wpdb->posts.post_status != 'wc-pending'
							and $wpdb->posts.post_date > '".$startdate."'
							and $wpdb->postmeta.meta_key = 'program' and $wpdb->postmeta.meta_value != ''
							and wp_woocommerce_order_items.order_id = ".$donation->id);
						foreach($subtractby as $subtract){
							$thisordertotal -= $subtract->cost;
						}
						$total += $thisordertotal/count($shows);
					}
					if ($total > 0) {
						$infos = $wpdb->get_results("SELECT name.meta_value AS name, name.post_id AS post_id,
							city.meta_value AS city, state.meta_value AS state, onair.meta_value AS onair,
							email.meta_value AS email FROM
							$wpdb->postmeta AS name INNER JOIN $wpdb->posts ON name.post_id = $wpdb->posts.ID
							INNER JOIN $wpdb->postmeta AS city ON name.post_id = city.post_id
							INNER JOIN $wpdb->postmeta AS state ON name.post_id = state.post_id
							INNER JOIN $wpdb->postmeta AS email ON name.post_id = email.post_id
							INNER JOIN $wpdb->postmeta AS onair ON name.post_id = onair.post_id WHERE
							name.meta_key = '_billing_first_name' AND city.meta_key = '_billing_city'
							AND email.meta_key = '_billing_email'
							AND state.meta_key = '_billing_state' AND onair.meta_key = '_can_we_say_your_name'
							AND $wpdb->posts.ID = '".$id."'");
						foreach($infos as $info){
							echo '<tr>';
							echo '<td>'.$info->post_id.'</td>';
							echo '<td>'.$info->name.'</td>';
							echo '<td>'.$info->city.', '.$info->state.'</td>';
							echo '<td>'.$info->onair.'</td>';
							echo '<td>'.$info->email.'</td>';
							echo '<td>$'.$total.'</td>';
							echo '<td><a href="https://telethon.wruw.org/wp-admin/post.php?post='.$info->post_id.'&action=edit">View Order</a></td>';
							echo '</tr>';
							break;
						}
					}
				}
		?>
		 </table>
	<?php } ?>
	 <!-- AddToAny END -->
	 <?php
				do_action( 'storefront_page_after' );

			endwhile; // End of the loop.
			?>


<?php
do_action( 'storefront_sidebar' );
get_footer();
