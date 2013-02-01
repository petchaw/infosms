<?php
include_once("translio.php");
include_once("currency_converter.php");
include_once("weather.php");

$prefix = substr($_REQUEST['Body'], 0, 2);
$text = substr($_REQUEST['Body'], 2);

if ($prefix == "t " || $prefix == "T ")
	translate($text);
else if ($prefix == "c " || $prefix == "C ")
	currency($text);
else if ($prefix == "w " || $prefix == "W ")
	weather($text);
else{
	header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo "<Response>";
    echo "<Sms>";
    echo "Invalid prefix";
    echo "</Sms>";
    echo "</Response>";
}
?>