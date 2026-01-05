<?php

/****
	en_price.php 
	Skapad av Patricio Santiago
	Copyright Kortsystem i Gislaved AB
	Version 1.0 - 2014-11-01
    Version 1.1 - 2016-03-10 (Ny rabatt grupp)
    Version 1.2 - 2017-09-05 (Grupper har blivit reviderad inför nya prislista)
****/	 

//	Att göra: #

/*
	Språk > Engelska

	Detta läser in variabler till article.php document.
	Arrays är de olika typer av mängdrabatter som Kortsystem har.
	n# är namn på rabatten.
	v# är start värdet på den specifik namn.
	Ex: 50-99 kartor är n3 position 3. Då blir v3 position 3 lika med 50.
	
	OBS! Namnen (n#) visas för kunder, men Värden (v#) används för att beräkna mängrabatt så de måste
	stämma överens annars blir det fel.

*/

// Antal i hela namn - Används för att beskriva rabattgrupper
$price_n[1] = array('1-3 pack','4-9 pack','10-24 pack','25-49 pack','50+ pack');
$price_n[2] = array('1 ea.','2 ea.','3 ea.','4 ea.','5+ ea.');
$price_n[3] = array('1-4 ea.','5-9 ea.','10-24 ea.','25-49 ea.','50+ ea.');
$price_n[4] = array('1-9 ea.','10-49 ea.','50-99 ea.','100-199 ea.','200+ ea.');
$price_n[5] = array('1-24 ea.','25-99 ea.','100-249 ea.','250-499 ea.','500+ ea.');

// Antal i endast siffror - Används för att räkna ut rabattgrupper
$price_v[1] = array('0', '3', '9', '24', '49');
$price_v[2] = array('0','1','2','3','4');
$price_v[3] = array('0', '4', '9', '24', '49');
$price_v[4] = array('0', '9','49', '99','199');
$price_v[5] = array('0','24','99','249','499');

// Antal benämning före - ex, Amount of pack: 10
$amount_n[1] = 'Amount of pack';
$amount_n[2] = 'Amount of each';
$amount_n[3] = 'Amount of each';
$amount_n[4] = 'Amount of each';
$amount_n[5] = 'Amount of each';

// Antal benämning efter - ex, 10 packs.
$amount_c[1] = 'packs';
$amount_c[2] = 'pcs';
$amount_c[3] = 'pcs';
$amount_c[4] = 'pcs';
$amount_c[5] = 'pcs';

?>