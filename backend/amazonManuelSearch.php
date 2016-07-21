<?php
$keyword = $_GET["keyword"];
$pages = $_GET["pages"];
if ($_GET["wiederverkaufspreisbasis"] == "ebaypreis") { 	$markerPreisEbay = "checked"; } else {	$markerPreisListenpreis = "checked";}
include ("../api/include.php");
include ("../etc/database.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Arbitrage <?php

if ($keyword != "") {
	echo ": $keyword";
}

?></title>
    <link rel="stylesheet" href="../style/backend.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.10/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css"/>
    <script type="text/javascript" src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/s/dt/jq-2.1.4,dt-1.10.10/datatables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.10/js/dataTables.jqueryui.min.js"></script>
    <script>
        $(document).ready(function() {
					$('#table_frame').hide();
            $('#table').DataTable( {
                "order": [[ 11, "desc" ]]
            } );
				
				$('#table_frame').hide();
				$('#table_frame').css("visibility","visible");
				$('#table_frame').fadeIn(500);
				$('#table_load_info').slideUp(300);
				 } );
    </script>
</head>
<body>
<div style="padding:10px">
    <div style="padding-bottom:10px;font-size:24px;font-weight:bold;">
        <form method="get">
            <div style="height:70px;">
                <div style="float: left;">
                    <div><label class="description" for="keyword">Amazon Keyword </label></div>
                    <input id="keyword" style="font-size:20px;" name="keyword" type="text" value="<?php echo $keyword;?>" maxlength="255" value=""/></div>

                <div style="float: left;margin-left:20px;">
                    <div><label class="description" for="pages">Seiten</label></div>
                    <input id="pages" style="font-size:20px;width:70px;" name="pages" type="text" value="<?php echo $pages; ?>">
                </div>
           
                <div style="float: left;margin-left:20px;">
                    <div><label class="description" for="vergleichspreis">Wiederverkaufspreisbasis:</label></div>
										<fieldset style="font-size:16px;font-weight:none;">
										<input <?php echo $markerPreisListenpreis; ?> type="radio" id="listenpreis" name="wiederverkaufspreisbasis" value="listenpreis">
										<label for="listenpreis"> Listenpreis</label> 
										<input <?php echo $markerPreisEbay; ?> type="radio" id="ebaypreis" name="wiederverkaufspreisbasis" value="ebaypreis">
										<label for="ebaypreis"> Ebay Ø Preis</label><br /> 
									</fieldset>
                </div>
								
								</div>
								
            <input type="submit" style="font-size:20px;" value="Suchen"/>
        </form>
    </div>
<h2 id="table_load_info">Daten werden geladen</h2>		
<div id="table_frame" style="visibility:hidden;">
    <table id="table" >
        <thead>
        <td>Rank</td>
        <td>ASIN</td>
        <td>EAN</td>
        <td>BILD</td>
        <td>Titel</td>
        <td>Listenpreis</td>
        <td>Ebay Ø Preis</td>
        <td>Amazonpreis</td>
        <td>Preisunterschied</td>
        <td>Gebühren Ebay</td>
        <td>Gebühren PayPal</td>
        <td>Gewinn</td>
        </thead>
        <tbody>

        <?php
$obj = new AmazonProductAPI();
$serach_PDO = createPDO();

for ($p = 1; $p <= $pages; $p++) {
	$result = $obj->searchProducts($keyword, $p);
	for ($x = 0; $x < count($result->Items->Item); $x++) {
		$listenpreis = $result->Items->Item[$x]->ItemAttributes->ListPrice->Amount / 100;
		if (($result->Items->Item[$x]->Offers->Offer->OfferListing->IsEligibleForPrime == "1") && ($listenpreis > 0) && ($result->Items->Item[$x]->Offers->Offer->OfferListing->AvailabilityAttributes->AvailabilityType == "now")) {
			$asin = $result->Items->Item[$x]->ASIN;
			$ean = $result->Items->Item[$x]->ItemAttributes->EAN;
			$niedrigpreis = $result->Items->Item[$x]->Offers->Offer->OfferListing->Price->Amount / 100;
			$ebayPreis = getDurchschnitt($ean);
			$noEbayPreis = false;
			if ($markerPreisEbay) {
				if ($ebayPreis < - 0.9) {
					$noEbayPreis = true;
				}
				$vergleichspreis = $ebayPreis;	}	else {	$vergleichspreis = $listenpreis;
			}

			$unterschied = $vergleichspreis - $niedrigpreis;

			// Paypal ?
			$paypal = ($vergleichspreis / 100 * 1.9) + 0.35;

			// EBay
			$ebay = ($vergleichspreis / 100 * 10) + 0.35;
			if ($ebay >= 200) {	$ebay = 199.99;	}

			$gewinn = $unterschied - $paypal - $ebay;
			$title = $result->Items->Item[$x]->ItemAttributes->Title;
				if (($gewinn > 0) && ($noEbayPreis === false)) {
				echo "<tr>";
				echo "<td>{$result->Items->Item[$x]->SalesRank}</td>";
				echo "<td><a target='_blank' href='" . $result->Items->Item[$x]->DetailPageURL . "'>$asin</a></td>";
				echo "<td>$ean</td>";
				echo "<td><img src=\"" . $result->Items->Item[$x]->MediumImage->URL . "\" /></td>";
				echo "<td>" . $title . "</td>";
				echo "<td style='text-align:right;'>" . number_format($listenpreis, 2, ',', '') . "</td>";
				echo "<td style='text-align:right;'><a target='_blank' href='http://www.ebay.de/sch/i.html?&_nkw=" . $title . "&_sacat=0" . "OK" . "'>" . number_format($ebayPreis, 2, ',', '') . "</a></td>";
				echo "<td style='text-align:right;'>" . number_format($niedrigpreis, 2, ',', '') . " </td>";
				echo "<td style='text-align:right;background-color:#d3fff4'>" . number_format($unterschied, 2, ',', '') . "</td>";
				echo "<td style='text-align:right;background-color:#ffb1b9'>" . number_format($ebay, 2, ',', '') . "</td>";
				echo "<td style='text-align:right;background-color:#ffb1b9'>" . number_format($paypal, 2, ',', '') . "</td>";
				echo "<td style='text-align:right;background-color:#8eff78'>" . number_format($gewinn, 2, ',', '') . "</td>";
				echo "</tr>";
			}
		}
	}
}

?>
        </tbody>
    </table>
		</div>
</div>
</body>
</html>

<script>


</script>