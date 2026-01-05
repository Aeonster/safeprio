<?php
session_start();

/****
	index.php 
	Skapad av Patricio Santiago
	Copyright Kortsystem i Gislaved AB
	Version 1.0 - 2014-11-01
****/	 

//	Att göra: section/content elements (con-style.css ska bort)

	// Läs in språk och valuta som ska användas
	require 'includes/language.php';
	require 'includes/' . $LanguagePrefix . 'language.php';
    require 'includes/currency.php';

	// Läs in sökvägar till länkar
	require 'includes/directory.php';

    // Koppla in databas
	require 'includes/db.php';

	//******** Header - No cache ********//
	header('Cache-Control: post-check=0, pre-check=0', FALSE);
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');

    /***** Här startar kommunikation med databasen *****/
    $z = $LanguagePrefix;

    // Sql Query
    $queryNews = $db->query("SELECT id, type, 
                            ".$z."header AS zheader, 
                            ".$z."text AS ztext, 
                            date_post 
                            FROM ks_news 
                            WHERE category = 1 
                            AND date_post <= CURDATE() 
                            AND (date_end > CURDATE() 
                            OR date_end = '0000-00-00') 
                            ORDER BY date_post DESC LIMIT 5");

    $queryEvents = $db->query("SELECT id, type, 
                            ".$z."header AS zheader, 
                            ".$z."text AS ztext, 
                            date_post 
                            FROM ks_news 
                            WHERE category = 2 
                            AND date_post <= CURDATE() 
                            AND (date_end > CURDATE() 
                            OR date_end = '0000-00-00') 
                            ORDER BY date_post DESC LIMIT 5"); // Vid pagination läggs en ", 6" för ange nästa start punkt.

	//******** Snippets ********//
	// Ladda in korrekt index språk.
	require 'pages/page/' . $LanguagePrefix . 'index.php';
	
	// Language Selection -- Start
	// Språk faller alltid till engelska om svenska inte finns någonstans (som session)
	$tm_language = ''; // init variabel för undvika startfel bara
	if($LanguageCode == 'SE'){ // Svenska som default
		$tm_language = '<a href="?language=se">Svenska</a>
										<div><a href="?language=en">English</a></div>';
		$tm_alternative = '<link rel="alternate" hreflang="sv" href="http://www.kortsystem.se/?language=se" />
						   <link rel="alternate" hreflang="en" href="http://www.kortsystem.se/?language=en" />';
	}
	else { // English som default
		$tm_language = '<a href="?language=en">English</a>
										<div><a href="?language=se">Svenska</a></div>';
		$tm_alternative = '<link rel="alternate" hreflang="en" href="http://www.kortsystem.se/?language=en" />
						   <link rel="alternate" hreflang="sv" href="http://www.kortsystem.se/?language=se" />';
	}
	// Language Selection -- End

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
<meta name="description" content="<?=$meta;?>" />
<meta name="keywords" content="<?=$keywords;?>" />

<title><?=$lg_page;?></title>
<link rel="stylesheet" href="<?=$pg_prefix;?>/styles/navigation.css?1337" type="text/css" />
<link rel="stylesheet" href="<?=$pg_prefix;?>/styles/con-styles.css?1337" type="text/css" />
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
<?=$tm_alternative;?>

<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
<!--[if lt IE 9]>
   <script>
      document.createElement('header');
      document.createElement('nav');
      document.createElement('section');
      document.createElement('article');
      document.createElement('aside');
      document.createElement('footer');
   </script>
   <style type="text/css">
      .navDesktop>.top>.wrapper>.logotype {background:url('../images/logo.png') no-repeat center;}
   </style>
<![endif]-->
<style type="text/css">
	html,body{height:92%;} /* Gäller endast index */
	header, nav, section, article, aside, footer {display:block;} /* Gäller IE8 & IE9 */
</style>
<?php //include_once("includes/analytics.php"); ?>
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
        <a href="<?=$pg_product;?>"><?=$lg_product;?></a>
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
    	<a href="<?=$pg_product;?>"><?=$lg_product;?></a>
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
    <section class="back_stretch"></section>
    <section class="content startpage">
    	<article>
          <?=$dt_page01;?>
      
          <div class="news">
            <div class="type">
                <div id="news" class="active"><?=$lg_news;?></div>
                <div id="events"><?=$lg_events;?></div>
            </div>
            <div id="news_display" class="itemGroup">
                <?php
                // Kontrollera ifall $queryNews finns - OBS!! Det måste finnas åtminstonde 1 resultat
					if(isset($queryNews))
					{
						// Dubbelkolla om vi fick resultat!
						if(mysqli_num_rows($queryNews) > 1)
						{	
                            $c = 0;
							while($result = $queryNews -> fetch_object())
							{
                                if($result->type == 1){
                                    ?>
                                        <div>
                                            <a data-id="<?=$result->id;?>" href="<?=$result->ztext;?>">
                                                <span><?=$result->date_post;?></span>
                                                <span><?=$result->zheader;?></span>
                                            </a>
                                        </div>
                                    <?php
                                }
                                else {
                                    ?>
                                        <div>
                                            <a data-id="<?=$result->id;?>">
                                                <span><?=$result->date_post;?></span>
                                                <span><?=$result->zheader;?></span>
                                            </a>
                                            <div id="ns<?=$result->id;?>" class="itemSelector"><?=$result->ztext;?></div>
                                        </div>
                                    <?php
                                }
                                
                                $c++;
                            }
                            // Var det under 5 svar så tas "nästa" knapp bort.
                            if ($c < 5) {
                                $nameDown = "none";
                                $tm_oldEvents = "&nbsp;"; // måste med vid tomt annars blir det fel storlek.
                            }
                            else {
                                $nameDown = "oldnews";
                                $tm_oldEvents = $lg_oldEvents;   
                            }
                        }
                    }
				?>
                <div class="pages">
                    <div data-id="0" data-name="none">&nbsp;</div>
                    <div data-id="5" data-name="<?=$nameDown;?>"><?=$tm_oldEvents;?></div>
                </div>
            </div>
            <div id="events_display" class="itemGroup">
                <?php
                // Kontrollera ifall $queryNews finns - OBS!! Det måste finnas åtminstonde 1 resultat
					if(isset($queryEvents))
					{
						// Dubbelkolla om vi fick resultat!
						if(mysqli_num_rows($queryEvents) > 1)
						{	
                            $c = 0;
							while($result = $queryEvents -> fetch_object())
							{
                                if($result->type == 1){
                                    ?>
                                        <div>
                                            <a data-id="<?=$result->id;?>" href="<?=$result->ztext;?>">
                                                <span><?=$result->date_post;?></span>
                                                <span><?=$result->zheader;?></span>
                                            </a>
                                        </div>
                                    <?php
                                }
                                else {
                                    ?>
                                        <div>
                                            <a data-id="<?=$result->id;?>">
                                                <span><?=$result->date_post;?></span>
                                                <span><?=$result->zheader;?></span>
                                            </a>
                                            <div id="ns<?=$result->id;?>" class="itemSelector"><?=$result->ztext;?></div>
                                        </div>
                                    <?php
                                }
                            }
                            // Var det under 5 svar så tas "nästa" knapp bort.
                            if ($c < 5) {
                                $nameDown = "none";
                                $tm_oldEvents = "&nbsp;"; // måste med vid tomt annars blir det fel storlek.
                            }
                            else {
                                $nameDown = "oldnews";
                                $tm_oldEvents = $lg_oldEvents;   
                            }
                        }
                    }
				?>
                <div class="pages">
                    <div data-id="0" data-name="none">&nbsp;</div>
                    <div data-id="5" data-name="<?=$nameDown;?>"><?=$tm_oldEvents;?></div>
                </div>
            </div>
          </div>

        <?=$dt_page02;?>
      </article>
    </section>

<!-- Footer -->
<div class="footer">
  <div class="company">Kortsystem i Gislaved AB</div>
  <div class="contact">0371-22 24 44 &bull; info@kortsystem.se</div><br>
  <div><?=$LanguageSelect;?>
   <br><?=$CurrencySelect;?></div>
  <div><a onclick="footCookie()" href=""><?=$lg_cookies;?></a></div>
</div>
  
</body>
<script src="/scripts/jquery-1.11.0.min.js" type="text/javascript"></script>
<script src="/scripts/jquery.backstretch.min.js" type="text/javascript"></script>
<script src="/scripts/jquery.sidr.min.js" type="text/javascript"></script>
<script src="/scripts/jquery.svgmagic.js" type="text/javascript"></script>
<script>

	// Mobile -- Menyer
	$(document).ready(function() {
	  $('.menuMobile').sidr({name:'sidr-left',side:'left'});
	  $('.searchMobile').sidr({name:'sidr-right',side:'right'});
	});

	// Converterar alla SVG bilder till PNG om det inte finns support för i browser	
	$(document).ready(function(){
	   $('img').svgmagic();
	});

	//~~~~ Backstretch (index only) ~~~~//
	// Skapa array med alla bilder
	var images = [
		"/images/backgrounds/bg001.jpg",
    "/images/backgrounds/bg002.jpg",
    "/images/backgrounds/bg003.jpg",
		"/images/backgrounds/bg004.jpg",
		"/images/backgrounds/bg005.jpg",
		"/images/backgrounds/bg006.jpg"
	];

	// "Pre-load" bilder
	$(images).each(function(){
	  $("<img/>")[0].src = this;
	});

	// Slumpa tal endast av antalet bilder
	var randomNumber = Math.floor( Math.random() * images.length );

	// Anropa backstretch nu och visa en random bild
	$.backstretch(images[randomNumber], { speed:1000 });

	// Ändra bild efter en viss tid
	setInterval(function() {
		index = Math.floor( Math.random() * images.length );
		$.backstretch(images[index]);
	}, 45000); // 1000=1s, 60000=1m
	//~~~~ Backstretch **END** ~~~~//

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
    function footCookie(){
      alert('<?=$lg_cookiesMessage;?>');
    }
	//~~~~ Footer **END** ~~~~//
  
  //~~~~ Detect IE8 and below ~~~~//
  $(document).ready(function() {	
	//~~~~ Detect IE8 and below ~~~~//
		var ie = (function(){ // Kolla vilken version av browser som används.
			var undef,
					v = 3,
					div = document.createElement('div'),
					all = div.getElementsByTagName('i');
			while (
					div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->',
					all[0]
			);
			return v > 4 ? v : undef;
		}());
		
		if(ie < 9){ // Allt under IE9 visas pop-up med fel.
			alert("<?=$lg_browser;?>");
		}
	//~~~~ Detect **END** ~~~~//
        
    //~~~~ News & Events ~~~~//
        $("#events_display").slideUp();
        
        $("#news").click(function(){
            $("#news_display").slideDown();
            $("#events_display").slideUp();
            $("#news").addClass("active");
            $("#events").removeClass("active");
        });
        
        $("#events").click(function(){
            $("#events_display").slideDown();
            $("#news_display").slideUp();
            $("#events").addClass("active");
            $("#news").removeClass("active");
        });

        // on ersätter click om det ska fungera efter ajax anropp
        $(".itemGroup").on("click","a",function(){
            var dataSelector = $(this).attr("data-id");
            if($("#ns" + dataSelector).length){
                $("#ns" + dataSelector).slideToggle();
            }
        });        
        
        // efter ajax kan man ej anropa de ändrade dom taggar, man måste länka sig ner från parent.
        $(".itemGroup").on("click",".pages div",function(){
            
            var dataString = $(this).attr("data-id");
            var dataType = $(this).attr("data-name");
            var htmlData = 1;
            
            if (dataType != "none"){
                if (dataType == "oldevents" || dataType == "newerevents") {
                    htmlData = 2;   
                }
                news(dataString,dataType,htmlData);
            }
        });

        //~~~~ News & Events **END** ~~~~// 
	}); // <-- tillhör footer.

    // Tillhör news/events, får bara inte vara i document ready.
    function news(dataString,dataType,htmlData) {
        $.post("/includes/news.php", {datastring: ""+dataString+"",datatype: ""+dataType+""}, function(data) { 
            if (htmlData == 1){
                $('#news_display').html(data);
            }
            else if (htmlData == 2){
                $('#events_display').html(data);
            }
        });
    };

</script>
</html>
