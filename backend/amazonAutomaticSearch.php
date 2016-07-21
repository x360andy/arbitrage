<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Arbitrage</title>
    <link rel="stylesheet" href="../style/backend.css">
</head>
<body>
<div style="padding:10px">
    <table>
        <thead style='background-color:#A1FF8F;'>
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
include ("../api/include.php");
include ("../etc/database.php");
			
$pages = 5;
$liste = file("../keywords/" .  ($_GET["list"]) . ".txt");

for ($y = 0; $y < count($liste); $y++) {
	$obj = new AmazonProductAPI();
	$serach_PDO = createPDO();
	for ($p = 1; $p <= $pages; $p++) {
		$result = $obj->searchProducts($liste[$y], $p);
		for ($x = 0; $x < count($result->Items->Item); $x++) {
			$listenpreis = $result->Items->Item[$x]->ItemAttributes->ListPrice->Amount / 100;
			if (($result->Items->Item[$x]->Offers->Offer->OfferListing->IsEligibleForPrime == "1") && ($listenpreis > 0) && ($result->Items->Item[$x]->Offers->Offer->OfferListing->AvailabilityAttributes->AvailabilityType == "now")) {
				$asin = $result->Items->Item[$x]->ASIN;
				$ean = $result->Items->Item[$x]->ItemAttributes->EAN;
				$niedrigpreis = $result->Items->Item[$x]->Offers->Offer->OfferListing->Price->Amount / 100;
				$ebayPreis = getDurchschnitt($ean);
				$noEbayPreis = false;
				if ($ebayPreis > - 0.1) {
					$vergleichspreis = $ebayPreis;
					$unterschied = $vergleichspreis - $niedrigpreis;

					// Paypal ?

					$paypal = ($vergleichspreis / 100 * 1.9) + 0.35;

					// EBay

					$ebay = ($vergleichspreis / 100 * 10) + 0.35;
					if ($ebay >= 200) {
						$ebay = 199.99;
					}

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
					// 	$serach_PDO->query("INSERT INTO amazon_offers  (`id` ,`titel` ,`gruppe` ,`listenpreis` ,`ebayAvgPreis` ,`amazonPreis` ,`gebuehr` ,`gewinn`) VALUES ( NULL,'$title','".$_GET['list']."','$listenpreis','$ebayPreis','$niedrigpreis','".($paypal + $ebay)."','$gewinn');");
					}
				}
			}
		}
	}
}
?>
        </tbody>
    </table>
</div>
</body>
</html>