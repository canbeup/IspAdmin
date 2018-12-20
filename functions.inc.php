<?php
require_once 'config.inc.php';


function vd ($var) {
	echo "<pre>";
	var_dump($var);
	echo "</pre>";
}


function restCall ($method, $data) {
	global $conf;
	
	if(!is_array($data)) return false;
	$json = json_encode($data);
	
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
	
	// needed for self-signed cert
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	// end of needed for self-signed cert
	
	curl_setopt($curl, CURLOPT_URL, $conf['ispconfig']['rest']['url'] . '?' . $method);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	
	$result = curl_exec($curl);
	curl_close($curl);
	
	return $result;
}


function IspGetActiveWebsites () {
	global $conf;
	$result = restCall('login', array('username' => $conf['ispconfig']['rest']['user'], 'password' => $conf['ispconfig']['rest']['password'], 'client_login' => false));
	if($result) {
		$data = json_decode($result, true);
		if(!$data) return false;
		$session_id = $data['response'];
		
		// get all actives web sites
		$result = restCall('sites_web_domain_get', array('session_id' => $session_id, 'primary_id' => ['active' => 'y']));
		if(!$result) die("error");
		// 	vd(json_decode($result, true));	exit;
		$domain_record = json_decode($result, true)['response'];
// 		echo count($domain_record) . "<br>\n";
		$res = [];
		foreach ($domain_record as $domain) {
			$res[] = $domain['domain'];
// 			echo $domain['domain']."<br>\n";
			// 		vd($domain);
		}
		
		// logout
		$result = restCall('logout', array('session_id' => $session_id));
		if(!$result) print "Could not get logout result\n";
		
		return $res;
	}
}