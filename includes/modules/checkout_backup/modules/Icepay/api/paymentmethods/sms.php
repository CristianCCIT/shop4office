<?php
class Icepay_Paymentmethod_Sms extends Icepay_Basicmode {
	public		$_version		= "1.0.1";
	public		$_method		= "SMS";
	public		$_readable_name		= "SMS Text";
	public		$_issuer		= array(
										"DEFAULT" => array(
												"name" => "SMS Text",
												"image" => "pay-by-sms.png",
										),
									);
	public		$_amount		= array(
										"minimum" => "30",
										"maximum" => "1000000",
									);
	public		$_status		= "0";
}
?>