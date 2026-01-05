<?php

/****
	se_language.php 
	Skapad av Patricio Santiago
	Copyright Kortsystem i Gislaved AB
	Version 1.3 - 2016-03-10
****/	 

//	Att göra: #

/*
	Språk > Svenska
	
	$lg_ är prefixen för språk som används på sidorna. Ordet som följer
	är det som är engelska benämning för ordet. Själva språket anges inom
	'' (taggarna).
	
	OBS! Ändra INTE variabel annars slutar sidorna fungera helt.
	Man ska endast ändra det inom '' (taggarna). Exempel:	
	
	$lg_variabel = 'Kan Ändras';
	
	Regler (för snygg formatering):
	- VIKTIG! Taggen ' får man EJ skriva in direkt annars slutar allt att fungera.
	  Är man tvungen att ha med vid exempelvis 'Don't' så skriver man 'Don\'t'.
	  Alltså för att få ut ' skrivs den som \' annars skapar man ett syntax error. 
	- Sätt inga mellanrum före eller efter ordet. Annars kan det se konstigt ut.
	- Sätt inga : (kolon) efter ord, dessa sätts automatiskt. Annars blir det "Pris::"
	- Sätt inga . (punkt/er) efter ord, dessa sätts automatiskt. Exempelvis:
	  'Sök' används vid flera tillfällen, som "Sök..." och i vissa menyer utan punkter.
	  Om man skriver in 'Sök...' som kommer det bli "Sök......" på sökfältet och vissa 
	  menyer blir det "Sök..." där dessa punkter inte ska vara med.
	- Skriv in ordet så rent som det går, inga special förklaringar, parenteser eller
	  andra onödiga tecken/krumellurer.
*/

$lg_language = 'Svenska'; // Aktiva språket
$lg_ = '';

$lg_swedish = 'Svenska';
$lg_english = 'Engelska';

// Menu
$lg_search = 'Sök';
$lg_product = 'Produkter';
$lg_document = 'Dokument';
$lg_contact = 'Kontakt';

$lg_cart = 'Kundvagn';
$lg_empty = 'Tomt';
$lg_items = 'varor';
$lg_order = 'Beställning';

$lg_home = 'Hem';
$lg_menu = 'Meny';
$lg_back = 'Tillbaka';

// Foten
$lg_copyright = '&copy; Kortsystem i Gislaved AB';
$lg_cookies = 'Information om cookies';
$lg_contactMessage = '<span>Telefon: <b>+46 371-222 444</b></span><i> | </i><span>E-post: <b>info@kortsystem.se</b></span>';
$lg_cookiesMessage = 'Vi använder cookies av tekniska skäl för att underlätta besökarens aktiviteter på Kortsystem.se. \nCookies används för bl.a. komma ihåg vald språk, valuta samt mäta trafiken på vår webbplats. \nInformationen som samlas in är anonym och innehåller inga personliga uppgifter såsom namn eller e-post. \nInformation om besökaren kan inte spåras av Kortsystem.';


// Kategori
$lg_next = 'Välj produkt';

// Artikel
$lg_price = 'Pris för';
$lg_total = 'Totala belopp';
$lg_discount = 'Mängdrabatt';
$lg_addCart = 'Lägg till kundvagn';
$lg_disableCart = 'Kontakta oss';  // "Ej tillgängligt" innan
$lg_disableCart2 = 'Ej tillgängligt';  // "Ej tillgängligt" innan
$lg_addSample = 'Få ett prov';
$lg_disableSample = 'Prov ej tillgängligt';
$lg_removeSample = 'Ta bort prov';
$lg_textSample = 'Få ett prov som visar allmänna egenskaper.';
$lg_artnr = 'art.nr.';
$lg_images = 'Klicka för större bilder';
$lg_choice = 'Anpassa din produkt';
$lg_noChoice = 'Produkten har inga anpassnings möjligheter';
$lg_description = 'Produktbeskrivning';

$lg_chooseModel = 'Välj modell';
$lg_chooseSize = 'Välj storlek (L x H)';
$lg_chooseAngle = 'Välj vinkel';
$lg_chooseAmount = 'Välj antal';

$lg_cartAlert = 'Detta är inte ett godkänd antal\nEndast hela siffror är tillåtet';

// Artikel - Produkt specifikt
$lg_noTecken = 'Inga tecken valda';
$lg_noGSS = 'Ingen serie skapad';

// Artikel - Endast GSS
$lg_createSerial = 'Skapa serie för';

$lg_startSerial = 'Starta serien från';
$lg_endSerial = 'och sluta serien på';

$lg_intervalSerial = 'Serie-intervall';
$lg_eachSerial = 'Serie';
$lg_otherSerial = 'Varannan';
$lg_tenthSerial = 'Tiotal';

$lg_repeatSerial = 'Upprepa samma serie';

$lg_serialWarningOne = '*Produkten säljs i hela kartor. Slutet av serien räknas automatiskt till slutet av sista kartan.';
$lg_serialWarningTwo = 'Observera att du måste avmarkera rutor (så de blir gröna) för att informationen och pris ska uppdateras.';

$lg_amountZero = 'Antal nollor';
$lg_amountMultiplied = 'antal gånger';
$lg_selectBackground = 'Välj bakgrundsfärg';

// Dokument
$lg_download = 'Ladda ner';
$lg_forDownload = 'för nerladdning';

// Sök
$lg_backResult = 'Visa tidigare resultat';
$lg_moreResult = 'Visa fler resultat';
$lg_endResult = 'Finns inte fler sökresultat att visa';

// Kundvagn
$lg_emptyCart = 'Kundvagnen är tom';
$lg_totalCart = 'Summa';
$lg_vatCart = 'exkl. moms';
$lg_continueCart = 'Fortsätt handla';
$lg_orderCart = 'Till beställning';

$lg_amountCart = 'Antal';
$lg_priceCart = 'Pris';

$lg_remove = 'Ta bort';
$lg_restore = 'Ångra';

$lg_special_1 = 'Specifikation för antal kartor';
$lg_special_2_yellow = 'Specifikation för <b>gula</b> serier';
$lg_special_2_white = 'Specifikation för <b>vita</b> serier';

$lg_messageCart = 'Spara ändringar och <b>'.$lg_continueCart.'</b> eller <b>'.$lg_orderCart.'</b>';

// Felsökning
$lg_error = 'Något fel har inträffat!'; // Generisk fel
$lg_noProduct = 'Produkten blev inte inlagd.'; // Sker när GET koden inte stämmer på kundvagn
$lg_browser = 'Din webbläsare är för gammal!\nWebbplatsen visas INTE korrekt!\nDu måste uppdatera om du vill kunna beställa från hemsida.'; // Varning för gammal < IE9

// Nyheter
$lg_news = "Nyheter";
$lg_events = "Aktuellt";
$lg_oldEvents = "&Auml;ldre h&auml;ndelser";
$lg_newEvents = "Nyare h&auml;ndelser";

// Färger
$lg_yellow = 'Gul';
$lg_white = 'Vit';

// Siffror
$lg_none = 'Inga';
$lg_two = 'Två';
$lg_three = 'Tre';
$lg_four = 'Fyra';

?>