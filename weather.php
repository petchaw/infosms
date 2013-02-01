<?php

function getWeather ($location) {
	$numOfDays = 2;
	$APIkey = "";						// Put you API key here
	
	$url = "http://free.worldweatheronline.com/feed/weather.ashx?key=" . $APIkey . "&q=" . $location . "&num_of_days=2&format=json";

	
	$json_string = file_get_contents($url);

	$json = json_decode($json_string);

	
	return "Now: " . $json->data->current_condition[0]->weatherDesc[0]->value . ", Temp: " . $json->data->current_condition[0]->temp_F . "&#186;F, Tomorrow: " . $json->data->weather[1]->weatherDesc[0]->value . ", Tempe: " . $json->data->weather[1]->tempMinF . "&#186;F to " . $json->data->weather[1]->tempMaxF . "&#186;F";	

}	//	fuction getWeather



function weather($body){
	$pieces = explode(",", $body);
		
	if ((sizeof($pieces) == 1) && (preg_match("#[0-9]{5}#", $pieces[0]) ))
	{
		$msg = getWeather($pieces[0]);
	}
	else if (sizeof($pieces) == 2)
	{
		$pieces[0] = str_replace(" ", "+", $pieces[0]);
		$pieces[1] = str_replace(" ", "+", $pieces[1]);
		$msg = getWeather($pieces[0] . "," . $pieces[1]);
	}
	else {
		$msg = "Please use correct structure: 'w zipcode' or 'w city,state'";
	}

	header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo "<Response>";
    echo "<Sms>";
    echo $msg;
    echo "</Sms>";
    echo "</Response>";
}
?>