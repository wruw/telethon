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
 * Template name: last
 * @package storefront
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<?php
			while ( have_posts() ) :
				the_post();
				do_action( 'storefront_page_before' );

				get_template_part( 'content', 'page' );
				/**
				 * Functions hooked in to storefront_page_after action
				 *
				 * @hooked storefront_display_comments - 10
				 */
                 if( current_user_can('editor') || current_user_can('administrator') ) {
                     ?>
                     <h3>Donors in the past 4 hours</h3>
                     <table>
                        <tr>
                            <th>
                                Name
                            </th>
                            <th>
                                City
                            </th>
                            <th>
                                Show
                            </th>
                            <th>
                                Say Name
                            </th>
                            <th>
                                Amount
                            </th>
                            <th>
                                View order
                            </th>
                        </tr>
                            <?php
                            echo current_time('mysql');
                            $sql = $wpdb->get_results("SELECT name.meta_value AS name, name.post_id AS post_id,  city.meta_value AS city, state.meta_value AS state, onair.meta_value AS onair, amount.meta_value AS amount, showname.meta_value AS showname FROM $wpdb->postmeta AS name INNER JOIN $wpdb->posts ON name.post_id = $wpdb->posts.ID INNER JOIN $wpdb->postmeta AS city ON name.post_id = city.post_id INNER JOIN $wpdb->postmeta AS state ON name.post_id = state.post_id INNER JOIN $wpdb->postmeta AS amount ON name.post_id = amount.post_id INNER JOIN $wpdb->postmeta AS onair ON name.post_id = onair.post_id INNER JOIN $wpdb->postmeta AS showname ON showname.post_id = name.post_id WHERE name.meta_key = '_billing_first_name' AND city.meta_key = '_billing_city' AND state.meta_key = '_billing_state' AND amount.meta_key = '_order_total' AND onair.meta_key = '_custom_can_we_say_your_name_on_the_air' AND post_date > '".current_time('mysql')."' - interval 4 hour");
                            foreach ($sql as $row){
                                echo '<tr>';
                                echo '<td>'.$row->name.'</td>';
                                echo '<td>'.$row->city.', '.$row->state.'</td>';
                                echo '<td>'.$row->showname.'</td>';
                                echo '<td>'.$row->onair.'</td>';
                                echo '<td>$'.$row->amount.'</td>';
                                echo '<td><a href="https://telethon.wruw.org/wp-admin/post.php?post='.$row->post_id.'&action=edit">View Order</a></td>';
                                echo '</tr>';
                            } ?>
                     </table>
                 <?php } ?>
                 <script>
                 window.setTimeout( function() {
                   window.location.reload();
                 }, 60000);</script>

	 <!-- AddToAny END -->
	 <?php
				do_action( 'storefront_page_after' );

			endwhile; // End of the loop.

do_action( 'storefront_sidebar' );
get_footer();
?>
