<?php
class Icepay_Paymentmethod_Ddebit extends Icepay_Basicmode {
	public		$_version		= "1.0.1";
	public		$_method		= "DDEBIT";
	public		$_readable_name		= "Direct Debit";
	public		$_issuer		= array(
										"INCASSO" => array(
												"name" => "direct-debit",
												"image" => "direct-debit.png",
												"status" => "on",
										),
									);
	public		$_amount		= array(
										"minimum" => "1",
										"maximum" => "200000",
									);
	public		$_status		= "1";
}
?>