<?php

/****
	currecny.php 
	Skapad av Patricio Santiago
	Copyright Kortsystem i Gislaved AB
	Version 1.0 - 2018-07-02
****/	 

//	Att göra: #

/*
	Valuta - Består av en funktion och två kontroller (fungerar som Språk)

	  -	Funktionen kontroller om en session finns för valuta redan.
        Finns ingen session ska man sätta en standard valuta (SEK).
        Finns en session/cookie ska detta valuta värde sparas i sessionen.
        Till skillnad från språk så görs ingen lokalisering, utan allt är SEK
        tills mans väljer EUR.
		
	  -	Kontroll 1 är om valuta ska ändras, sker genom en GET variable (currency).
        sek för svenska valuta och eur för euro valuta.
        Första kontrollen är om valuta vill ändras, detta sker endast om en
		GET (adressfält) variabel (currency) skickas. Är variabeln 'eur'
		så skapas en cookie och en session för eu valuta (EUR).
		Om annat står faller cookie och session till svensk valuta (SEK).
		
	  -	Andra kontrollen är om det finns en session redan så anropas funktion 
        direkt.
		Finns det ingen session kontrolleras om cookie finns. Finns det så
		skapas en session och anropar funktionen.
		Finns det ingen cookie så gäller SEK.
		En session skapas och funktionen anropas men ingen cookie skapas.
		'EUR' för EU Valuta, allt annat blir svensk krona 'SEK'.
	
	Säkerhet skapas genom att det finns 2 val, SEK och EUR. Anges inte korrekt kod 
	för EUR så blir det automatiskt SEK. Finns inga andra val.
	Så att skriva currency=whatever så blir det SEK för att det är inte EUR.
	 
	Variabler & Funktioner som används i detta script:
	$_SESSION['currency'] // Sessionen för valuta
	$_COOKIE['currency'] // Cookie för valuta
	$_GET['currency'] // Adressfält variabel för valutabyte
	$CurrencyCode // Global variabel för språk SEK/EUR
	$CurrencyPrefix // Global prefix för databas åtkomst sek_/eur_
	$Currency // Local variabel som endast används i funktion (går ej komma åt från utsidan)
	setCurrency() // Funktionen som sätter språkvariabler
	$CurrencySelect // Nytt OOP lösning för språkval i menyn.
*/

// Nollställ språk variabler
$CurrencyCode = '';
$CurrencyPrefix = '';

// Funktion som sätter språk variabler
function setCurrency($Currency)
{
	global $CurrencyCode, $CurrencyPrefix, $CurrencyHead, $CurrencyNumber, $y, $yN;
	
	// Vid EUR - Engelsk variabler används
	if($Currency == 'EUR')
	{
		$CurrencyCode = 'EUR';
		$y = $CurrencyPrefix = 'eur_';
		$CurrencyHead = 'eur';
        $yN = $CurrencyNumber = 2;
	}
	// Allt annat faller till Svensk valuta (SEK)
	else
	{
		$CurrencyCode = 'SEK';
		$y = $CurrencyPrefix = 'sek_';
		$CurrencyHead = 'sek';
        $yN = $CurrencyNumber = 1;
	}
}

// Kontrollera om valuta vill bytas
if(isset($_GET['currency']))
{
	// Om valuta ska euro
	if($_GET['currency'] == 'eur')
	{
		// Skapa cookie (1år), session
		setcookie('currency','EUR',time()+60*60*24*365);
		$_SESSION['currency'] = 'EUR';
	}
	// Allt annat blir sek
	else
	{
		// Skapa cookie (1år), session
		setcookie('currency','SEK',time()+60*60*24*365);
		$_SESSION['currency'] = 'SEK';
	}
}

// Valuta session finns
if(isset($_SESSION['currency']))
{
	// Anropa funktion med session variabel
	setCurrency($_SESSION['currency']);
}
// Valuta session finns inte
else 
{
	// Cookie finns
	if(isset($_COOKIE['currency']))
	{
		// Aktivera session och anropa funktion med cookie variabel
		$_SESSION['currency'] = $_COOKIE['currency'];
		setCurrency($_COOKIE['currency']);
	}
	// Cookie finns inte
	else
	{
        // Aktivera session och anropa funktion med SEK
		$_SESSION['currency'] = 'SEK';
		setCurrency('SEK');
		
	}
}

// Val av valuta från menyerna (eftersom samma kod används överallt är detta en OOP lösning)
if($CurrencyCode == 'EUR')
{
	$CurrencySelect = 'Byt valuta till <a href="?currency=sek">SEK</a>';
}
else
{
	$CurrencySelect = 'Change currency to <a href="?currency=eur">EUR</a>';
}
?>