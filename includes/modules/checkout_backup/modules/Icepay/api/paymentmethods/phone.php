<?php
class Icepay_Paymentmethod_Phone extends Icepay_Basicmode {
	public		$_version		= "1.0.1";
	public		$_method		= "PHONE";
	public		$_readable_name		= "Phone (Progressbar)";
	public		$_issuer		= array(
										"PBAR" => array(
												"name" => "Phone",
												"image" => "pay-by-phone.png",
										),
									);
	public		$_amount		= array(
										"minimum" => "30",
										"maximum" => "1000000",
									);
	public		$_status		= "0";
}
?>