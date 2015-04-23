<?php if ( ! defined( 'ABSPATH' ) ) exit;

class EFWHelper {
	const OPTION_NAME = 'ezihosting_freight_config';

	public static function assets_version() {
		return '0.0.3';
	}

	public static function plugin_url() {
		$file = str_replace('/includes', '', __FILE__);
		return untrailingslashit( plugins_url( '/', $file ) );
	}

	public static function get_config() {

		if($config = get_option(self::OPTION_NAME)) {
			return json_decode($config);
		} else {
			$config = self::get_default_config();
			add_option(self::OPTION_NAME, json_encode($config), '', 'no');
			return (Object) $config;
		}
	}

	public static function set_config($config_data) {
		$default_config = self::get_default_config();

		$old_config = self::get_config();
		if($config_data['efw_license_key'] != $old_config->efw_license_key)
			delete_transient('efw_cached_local_key');

		$update = array();
		foreach($default_config as $k=>$v) {
			$update[$k] = $config_data[$k];
		}

		update_option(self::OPTION_NAME, json_encode($update));
	}

	private static function get_default_config() {
		return array(
			'efw_enable_tb' => false,
			'efw_show_tb' => false,
			'efw_debug' => false,
			'efw_method_title' => 'Woo Aus EZi Freight',
			'efw_ship_to_countries' => 'all',
			'efw_countries' => array(),
			'efw_pickup_postcode' => '',
			'efw_pickup_city' => '',
			'efw_packing_method' => 'all',
			'efw_tax_rate' => 10,
			'efw_rate_offer' => 'all',
			'efw_receipts' => false,
			'efw_packaging_days' => 1,
			'efw_handling_cost' => 0,
			'efw_services_limit' => 0,
			'efw_license_key' => '',
			'efw_interparcel_username' => '',
			'efw_interparcel_password' => '',
		);
	}

