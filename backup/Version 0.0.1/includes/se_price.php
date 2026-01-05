<?php

/****
	se_price.php 
	Skapad av Patricio Santiago
	Copyright Kortsystem i Gislaved AB
	Version 1.0 - 2014-11-01
    Version 1.1 - 2016-03-10 (Ny rabatt grupp)
    Version 1.2 - 2017-09-05 (Grupper har blivit reviderad inför nya prislista)
****/	 

//	Att göra: #

/*
	Språk > Svenska

	Detta läser in variabler till article.php document.
	Arrays är de olika typer av mängdrabatter som Kortsystem har.
	n# är namn på rabatten.
	v# är start värdet - 1 på den specifik namn.
	Ex: 50-99 kartor är n3 position 3. Då blir v3 position 3 lika med 49.
	
	OBS! Namnen visas för kunder, men Värden används för att beräkna mängrabatt så de måste
	stämma överens annars blir det fel.

*/

// Antal i hela namn - Används för att beskriva rabattgrupper
$price_n[1] = array('1-3 förp.','4-9 förp.','10-24 förp.','25-49 förp.','50+ förp.');
$price_n[2] = array('1 st.','2 st.','3 st.','4 st.','5+ st.');
$price_n[3] = array('1-4 st.','5-9 st.','10-24 st.','25-49 st.','50+ st.');
$price_n[4] = array('1-9 st.','10-49 st.','50-99 st.','100-199 st.','200+ st.');
$price_n[5] = array('1-24 st.','25-99 st.','100-249 st.','250-499 st.','500+ st.');

// Antal i endast siffror - Används för att räkna ut rabattgrupper
$price_v[1] = array('0', '3', '9', '24', '49');
$price_v[2] = array('0','1','2','3','4');
$price_v[3] = array('0', '4', '9', '24', '49');
$price_v[4] = array('0', '9','49', '99','199');
$price_v[5] = array('0','24','99','249','499');

// Antal benämning före - ex, Antal förpackning: 10
$amount_n[1] = 'Antal förpackning';
$amount_n[2] = 'Antal styck';
$amount_n[3] = 'Antal styck';
$amount_n[4] = 'Antal styck';
$amount_n[5] = 'Antal styck';

// Antal benämning efter - ex, 10 förp.
$amount_c[1] = 'förp.';
$amount_c[2] = 'st.';
$amount_c[3] = 'st.';
$amount_c[4] = 'st.';
$amount_c[5] = 'st.';

?>