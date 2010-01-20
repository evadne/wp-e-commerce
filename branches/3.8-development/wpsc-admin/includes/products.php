<?php
/**
 * WP eCommerce product page functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.8
 */

/**
 * wpsc_product_rows function, copies the functionality of the wordpress code for siplaying posts and pages, but is for products
 * 
 */
function wpsc_admin_product_listing() {
	global $wp_query, $wpsc_products, $mode;

	add_filter('the_title','esc_html');

	// Create array of post IDs.
	$product_ids = array();

	if ( empty($wpsc_products) ) {
		$wpsc_products = &$wp_query->posts;
	}
	
	foreach ( $wpsc_products as $product ) {
		$product_ids[] = $product->ID;
	}
//	exit('<pre>'.print_r($wpsc_products, true).'</pre>');
	foreach ( $wpsc_products as $product ) {
		wpsc_product_row($product);
	}
}

function wpsc_product_row(&$product) {
	global $wp_query, $wpsc_products, $mode, $current_user;
	static $rowclass;

	$global_product = $product;
	setup_postdata($product);

	$rowclass = 'alternate' == $rowclass ? '' : 'alternate';
	$post_owner = ( $current_user->ID == $product->post_author ? 'self' : 'other' );
	$edit_link = get_edit_post_link( $product->ID );

	$title = get_the_title( $product->ID);
	if ( empty($title) ) {
		$title = __('(no title)');
	}
	
	?>
	
	<tr id='post-<?php echo $product->ID; ?>' class='<?php echo trim( $rowclass . ' author-' . $post_owner . ' status-' . $product->post_status ); ?> iedit' valign="top">
	<?php
	$posts_columns = get_column_headers('display-product-list');
	$hidden = get_hidden_columns('display-product-list');

	foreach ( $posts_columns as $column_name=>$column_display_name ) {
		$class = "class=\"$column_name column-$column_name\"";

		$style = '';
		if ( in_array($column_name, $hidden) )
			$style = ' style="display:none;"';

		$attributes = "$class$style";


		
		//echo "<pre>".print_r($column_name,true)."</pre>";
		switch ($column_name) {

		case 'cb':
		?>
		<th scope="row" class="check-column"><?php if ( current_user_can( 'edit_post', $product->ID ) ) { ?><input type="checkbox" name="post[]" value="<?php the_ID(); ?>" /><?php } ?></th>
		<?php
		break;

		case 'date':
			if ( '0000-00-00 00:00:00' == $product->post_date && 'date' == $column_name ) {
				$t_time = $h_time = __('Unpublished');
				$time_diff = 0;
			} else {
				$t_time = get_the_time(__('Y/m/d g:i:s A'));
				$m_time = $product->post_date;
				$time = get_post_time('G', true, $post);

				$time_diff = time() - $time;

				if ( $time_diff > 0 && $time_diff < 24*60*60 )
					$h_time = sprintf( __('%s ago'), human_time_diff( $time ) );
				else
					$h_time = mysql2date(__('Y/m/d'), $m_time);
			}

			echo '<td ' . $attributes . '>';
			if ( 'excerpt' == $mode )
				echo apply_filters('post_date_column_time', $t_time, $post, $column_name, $mode);
			else
				echo '<abbr title="' . $t_time . '">' . apply_filters('post_date_column_time', $h_time, $post, $column_name, $mode) . '</abbr>';
			echo '<br />';
			if ( 'publish' == $product->post_status ) {
				_e('Published');
			} elseif ( 'future' == $product->post_status ) {
				if ( $time_diff > 0 )
					echo '<strong class="attention">' . __('Missed schedule') . '</strong>';
				else
					_e('Scheduled');
			} else {
				_e('Last Modified');
			}
			echo '</td>';
		break;

		case 'title':
			$attributes = 'class="post-title column-title"' . $style;
			$edit_link = wp_nonce_url("admin.php?page=wpsc-edit-products&amp;product={$product->ID}", 'edit-product_'.$product->ID);
		?>
		<td <?php echo $attributes ?>>
			<strong>

			<?php if ( current_user_can('edit_post', $product->ID) && $product->post_status != 'trash' ) { ?>
				<a class="row-title" href="<?php echo $edit_link; ?>" title="<?php echo esc_attr(sprintf(__('Edit &#8220;%s&#8221;'), $title)); ?>"><?php echo $title ?></a>
			<?php } else {
				echo $title;
			};
			 _post_states($post);
			 ?>
			</strong>
			<?php

			$actions = array();
			if ( current_user_can('edit_post', $product->ID) && 'trash' != $product->post_status ) {
				$actions['edit'] = '<a  class="edit-product" href="'.$edit_link.'" title="' . esc_attr(__('Edit this product', 'wpsc')) . '">'. __('Edit', 'wpsc') . '</a>';
			}

			if ( current_user_can('delete_post', $product->ID) ) {
				if ( 'trash' == $product->post_status ) {
					$actions['untrash'] = "<a title='" . esc_attr(__('Restore this product from the Trash', 'wpsc')) . "' href='" . wp_nonce_url("admin.php?page=wpsc-edit-products&amp;wpsc_admin_action=untrash&amp;product={$product->ID}", 'untrash-product_' . $product->ID) . "'>".__('Restore')."</a>";
				} else if ( EMPTY_TRASH_DAYS ) {
					$actions['trash'] = "<a class='submitdelete' title='".esc_attr(__('Move this product to the Trash', 'wpsc'))."' href='" . wp_nonce_url("admin.php?page=wpsc-edit-products&amp;wpsc_admin_action=trash&amp;product={$product->ID}", 'trash-product_'.$product->ID) . "'>".__('Trash')."</a>";
				}
				
				if ( 'trash' == $product->post_status || !EMPTY_TRASH_DAYS ) {
					$actions['delete'] = "<a class='submitdelete' title='".esc_attr(__('Delete this product permanently', 'wpsc'))."' href='" . wp_nonce_url("admin.php?page=wpsc-edit-products&amp;wpsc_admin_action=delete&amp;product={$product->ID}", 'delete-product_'.$product->ID)."'>".__('Delete Permanently')."</a>";
				}
			}
			
			if ( in_array($product->post_status, array('pending', 'draft')) ) {
				if ( current_user_can('edit_product', $product->ID) ) {
					$actions['view'] = '<a href="'.get_permalink($product->ID).'" title="'.esc_attr(sprintf(__('Preview &#8220;%s&#8221;'), $title)) . '" rel="permalink">'.__('Preview').'</a>';
				}
			} else if ( 'trash' != $product->post_status ) {
				$actions['view'] = '<a href="'.get_permalink($product->ID).'" title="'.esc_attr(sprintf(__('View &#8220;%s&#8221;'), $title)).'" rel="permalink">'.__('View').'</a>';
			}
			
			$actions['duplicate'] = "<a class='submitdelete' title='".esc_attr(__('Duplicate', 'wpsc'))."' href='" . wp_nonce_url("admin.php?page=wpsc-edit-products&amp;wpsc_admin_action=duplicate_product&amp;product={$product->ID}", 'duplicate-product_'.$product->ID)."'>".__('Duplicate')."</a>";

			
			$actions = apply_filters('post_row_actions', $actions, $post);
			$action_count = count($actions);
			$i = 0;
			echo '<div class="row-actions">';
			
			foreach ( $actions as $action => $link ) {
				++$i;
				( $i == $action_count ) ? $sep = '' : $sep = ' | ';
				echo "<span class='$action'>$link$sep</span>";
			}
			
			echo '</div>';
			get_inline_data($post);
		?>
		</td>
		<?php
		break;



		case 'image':
			?>
			<td class="product-image ">
			<?php

			$args = array(
				'post_type' => 'attachment',
				'numberposts' => 1,
				'post_status' => null,
				'post_parent' => $product->ID,
				'orderby' => 'menu_order',
				'order' => 'ASC'
				);

			$attachments = get_posts($args);
			if (count($attachments) >= 1) {
				$attachment = array_pop($attachments);
				$image_url = "index.php?wpsc_action=scale_image&amp;attachment_id={$attachment->ID}&amp;width=38&amp;height=38&amp;crop=true";
				?>
					<img title='Drag to a new position' src=<?php echo $image_url; ?>' alt='<?php echo $title; ?>' />
				<?php
			} else {
				$image_url = WPSC_URL."/images/no-image-uploaded.gif";
				?>
					<img title='Drag to a new position' src='<?php echo $image_url; ?>' alt='<?php echo $title; ?>' width='38' height='38' />
				<?php
				}
				?>
			</td>
			<?php
		break;
		
		case 'price':
			$price = get_post_meta($product->ID, '_wpsc_price', true);
		//	exit($product->ID.'PRICE IS: <pre>'.print_r($price, true).'</pre>');
			?>
				<td  <?php echo $attributes ?>>
					<?php echo nzshpcrt_currency_display($price, 1); ?>
					<div class='price-editing-fields' id='price-editing-fields-<?php echo $product->ID; ?>'>
						<input type='text' class='the-product-price' name='product_price[<?php echo $product->ID; ?>][price]' value='<?php echo number_format($price,2,'.',''); ?>' />
						<input type='hidden' name='product_price[<?php echo $product->ID; ?>][id]' value='<?php echo $product->ID; ?>' />
						<input type='hidden' name='product_price[<?php echo $product->ID; ?>][nonce]' value='<?php echo wp_create_nonce('edit-product_price-'.$product->ID); ?>' />


					</div>
				</td>
			<?php
		break;

		case 'categories':
		?>
		<td <?php echo $attributes ?>><?php
			$categories = get_the_product_category($product->ID);
			if ( !empty( $categories ) ) {
				$out = array();
				foreach ( $categories as $c )
					$out[] = "<a href='edit.php?category_name=$c->slug'> " . esc_html(sanitize_term_field('name', $c->name, $c->term_id, 'category', 'display')) . "</a>";
					echo join( ', ', $out );
			} else {
				_e('Uncategorized');
			}
		?></td>
		<?php
		break;

		case 'tags':
		?>
		<td <?php echo $attributes ?>><?php
			$tags = get_the_tags($product->ID);
			if ( !empty( $tags ) ) {
				$out = array();
				foreach ( $tags as $c )
					$out[] = "<a href='edit.php?tag=$c->slug'> " . esc_html(sanitize_term_field('name', $c->name, $c->term_id, 'post_tag', 'display')) . "</a>";
				echo join( ', ', $out );
			} else {
				_e('No Tags');
			}
		?></td>
		<?php
		break;

		case 'comments':
		?>
		<td <?php echo $attributes ?>><div class="post-com-count-wrapper">
		<?php
			$pending_phrase = sprintf( __('%s pending'), number_format( $pending_comments ) );
			if ( $pending_comments )
				echo '<strong>';
				comments_number("<a href='edit-comments.php?p=$product->ID' title='$pending_phrase' class='post-com-count'><span class='comment-count'>" . /* translators: comment count link */ _x('0', 'comment count') . '</span></a>', "<a href='edit-comments.php?p=$product->ID' title='$pending_phrase' class='post-com-count'><span class='comment-count'>" . /* translators: comment count link */ _x('1', 'comment count') . '</span></a>', "<a href='edit-comments.php?p=$product->ID' title='$pending_phrase' class='post-com-count'><span class='comment-count'>" . /* translators: comment count link: % will be substituted by comment count */ _x('%', 'comment count') . '</span></a>');
				if ( $pending_comments )
				echo '</strong>';
		?>
		</div></td>
		<?php
		break;

		case 'author':
		?>
		<td <?php echo $attributes ?>><a href="edit.php?author=<?php the_author_meta('ID'); ?>"><?php the_author() ?></a></td>
		<?php
		break;

		
		case 'control_view':
		?>
		<td><a href="<?php the_permalink(); ?>" rel="permalink" class="view"><?php _e('View'); ?></a></td>
		<?php
		break;

		case 'control_edit':
		?>
		<td><?php if ( current_user_can('edit_post', $product->ID) ) { echo "<a href='$edit_link' class='edit'>" . __('Edit') . "</a>"; } ?></td>
		<?php
		break;

		case 'control_delete':
		?>
		<td><?php if ( current_user_can('delete_post', $product->ID) ) { echo "<a href='" . wp_nonce_url("post.php?action=delete&amp;post=$id", 'delete-post_' . $product->ID) . "' class='delete'>" . __('Delete') . "</a>"; } ?></td>
		<?php
		break;

		default:
		?>
		<td <?php echo $attributes ?>><?php do_action('manage_posts_custom_column', $column_name, $product->ID); ?></td>
		<?php
		break;
	}
}
?>
	</tr>
<?php
	$product = $global_product;
}











