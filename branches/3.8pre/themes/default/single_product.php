<?php
global $wpsc_query, $wpdb;
$image_width = get_option('single_view_image_width');
$image_height = get_option('single_view_image_height');
?>
<div id='products_page_container' class="wrap wpsc_container">
	
	
	<?php if(wpsc_has_breadcrumbs()) :?>
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
	
	<div class="productdisplay">
	<?php /** start the product loop here, this is single products view, so there should be only one */?>
		<?php while (wpsc_have_products()) :  wpsc_the_product(); ?>
			<div class="single_product_display product_view_<?php echo wpsc_the_product_id(); ?>">
				<div class="textcol">
					<div class="imagecol">
						<?php if(wpsc_the_product_thumbnail()) :?> 	   
								<a rel="<?php echo str_replace(" ", "_", wpsc_the_product_title()); ?>" class="thickbox preview_link" href="<?php echo wpsc_the_product_image(); ?>">
									<img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_image($image_width, $image_height); ?>"/>
								</a>
						<?php else: ?> 
							<div class="item_no_image">
								<a href="<?php echo wpsc_the_product_permalink(); ?>">
								<span>No Image Available</span>
								</a>
							</div>
						<?php endif; ?> 
					</div>
		
		
					<div class="producttext">
						<h2 class="prodtitles"><span><?php echo wpsc_the_product_title(); ?></span><?php echo wpsc_edit_the_product_link(); ?></h2>
							<?php				
								if((wpsc_product_has_file() == true)  && (function_exists('listen_button'))){
									$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$wpsc_query->product['file']."' LIMIT 1",ARRAY_A);
									if($file_data != null) {
										echo listen_button($file_data['idhash'], $file_data['id']);
									}
								}
						?>
						
						
						<div class="wpsc_description"><?php echo wpsc_the_product_description(); ?></div>
		
						<?php
							do_action('wpsc_product_addons', wpsc_the_product_id());
						?>
						<?php if(wpsc_the_product_additional_description()) : ?>
						<br clear="all" /><p class="single_additional_description">
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
						</p>
					<?php endif; ?>
				
					<?php do_action('wpsc_product_addon_after_descr', wpsc_the_product_id()); ?>

					<?php /** the custom meta HTML and loop */ ?>
					<div class="custom_meta">
						<?php while (wpsc_have_custom_meta()) : wpsc_the_custom_meta(); 	?>
							<strong><?php echo wpsc_custom_meta_name(); ?>: </strong><?php echo wpsc_custom_meta_value(); ?><br />
						<?php endwhile; ?>
					</div>
					<?php /** the custom meta HTML and loop ends here */?>
					
					
					<form class='product_form' enctype="multipart/form-data" action="<?php echo wpsc_this_page_url(); ?>" method="post" name="1" id="product_<?php echo wpsc_the_product_id(); ?>">
					<?php if(wpsc_product_has_personal_text()) : ?>
						<div class='custom_text'>
							<h4><?php echo TXT_WPSC_PERSONALIZE_YOUR_PRODUCT; ?></h4>
							<?php echo TXT_WPSC_PERSONALIZE_YOUR_PRODUCT_DESCRIPTION; ?><br />
							<input type='text' name='custom_text' value=''  />
						</div>
					<?php endif; ?>
					
					<?php if(wpsc_product_has_supplied_file()) : ?>
						<div class='custom_file'>
							<h4><?php echo TXT_WPSC_UPLOAD_A_FILE; ?></h4>
							<?php echo TXT_WPSC_UPLOAD_A_FILE_DESCRIPTION; ?><br />
							<input type='file' name='custom_file' value=''  />
						</div>
					<?php endif; ?>
					
					
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
					<?php if(function_exists('wpsc_akst_share_link') && (get_option('wpsc_share_this') == 1)) {
						echo wpsc_akst_share_link('return');
					} ?>
						
					<input type="hidden" value="add_to_cart" name="wpsc_ajax_action"/>
					<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="product_id"/>
							
					<?php if(wpsc_product_is_customisable()) : ?>				
						<input type="hidden" value="true" name="is_customisable"/>
					<?php endif; ?>
					
					
					<!-- END OF QUANTITY OPTION -->
					<?php if((get_option('hide_addtocart_button') == 0) && (get_option('addtocart_or_buynow') !='1')) : ?>
						<?php if(wpsc_product_has_stock()) : ?>
							<?php if(wpsc_product_external_link(wpsc_the_product_id()) != '') : ?>
										<?php	$action =  wpsc_product_external_link(wpsc_the_product_id()); ?>
										<input class="wpsc_buy_button" type='button' value='<?php echo TXT_WPSC_BUYNOW; ?>' onclick='gotoexternallink("<?php echo $action; ?>")'>
										<?php else: ?>
									<input type="submit" value="<?php echo TXT_WPSC_ADDTOCART; ?>" name="Buy" class="wpsc_buy_button" id="product_<?php echo wpsc_the_product_id(); ?>_submit_button"/>
										<?php endif; ?>
							
							<div class='wpsc_loading_animation'>
								<img title="Loading" alt="Loading" src="<?php echo WPSC_URL ;?>/images/indicator.gif" class="loadingimage" />
								<?php echo TXT_WPSC_UDPATING_CART; ?>
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
							echo gold_shpcrt_display_gallery(wpsc_the_product_id());
						endif;
					?>
					</div>
		
					<form onsubmit="submitform(this);return false;" action="<?php echo wpsc_this_page_url(); ?>" method="post" name="product_<?php echo wpsc_the_product_id(); ?>" id="product_extra_<?php echo wpsc_the_product_id(); ?>">
						<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="prodid"/>
						<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="item"/>
					</form>
				</div>
			</div>
		</div>
		
		<?php echo wpsc_product_comments(); ?>
<?php endwhile; ?>
<?php /** end the product loop here */?>

		<?php
		if(function_exists('fancy_notifications')) {
			echo fancy_notifications();
		}
		?>
	

</div>