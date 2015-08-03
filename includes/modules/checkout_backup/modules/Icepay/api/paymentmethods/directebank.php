<?php
class Icepay_Paymentmethod_Directebank extends Icepay_Basicmode {
	public		$_version		= "1.0.1";
	public		$_method		= "DIRECTEBANK";
	public		$_readable_name		= "Sofort banking";
	public		$_issuer		= array(
										"DEFAULT" => array(
												"name" => "sofortbanking",
												"image" => "sofortbanking.png",
										),
									);
	public		$_amount		= array(
										"minimum" => "30",
										"maximum" => "1000000",
									);
	public		$_status		= "0";
}
?>