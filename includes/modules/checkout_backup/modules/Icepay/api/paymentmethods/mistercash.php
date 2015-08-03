<?php
class Icepay_Paymentmethod_Mistercash extends Icepay_Basicmode {
	public		$_version		= "1.0.0";
	public		$_method		= "MISTERCASH";
	public		$_readable_name		= "MisterCash";
	public		$_issuer		= array(
										"MISTERCASH" => array(
												"name" => "MISTERCASH",
												"image" => "mistercash.png",
										),
									);
	public		$_amount		= array(
										"minimum" => "200",
										"maximum" => "200000",
									);
	public		$_status		= "0";
}
?>