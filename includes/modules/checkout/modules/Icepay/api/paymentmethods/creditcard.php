<?php
class Icepay_Paymentmethod_Creditcard extends Icepay_Basicmode {
	public		$_version		= "1.0.1";
	public		$_method		= "CREDITCARD";
	public		$_readable_name		= "Creditcards";
	public		$_issuer		= array(
										"AMEX" => array(
												"name" => "american-express",
												"image" => "american-express.png",
												"status" => "on",
										),
										"MASTER" => array(
												"name" => "mastercard",
												"image" => "mastercard.png",
												"status" => "on",
										),
										"VISA" => array(
												"name" => "visa",
												"image" => "visa.png",
												"status" => "on",
										),
									);
	public		$_amount		= array(
										"minimum" => "30",
										"maximum" => "1000000",
									);
	public		$_status		= "1";
}
?>