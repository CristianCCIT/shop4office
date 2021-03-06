<?php
class Icepay_Paymentmethod_Wire extends Icepay_Basicmode {
	public		$_version		= "1.0.1";
	public		$_method		= "WIRE";
	public		$_readable_name		= "Wire Transfer";
	public		$_issuer		= array(
										"DEFAULT" => array(
												"name" => "Wire Transfer",
												"image" => "wire-transfer.png",
										),
									);
	public		$_amount		= array(
										"minimum" => "30",
										"maximum" => "1000000",
									);
	public		$_status		= "0";
}
?>