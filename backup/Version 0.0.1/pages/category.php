<?php
session_start();

/****
	category.php 
	Skapad av Patricio Santiago
	Copyright Kortsystem i Gislaved AB
	Version 1.0 - 2014-11-01
	Databas - ks_category
****/	 

//	Att göra: css måste skrivas om

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
  // Kontrollerar om id (category) är i addressen, behövs för att få ut product.
	if(isset($_GET['id'])) {

		// Skydda mot SQL injekt
		$queryId = $db->real_escape_string($_GET['id']);
	
			// Hämtar all dokument information, ordnas efter ORDER raden.
			$z = $LanguagePrefix; // Endast för att korta sql frågan.
			
			// OBS! order är protected använder 'order'
			$query = $db->query("SELECT id, product_id, order_nr, url_redirect, url_link, image, 
													".$z."header AS header, 
													".$z."subheader AS subheader, 
													".$z."text AS text 
													FROM ks_category 
													WHERE product_id=".$queryId." 
													ORDER BY order_nr ASC");
			
			if($query){ // Skicka frågan, vid fel kolla db.php
				if(mysqli_num_rows($query) > 0){ // Om databasen skickade resultat, vid 0 kolla sql-frågan
					while($result = $query -> fetch_object()){ // Skapa arrays av posterna, 'order' är array siffran
						$dba_id[$result->order_nr] = $result->id;
						$dba_mnCat[$result->order_nr] = $result->product_id;				
						$dba_order[$result->order_nr] = $result->order_nr;
						$dba_redirectURL[$result->order_nr] = $result->url_redirect;
						$dba_linkURL[$result->order_nr] = $result->url_link;
						$dba_image[$result->order_nr] = $result->image;
						$dba_header[$result->order_nr] = $result->header;
						$dba_subheader[$result->order_nr] = $result->subheader;
						$dba_text[$result->order_nr] = $result->text; }
				} else { echo 'Det har hänt något fel. (2310)'; }
			} else { echo 'Det har hänt något fel. (2300)'; }
			
			$dbt_count = count($dba_id);
			
			$pageHead = $dba_header[1].' '.$dba_subheader[1];
			$mnCat = $dba_mnCat[1];
	}
/***** Här slutar kommunikation med databas (endast variabler finns kvar) *****/

	//******** Snippets ********//
	// Language Selection -- Start
	// Språk faller alltid till engelska om svenska inte finns någonstans (som session)
	if($LanguageCode == 'SE'){ // Svenska som default
		$tm_language = '<a href="?id='.$mnCat.'&language=se">Svenska</a>
										<div><a href="?id='.$mnCat.'&language=en">English</a></div>';
	}
	else { // English som default
		$tm_language = '<a href="?id='.$mnCat.'&language=en">English</a>
                    <div><a href="?id='.$mnCat.'&language=se">Svenska</a></div>';
	}
	// Language Selection -- End

	//******** Snippets ********//
	// Language Selection -- Start
	// Språk faller alltid till engelska om svenska inte finns någonstans (som session)
	if($LanguageCode == 'SE'){ // Svenska som default
		$LanguageSelect = 'Change language to <a href="?id='.$mnCat.'&language=en">English</a>';
	}
	else { // English som default
		$LanguageSelect = 'Byt språk till <a href="?id='.$mnCat.'&language=se">Svenska</a>';
	}
	// Language Selection -- End
    // Currency Selection -- Start
    if($CurrencyCode == 'EUR'){ // SEK som default
        $CurrencySelect = 'Byt valuta till <a href="?id='.$mnCat.'&currency=sek">SEK</a>';
    }
    else {
        $CurrencySelect = 'Change currency to <a href="?id='.$mnCat.'&currency=eur">EUR</a>';
    }
    // Currency Selection -- End

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
<meta name="description" content="<?=substr($dba_text[1],0,150).'...';?>" />
<meta name="keywords" content>
<meta name="robots" content="index, follow">

<title><?=$pageHead;?> | Kortsystem</title>
<link rel="stylesheet" href="<?=$pg_prefix;?>/styles/navigation.css?1337" type="text/css" />
<link rel="stylesheet" href="<?=$pg_prefix;?>/styles/category.css?1337" type="text/css" />
<link rel='shortcut icon' type='image/x-icon' href='/favicon.ico' />
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
          <a href="<?=$pg_category;?>?id=<?=$mnCat;?>"><?=$pageHead;?></a>
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
  
    <!-- Content -->
    <div class="content">
    	<div class="wrapper">
      	<div class="mn_header"><?=$dba_header[1];?></div>
        <div class="mn_sbheader"><?=$dba_subheader[1];?></div>
        <div><?=$dba_text[1];?></div>
        
<?php
// Loopen som visar alla val möjligheter. $dbt_count gjordes i samband med db anropp.
for ($i=2; $i<=$dbt_count; $i++) { // ALLA val utom 1an som ska vara rubrik

	// Är det till artikelnummer? Annars en annan meny :D
	if($dba_redirectURL[$i] == 1)
	{ $realURL = 'article.php?artno='.$dba_linkURL[$i]; }
	else
	{ $realURL = '?id='.$dba_linkURL[$i]; }

?>
        <div id="<?=$i;?>"></div>
        <a href="<?=$realURL;?>" class="selection">
        	<div class="image"><img src="../images/products/<?=$dba_image[$i];?>" /></div>
          <div class="box">
          	<div class="sb_header"><?=$dba_header[$i];?></div>
            <div class="text"><?=$dba_text[$i];?></div>
            <div class="next"><?=$lg_next;?></div>
          </div><!-- box -->
        </a><!-- selection -->
<?php
}
?>
      </div><!-- wrapper -->
    </div><!-- content -->
    
<!-- Footer -->
<!-- Footer -->
<div class="footer">
  <div class="company">Kortsystem i Gislaved AB</div>
  <div class="contact">0371-22 24 44 &bull; info@kortsystem.se</div><br>
  <div><?=$LanguageSelect;?>
   <br><?=$CurrencySelect;?></div>
  <div><a onclick="footCookie()" href=""><?=$lg_cookies;?></a></div>
</div>

</body>
<script src="../scripts/jquery-1.11.0.min.js" type="text/javascript"></script>
<script src="../scripts/jquery.sidr.min.js" type="text/javascript"></script>
<script src="../scripts/jquery.svgmagic.js" type="text/javascript"></script>
<script src="../scripts/novalise.js" type="text/javascript"></script>
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
              $(".footer").css('margin-top',-20+(docHeight-footerTop)+'px'); // Beräknar mellan skillnaden
          }
      });
      function footCookie(){
        alert('<?=$lg_cookiesMessage;?>');
      }
      //~~~~ Footer **END** ~~~~//
</script>
</html>
