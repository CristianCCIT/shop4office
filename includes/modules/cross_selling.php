<?php
if (CROSS_SELLING_LIST == 'true') {
if (!is_object($cross_products)) {
	$cross_products = new cross($product_info['products_model'], $current_category);
}
$max_count_preview_cross = PRODUCT_INFO_MAX_PREVIEW_CROSS;
$max_count_cross = (count($cross_products->products) > PRODUCT_INFO_MAX_CROSS?PRODUCT_INFO_MAX_CROSS:count($cross_products->products));
if (count($cross_products->products) > 0) {
?>
<div class="product-cross">
	<h3><?php echo Translate('Accessoires');?></h3>
	<div class="product-cross-preview">
	<?php
	$carts_products = $cart->get_products();
	$count_cross = 0;
	foreach($cross_products->products as $id=>$cross_info) {
		if ($count_cross >= $max_count_preview_cross) {
			break;
		} else {
		?>
		<div class="product-cross-product">
			<div class="product-cross-image">
			<?php echo tep_image(DIR_WS_IMAGES.$cross_info['products_image'], $cross_info['products_name'], CROSS_PRODUCT_LIST_IMAGE_WIDTH, CROSS_PRODUCT_LIST_IMAGE_HEIGHT);?>
			</div>
			<div class="product-cross-description">
				<div class="product-cross-title">
					<a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$cross_info['products_id']);?>" title="<?php echo $cross_info['products_name'];?>" class="popup">
						<?php echo $cross_info['categories_name'].' - '.$cross_info['products_name'];?>
					</a>
				</div>
				<?php echo shorten_text($cross_info['products_description'], 50, 1);?>
			</div>
			<div class="product-cross-price">
				<?php
				if (CanShop() == 'true') { 
					if ($cross_info['specials_new_products_price'] > 0) {
						echo '<span class="specialprice">'.$currencies->format($cross_info['specials_new_products_price']).'</span> <span class="oldprice">'.$currencies->format($cross_info['products_price']).'</span>';
					} else {
						echo '<span class="yourprice">'.$currencies->format($cross_info['products_price']).'</span>';
					}
				}
				?>
			</div>
			<div class="product-cross-order">
				<?php
				FB::info($cross_info);
				if (CanShop() == 'true') {
					$this_checked = false;
					if (isset($cross_info['attribute_ids']) && is_array($cross_info['attribute_ids'])) {
						foreach($cross_info['attribute_ids'] as $attr_id) {
							if ($cart->get_quantity($attr_id) > 0) {
								$this_checked = true;
							}
						}
					} else {
						if ($cart->get_quantity($cross_info['products_id']) > 0) {
							$this_checked = true;
						}
					}
				?>
				<div class="product-cross-checkbox">
					<input type="checkbox" class="<?php echo ($this_checked?'remove_product':'add_product');?>" name="<?php echo $cross_info['products_name'];?>" id="<?php echo $cross_info['products_id'];?>" <?php echo ($this_checked?'checked=checked ':'');?>/>
				</div>
				<a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$cross_info['products_id']);?>" name="<?php echo $cross_info['products_name'];?>" id="<?php echo $cross_info['products_id'];?>" title="<?php echo Translate('Bestel').' '.$cross_info['products_name'];?>" class="button-b <?php echo ($this_checked?'remove_product':'add_product');?>">
					<?php echo ($this_checked?Translate('Besteld'):Translate('Bestellen'));?>
				</a>
				<?php
				}
				?>
			</div>
			<div class="product-cross-attributes" id="product-cross-attributes-<?php echo $cross_info['products_id'];?>" style="display:none;">
				<?php
				foreach($cross_info['attributes'] as $option=>$values) {
					echo $values['options_name'].': ';
					if (count($values['values']) > 1) {
						echo '<select name="'.$option.'">';
						foreach($values['values'] as $value=>$option_info) {
							echo '<option value="'.$value.'">'.$option_info['products_options_values_name'].'('.$option_info['price_prefix'].$currencies->format($option_info['options_values_price']).')'.'</option>';
						}
						echo '</select>';
					} else {
						foreach($values['values'] as $value=>$option_info) {
							echo '<input type="hidden" name="'.$option.'" value="'.$value.'" />';
							echo $option_info['products_options_values_name'].'('.$option_info['price_prefix'].$currencies->format($option_info['options_values_price']).')';
						}
					}
				}
				?>
			</div>
		</div>
		<?php
		}
		$count_cross++;
	}
	if (count($cross_products->products) > $max_count_preview_cross) {
	?>
	<div id="show-product-cross-list"><?php echo sprintf(Translate('Toon alle %s accessoires'), $max_count_cross);?></div>
	<?php
	}
	?>
	</div>
	<div class="product-cross-list" style="display:none;">
	<?php
	$count_cross = 0;
	if (CROSS_PRODUCTS_GROUPED == 'manufacturers') {
		$products_cross_list = $cross_products->manufacturers;
	} else {
		$products_cross_list = $cross_products->categories;
	}
	foreach($products_cross_list as $id=>$cross_info) {
		if ($count_cross >= $max_count_cross) {
			break;
		} else {
		?>
		<div class="product-cross-group">
			<h4 class="product-cross-group-title">
				<?php echo $cross_info['name'];?>
			</h4>
			<div class="product-cross-group-products">
				<?php
				foreach($cross_info['products'] as $pid=>$cross_product_info) {
					if ($count_cross >= $max_count_cross) {
						break;
					} else {
				?>
				<div class="product-cross-product">
					<div class="product-cross-image">
					<?php echo tep_image(DIR_WS_IMAGES.$cross_products->products[$cross_product_info]['products_image'], $cross_products->products[$cross_product_info]['products_name'], CROSS_PRODUCT_LIST_IMAGE_WIDTH, CROSS_PRODUCT_LIST_IMAGE_HEIGHT);?>
					</div>
					<div class="product-cross-description">
						<div class="product-cross-title">
							<a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$cross_products->products[$cross_product_info]['products_id']);?>" title="<?php echo $cross_products->products[$cross_product_info]['products_name'];?>">
								<?php echo $cross_products->products[$cross_product_info]['categories_name'].' - '.$cross_products->products[$cross_product_info]['products_name'];?>
							</a>
						</div>
						<?php echo shorten_text($cross_products->products[$cross_product_info]['products_description'], 50, 1);?>
					</div>
					<div class="product-cross-price">
						<?php
						if (CanShop() == 'true') { 
							if ($cross_products->products[$cross_product_info]['specials_new_products_price'] > 0) {
								echo '<span class="specialprice">'.$currencies->format($cross_products->products[$cross_product_info]['specials_new_products_price']).'</span> <span class="oldprice">'.$currencies->format($cross_products->products[$cross_product_info]['products_price']).'</span>';
							} else {
								echo '<span class="yourprice">'.$currencies->format($cross_products->products[$cross_product_info]['products_price']).'</span>';
							}
						}
						?>
					</div>
					<div class="product-cross-order">
						<?php
						if (CanShop() == 'true') {
							$this_checked = false;
							if (isset($cross_products->products[$cross_product_info]['attribute_ids']) && is_array($cross_products->products[$cross_product_info]['attribute_ids'])) {
								foreach($cross_products->products[$cross_product_info]['attribute_ids'] as $attr_id) {
									if ($cart->get_quantity($attr_id) > 0) {
										$this_checked = true;
									}
								}
							} else {
								if ($cart->get_quantity($cross_product_info) > 0) {
									$this_checked = true;
								}
							}
						?>
						<div class="product-cross-checkbox">
							<input type="checkbox" class="<?php echo ($this_checked?'remove_product':'add_product');?>" name="<?php echo $cross_products->products[$cross_product_info]['products_name'];?>" id="<?php echo $cross_product_info;?>" <?php echo ($this_checked?'checked=checked ':'');?>/>
						</div>
						<a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$cross_product_info);?>" name="<?php echo $cross_products->products[$cross_product_info]['products_name'];?>" id="<?php echo $cross_product_info;?>" title="<?php echo Translate('Bestel').' '.$cross_products->products[$cross_product_info]['products_name'];?>" class="button-b <?php echo ($this_checked?'remove_product':'add_product');?>">
							<?php echo ($this_checked?Translate('Besteld'):Translate('Bestellen'));?>
						</a>
						<?php
						}
						?>
					</div>
					<div class="product-cross-attributes" id="product-cross-attributes-<?php echo $cross_product_info;?>" style="display:none;">
						<?php
						foreach($cross_products->products[$cross_product_info]['attributes'] as $option=>$values) {
							echo $values['options_name'].': ';
							if (count($values['values']) > 1) {
								echo '<select name="'.$option.'">';
								foreach($values['values'] as $value=>$option_info) {
									echo '<option value="'.$value.'">'.$option_info['products_options_values_name'].'('.$option_info['price_prefix'].$currencies->format($option_info['options_values_price']).')'.'</option>';
								}
								echo '</select>';
							} else {
								foreach($values['values'] as $value=>$option_info) {
									echo '<input type="hidden" name="'.$option.'" value="'.$value.'" />';
									echo $option_info['products_options_values_name'].'('.$option_info['price_prefix'].$currencies->format($option_info['options_values_price']).')';
								}
							}
						}
						?>
					</div>
				</div>
				<?php
					}
					$count_cross++;
				}
				?>
			</div>
		</div>
		<?php
		}
	}
	?>
	<div id="hide-product-cross-list"><?php echo Translate('Toon minder accessoires');?></div>
	</div>
