<?php
class Icepay_Paymentmethod_Paysafecard extends Icepay_Basicmode {
	public		$_version		= "1.0.1";
	public		$_method		= "PAYSAFECARD";
	public		$_readable_name		= "PaySafeCard";
	public		$_issuer		= array(
										"DEFAULT" => array(
												"name" => "PaySafeCard",
												"image" => "paysafecard.png",
										),
									);
	public		$_amount		= array(
										"minimum" => "30",
										"maximum" => "1000000",
									);
	public		$_status		= "0";
}
?>