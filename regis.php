<?php
function request($url=null,$data=null,$metod=null,$token=null,$uuid=null,$gptoken=null,$otp_pin=null,$pin=null){
$header[] = "Host: api.gojekapi.com";
$header[] = "User-Agent: okhttp/3.10.0";
$header[] = "Accept: application/json";
$header[] = "Accept-Language: en-ID";
$header[] = "Content-Type: application/json; charset=UTF-8";
$header[] = "X-AppVersion: 3.16.1";
$header[] = "X-UniqueId: 106605982657".mt_rand(1000,9999);
$header[] = "Connection: keep-alive";    
$header[] = "X-User-Locale: en_ID";
$header[] = "X-Location: -7.613805,110.633676";
$header[] = "X-Location-Accuracy: 3.0";
if ($pin):
$header[] = "pin: $pin";    
    endif;
if ($token):
$header[] = "Authorization: Bearer $token";
endif;
if ($uuid):
$header[] = "User-uuid: $uuid";
endif;
if ($gptoken):
$header[] = "GPToken: $gptoken";    
   endif;
   if ($otp_pin):
$header[] = "otp: $otp_pin";    
   endif;
	$c = curl_init("https://api.gojekapi.com".$url);
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($c, CURLOPT_CUSTOMREQUEST, $metod);
    if ($data):
    curl_setopt($c, CURLOPT_POSTFIELDS, $data);
    curl_setopt($c, CURLOPT_POST, true);
    endif;
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_HEADER, true);
    curl_setopt($c, CURLOPT_HTTPHEADER, $header);
    $response = curl_exec($c);
    $httpcode = curl_getinfo($c);
	return $response;
}
function gen_nama($nama){
$c = curl_init("https://randomuser.me/api/?inc=name&nat=us");
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($c, CURLOPT_MAXREDIRS, 15);
	curl_setopt($c, CURLOPT_TIMEOUT, 30);
	curl_setopt($c, CURLOPT_ENCODING, "");
	curl_setopt($c, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($c, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_HEADER, true);
    $response = curl_exec($c);
	if ($nama=="f"):
	$f=get_between($response, '"first":"', '"');
	return $f;
	endif;
	if ($nama=="l"):
	$l=get_between($response, '"last":"', '"');
	return $l;
	endif;
}
function get_between($string, $start, $end) 
	{
	    $string = " ".$string;
	    $ini = strpos($string,$start);
	    if ($ini == 0) return "";
	    $ini += strlen($start);
	    $len = strpos($string,$end,$ini) - $ini;
	    return substr($string,$ini,$len);
	}
	
function register($nomer)
	{
	$nama = gen_nama("f").' '.gen_nama("l");
	$domain=array("tempmail.win","spambox.xyz");
	$email=str_replace(' ', '', strtolower($nama.''.mt_rand(0,999).'@'.$domain[mt_rand(0,1)]));
	$data = '{"email":"'.$email.'","name":"'.$nama.'","phone":"+'.$nomer.'","signed_up_country":"ID"}';
	$register = request("/v5/customers", $data,"POST");
	$token=get_between($register,'"otp_token":"','","');
	return array('nama' => $nama, 'token' => $token,'email' => $email);
	}
	
function verif($otp, $otp_token)
	{
	$data = '{"client_name":"gojek:cons:android","client_secret":"83415d06-ec4e-11e6-a41b-6c40088ab51e","data":{"otp":"'.$otp.'","otp_token":"'.$otp_token.'"}}';
	$verif = request("/v5/customers/phone/verify", $data,"POST");
	$uuid=get_between($verif,'"resource_owner_id":',',"');
	$token=get_between($verif,'"access_token":"','","');
	return array('uuid' => $uuid, 'token' => $token);
	}
function claim($token,$kodevoucher)
	{
	$data = '{"promo_code":"'.$kodevoucher.'"}';
	$claim = request("/go-promotions/v1/promotions/enrollments",$data,"POST",$token);
	$status=get_between($claim,'{"message":"','","');
	if($status=="Your promo is now ready to use! Use it now, shall we?"){
		return "SUKSES CLAIM BOS";
	}
	else{
	return "GAGAL CLAIM BOS";
	}
	}
function ganti_nomor($nomer,$email,$nama,$token,$uuid)
	{
	$data = '{"email":"'.$email.'","name":"'.$nama.'","phone":"+'.$nomer.'"}';
	$ganti = request("/v4/customers", $data,"PATCH",$token,$uuid);
	$gptoken=get_between($ganti,'GPToken: ','ETag:');
	return str_replace("\n", '', $gptoken);
	}
	
function verif_ganti($nomer,$uuid,$otp_ganti,$token,$gptoken)
	{
	$data = '{"id":'.$uuid.',"phone":"+'.$nomer.'","verificationCode":"'.$otp_ganti.'"}';
	$verif_ganti = request("/v4/customer/verificationUpdateProfile", $data,"POST",$token,$uuid,$gptoken);
	$status=get_between($verif_ganti,'"message":"','"}');
	return $status;
	}

function set_pin($uuid,$token)
	{
	$data = '{"pin":"100798"}';
	$set_pin = request("/wallet/pin", $data,"POST",$token,$uuid);
	$status=get_between($set_pin,'"message_title":"','","');
	return $status;
	}
function verif_pin($uuid,$token,$otp_pin)
	{
	$data = '{"pin":"100798"}';
	$verif_pin = request("/wallet/pin", $data,"POST",$token,$uuid,"",$otp_pin);
	$status=get_between($verif_pin,'{"success":',',"');
	return $status;
	}
	
function ganti_nomor2($email,$nama,$nomer,$token,$uuid)
	{
	$data = '{"email":"'.$email.'","name":"'.$nama.'","phone":"+'.$nomer.'"}';
	$ganti1 = request("/v4/customers", $data,"PATCH",$token,$uuid);
	$ganti2 = request("/v4/customers", $data,"PATCH",$token,$uuid,"","","100798");
	$gptoken=get_between($ganti2,'GPToken: ','ETag:');
	return str_replace("\n", '', $gptoken);
	}
function verif_ganti2($uuid,$nomer,$otp_ganti2,$token,$gptoken)
	{
	$data = '{"id":'.$uuid.',"phone":"+'.$nomer.'","verificationCode":"'.$otp_ganti2.'"}';
	$verif_ganti = request("/v4/customer/verificationUpdateProfile", $data,"POST",$token,$uuid,$gptoken);
	$status=get_between($verif_ganti,'"message":"','"}');
	return $status;
	}
$nomer2='NOHP';
$kodevoucher="COBAINGOJEK";
echo "MASUKAN NOMER REGIS = ";
$nomer=trim(fgets(STDIN));
$register=register($nomer);
if($register['token']==!null){
	echo "MASUKAN OTP REGIS = ";
	$otp=trim(fgets(STDIN));
	$verif = verif($otp,$register['token']);
	if ($verif['token']==!null){
		echo "SUKSES REGIS\n";
		$claim=claim($verif['token'],$kodevoucher);
		echo $claim."\n";
		$ganti=ganti_nomor($nomer2,$register['email'],$register['nama'],$verif['token'],$verif['uuid']);
		if($ganti==!null){
			echo "Masukan OTP GANTI NOMER = ";
			$otp_ganti=trim(fgets(STDIN));
			$verif_ganti=verif_ganti($nomer2,$verif['uuid'],$otp_ganti,$verif['token'],$ganti);
			if($verif_ganti=="OK"){
				$set_pin=set_pin($verif['uuid'],$verif['token']);
				if($set_pin=="Enter OTP"){
					echo "Masukan OTP SET PIN = ";
					$otp_pin=trim(fgets(STDIN));
					$verif_pin=verif_pin($verif['uuid'],$verif['token'],$otp_pin);
					if($verif_pin=="true"){
						$ganti2=ganti_nomor2($register['email'],$register['nama'],$nomer,$verif['token'],$verif['uuid']);
						if($ganti2==!null){
							echo "Masukan OTP GANTI NOMER 2 = ";
							$otp_ganti2=trim(fgets(STDIN));
							$verif_ganti2=verif_ganti2($verif['uuid'],$nomer,$otp_ganti2,$verif['token'],$ganti2);
							if($verif_ganti2=="OK"){
								echo "TUNTAS BOS\n";
								$simpan =fopen('result_TOKENBARRER.txt', 'a');
								fwrite($simpan, $verif['token']."|UUID= ".$verif['uuid']."\n");
							}
							else{
								echo "OTP SALAH";
							}
						}
						else{
							echo "GAGAL GANTI NOMOR KEMBALI"; 
						}
						
					}
					else{
						echo "OTP SETPIN SALAH";
					}
					
				}
				else{
					echo "GAGAL SETPIN";
				}
				
			}
			else{
				echo "GAGAL GANTI NOMER";
			}
			
		}
		else{
			echo "GAGAL GANTI NOMER HP\n";
		}
	}
	else{
		echo "GAGAL OTP SALAH\n";
	}
}
else{
	echo "\nGAGAL";
}