</div>
<div style="display:none">
	<div id="addCartBlock">
		<div class="addCartBlock-title">
			<h1><?php echo Translate('Winkelwagen');?></h1>
		</div>
		<div class="addCartBlock-product">
			
		</div>
		<?php echo Translate('werd toegevoegd aan uw winkelwagen');?>
		<div class="addCartBlock-buttons">
			<input type="button" class="button-c" name="close" value="<?php echo Translate('Verder winkelen');?>" />
			<input type="button" class="button-b" name="cart" value="<?php echo Translate('Naar winkelwagen');?>" />
		</div>
	</div>
</div>
<script type="text/javascript">
	$('#show-product-cross-list').live('click', function() {
		$('.product-cross-list').show();
		$('.product-cross-preview').hide();
	});
	$('#hide-product-cross-list').live('click', function() {
		$('.product-cross-list').hide();
		$('.product-cross-preview').show();
	});
	$('.remove_product').live('click', function(event) {
		event.preventDefault();
		var element = $(this);
		var prodId = $(this).attr('id');
		$.ajax({
			url: 'ajax_search.php?mode=productWithAttrInCart&products_id='+prodId,
			dataType: 'json',
			success: function(data) {
				if (data.success == true) {
					if (data.count == 1) {
						removeProductFromCart(element, data.attr[0])
					} else {
						//product zit in winkelmandje met verschillende attributen
					}
				} else {
					removeProductFromCart(element, prodId)
				}
			}
		});
		return false;
	});
	$('.add_product').live('click', function(event) {
		var element = $(this);
		var prodId = $(this).attr('id');
		var prodName = $(this).attr('name');
		$('.addCartBlock-product').html('1x '+prodName)
		if (element.is('input')) {
			element.attr('checked', 'checked').removeClass('add_product').addClass('remove_product');
			element.closest('.product-cross-order').children('a').removeClass('add_product').addClass('remove_product').html('<?php echo Translate('Besteld');?>');
		} else {
			event.preventDefault();
			element.removeClass('add_product').addClass('remove_product');
			element.html('<?php echo Translate('Besteld');?>');
			var input = element.parent('div').children('div').children('input');
			input.removeClass('add_product');
			input.addClass('remove_product');
			input.attr('checked', 'checked');
		}
		var attributes = $('#product-cross-attributes-'+prodId);
		if (attributes.children('select').length > 0) {
			//meerdere attributen
		} else {
			var data = 'products_id='+prodId+'&quantity=1';
			if (attributes.children('input').length > 0) {
				data += '&attributes['+attributes.children('input').attr('name')+']='+attributes.children('input').val();
			}			
			$.ajax({
				url: 'ajax_search.php?mode=addToCart',
				data: data,
				success: function() {
					refreshShoppingCart()
				}
			});
		}
		popupAfterAddToCart();
	});
	$('.popup').live('click', function() {
		
	});
	function removeProductFromCart(element, prodId) {
		$.ajax({
			url: 'ajax_search.php?mode=removeFromCart&product='+prodId,
			success: function() {
				if (element.is('input')) {
					element.removeAttr('checked').removeClass('remove_product').addClass('add_product');
					element.closest('.product-cross-order').children('a').removeClass('remove_product').addClass('add_product').html('<?php echo Translate('Bestellen');?>');
				} else {
					element.removeClass('remove_product').addClass('add_product');
					element.html('<?php echo Translate('Bestellen');?>');
					var input = element.parent('div').children('div').children('input');
					input.removeClass('remove_product');
					input.addClass('add_product');
					input.removeAttr('checked');
				}
				refreshShoppingCart();
			}
		});
	}
	function refreshShoppingCart() {
		$.ajax({
			url: 'ajax_search.php?mode=shoppingCart',
			success: function(data) {
				$('.box.shopping_cart').html(data);
			}
		});
	}
	function popupAfterAddToCart() {
		$.colorbox({
			inline: true,
			href: '#addCartBlock',
			overlayClose: false,
			escKey: false,
			extraClass: 'notClose'
		});
		$('input[name=close]').live('click', function() {
			$.colorbox.close();
		});
		$('input[name=cart]').live('click', function() {
			document.location = '<?php echo tep_href_link(FILENAME_SHOPPING_CART);?>';
			$.colorbox.close();
		});
	}
</script>
<?php
}
}
?>