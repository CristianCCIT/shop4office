<div id="shoppingCart"><table border="0" width="100%" cellspacing="0" cellpadding="2">
 <tr>
  <!--<td class="smallText"><b><?php echo Translate('Model');?></b></td>-->
  <td class="smallText"><b><?php echo Translate('Product');?></b></td>
  <?php if (USE_PRICES_TO_QTY == 'true') { ?>
  <td class="smallText"><b><?php echo Translate('Maat');?></b></td>
  <?php } ?>
  <td class="smallText"><b><?php echo Translate('Aantal');?></b></td>
  <td class="smallText" align="right"><b><?php echo Translate('Prijs/stuk');?></b></td>
  <td class="smallText" align="right"><b><?php echo Translate('Prijs');?></b></td>
  <td class="smallText" align="right"></td>
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
 <tr class="<?php echo $class;?>">
  <!--<td class="main" valign="top"><?php echo $order->products[$i]['model'];?></td>-->
  <td class="main" valign="top"><?php echo $order->products[$i]['name'] . $stockCheck . $productAttributes;?></td>
  <?php if (USE_PRICES_TO_QTY == 'true') { ?>
  <td class="main" valign="top"><?php echo $order->products[$i]['maat'];?></td>
  <?php } ?>
  <td class="main" valign="middle"><?php
     if (USE_PRICES_TO_QTY == 'true') {
	  	echo tep_draw_input_field('qty[' . $order->products[$i]['size_id'] . ']', $order->products[$i]['qty'], 'size="3" onkeyup="$(\'input[name^=qty]\').attr(\'readonly\', true); $(\'#updateCartButton\').trigger(\'click\')"');
  } else {
	  echo tep_draw_input_field('qty[' . $order->products[$i]['id'] . ']', $order->products[$i]['qty'], 'size="3" onkeyup="$(\'input[name^=qty]\').attr(\'readonly\', true); $(\'#updateCartButton\').trigger(\'click\')"');
  }
  ?></td>
  <td class="main" align="right" valign="middle"><?php
   echo $currencies->display_price($order->products[$i]['final_price'],$order->products[$i]['tax']);

  ?></td>
  <td class="main" align="right" valign="middle"><?php
   echo $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']);
  ?></td>
    <?php
  if (USE_PRICES_TO_QTY == 'true') {
  ?>
  <td class="main" align="right" valign="middle"><a href="Javascript:void();" linkData="action=removeProduct&pID=<?php echo $order->products[$i]['size_id'];?>" class="removeFromCart"><img border="0" src="<?php echo DIR_WS_IMAGES;?>icons/cross.gif"></a></td>
  <?php
  } else {
	  ?>
	 <td class="main" align="right" valign="middle"><a href="Javascript:void();" linkData="action=removeProduct&pID=<?php echo $order->products[$i]['id'];?>" class="removeFromCart"><img border="0" src="<?php echo DIR_WS_IMAGES;?>icons/cross.gif"></a></td>  
	  <?php
  }
	  ?>
 </tr>
<?php
 }
?>
</table></div>