<table border="0" width="100%" cellspacing="0" cellpadding="0" class="data-table">
    <tr class="title">
        <td><?php echo Translate('Product');?></td>
        <td><?php echo Translate('Prijs'); ?></td>
    </tr>
	<?php
	for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
		$stockCheck = '';
		if (STOCK_CHECK == 'true') {
			$stockCheck = tep_check_stock($order->products[$i]['id'], $order->products[$i]['qty']);
		}
		$productAttributes = '';
		if (isset($order->products[$i]['attributes']) && sizeof($order->products[$i]['attributes']) > 0) {
			for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
				$productAttributes .= '<br><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] . '</i></small></nobr>' . tep_draw_hidden_field('id[' . $order->products[$i]['id'] . '][' . $order->products[$i]['attributes'][$j]['option_id'] . ']', $order->products[$i]['attributes'][$j]['value_id']);
			}
		}
		if ($i%2) {
			$class = 'odd';
		} else {
			$class = 'even';
		}
		?>     
        <tr class="data <?php echo $class;?>">
            <td><?php echo $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' ( ' . $order->products[$i]['model'] . ' ) ' . $stockCheck . $productAttributes;?></td>
            <td><?php echo $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']); ?></td>
        </tr>
		<?php           
        }
        ?>
</table>