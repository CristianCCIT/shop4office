<?php
class Icepay_Paymentmethod_Ideal extends Icepay_Basicmode {
	public		$_version		= "1.0.1";
	public		$_method		= "IDEAL";
	public		$_readable_name		= "iDEAL";
	public		$_issuer		= array(
										"ABNAMRO" => array(
												"name" => "ABNAMRO",
												"image" => "ideal.png",
										),
										"ASNBANK" => array(
												"name" => "ASNBANK",
												"image" => "ideal.png",
										),
										"FRIESLAND" => array(
												"name" => "FRIESLAND",
												"image" => "ideal.png",
										),
										"ING" => array(
												"name" => "ING",
												"image" => "ideal.png",
										),
										"RABOBANK" => array(
												"name" => "RABOBANK",
												"image" => "ideal.png",
										),
										"SNSBANK" => array(
												"name" => "SNSBANK",
												"image" => "ideal.png",
										),
										"SNSREGIOBANK" => array(
												"name" => "SNSREGIOBANK",
												"image" => "ideal.png",
										),
										"TRIODOSBANK" => array(
												"name" => "TRIODOSBANK",
												"image" => "ideal.png",
										),
										"VANLANSCHOT" => array(
												"name" => "VANLANSCHOT",
												"image" => "ideal.png",
										),
									);
	public		$_amount		= array(
										"minimum" => "30",
										"maximum" => "1000000",
									);
	public		$_status		= "0";
}
?>