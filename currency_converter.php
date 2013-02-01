<?
function get_response() {
	//Cached file with rates
	$local_currency_file = "rates.xml";
	//Interval of redownloading
	$redownloading_interval = 60 * 60;
	//Max sms size, more than this number of symbols - and the user will receive nothing
	$max_sms_size = 160;
	//Everything in the source data is calculcated relatively to this currency
	$reference_currency = "EUR";

	if (file_exists($local_currency_file) && (time() - filemtime($local_currency_file)) < $redownloading_interval) {
		$currency_data = file_get_contents($local_currency_file);
	} else {
		$currency_data = file_get_contents("http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml");
		if (!$currency_data) {
			//Couldn't download the new file, let's see if we can work with the old one
			$currency_data = file_get_contents($local_currency_file);
			if (!$currency_data) return "Sorry, I don't have the rates";
		} else {
			file_put_contents($local_currency_file, $currency_data, LOCK_EX);
		}
	}

	$currency_xml = simplexml_load_string($currency_data);
	$rates = array();
	foreach ($currency_xml->Cube->Cube->Cube as $item) {
		$rates[(string) $item['currency']] = (string) $item['rate'];
	}

	if (count($rates) == 0) return "Sorry, but I can't read the values for rates right now";

	function filter($currency) {
		$regex = "/\b".$currency."\b/i";
		return preg_match($regex, $_REQUEST['Body']);
	}
	$filtered_rates = array_flip(array_filter(array_flip($rates), "filter"));

	$report_reference_currency = preg_match("/\b".$reference_currency."\b/i", $_REQUEST['Body']);
	if ($report_reference_currency) $filtered_rates[$reference_currency] = "1.00";

	if (count($filtered_rates) < 2) {
		$response = "You should name at least two supported currencies";
		if (count($filtered_rates) == 0) {
			$response .= ", but you named none";
		} else {
			reset($filtered_rates);
			$response .= ", but I see only one: ".key($filtered_rates);
		}
		return $response;
	} else {
		if ($report_reference_currency) {
			$new_reference_currency = $reference_currency;
			unset($filtered_rates[$new_reference_currency]);
		} else {
			global $new_reference_currency_original_rate;
			$new_reference_currency_original_rate = reset($filtered_rates);
			$new_reference_currency = key($filtered_rates);
			unset($filtered_rates[$new_reference_currency]);

			function recalculate_rate($old_rate) {
				global $new_reference_currency_original_rate;
				return bcdiv($old_rate, $new_reference_currency_original_rate, 5);
			}
			$filtered_rates = array_map("recalculate_rate", $filtered_rates);
		}

		$delimiter = ", ";
		$response_builder = "";
		foreach ($filtered_rates as $currency => $value) {
			$response_builder .= "1 ".$new_reference_currency." = ".$value." ".$currency.$delimiter;
		}
		$response = substr($response_builder, 0, -strlen($delimiter));
		if (strlen($response) > $max_sms_size) return "I can't report so many rates, they don't fit in the message";
		return $response;
	}
}


function currency(){
	$sms = get_response();
	header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo "<Response>";
    echo "<Sms>";
    echo $sms;
    echo "</Sms>";
    echo "</Response>";
}
?>