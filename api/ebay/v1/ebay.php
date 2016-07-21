<?php
ini_set("allow_url_fopen", true);

// Generates an indexed URL snippet from the array of item filters
function buildURLArray ($filterarray) {
    global $urlfilter;
    global $i;
    // Iterate through each filter in the array
    foreach($filterarray as $itemfilter) {
        // Iterate through each key in the filter
        foreach ($itemfilter as $key =>$value) {
            if(is_array($value)) {
                foreach($value as $j => $content) { // Index the key for each value
                    $urlfilter .= "&itemFilter($i).$key($j)=$content";
                }
            }
            else {
                if($value != "") {
                    $urlfilter .= "&itemFilter($i).$key=$value";
                }
            }
        }
        $i++;
    }
    return "$urlfilter";
} // End of buildURLArray function


function getDurchschnitt($EAN){

// API request variables
$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';  // URL to call
$version = '1.0.0';  // API version supported by your application
$appid = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';  // Replace with your own AppID
$globalid = 'EBAY-DE';  // Global ID of the eBay site you want to search (e.g., EBAY-DE)
$safequery = urlencode($EAN);  // Make the query URL-friendly
$i = '0';  // Initialize the item filter index to 0

// Create a PHP array of the item filters you want to use in your request
$filterarray =
    array(
             array(
            'name' => 'FreeShippingOnly',
            'value' => 'false',
            'paramName' => '',
            'paramValue' => ''),

        array(
            'name' => 'ListingType',
            'value' => array('AuctionWithBIN','FixedPrice','StoreInventory'),
            'paramName' => '',
            'paramValue' => ''),
    );

// Build the indexed item filter URL snippet
$filters = buildURLArray($filterarray);

// Construct the findItemsByKeywords HTTP GET call
$apicall = "$endpoint?";
$apicall .= "OPERATION-NAME=findItemsByProduct";
$apicall .= "&SERVICE-VERSION=$version";
$apicall .= "&SECURITY-APPNAME=$appid";
$apicall .= "&GLOBAL-ID=$globalid";
$apicall .= "&paginationInput.entriesPerPage=10";
$apicall .= "&productId.@type=EAN";
$apicall .= "&productId=$safequery";
// $apicall .= "$filters";
// print($apicall); 
// Load the call and capture the document returned by eBay API
$resp = simplexml_load_file($apicall);
$summe = 0; 
$counter = 0; 
// Check to see if the request was successful, else print an error
if ($resp->ack == "Success") {
  
    // If the response was loaded, parse it and build links
    foreach($resp->searchResult->item as $item) {
 				$summe = $summe + $item->sellingStatus->currentPrice; 				
				$counter++; 

    }
		
		if ($counter!=0){
		$result = $summe/$counter;
		}else{
		$result = -1; 
		}
}
// If the response does not indicate 'Success,' print an error
else {
    $result  = -1;
}
return $result; 
}

?>