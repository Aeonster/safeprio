<?php
session_start();

/****
	product.php 
	Skapad av Patricio Santiago
	Copyright Kortsystem i Gislaved AB
	Version 1.0 - 2014-11-01
	Databas - ks_product
****/	 

//	Att göra: Språk för 4 ben är hårdkodad. De måste ut ur sidan!

	// Läs in språk och valuta som ska användas
	require '../includes/language.php';
	require '../includes/' . $LanguagePrefix . 'language.php';
    require '../includes/currency.php';

	// Läs in sökvägar till länkar
	require '../includes/directory.php';
	
	// Koppla in databas
	require '../includes/db.php';
	
	//******** Header - No cache ********//
	header('Cache-Control: post-check=0, pre-check=0', FALSE);
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

/***** Här startar kommunikation med databasen *****/
/* Enda input är språkkod för att hämta rätt språk. Koden sätts i
   sql frågan för att inte hämta massa extra onödig information. */

		// Hämtar all dokument information, ordnas efter ORDER raden.
		$z = $LanguagePrefix; // Endast för att korta sql frågan.
		$query = $db->query("SELECT id, column_nr, order_nr, sub_order, 
												".$z."header AS header 
												FROM sp_product ORDER BY id ASC");
		
		if($query){ // Skicka frågan, vid fel kolla db.php
			if(mysqli_num_rows($query) > 0){ // Om databasen skickade resultat, vid 0 kolla sql-frågan
				while($result = $query -> fetch_object()){ // Skapa arrays av posterna, 'order' är array siffran
					$dba_id[$result->id] = $result->id;
					$dbt_column[$result->id] = $result->column_nr;				
					$dbt_order[$result->id] = $result->order_nr;
					$dbt_sub_order[$result->id] = $result->sub_order;
					$dba_header[$result->id] = $result->header;
				}
				$dbt_count = count($dba_id); // Räknar hur många resultat kommer fram, används i for-sats senare
			} else { echo 'Det har hänt något fel. (2110)'; }
		} else { echo 'Det har hänt något fel. (2100)'; }
/***** Här slutar kommunikation med databas (endast variabler finns kvar) *****/	
	
	//******** Språk för pelare (tillfälligt lösning) ********//
	if($LanguageCode == 'SE'){ // Svenska
		$tx_LagerHeader = 'Lager';
		$tx_LagerText = 'Läs mer om vårt märksystem och långa erfarenhet av att märka lager.';
		$tx_ProduktionHeader = 'Produktion';
		$tx_ProduktionText = 'Läs mer om vårt märksystem och långa erfarenhet av produktions-flödesmärkning.';
		$tx_ButikHeader = 'Butik';
		$tx_ButikText = 'Läs mer om vad vi kan erbjuda inom butiksmärkning.';
		$tx_IndustriHeader = 'Industri';
		$tx_IndustriText = 'Läs mer om vad vi kan erbjuda inom industrimärkning.';
	}
	else { // Annars engelska
		$tx_LagerHeader = 'Warehousing';
		$tx_LagerText = 'Find out more about our warehouse labelling systems and our extensive experience here.';
		$tx_ProduktionHeader = 'Production';
		$tx_ProduktionText = 'Find out more about our production flow labelling systems and our extensive experience here.';
		$tx_ButikHeader = 'Stores';
		$tx_ButikText = 'Find out more about the store labelling we offer here.';
		$tx_IndustriHeader = 'Industry';
		$tx_IndustriText = 'Find out more about the industrial labelling we offer here.';
	}
	


	//******** Snippets ********//
// Cart Button -- Start
// Ser om det finns kundvagn, det plusar alla varor och summor
if(isset($_SESSION['article'])) {
    $sn_cart = $_SESSION['article'];
    $sn_cartCount = count($sn_cart);
    $sn_value = 0;
    for($i=1; $i<=$sn_cartCount; $i++) {
        $s=$i-1;
        $sn_value = $yN==1 ? $sn_value + $sn_cart[$s][11] : $sn_value + $sn_cart[$s][17]; // 1 = SEK
      
    }
    $tm_empty = '';
    $tm_emptyMessage = '<span>'.number_format($sn_value,2,',',' ').'</span> ('.$CurrencyCode.')';
}
else { // Fanns inget kundvagn ska allt blir empty
    $tm_empty = ' empty';
    $tm_emptyMessage = $lg_empty;
}
// Cart Button -- End

	// Meta description
	$meta = ''; // init variabel för undvika startfel bara
	if($LanguageCode == 'SE'){ // Svenska som default
		$meta = 'Välkommen in och se vårt sortiment, vi tillverkar och utvecklar produkter som etiketter, skyltar och hållare.';
	}
	else { // English som default
		$meta = 'You are welcome to see our variety of products we manufacture and develop, such as labels, signs and label holders.';
	}
	// Meta description -- End

// Telia Header
header('Content-Type: text/html; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8"?>
';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=$LanguageHead;?>" lang="<?=$LanguageHead;?>" default_mimetype="text/html" default_charset="UTF-8">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
<meta name="description" content="<?=$meta;?>" />
<meta name="keywords" content>
<meta name="robots" content="index, follow">

<title><?=$lg_product;?> | Kortsystem</title>
<link rel="stylesheet" href="<?=$pg_prefix;?>/styles/navigation.css?1337" type="text/css" />
<link rel="stylesheet" href="<?=$pg_prefix;?>/styles/product.css?1335" type="text/css" />
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
<!--[if lt IE 9]>
   <style type="text/css">
      .navDesktop>.top>.wrapper>.logotype {background:url('../images/logo.png') no-repeat center;}
   </style>
<![endif]-->
<?php // include_once("../includes/analytics.php"); ?>
</head>

<body>
<!-- Navigation.css -->
	<!-- Search -->
	<div id="searchDesktop">
  	<div class="searchPosition">
    	<div id="searchSuggestion_Desktop"></div>
    </div><!-- searchPosition -->
  </div><!-- searchDesktop -->

	<!-- Navigation: Desktop -->
  <div class="navDesktop">
  	<div class="top">
    	<div class="wrapper">
        <a class="search"><input id="search" type="search" autocomplete="off" placeholder="<?=$lg_search;?>..." onkeyup="search(this.value);" /></a>
        <a href="<?=$pg_product;?>" class="selected"><?=$lg_product;?></a>
        <a href="<?=$pg_index;?>" class="logotype"></a>
        <a href="<?=$pg_document;?>"><?=$lg_document;?></a>
        <a href="<?=$pg_contact;?>"><?=$lg_contact;?></a>
      	<a href="<?=$pg_cart;?>" class="cart<?=$tm_empty;?>"><?=$lg_cart;?><div><?=$tm_emptyMessage;?></div></a>
    	</div><!-- wrapper -->
    </div><!-- top -->
  	<div class="bottom">
    	<div class="wrapper">
        <div class="breadcrumb">
          <a href="<?=$pg_index;?>"><?=$lg_home;?></a>
          <a href="<?=$pg_product;?>"><?=$lg_product;?></a>
        </div><!-- breadcrumb -->

    	</div><!-- wrapper -->
	  </div><!-- bottom -->
  </div><!-- navDesktop -->

  <!-- Navigation: Mobile -->
	<div class="navMobile">
  	<div class="top">
    	<a href="#" class="menuMobile"><?=$lg_menu;?></a>
      <a href="<?=$pg_index;?>" class="logotype"></a>
      <a href="#" class="searchMobile"><?=$lg_search;?></a>
    </div><!-- top -->
    <div class="bottom"></div>
  </div><!-- navMobile -->

		<!-- Navigation: Mobile - Left Side -->
		<div id="sidr-left">
    	<a href="#" class="menuMobile back"><?=$lg_back;?></a>
    	<a href="<?=$pg_product;?>" class="selected"><?=$lg_product;?></a>
      <a href="<?=$pg_document;?>"><?=$lg_document;?></a>
      <a href="<?=$pg_contact;?>"><?=$lg_contact;?></a>
      <a href="<?=$pg_cart;?>"><?=$lg_cart;?></a>

    </div><!-- sidr-left -->
    
		<!-- Navigation: Mobile - Right Side -->
		<div id="sidr-right">
    	<a href="#" class="searchMobile back"><?=$lg_back;?></a>
      <a class="search"><input type="search" autocomplete="off" placeholder="<?=$lg_search;?>..." onkeyup="searchMobile(this.value);" /></a>
      <div id="searchSuggestion_Mobile"></div>
    </div><!-- sidr.right -->
<!-- Navigation.css **END** -->
    
<!-- Content product.css -->
<div class="content">
	<div class="article">
		<div class="menu">
<?php
// Här skrivs ut alla menyer
echo '<div class="column">'; // Startar första kolumn (av 3).
for ($i=1; $i<=$dbt_count; $i++) { // For-loop som går igenom ALLA meny positioner
	
	if($dbt_column[$i] == 1) // Om den tillhör första columnen
	{
		$sub_order = ''; // Nollställ sub_order för varje loop omgång
		if($dbt_sub_order[$i] == 1) // Om databas sub_order är lika med 1 ska subCategori sättas.
		{ $sub_order = 'class="sub"'; }
		
		echo '<a '.$sub_order.' href="'.$pg_category.'?id='.$dba_id[$i].'">'.$dba_header[$i].'</a>'; // Skriv ut menyrad.
	}
	elseif($dbt_column[$i] == 2) // Kolumn 2
	{
		if($dbt_order[$i] == 1) // Om detta är första order i nya kolumn, ska K1 stängas och starta K2
		{ echo '</div><div class="column">'; }

		$sub_order = '';
		if($dbt_sub_order[$i] == 1)
		{ $sub_order = 'class="sub"'; }
		
		echo '<a '.$sub_order.' href="'.$pg_category.'?id='.$dba_id[$i].'">'.$dba_header[$i].'</a>';
	}
	elseif($dbt_column[$i] == 3) // Kolumn 3
	{
		if($dbt_order[$i] == 1)
		{ echo '</div><div class="column">'; }

		$sub_order = '';
		if($dbt_sub_order[$i] == 1)
		{	$sub_order = 'class="sub"'; }
		
		echo '<a '.$sub_order.' href="'.$pg_category.'?id='.$dba_id[$i].'">'.$dba_header[$i].'</a>';
	}
	else
	{
		echo $lg_error;
	}
}
echo '</div>'; // Avsluta kolumn (i detta fall borde blivit kolumn 3)
?>
	  </div><!-- menu -->
	  

<?php
// Varnings meddelande eller information

// Sommarstängt

if($z == "se_"){
	$temp_header = 'Viktig information';
	$temp_info = 'Vi har störningar i vår nät & telefoni. Just nu når du oss via direktnummer: 076-303 14 08';
	
	
}
else {
	$temp_header = 'Important information';
	$temp_info = 'We are having some network & phone issues. At the moment you can reach us directly to this number: +46 (0) 76-303 14 08';
}


// Julstängt
/*
if($z == "se_"){
	$temp_header = 'Viktig information';
	$temp_info = 'Vi stänger för jul och nyår den 23:e december och öppnar upp igen den 2:a januari 2023.<br /> 
	Beställningar på webbshoppen hanteras först under vecka 1. God jul och gott nytt år!';
	
	
}
else {
	$temp_header = 'Important information';
	$temp_info = 'We close for Christmas and New Year on the 23rd of December and open again on the 2nd of January 2023.<br />
	Orders from our web shop will be in a pending status until we get back to regular business hours after 2nd of January. Have a nice holiday!';
}
*/
// Covid special
/* if($z == "se_"){
	$temp_header = '<b>COVID-19</b> - Håll i och håll ut, vi kan hjälpa till!';
	$temp_info = 'Vi tillverkar <b>kundanpassade</b> dekaler, skyltar, golvmärkning och kassaskydd - <i>allt med snabba leveranser.</i><br /><br />
	<img src="/images/others/covid001.png" />&nbsp; &nbsp;<img src="/images/others/covid006.png" /><br /><br />
	För mer information, färger och former - <b>kontakta oss</b> genom telefon eller e-post <b><a href="mailto:info@kortsystem.se">info@kortsystem.se</a></b><br />';
	
	
}
else {
	$temp_header = '<b>COVID-19</b> - Hang in there, we can help!';
	$temp_info = 'We produce <b>client specific</b> decals labels, aisle sign and floor markings - <i>all with fast delivery.</i><br /><br />
	<img src="/images/others/covid001.png" />&nbsp; &nbsp;<img src="/images/others/covid006.png" /><br /><br />
	For more information, colours and shapes - <b>contact us</b> by phone or e-mail <b><a href="mailto:info@kortsystem.se">info@kortsystem.se</a></b><br />';
}
*/

$temp_output = '<div class="important"><div>'.$temp_header.'</div><div>'.$temp_info.'</div></div>';

// Själv output, kommentera för stänga, avkommentera för öppna.
// echo $temp_output;


?>
      
    <div class="pillar">
      <a href="/pages/page.php?id=lager">
        <div><img src="/images/others/lager.jpg" /></div>
        <div class="base">
          <div><?=$tx_LagerHeader;?></div>
          <div><?=$tx_LagerText;?></div>
        </div>
      </a>
      <a href="/pages/page.php?id=produktion">
        <div><img src="/images/others/produktion.jpg" /></div>
        <div class="base">
          <div><?=$tx_ProduktionHeader;?></div>
          <div><?=$tx_ProduktionText;?></div>
        </div>
      </a>
      <a href="/pages/page.php?id=butik">
        <div><img src="/images/others/butik.jpg" /></div>
        <div class="base">
          <div><?=$tx_ButikHeader;?></div>
          <div><?=$tx_ButikText;?></div>
        </div>
      </a>
      <a href="/pages/page.php?id=industri">
        <div><img src="/images/others/industri.jpg" /></div>
        <div class="base">
          <div><?=$tx_IndustriHeader;?></div>
          <div><?=$tx_IndustriText;?></div>
        </div>
      </a>
    </div><!-- pillar --> 
  </div><!-- article -->
</div><!-- Content **END** -->

<!-- Footer -->
<div class="footer">
  <div class="company">SafePrio by Kortsystem</div>
  <div class="contact">0371-22 24 44 &bull; info@kortsystem.se</div><br>
  <div><?=$LanguageSelect;?>
   <br><?=$CurrencySelect;?></div>
  <div><a onclick="footCookie()" href=""><?=$lg_cookies;?></a></div>
</div>
  
</body>
<script src="../scripts/jquery-1.11.0.min.js" type="text/javascript"></script>
<script src="../scripts/jquery.sidr.min.js" type="text/javascript"></script>
<script src="../scripts/jquery.svgmagic.js" type="text/javascript"></script>
<script>
	// Mobile -- Menyer
	$(document).ready(function() {
	  $('.menuMobile').sidr({name:'sidr-left',side:'left'});
	  $('.searchMobile').sidr({name:'sidr-right',side:'right'});
	});

	// Converterar alla SVG bilder till PNG om det inte finns support för i browser	(ej IE8)
	$(document).ready(function(){
	   $('img').svgmagic();
	});
	
	//~~~~ Search ~~~~//
	// Fade out sökförslag när sökrutan tappar fokus
	$("input").blur(function(){
	 	$('#searchDesktop').fadeOut();
	 });

	// Search Desktop - Ajax, ska visa eller gömma en fixed ruta
	function search(inputString) {
		if(inputString.length == 0) {
			$('#searchDesktop').fadeOut(); // Om det inte finns text, ska inga förslag visas
		} else {
			$.post("/includes/search.php", {queryString: ""+inputString+""}, function(data) { // Ajax: Skicka förfrågan till search
				$('#searchDesktop').fadeIn(); // Fade in sökförslag rutan
				$('#searchSuggestion_Desktop').html(data); // Fyll sökförslagen med resultat
			});
		}
	}
		
	// Search Mobile - Ajax, ska rensa eller lägga in data (ingen ruta)
	function searchMobile(inputString) {
		if(inputString.length == 0) {
			$('#searchSuggestion_Mobile').html(''); // Om det inte finns text, ska inga förslag visas
		} else {
			$.post("/includes/search.php", {queryString: ""+inputString+""}, function(data) { // Ajax: Skicka förfrågan till search
				$('#searchSuggestion_Mobile').html(data); // Fyll sökförslagen med resultat
			});
		}
	}
	//~~~~ Search **END** ~~~~//

      //~~~~ Footer ~~~~//
      $(document).ready(function() {
          // Ställer footer på botten av sidan. (om sidan är mindre än 100%)
          var docHeight = $(window).height(); // Hämta höjd på browser fönster
          var footerHeight = $(".footer").height(); // Hämtar höjd på footen
          var footerTop = $(".footer").position().top + footerHeight; // Kollar var i sidan footen är

          if(footerTop < docHeight){ // Är sidan större än footens position, måste den flyttas ner
              $(".footer").css('margin-top',-60+(docHeight-footerTop)+'px'); // Beräknar mellan skillnaden
          }
      });
      function footCookie(){
        alert('<?=$lg_cookiesMessage;?>');
      }
      //~~~~ Footer **END** ~~~~//
</script>
</html>