	public static function get_au_rates($packages, $config, $destination) {

		if(!$config->efw_interparcel_username || !$config->efw_interparcel_password)
			throw new EFWException('Interparcel username and password is required, please register at <a href="http://www.interparcel.com.au/">Interparcel</a> ');

		if(!$config->efw_pickup_city || !$config->efw_pickup_postcode)
			throw new EFWException('the Origin pickup city and postcode is required.');

		if(!$destination['postcode'])
			throw new EFWException('the postcode is required.');

		if(strtolower($destination['country']) == 'au' && !$destination['city']) {
			$city = self::getCityByPostcode($destination['postcode'], $destination['country']);
		} else {
			$city =  $destination['city'];
		}

		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= "<Request>";
		$xml .= "<Authentication>";
		$xml .= "<UserID>{$config->efw_interparcel_username}</UserID>";
		$xml .= "<Password>{$config->efw_interparcel_password}</Password>";
		$xml .= "<Version>1.0</Version>";
		$xml .= "</Authentication>";
		$xml .= "<RequestType>Rates</RequestType>";
		$xml .= "<ShowAvailability>N</ShowAvailability>";
		$xml .= "<Shipment>";
		$xml .= "<Collection>";
		$xml .= "<City>{$config->efw_pickup_city}</City>";
		$xml .= "<Country>AU</Country>";
		$xml .= "<PostCode>{$config->efw_pickup_postcode}</PostCode>";
		$xml .= "</Collection>";
		$xml .= "<Delivery>";
		$xml .= "<City>".$city."</City>";
		$xml .= "<Country>".$destination['country']."</Country>";
		$xml .= "<PostCode>".$destination['postcode']."</PostCode>";
		$xml .= "</Delivery>";

		foreach($packages as $p) {
			$xml .= "<Package>";
			$xml .= "<Weight>". ceil($p->weight) ."</Weight>";
			$xml .= "<Length>". ceil($p->length) ."</Length>";
			$xml .= "<Width>". ceil($p->width) ."</Width>";
			$xml .= "<Height>". ceil($p->height) ."</Height>";
			$xml .= "</Package>";
		}

		$xml .= "</Shipment>";
		$xml .= "</Request>";


		$url = 'https://www.interparcel.com.au/api/xml/rates.php';
		$header[] = "Content-type: text/xml";//定义content-type为xml
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt ($ch, CURLOPT_CAINFO, EFW_PLUGIN_DIR."/includes/cacert.pem");
		curl_setopt($ch, CURLOPT_SSLVERSION, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$response = curl_exec($ch);
		if(curl_errno($ch))
		{
			throw new EFWException(curl_error($ch));
		}
		curl_close($ch);

		return array(
			'original' => $response,
			'rates' => self::parseXml($response),
		);
	}

	private static function getCityByPostcode($post_code, $country) {
		if(!$country)
			return '';

		$cities = json_decode(file_get_contents(EFW_PLUGIN_DIR."/includes/au.postcode"));

		if(!$cities->$post_code)
			throw new EFWException('get city by postcode failed.');

		return $cities->$post_code;
	}


	private function parseXml($string) {
		$xml = simplexml_load_string($string);

		if($xml->Status != 'OK')
			throw new EFWException((string) $xml->StatusMessage);

		$rates = array();
		foreach($xml->Rates->children() as $arr) {
			$rates[] = array(
				'name' => (string) $arr->Name,
				'carrier' => (string) $arr->Carrier,
				'price' => (float) $arr->Price,
				'tax' => (float) $arr->Tax,
				'total' => (float) $arr->Total,
				'transit_cover' => (float) $arr->TransitCover,
			);
		}

		return $rates;
	}


	public static function validLicense($licensekey) {
		if(!$licensekey)
			return 'license key can not be blank';

		$localkey  = get_transient( 'efw_cached_local_key' );
		$check_result = self::checkLicense($licensekey, $localkey);

		if($check_result['status'] == 'Active') {
			if($check_result['localkey'])
				set_transient( 'efw_cached_local_key', $check_result['localkey'], 86400 * 7 );

			return true;
		} else {
			return $check_result['message'];
		}
	}

	private  static function checkLicense($licensekey, $localkey= '') {

		$whmcsurl = 'http://www.ezihosting.com/billing/';
		$licensing_secret_key = 'S3*7^hT$lseGY$@s(*7S';
		$localkeydays = 1;
		$allowcheckfaildays = 0;


		$check_token = time() . md5(mt_rand(1000000000, 9999999999) . $licensekey);
		$checkdate = date("Ymd");
		$domain = $_SERVER['SERVER_NAME'];
		$usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
		$dirpath = dirname(__FILE__);
		$verifyfilepath = 'modules/servers/licensing/verify.php';
		$localkeyvalid = false;
		if ($localkey) {
			$localkey = str_replace("\n", '', $localkey); # Remove the line breaks
			$localdata = substr($localkey, 0, strlen($localkey) - 32); # Extract License Data
			$md5hash = substr($localkey, strlen($localkey) - 32); # Extract MD5 Hash
			if ($md5hash == md5($localdata . $licensing_secret_key)) {
				$localdata = strrev($localdata); # Reverse the string
				$md5hash = substr($localdata, 0, 32); # Extract MD5 Hash
				$localdata = substr($localdata, 32); # Extract License Data
				$localdata = base64_decode($localdata);
				$localkeyresults = unserialize($localdata);
				$originalcheckdate = $localkeyresults['checkdate'];
				if ($md5hash == md5($originalcheckdate . $licensing_secret_key)) {
					$localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $localkeydays, date("Y")));
					if ($originalcheckdate > $localexpiry) {
						$localkeyvalid = true;
						$results = $localkeyresults;
						$validdomains = explode(',', $results['validdomain']);
						if (!in_array($_SERVER['SERVER_NAME'], $validdomains)) {
							$localkeyvalid = false;
							$localkeyresults['status'] = "Invalid";
							$results = array();
						}
						$validips = explode(',', $results['validip']);
						if (!in_array($usersip, $validips)) {
							$localkeyvalid = false;
							$localkeyresults['status'] = "Invalid";
							$results = array();
						}
						$validdirs = explode(',', $results['validdirectory']);
						if (!in_array($dirpath, $validdirs)) {
							$localkeyvalid = false;
							$localkeyresults['status'] = "Invalid";
							$results = array();
						}
					}
				}
			}
		}
		if (!$localkeyvalid) {
			$responseCode = 0;
			$postfields = array(
				'licensekey' => $licensekey,
				'domain' => $domain,
				'ip' => $usersip,
				'dir' => $dirpath,
			);
			if ($check_token) $postfields['check_token'] = $check_token;
			$query_string = '';
			foreach ($postfields AS $k=>$v) {
				$query_string .= $k.'='.urlencode($v).'&';
			}
			if (function_exists('curl_exec')) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $whmcsurl . $verifyfilepath);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$data = curl_exec($ch);
				$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
			} else {
				$responseCodePattern = '/^HTTP\/\d+\.\d+\s+(\d+)/';
				$fp = @fsockopen($whmcsurl, 80, $errno, $errstr, 5);
				if ($fp) {
					$newlinefeed = "\r\n";
					$header = "POST ".$whmcsurl . $verifyfilepath . " HTTP/1.0" . $newlinefeed;
					$header .= "Host: ".$whmcsurl . $newlinefeed;
					$header .= "Content-type: application/x-www-form-urlencoded" . $newlinefeed;
					$header .= "Content-length: ".@strlen($query_string) . $newlinefeed;
					$header .= "Connection: close" . $newlinefeed . $newlinefeed;
					$header .= $query_string;
					$data = $line = '';
					@stream_set_timeout($fp, 20);
					@fputs($fp, $header);
					$status = @socket_get_status($fp);
					while (!@feof($fp)&&$status) {
						$line = @fgets($fp, 1024);
						$patternMatches = array();
						if (!$responseCode
							&& preg_match($responseCodePattern, trim($line), $patternMatches)
						) {
							$responseCode = (empty($patternMatches[1])) ? 0 : $patternMatches[1];
						}
						$data .= $line;
						$status = @socket_get_status($fp);
					}
					@fclose ($fp);
				}
			}
			if ($responseCode != 200) {
				$localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - ($localkeydays + $allowcheckfaildays), date("Y")));
				if ($originalcheckdate > $localexpiry) {
					$results = $localkeyresults;
				} else {
					$results = array();
					$results['status'] = "Invalid";
					$results['description'] = "Remote Check Failed";
					return $results;
				}
			} else {
				preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches);
				$results = array();
				foreach ($matches[1] AS $k=>$v) {
					$results[$v] = $matches[2][$k];
				}
			}
			if (!is_array($results)) {
				die("Invalid License Server Response");
			}
			if ($results['md5hash']) {
				if ($results['md5hash'] != md5($licensing_secret_key . $check_token)) {
					$results['status'] = "Invalid";
					$results['description'] = "MD5 Checksum Verification Failed";
					return $results;
				}
			}
			if ($results['status'] == "Active") {
				$results['checkdate'] = $checkdate;
				$data_encoded = serialize($results);
				$data_encoded = base64_encode($data_encoded);
				$data_encoded = md5($checkdate . $licensing_secret_key) . $data_encoded;
				$data_encoded = strrev($data_encoded);
				$data_encoded = $data_encoded . md5($data_encoded . $licensing_secret_key);
				$data_encoded = wordwrap($data_encoded, 80, "\n", true);
				$results['localkey'] = $data_encoded;
			}
			$results['remotecheck'] = true;
		}
		unset($postfields,$data,$matches,$whmcsurl,$licensing_secret_key,$checkdate,$usersip,$localkeydays,$allowcheckfaildays,$md5hash);
		return $results;
	}
}

class EFWException extends Exception{}