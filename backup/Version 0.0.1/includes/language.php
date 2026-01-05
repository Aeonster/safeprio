<?php

/****
	language.php 
	Skapad av Patricio Santiago
	Copyright Kortsystem i Gislaved AB
	Version 1.0 - 2014-11-01
****/	 

//	Att göra: #

/*
	Språk - Består av en funktion och två kontroller

	  -	Funktionen anropas efter andra kontrollen och sätter språkkod och prefix.
		Skyddar mot injektioner genom att endast tillåta korrekt kod (SE),
		allt annat faller till standard (EN).
		
	  -	Första kontrollen är om språk vill ändras, detta sker endast om en
		GET (adressfält) variabel (language) skickas. Är variabeln 'se'
		så skapas en cookie och en session för svenska (SE).
		Om annat står faller cookie och session till engelska (EN).
		
	  -	Andra kontrollen är om det finns en session redan så anropas funktion direkt.
		Finns det ingen session kontrolleras om cookie finns. Finns det så
		skapas en session och anropar funktionen.
		Finns det ingen cookie så kontrolleras webbläsarspråket.
		En session skapas och funktionen anropas men ingen cookie skapas.
		'sv' för svenska (SE), allt annat blir engelska (EN).
	
	Säkerthet skapas genom att det finns 2 val, svenska och engelska. Anges inte korrekt kod 
	för svenska som är SE så blir det automatiskt engelska som är EN. Finns inga andra val.
	Så att skriva language=whatever så blir det engelska (EN) för att det är inte svenska (SE).
	 
	Variabler & Funktioner som används i detta script:
	$_SESSION['language'] // Sessionen för språk
	$_COOKIE['language'] // Cookie för språk
	$_GET['language'] // Adressfält variabel för språkbyte
	$LanguageCode // Global variabel för språk SE/EN
	$LanguagePrefix // Global prefix för databas åtkomst se_/en_
	$LanguageBrowser // Variabel med webbläsarens språkkod sv/en...osv
	$Language // Local variabel som endast används i funktion (går ej komma åt från utsidan)
	setLanguage() // Funktionen som sätter språkvariabler
	$LanguageSelect // Nytt OOP lösning för språkval i menyn.
*/

// Nollställ språk variabler
$LanguageCode = '';
$LanguagePrefix = '';

// Funktion som sätter språk variabler
function setLanguage($Language)
{
	global $LanguageCode, $LanguagePrefix, $LanguageHead, $LanguageNumber, $z, $zN;
	
	// Vid SE - Svenska variabler används
	if($Language == 'SE')
	{
		$LanguageCode = 'SE';
		$z = $LanguagePrefix = 'se_';
		$LanguageHead = 'sv';
        $zN = $LanguageNumber = 2;
	}
	// Allt annat faller till Engelska (EN)
	else
	{
		$LanguageCode = 'EN';
		$z = $LanguagePrefix = 'en_';
		$LanguageHead = 'en';
        $zN = $LanguageNumber = 1;
	}
}

// Kontrollera om språk vill bytas
if(isset($_GET['language']))
{
	// Om språket ska vara svenska
	if($_GET['language'] == 'se')
	{
		// Skapa cookie (1år), session
		setcookie('language','SE',time()+60*60*24*365);
		$_SESSION['language'] = 'SE';
	}
	// Allt annat blir engelska
	else
	{
		// Skapa cookie (1år), session
		setcookie('language','EN',time()+60*60*24*365);
		$_SESSION['language'] = 'EN';
	}
}

// Språk session finns
if(isset($_SESSION['language']))
{
	// Anropa funktion med session variabel
	setLanguage($_SESSION['language']);
}
// Språk session finns inte
else 
{
	// Cookie finns
	if(isset($_COOKIE['language']))
	{
		// Aktivera session och anropa funktion med cookie variabel
		$_SESSION['language'] = $_COOKIE['language'];
		setLanguage($_COOKIE['language']);
	}
	// Cookie finns inte
	else
	{
		// Frågar webbläsare vilken språk den är inställd på
		$LanguageBrowser = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

		// För svenska är variabeln 'sv'
		if($LanguageBrowser == 'sv' || $LanguageBrowser == 'da' || $LanguageBrowser == 'no' || $LanguageBrowser == 'nb')
		{
			// Aktivera session och anropa funktion med SE för svenska
			$_SESSION['language'] = 'SE';
			setLanguage('SE');
		}
		// Alla andra språk faller till EN för engelska
		else
		{
			// Aktivera session och anropa funktion med EN för engelska
			$_SESSION['language'] = 'EN';
			setLanguage('EN');
		}
	}
}

// Val av språk från menyerna (eftersom samma kod används överallt är detta en OOP lösning)
if($LanguageCode == 'SE')
{
	$LanguageSelect = 'Change language to <a href="?language=en">English</a>';
}
else
{
	$LanguageSelect = 'Byt språk till <a href="?language=se">Svenska</a>';
}
?>