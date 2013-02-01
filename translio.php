<?php
include_once("Translator.php");
include_once("translatorlib.php");

function translate($msg){


    try {
        //Client ID of the application.
        $clientID       = "";                       // Put you clientID (given from Bing Translator API)
        //Client Secret key of the application.
        $clientSecret = "";                         // Put your secret code here (given from Bing Translator API)
        //OAuth Url.
        $authUrl      = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";
        //Application Scope Url
        $scopeUrl     = "http://api.microsofttranslator.com";
        //Application grant type
        $grantType    = "client_credentials";

        //Create the AccessTokenAuthentication object.
        $authObj      = new AccessTokenAuthentication();
        //Get the Access token.
        $accessToken  = $authObj->getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl);
        //Create the authorization Header string.
        $authHeader = "Authorization: Bearer ". $accessToken;

        //Set the params.//
        $twilio = explode(":", $msg);

        $fromLanguage = $twilio[0];
        $toLanguage   = $twilio[1];
        $inputStr     = $twilio[2];
        $contentType  = 'text/plain';
        $category     = 'general';
        
        $params = "text=".urlencode($inputStr)."&to=".$toLanguage."&from=".$fromLanguage;
        $translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/Translate?$params";
        //Create the Translator Object.
        $translatorObj = new HTTPTranslator();
        
        //Get the curlResponse.
        $curlResponse = $translatorObj->curlRequest($translateUrl, $authHeader);
        
        //Interprets a string of XML into an object.
        $xmlObj = simplexml_load_string($curlResponse);
        foreach((array)$xmlObj[0] as $val){
            $translatedStr = $val;
        }

        header("content-type: text/xml");
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<Response>";
        echo "<Sms>";
        echo $translatedStr;
        echo "</Sms>";
        echo "</Response>";
    }
    catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . PHP_EOL;
    }
}
?>