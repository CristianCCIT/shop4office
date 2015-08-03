<?php
class Icepay_Paymentmethod_Paypal extends Icepay_Basicmode {
	public		$_version		= "1.0.1";
	public		$_method		= "PAYPAL";
	public		$_readable_name		= "PayPal";
	public		$_issuer		= array(
										"DEFAULT" => array(
												"name" => "Paypal",
												"image" => "paypal.png",
										),
									);
	public		$_amount		= array(
										"minimum" => "30",
										"maximum" => "1000000",
									);
	public		$_status		= "0";
}
?>