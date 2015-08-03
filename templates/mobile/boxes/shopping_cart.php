<div class="col-xs-6 col-sm-6 mb-20">
	<?php if (CanShop() == 'true') { ?>
		<?php if(!is_object($cart)) { $cart = new shoppingCart();} ?>
		<?php if(!is_object($currencies)) { $currencies = new currencies();} ?>
		<a href="<?php echo tep_href_link(FILENAME_SHOPPING_CART); ?>" class="text-warning"><?php echo Translate('Winkelwagen'); ?></a></br>
		<?php echo Translate('Items'),': ','<span class="text-info">',$cart->count_contents(),'</span><br/>'; ?>
		<?php echo Translate('Totaal'),': ','<span class="text-info">',$currencies->format($cart->show_total()),'</span>'; ?>
	<?php } else { ?>
		<?php echo Translate('Bezig met verwerking...'); ?>
	<?php } ?>
</div>