function wpsc_old_product_row(&$product) {
	global $wp_query, $wpsc_products, $mode;
	echo "<pre>".print_r($product,true)."</pre>";

	
	
	//first set the patch to the default
	$image_path = WPSC_URL."/images/no-image-uploaded.gif";
// 	if(is_numeric($product['image'])) { // check for automatic thumbnail images
// 		// file_exists(WPSC_THUMBNAIL_DIR.$product['image'])
// 		$product_image = $wpdb->get_var("SELECT `image` FROM  `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id` = '{$product['image']}' LIMIT 1");
// 		// if the image exists, set the image path to it.
// 		if(($product_image != null) && file_exists(WPSC_THUMBNAIL_DIR.$product_image)) {
// 			$image_path = WPSC_THUMBNAIL_URL.$product_image;
// 		}
// 	}

	// get the  product name, unless there is no name, in which case, display text indicating so
	if ($product->name=='') {
		$product_name = "(".__('No Name', 'wpsc').")";
	} else {
		$product_name = htmlentities(stripslashes($product->name), ENT_QUOTES, 'UTF-8');
	}


$category_html = '';
$category_list = $wpdb->get_results("SELECT `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`id`,`".WPSC_TABLE_PRODUCT_CATEGORIES."`.`name` FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` , `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` IN ('".$product['id']."') AND `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id` = `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`id` AND `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`active` IN('1')",ARRAY_A);
$i = 0;
foreach((array)$category_list as $category_row) {
	if($i > 0) {
		$category_html .= "<br />";
	}


	$category_html .= "<a class='category_link' href='". htmlentities(remove_query_arg('product_id',add_query_arg('category_id', $category_row['id'])))."'>".stripslashes($category_row['name'])."</a>";
	$i++;
}


	?>
		<tr class="product-edit <?php echo ( wpsc_publish_status($product['id']) ) ? ' wpsc_published' : ' wpsc_not_published'; ?>" id="product-<?php echo $product['id']?>" >
				<th class="check-column" scope="row"><input type='checkbox' name='product[]' class='deletecheckbox' value='<?php echo $product['id'];?>' /></th>


				<td class="product-image ">
					<img title='Drag to a new position' src='<?php echo $image_path; ?>' alt='<?php echo $product['name']; ?>' width='38' height='38' />
				</td>
				<td class="product-title column-title">
					<?php
					$edit_product_url = wp_nonce_url(htmlentities(add_query_arg('product_id', $product['id'])), 'edit_product_' . $product['id']);
					?>
					<a class='edit-product' href='<?php echo $edit_product_url; ?>'><?php echo $product_name; ?></a>
						<?php
						if($product['publish'] != 1 ) {
							?> - <strong> <?php 	_e('Draft', 'wpsc'); ?>	</strong>	<?php
						}
						?>
						<?php
						$product_alert = apply_filters('wpsc_product_alert', array(false, ''), $product);
						if(count($product_alert['messages']) > 0) {
							$product_alert['messages'] = implode("\n",(array)$product_alert['messages']);
						}
						if($product_alert['state'] === true) {
							?>
							<img alt='<?php echo $product_alert['messages'];?>' title='<?php echo $product_alert['messages'];?>' class='product-alert-image' src='<?php echo  WPSC_URL;?>/images/product-alert.jpg' alt='' />
							<?php
						}
						?>
						<img class='loadingImg' style='display:none;' src='<?php echo get_option('siteurl'); ?>/wp-admin/images/wpspin_light.gif' alt='loading' />


					<div class="wpsc-row-actions">
						<span class="edit">
							<a class='edit-product' title="Edit this post" href='<?php echo $edit_product_url; ?>' style="cursor:pointer;">Edit</a>
						</span>
							|
						<span class="delete">
							<a class='submitdelete'
								title='<?php echo attribute_escape(__('Delete this product', 'wpsc')); ?>'
								href='<?php echo wp_nonce_url("admin.php?wpsc_admin_action=delete_product&amp;product={$product['id']}", 'delete_product_' . $product['id']); ?>'
								onclick="if ( confirm(' <?php echo js_escape(sprintf( __("You are about to delete this product '%s'\n 'Cancel' to stop, 'OK' to delete."), $product['name'] )) ?>') ) { return true;}return false;"
								>
								<?php _e('Delete') ?>
							</a>
						</span>
							|
						<span class="view">
							<a target="_blank" rel="permalink" title='View <?php echo $product_name; ?>' href="<?php echo wpsc_product_url($product['id']); ?>">View</a>
						</span>
						|
						<span class="view">
							<a rel="permalink"
								title='Duplicate <?php echo $product_name; ?>'
								href="<?php echo wp_nonce_url("admin.php?wpsc_admin_action=duplicate_product&amp;product={$product['id']}", 'duplicate_product_' . $product['id']); ?>
								">
								Duplicate
							</a>
						</span>
						|
						<span class="publish_toggle">
							<a title="Change publish status"
								href="<?php echo wp_nonce_url("admin.php?wpsc_admin_action=toggle_publish&product=".$product['id'], 'toggle_publish_'.$product['id']); ?>"
								>
								<?php
								if($product['publish'] == 1 ) {
									_e('Unpublish', 'wpsc');
								} else {
									_e('Publish', 'wpsc');
								}
								?>
							</a>
						</span>
					</div>
				</td>

				<td class="product-price column-price">

				<?php echo nzshpcrt_currency_display($product['price'], 1); ?>
				<div class='price-editing-fields' id='price-editing-fields-<?php echo $product['id']; ?>'>
					<input type='text' class='the-product-price' name='product_price[<?php echo $product['id']; ?>][price]' value='<?php echo number_format($product['price'],2,'.',''); ?>' />
					<input type='hidden' name='product_price[<?php echo $product['id']; ?>][id]' value='<?php echo $product['id']; ?>' />
					<input type='hidden' name='product_price[<?php echo $product['id']; ?>][nonce]' value='<?php echo wp_create_nonce('edit-product_price-'.$product['id']); ?>' />


				</div>
				</td>
				<td class="column-categories"><?php echo $category_html; ?></td>
		</tr>
	<?php
}




/*

*/
?>