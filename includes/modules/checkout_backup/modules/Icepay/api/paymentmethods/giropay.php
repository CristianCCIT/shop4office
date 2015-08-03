<?php
class Icepay_Paymentmethod_Giropay extends Icepay_Basicmode {
	public		$_version		= "1.0.1";
	public		$_method		= "GIROPAY";
	public		$_readable_name		= "Giropay";
	public		$_issuer		= array(
										"DEFAULT" => array(
												"name" => "giropay",
												"image" => "giropay.png",
										),
									);
	public		$_amount		= array(
										"minimum" => "30",
										"maximum" => "1000000",
									);
	public		$_status		= "0";
}
?>