<?php
global $wpsc_query, $wpdb;
?>

<div id='products_page_container' class="wrap wpsc_container">

<?php if(wpsc_has_breadcrumbs()) : ?>
		<div class='breadcrumb'>
			<a href='<?php echo get_option('home'); ?>'><?php echo get_option('blogname'); ?></a> &raquo;
			<?php while (wpsc_have_breadcrumbs()) : wpsc_the_breadcrumb(); ?>
				<?php if(wpsc_breadcrumb_url()) :?> 	   
					<a href='<?php echo wpsc_breadcrumb_url(); ?>'><?php echo wpsc_breadcrumb_name(); ?></a> &raquo;
				<?php else: ?> 
					<?php echo wpsc_breadcrumb_name(); ?>
				<?php endif; ?> 
			<?php endwhile; ?>
		</div>
	<?php endif; ?>
	
	<?php do_action('wpsc_top_of_products_page'); // Plugin hook for adding things to the top of the products page, like the live search ?>
	
	<?php if(wpsc_is_in_category()) : ?>
		<div class='wpsc_category_details'>
			<?php if(get_option('show_category_thumbnails') && wpsc_category_image()) : ?>
				<img src='<?php echo wpsc_category_image(); ?>' alt='<?php echo wpsc_category_name(); ?>' title='<?php echo wpsc_category_name(); ?>' />
			<?php endif; ?>
			
			<?php if(get_option('wpsc_category_description') &&  wpsc_category_description()) : ?>
				<?php echo wpsc_category_description(); ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	
	<?php if(wpsc_has_pages() && ((get_option('wpsc_page_number_position') == 1 ) || (get_option('wpsc_page_number_position') == 3)))  : ?>
		<div class='wpsc_page_numbers'>
		  Pages: 
			<?php while (wpsc_have_pages()) : wpsc_the_page(); ?>
				<?php if(wpsc_page_is_selected()) :?> 	   
					<a href='<?php echo wpsc_page_url(); ?>' class='selected'><?php echo wpsc_page_number(); ?></a>
				<?php else: ?> 
					<a href='<?php echo wpsc_page_url(); ?>'><?php echo wpsc_page_number(); ?></a>
				<?php endif; ?> 
			<?php endwhile; ?>
		</div>
	<?php endif; ?>
	<?php /** start the product loop here */?>
	<?php while (wpsc_have_products()) :  wpsc_the_product(); ?>
		<div class="productdisplay default_product_display product_view_<?php echo wpsc_the_product_id(); ?> <?php echo wpsc_category_class(); ?>">      
			<div class="textcol">
			
				<?php if(get_option('show_thumbnails')) :?>
					<div class="imagecol">
						<?php if(wpsc_the_product_thumbnail()) :?>
							<a rel="<?php echo wpsc_the_product_title(); ?>" class="thickbox preview_link" href="<?php echo wpsc_the_product_image(); ?>">
								<img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_thumbnail(); ?>"/>
							</a>
						<?php else: ?>
							<div class="item_no_image">
								<a href="<?php echo wpsc_the_product_permalink(); ?>">
								<span>No Image Available</span>
								</a>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				
				<div class="producttext">
					<h2 class="prodtitles">
					  <?php if(get_option('hide_name_link') == 1) : ?>
							<span><?php echo wpsc_the_product_title(); ?></span>
						<?php else: ?> 
							<a class="wpsc_product_title" href="<?php echo wpsc_the_product_permalink(); ?>"><?php echo wpsc_the_product_title(); ?></a>
						<?php endif; ?> 				

						<?php echo wpsc_edit_the_product_link(); ?>
					</h2>
					<?php
						do_action('wpsc_product_addons', wpsc_the_product_id());
						
						if((wpsc_product_has_file() == true)  && (function_exists('listen_button'))){
							$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$wpsc_query->product['file']."' LIMIT 1",ARRAY_A);
							if($file_data != null) {
								echo listen_button($file_data['idhash'], $file_data['id']);
							}
						}
				?>
					
					
					<div class='wpsc_description'><?php echo wpsc_the_product_description(); ?></div>
					
					
					<?php if(wpsc_the_product_additional_description()) : ?>
					<div class='additional_description_span'>
						<a href='<?php echo wpsc_the_product_permalink(); ?>' class='additional_description_link'>
							<img class='additional_description_button'  src='<?php echo WPSC_URL; ?>/images/icon_window_expand.gif' title='Additional Description' alt='Additional Description' /><?php echo TXT_WPSC_MOREDETAILS; ?>
						</a>
						<span class='additional_description'><br />
							<?php
								$value = '';
								$the_addl_desc = wpsc_the_product_additional_description();
								if( is_serialized($the_addl_desc) ) {
									$addl_descriptions = @unserialize($the_addl_desc);
								} else {
									$addl_descriptions = array('addl_desc', $the_addl_desc);
								}
								
								if( isset($addl_descriptions['addl_desc']) ) {
									$value = $addl_descriptions['addl_desc'];
								}
							
								if( function_exists('wpsc_addl_desc_show') ) {
									echo wpsc_addl_desc_show( $addl_descriptions );
								} else {
									echo $the_addl_desc;
								}
							?>
						</span>
						<br />
					</div>
					<?php endif; ?>
					
					<?php if(wpsc_product_external_link(wpsc_the_product_id()) != '') : ?>
					<?php	$action =  wpsc_product_external_link(wpsc_the_product_id()); ?>
					<?php else: ?>
					<?php	$action =  wpsc_this_page_url(); ?>						
					<?php endif; ?>
					<form class='product_form'  enctype="multipart/form-data" action="<?php echo $action; ?>" method="post" name="product_<?php echo wpsc_the_product_id(); ?>" id="product_<?php echo wpsc_the_product_id(); ?>" >
						<?php do_action('wpsc_product_addon_after_descr', wpsc_the_product_id()); ?>
						
						<?php /** the custom meta HTML and loop */?>
						<div class="custom_meta">
							<?php while (wpsc_have_custom_meta()) : wpsc_the_custom_meta(); 	?>
								<strong><?php echo wpsc_custom_meta_name(); ?>: </strong><?php echo wpsc_custom_meta_value(); ?><br />
							<?php endwhile; ?>
						</div>
						<?php /** the custom meta HTML and loop ends here */?>
						
						<?php /** add the comment link here */?>
						<?php echo wpsc_product_comment_link();	?>
						
						
						<?php /** the variation group HTML and loop */?>
						<div class="wpsc_variation_forms">
							<?php while (wpsc_have_variation_groups()) : wpsc_the_variation_group(); ?>
								<p>
									<label for="<?php echo wpsc_vargrp_form_id(); ?>"><?php echo wpsc_the_vargrp_name(); ?>:</label>
									<?php /** the variation HTML and loop */?>
									<select class='wpsc_select_variation' name="variation[<?php echo wpsc_vargrp_id(); ?>]" id="<?php echo wpsc_vargrp_form_id(); ?>">
									<?php while (wpsc_have_variations()) : wpsc_the_variation(); ?>
										<option value="<?php echo wpsc_the_variation_id(); ?>"><?php echo wpsc_the_variation_name(); ?></option>
									<?php endwhile; ?>
									</select> 
								</p>
							<?php endwhile; ?>
						</div>
						<?php /** the variation group HTML and loop ends here */?>
												
					<!-- THIS IS THE QUANTITY OPTION MUST BE ENABLED FROM ADMIN SETTINGS -->
					<?php if(wpsc_has_multi_adding()): ?>
						<label class='wpsc_quantity_update' for='wpsc_quantity_update'><?php echo TXT_WPSC_QUANTITY; ?>:</label>
						
						<input type="text" id='wpsc_quantity_update' name="wpsc_quantity_update" size="2" value="1"/>
						<input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>"/>
						<input type="hidden" name="wpsc_update_quantity" value="true"/>
					<?php endif ;?>

						<p class="wpsc_extras_forms"/>
						<div class="wpsc_product_price">
							<?php if(wpsc_product_is_donation()) : ?>
								<label for='donation_price_<?php echo wpsc_the_product_id(); ?>'><?php echo TXT_WPSC_DONATION; ?>:</label>
								<input type='text' id='donation_price_<?php echo wpsc_the_product_id(); ?>' name='donation_price' value='<?php echo $wpsc_query->product['price']; ?>' size='6' />
								<br />
							
							
							<?php else : ?>
								<?php if(wpsc_product_on_special()) : ?>
									<span class='oldprice'><?php echo TXT_WPSC_PRICE; ?>: <?php echo wpsc_product_normal_price(); ?></span><br />
								<?php endif; ?>
								<?php echo TXT_WPSC_PRICE; ?>:  <span id="product_price_<?php echo wpsc_the_product_id(); ?>" class="pricedisplay"><?php echo wpsc_the_product_price(); ?></span><br/>
								<?php if(get_option('display_pnp') == 1) : ?>
									<?php echo TXT_WPSC_PNP; ?>:  <span class="pricedisplay"><?php echo wpsc_product_postage_and_packaging(); ?></span><br />
								<?php endif; ?>							
							<?php endif; ?>
						</div>
						
						<input type="hidden" value="add_to_cart" name="wpsc_ajax_action"/>
						<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="product_id"/>
				
						<!-- END OF QUANTITY OPTION -->
						<?php if((get_option('hide_addtocart_button') == 0) &&  (get_option('addtocart_or_buynow') !='1')) : ?>
							<?php if(wpsc_product_has_stock()) : ?>
								<div class='wpsc_buy_button_container'>
																			<?php if(wpsc_product_external_link(wpsc_the_product_id()) != '') : ?>
										<?php	$action =  wpsc_product_external_link(wpsc_the_product_id()); ?>
										<input class="wpsc_buy_button" type='button' value='<?php echo TXT_WPSC_BUYNOW; ?>' onclick='gotoexternallink("<?php echo $action; ?>")'>
										<?php else: ?>
										<input type='image' src='<?php echo WPSC_URL; ?>/themes/iShop/images/buy_button.gif' id='product_<?php echo wpsc_the_product_id(); ?>_submit_button' class='wpsc_buy_button' name='Buy'  value="<?php echo TXT_WPSC_ADDTOCART; ?>" />
										<?php endif; ?>
										<div class='wpsc_loading_animation'>
										<img title="Loading" alt="Loading" src="<?php echo WPSC_URL; ?>/images/indicator.gif" class="loadingimage"/>
										<?php echo TXT_WPSC_UDPATING_CART; ?>
									</div>
								</div>
							<?php else : ?>
								<p class='soldout'><?php echo TXT_WPSC_PRODUCTSOLDOUT; ?></p>
							<?php endif ; ?>
						<?php endif ; ?>
					</form>
					
				  <?php if((get_option('hide_addtocart_button') == 0) && (get_option('addtocart_or_buynow')=='1')) : ?>
						<?php echo wpsc_buy_now_button(wpsc_the_product_id()); ?>
					<?php endif ; ?>
					
					<?php echo wpsc_product_rater(); ?>
					<?php
						if(function_exists('gold_shpcrt_display_gallery')) :					
							echo gold_shpcrt_display_gallery(wpsc_the_product_id(), true);
						endif;
						?>
					
				</div>
		</div>
	</div>

	<?php endwhile; ?>
	<?php /** end the product loop here */?>
	
	
	<?php if(wpsc_product_count() < 1):?>
		<p><?php  echo TXT_WPSC_NOITEMSINTHISGROUP; ?></p>
	<?php endif ; ?>

<?php

if(function_exists('fancy_notifications')) {
  echo fancy_notifications();
}
?>

	<?php if(wpsc_has_pages() &&  ((get_option('wpsc_page_number_position') == 2) || (get_option('wpsc_page_number_position') == 3))) : ?>
		<div class='wpsc_page_numbers'>
		  Pages: 
			<?php while ($wpsc_query->have_pages()) : $wpsc_query->the_page(); ?>
				<?php if(wpsc_page_is_selected()) :?> 	   
					<a href='<?php echo wpsc_page_url(); ?>' class='selected'><?php echo wpsc_page_number(); ?></a>
				<?php else: ?> 
					<a href='<?php echo wpsc_page_url(); ?>'><?php echo wpsc_page_number(); ?></a>
				<?php endif; ?> 
			<?php endwhile; ?>
		</div>
	<?php endif; ?>
</div>