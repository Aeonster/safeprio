<?php

/****
	directory.php 
	Skapad av Patricio Santiago
	Copyright Kortsystem i Gislaved AB
	Version 1.0 - 2014-11-01
****/	 

//	Att göra: #


/*
	Adresser - Sökvägar till alla sidor inom webbplatsen.
	
	Meningen är att samla alla sökvägar på denna sida och inte 
	på webbsidorna för att undvika framtida problem. Som när
	man ska ändra en sökväg behövs det inte gå igenom alla sidor.
	
	$pg_ är prefixen för sökväg som används på sidorna. Ordet som följer
	är det engelska benämning för sökvägen, ska vara samma namn som fil.
	Börja alltid med / som anger at starta vid rooten av webbplatsen.
	
	"$pg_namn" är en global variabel och får INTE ändras annars orsakar
	man en syntax error och allt slutar att fungera.
	Själva sökvägen anges inom	' ' (taggarna).
*/

	// Sökvägar och Argument från server (dynamisk)
	$pg_urlComplete = $_SERVER['REQUEST_URI'];	//** /mapp/fil.php?variabel=argument&variab...
	$pg_urlPage = $_SERVER['SCRIPT_NAME'];		//** /mapp/fil.php
	$pg_urlGet = $_SERVER['QUERY_STRING'];		//** variabel=argument&variab...
	
	// Fasta sidor
	$pg_index = 'http://www.kortsystem.se';
		$pg_product = '/safeprio/pages/product.php';
			$pg_category = '/safeprio/pages/category.php';
				$pg_article = '/safeprio/pages/article.php';
		$pg_document = '/safeprio/pages/document.php';
		$pg_contact = '/safeprio/pages/contact.php';
		$pg_search = '/safeprio/pages/search.php';
		$pg_cart = '/safeprio/pages/cart.php';
		$pg_order = '/safeprio/pages/order.php';

		$pg_prefix = '/safeprio/';
		
	// Sidor där ingen direkt tillgång ska finnas.
	/* 
			redirect.php
		 	finish.php
		 	Alla sidor under "pages/page" mappen
		 	Alla sidor under "includes" mappen 
	*/
?>