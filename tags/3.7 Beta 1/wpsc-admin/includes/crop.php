<?php

/**
 * Jcrop image cropping plugin for jQuery
 * Example cropping script
 * @copyright 2008 Kelly Hallman
 * More info: http://deepliquid.com/content/Jcrop_Implementation_Theory.html
 */
if(isset($_GET['imagename'])){
	$imagename = $_GET['imagename'];
}
$directory = WPSC_IMAGE_URL;//set directory
$width = $_GET['imgwidth'];//set image dimensions
$height = $_GET['imgheight'];
$product_id = $_GET['product_id'];
if ($width > 400){
	$layoutlandscape = 'wpsc_thumbnail_preview_landscape';
}else{
	$layoutlandscape = "wpsc_thumbnail_preview";
}

?><html>
	<head>

		<script language="javascript" type="text/javascript">

			jQuery(function(){

				jQuery('#cropbox').Jcrop({
					aspectRatio: 1,
					onSelect: showPreview,
					onChange: updateCoords,
				});
	
			});

			function updateCoords(c)
			{
				jQuery('#x').val(c.x);
				jQuery('#y').val(c.y);
				jQuery('#w').val(c.w);
				jQuery('#h').val(c.h);
			};

			function checkCoords()
			{
				if (parseInt(jQuery('#w').val())) return true;
				alert('Please select a crop region then press submit.');
				return false;
			};
			function showPreview(coords)
			{
				var rx = 100 / coords.w ;
				var ry = 100 / coords.h;
				jQuery('#preview').css({
					width: Math.round(rx * <?php echo $width; ?>) + 'px',
					height: Math.round(ry * <?php echo $height; ?>) + 'px',
					marginLeft: '-' + Math.round(rx * coords.x) + 'px',
					marginTop: '-' + Math.round(ry * coords.y) + 'px'
				});
			};

		</script>

	</head>

	<body>

	<div id="outer">
		<?php if($layoutlandscape != 'wpsc_thumbnail_preview'){ ?>
			<img src="<?php echo $directory.$imagename; ?>" id="cropbox" />
		<?php } ?>
		<div id='<?php echo $layoutlandscape; ?>'>	
			<div id='wpsc_crop_preview'>
				<img src="<?php echo $directory.$imagename; ?>" id="preview" />
			</div>
			<?php if($layoutlandscape == 'wpsc_thumbnail_preview'): ?>
			<br style='clear:both' />
			<?php endif; ?>
			<!-- This is the form that our event handler fills -->
			<form action="" method="post" onsubmit="return checkCoords();">
				<input type="hidden" id="x" name="x" />
				<input type="hidden" id="y" name="y" />
				<input type="hidden" id="h" name="h" />
				<input type="hidden" id="w" name="w" />
				<input type="hidden" id="imagename" name="imagename" value="<?php echo $imagename; ?>" />		
				<input type="hidden" name="wpsc_admin_action" value="crop_thumb" />
				<input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
				<p><label for="jpegquality">Jpeg Quality :</label><input size='2' type="text" id="jpegquality" name="jpegquality" value='70' /> %<br /></p>
				<p><label for="thumbsize">Thumbnail Size :</label><input size='2' type="text" id="thumbsize" name="thumbsize" value='100' /> px<br /></p>
				<p><input class="button-secondary action"  type="submit" value="Crop Image" /></p>
			</form>
		</div>

		<!-- This is the image we're attaching Jcrop to -->
		<?php if($layoutlandscape == 'wpsc_thumbnail_preview'){ ?>
		<img src="<?php echo $directory.$imagename; ?>" id="cropbox" />
		<?php } ?>
	</div>
	</body>

</html>