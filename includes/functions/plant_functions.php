<?php
function getPricesToQty($price_column_)
{
	$qty_array = Array();
	$prices = split(',',$price_column_);
	foreach ($prices AS $price)
	{
		$qty_array[] = split(':', $price);
	}
	return $qty_array;
}

function calculatePrice($price_column_, $qty_)
{
	$qty_array = getPricesToQty($price_column_);
	$total_price = 0;
	foreach ($qty_array AS $price)
	{
		if ($qty_ >= $price[0])
			$total_price = $price[1];
	}
	return $total_price;
}
?>
