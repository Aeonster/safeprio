<?php
session_start();

/****
	article.php 
	Skapad av Patricio Santiago
	Copyright Kortsystem i Gislaved AB
	Version 1.0 - 2014-11-01
    Version 1.1 - 2014-12-01 (T-tecken)
    Version 1.2 - 2015-04-01 (GSS)
    Version 1.3 - 2016-03-10 (Varselmärkning)
	Databas - ks_article
****/	 

//	Att göra: css fil måste skrivas om

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
  // Kontrollerar om det en artno i addressen, behövs för att få ut product.
	if(isset($_GET['artno'])) {

		// Skydda mot SQL injekt
		$queryArtno = $db->real_escape_string($_GET['artno']);
		
		// Endast för att korta sql frågan.
		$z = $LanguagePrefix;
        $y = $CurrencyPrefix;
		
		// Query till SQL - 1
		$query = $db->query("SELECT 
																id, 
																artno, 
																name, 
																image, 
																image_extra, 
																".$z."description AS description, 
																price_group,
																".$y."price_1 AS price_1, 
																".$y."price_2 AS price_2, 
																".$y."price_3 AS price_3, 
																".$y."price_4 AS price_4, 
																".$y."price_5 AS price_5, 
																".$z."amount AS amount, 
																page_head, 
																crumb_id, 
																".$z."crumb AS 'crumb',
                                buyable, 
                                sample,  
																type, 
																box_lvl,
																box_id, 
																box_id1, 
																box_id2, 
																box_id3, 
																".$z."box_h1 AS boxh1,  
																".$z."box_h2 AS boxh2, 
																".$z."box_h3 AS boxh3, 
                                ".$z."information as information, 
                                vm_id 
																FROM ks_article WHERE artno= '" . $queryArtno . "'");

		if($query) { // Skicka frågan, vid fel kolla db.php
			if(mysqli_num_rows($query) > 0) { // Om databasen skickade resultat, vid 0 kolla sql-frågan
				$result = $query -> fetch_object(); // Hämtar endast första raden den får träff på
				
				// Standard data
				$dba_id = $result->id;
				$dba_artno = $result->artno;
				$dba_name = $result->name;
				$dba_image = $result->image;
				$dbt_imageExtra = $result->image_extra;
				$dba_desc = $result->description;
        $dba_info = $result->information;
        $dba_vm = $result->vm_id;
				
				$dba_amount = $result->amount;
        $dbt_buyable = $result->buyable;
        $dbt_sample = $result->sample;
				$dbt_type = $result->type;
				
				$dba_pageHead = $result->page_head;
				$dba_crumbId = $result->crumb_id;
				$dba_crumb = $result->crumb;
				
				// Pris data
				$dbt_price = $result->price_group;
				$dba_price1 = $result->price_1;
				$dba_price2 = $result->price_2;
				$dba_price3 = $result->price_3;
				$dba_price4 = $result->price_4;
				$dba_price5 = $result->price_5;
        
				$dba_price1f = number_format($dba_price1,2,',',' ');
				$dba_price2f = number_format($dba_price2,2,',',' ');
				$dba_price3f = number_format($dba_price3,2,',',' ');
				$dba_price4f = number_format($dba_price4,2,',',' ');
				$dba_price5f = number_format($dba_price5,2,',',' ');
				
				require '../includes/' . $z . 'price.php';
			
			  $price_n = $price_n[$dbt_price];
				$price_v = $price_v[$dbt_price];
				$amount_n = $amount_n[$dbt_price];
				
				// Anpassad data
				$dbt_boxLvl = $result->box_lvl; // Hur många val
				$dbt_boxIds = $result->box_id; // Grupp ID (detta är hela grupper ex: EBA, EBB, EBVA, EBVB 27+45)

				/* Skapar en 4siffrig slump nummer som används att skicka till kundvagn
						kundvagn använder detta för att undvika dubbletter. Kolla mer info där */
				$tm_randomID = rand(1000,9999);
				
				// Denna for loop laddar in endast antalet rubriker samt id som behöver (0 upp till 3)
				for ($i=1; $i<=$dbt_boxLvl; $i++) {
					$boxidno = 'box_id'.$i;
					$boxhno = 'boxh'.$i;
					$dba_boxId[$i] = $result->$boxidno;
					$dba_boxH[$i] = $result->$boxhno;
					
					// Query till SQL - 2, (3), (4)
					$query2 = $db->query("SELECT 
																id, 
																artno, 
																".$z."box_s".$i." AS boxs 
																FROM ks_article 
																WHERE box_id=".$dbt_boxIds."
																AND box_id".$i."=".$dba_boxId[$i]."
																ORDER BY id ASC");

					if($query2){ // Skicka frågan, vid fel kolla db.php
						if(mysqli_num_rows($query2) > 0){ // Om databasen skickade resultat, vid 0 kolla sql-frågan
						 	$k = 1;
							while($result2 = $query2 -> fetch_object()){ // Skapa arrays av posterna, 2D array!
								$dba_idBox[$i][$k] = $result2->id; // Första array är boxlvl, den andra array är boxid# typ
								$dba_artBox[$i][$k] = $result2->artno;				
								$dba_subBox[$i][$k] = $result2->boxs;
								$k++;
							}
						} else { echo 'Det har hänt något fel. (1212)'; }
					} else { echo 'Det har hänt något fel. (1213)'; }
				}
			
                if($dbt_type == '3') { // Varselmärkning
                                    
                    // Kontrollera om ID finns, detta laddar in rätt bild. (id == bild)
                    if(isset($_GET['id'])) {
                        $vm_image = $db->real_escape_string($_GET['id']);
                        $vm_id = 1;
                    }
                    else {
                        // Om id inte finns ska en default bild laddas och ska inte gå köpa default.
                        $vm_id = 0;
                    }
                    
                    $vm_artno = $db->real_escape_string($_GET['artno']);

                    /* OLD WAYS! Nostalgia
                    // Type 3 innebär varsel, ladda in rätt varsel typ, beskrivs i artno sista tecken.
                    // Obs! finns id är den i vm_image, finns id är vm_id like med 1.
                    $vm_type = substr($vm_artno,-1); // Detta plockar ut sista bokstaven i artnr (ex M, P, W osv...)

                    // Ändringen är att det finns nu en VM_ID direkt i artnr. Så inga med bokstäver
                    // Denna ID är det som den nya... så $vm_type == $dba_vm
                    */

                    // Hämta db info om alla varsel efter vm_type (ex M, W, P osv.)
                    $query3 = $db->query("SELECT name, image, grupp 
                                            FROM ks_varsel 
                                            WHERE type='".$dba_vm."' 
                                            ORDER BY id ASC");
                    
                    if ($query3) {
                        if (mysqli_num_rows($query3) > 0){ //Finns grupp ladda in alla varsel till den, annars error.
                            $i = 1;
                            while ($result3 = $query3 -> fetch_object()) {
                                $vma_name[$i] = $result3->name;
                                $vma_image[$i] = $result3->image;
                                $vma_group[$i] = $result3->grupp;
                                $i++;
                            }
                        } else { echo 'Det har hänt något fel. (1222)'; }
                    } else { echo 'Det har hänt något fel. (1223)'; }
                    
                    $vm_count = count($vma_name);
                }
            } else { echo 'Det har hänt något fel. (1110)'; }
		} else { echo 'Det har hänt något fel. (1100)'; }
	}
	else {
		// Redirect
		echo 'Redirect!';
	}


/***** Här slutar kommunikation med databas (endast variabler finns kvar) *****/	

	//******** Snippets ********//
	// Language Selection -- Start
	// Språk faller alltid till engelska om svenska inte finns någonstans (som session)
	if($LanguageCode == 'SE'){ // Svenska som default
		$LanguageSelect = 'Change language to <a href="?artno='.$dba_artno.'&language=en">English</a>';
	}
	else { // English som default
		$LanguageSelect = 'Byt språk till <a href="?artno='.$dba_artno.'&language=se">Svenska</a>';
	}
	// Language Selection -- End
    // Currency Selection -- Start
    if($CurrencyCode == 'EUR'){ // SEK som default
        $CurrencySelect = 'Byt valuta till <a href="?artno='.$dba_artno.'&currency=sek">SEK</a>';
    }
    else {
        $CurrencySelect = 'Change currency to <a href="?artno='.$dba_artno.'&currency=eur">EUR</a>';
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
<meta name="description" content="<?=substr($dba_desc,0,150);?>" />
<meta name="keywords" content>
<meta name="robots" content="index, follow">

<title><?=$dba_pageHead;?> | Kortsystem</title>
<link rel="stylesheet" href="<?=$pg_prefix;?>/styles/navigation.css?1857" type="text/css" />
<link rel="stylesheet" href="<?=$pg_prefix;?>/styles/article.css?2111" type="text/css" />
<link rel="stylesheet" href="<?=$pg_prefix;?>/styles/magnific-popup.css" type="text/css" />
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
          <a href="<?=$pg_category;?>?id=<?=$dba_crumbId;?>"><?=$dba_crumb;?></a>
          <a href="?artno=<?=$dba_artno;?>"><?=$dba_pageHead;?></a>
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

		<!-- Header för print -->
		<div class="print_logotype"></div>
<!-- Navigation.css **END** -->



<!-- Content -->
<div class="content">
  <div class="article">
      <?php
        // Header tillägg för varselmärkning
        if ($dbt_type == 3) {
            if ($vm_id == 1) {
                $vm_info = " (" . $vm_image . ")";
            }
            else {
                $vm_info = "";
            }
        }
        else {
            $vm_info = "";
        }
      ?>
    <div class="header"><?=$dba_name;?><?=$vm_info;?></div>
    <div class="description"><?=$dba_desc;?></div>
    <div class="wrapper">
    	<div class="images">
      	<div class="image_big"><?php
            
            if(isset($vm_image)){
                ?><img src="/images/vm/<?=$vm_image;?>_C.svg" alt="<?=$dba_name;?>" /><?php
            }
            else {
                ?><img src="/images/products/<?=$dba_image;?>" alt="<?=$dba_name;?>" /><?php
            }            
         ?></div>
        <?php // Här startar extra bilder delen
				if ($dbt_imageExtra != 0)
				{
					for ($i=1; $i<=$dbt_imageExtra; $i++) {
					?>
          <div class="image_small"><a href="/images/products/display/<?=$dba_artno?>(<?=$i;?>).jpg" title="<?=$dba_name;?>"><img src="/images/products/display/<?=$dba_artno?>(<?=$i;?>).jpg" alt="<?=$dba_name;?>" /></a></div>
          <?php					
					}
					?>
          <div class="image_text">&bull; <?=$lg_images;?> &bull;</div>
          <?php
				}	
				// Här slutar extra bilder delen			
				?>
      </div>
      <div class="selection">
        <div class="selection_group">
          <div class="selection_type"><?=$lg_description;?>:</div>
          <?=$dba_info;?>
        </div>
<?php
// Här startar valen för "anpassa din produkt"
	if($dbt_boxLvl != 0){
		?>
        <div id="selection_toggle" class="selection_header"><?=$lg_choice;?></div>
					<div id="selection_hide">
    <?php
		// Loop 1 - Går igenom hur många val produkten har, från 1 till max 3
		for ($i=1; $i<=$dbt_boxLvl; $i++) {
			// Skriver ut rubrik en gång innan den går igenom valen.
			?>
            <div class="selection_group">
              <div class="selection_type"><?=$i;?>. <?=$dba_boxH[$i];?></div>
              <div class="button_group">
              <?php
							// Räknar hur många val som finns som ska loopas igenom
							$for_count = count($dba_idBox[$i]);
							
							// Loop 2 - Går igenom alla val en och en genom 2D Array
							for ($k=1; $k<=$for_count;$k++){
								
								// Om id är samma som idBox (vilken är ID på den activa produkt, gör en active class.
								if($dba_idBox[$i][$k]==$dba_id){ 
                                    $a = ' active'; 
                                    
                                    // Detta variabel används endast med GSS just nu
                                    // Spara activa länk beroende på ruta efter nr (där 1 är första från vänster)
                                    // Själva i är rutan ovanför och ner där 1 är längst upp
                                    $gss_nr[$i] = $k;
                                } 
								else { $a = ''; }

								// Skriver ut raden med länk, class (om) samt namn. Börjar en rad efter för "mellanrum" i koden.
								echo '
											<a href="?artno='.$dba_artBox[$i][$k].'" class="button'.$a.'">'.$dba_subBox[$i][$k].'</a>';
							}
							?>
              </div><!-- button_group -->
            </div><!-- selection_group -->
    
      <?php			
		}
	?>
				</div><!-- selection_hide -->    
    <?php 
	} else { // Denna kod vissas om det inte finns några anpassa val i produkten.
		?>
    	<div class="selection_header"><?=$lg_noChoice;?></div>
    <?php
	}
// Här slutar valen för "anpassa ding produkt"
?>          
	  </div><!-- selection --> 
	</div><!-- wrapper -->
  
  <div class="order">
  	<div class="order_head"><?=$lg_price;?> <b><?=$dba_name;?></b></div>
		<div class="order_content"><?=$lg_total;?>:
      <span id="price"><?=$dba_price1f;?></span> (<?=$CurrencyCode;?>)
      <?php
            // Kollar om Varsel id finns
            // Om ej finns så är det inte varsel, finns så är det varsel. 0 är ingen skylt vald, 1 är skylt vald och går att köpa.
            $vm_link = "";
            
            if (isset($vm_id)) {
                if ($vm_id == 0 && $dbt_type == '3') {
                    // Stäng av köp knapp och skylten är inte vald än.
                    $dbt_buyable = 0;
                    // Injektion av lg_disableCart från "Ej tillgängligt" till "Välj produkt nedan"
                    $lg_disableCart = "Välj ". $dba_crumb ." nedan";
                }
                elseif ($vm_id == 1 && $dbt_type == '3') {
                    $vm_link = "&type=" . $vm_image;
                }
            }

        /*  Här börjar kontroll om vilka som ska visas när man köper eller beställer prover.

        Finns 3 alternativ för köp knapp, 2 kommer från databas, 3e kommer från session.
        - om dbt_buyable är 1 så är knapp aktiv
        - om dbt_buyable är 0 så är knapp inaktiv
        - om artnr finns i session som prov aktiv är knapp inaktiv

        Det finns 4 alternativ för prov knapp, 3 kommer från databas, 4e kommer från session.
        - om dbt_sample är 2 så är knapp aktiv plus extra content om GE/FMP
        - om dbt_sample är 1 så är knapp aktiv
        - om dbt_sample är 0 så är knapp inaktiv
        - om artnr finns i session som prov ska man kunna "avaktivera prov"


        // Ta redan på vad som finns att göra (main selector)
        for (loopa igenom kundvagn session och matcha art)
        - vid matchning, ta reda på om det är ett prov.
        - sessionMatch = true // om artnr finns, då finns ju produkt i kundvagn
        - sessionSample = true // om sant innebär att produkt i kundvagn är prov, buyable blir disabled då

        if (kolla session om artnr finns OCH om prov tag finns.)
            - Då ska dbt_buyable = 0 //Ska alltså avaktivera, går ej att köpa produkt
            - dbt_sample = 4 //Innebär att prov finns kundvagn, 4 = länk för avboka prov.

        om if inte gick igenom innebär att det finns ingen prov i kundvagn, kanske artnr, man då faller allt som vanlig för buyable.
        


        Här kommer processen om hur detta ska fungera.
        ändrigar so far:
        sample kolumn i excel or databas
        last lg sample på se och en language.
        lagt in så sample koden läses från databas, sparas i $dbt_sample.
        0 = disable, 1 = vanlig prov, 2 = special prov (ge, fmp osv), 3 = finns i kundvagn

        lagt in en knapp... måste öka funktionalitet.
        i cart måste kunna läsa in om det är prov eller produkt
        lägga in i session sen tillbaka hit
        här måste vi läsa in om prov redan finns i kundvagn, lägg in en ta bort knapp,
        vid klick ska man skickas till en redirect och ta bort prov från kundvagn.

      */
      // Går igenom kundvagn om det finns, hitta artnr och prover i den.
      if(isset($_SESSION['article'])) { // Kolla om kundvagn finns
        $sCart = $_SESSION['article']; // Spara kundvagn i sCart (sampleCart)
        $sCartCount = count($sCart); // Räkna antalet artiklar
        
        for($i=1; $i<=$sCartCount; $i++) { // Loopa igenom alla artiklar
          $s=$i-1; // Rätta till array (börjar på 0 ju)
          $sArtno = $sCart[$s][1]; // Array 1 är artnr unik (som i databasen)
          $sSample = $sCart[$s][21]; // Array 21 är prov, 0 = inte prov, 1 = är prov

          if ($sArtno == $dba_artno) { // Artnr matchar nu varande artikel

            if ($sSample == 1) { // Prov finns till denna artikelnr
              $dbt_buyable = 2; // Inaktivera "köp" knapp
              $dbt_sample = 3; // Aktiva "ta bort prov" knapp
            }
            else { // artnr finns men prov fanns inte
              $dbt_sample = 0; // Om produkt finns i kundvagn, går inte beställa prov också
            }
            break; // Avsluta loop, då artnr hittad, finns bara 1 UNIK artnr i sessionen. Behöver inte loopa allt.
          }
        }
        // Notera om kundvagn är tom eller produkt inte finns, ska dbt_buyable och dbt_sample inte ersättas från databas.
      }

      // Här startar om köp knappen ska vara aktiv eller inte.
      if ($dbt_buyable == 2){ // 1 tillåts köpa, 0 & 2 = disabled - bara text skiljer åt.
        ?>
        <a id="disableCart" href="#"><?=$lg_disableCart2;?></a>
        <?php
      }
			elseif ($dbt_buyable == 1){
				?>
      	<a id="addCart" href="cart.php?action=add&artno=<?=$dba_artno;?>&code=<?=$tm_randomID;?>&amount=1<?=$vm_link;?>"><?=$lg_addCart;?></a>
        <?php
			} else {
				?>
        <a id="disableCart" href="#"><?=$lg_disableCart;?></a>
        <?php
			}
			// Här slutar om köp knappen ska vara aktiv eller inte.
      
      // Här börjar Prov beställningsknappen och dess form
      if($dbt_sample == 3){ // Prov finns, knapp "Tar bort prov"
        ?>
        <a id="removeSample" href="redirect.php?action=remsam&artno=<?=$dba_artno;?>&code=<?=$tm_randomID;?>"><?=$lg_removeSample;?></a>
        <?php
      } elseif ($dbt_sample == 2) { // Lägg till special prov
        ?>
        <a id="addSample" href="cart.php?action=sample&artno=<?=$dba_artno;?>&code=<?=$tm_randomID;?>&amount=1"><?=$lg_addSample;?></a>
        <?=$lg_textSample;?>
        <?php
      } elseif ($dbt_sample == 1) { // Lägg till prov (normal)
        ?>
        <a id="addSample" href="cart.php?action=sample&artno=<?=$dba_artno;?>&code=<?=$tm_randomID;?>&amount=1"><?=$lg_addSample;?></a>
        <?php
      } else { // Allt annat visar inaktiv ej tillgänglig knapp (inkl. 0)
        ?>
        <a id="disableSample" href="#"><?=$lg_disableSample;?></a>
        <?php
      }
      // Här slutar Prov beställningsknappen och dess form

			// Här startar om man får välja antal eller annan avancerad situation
			if ($dbt_type == 1) { // 1 = T-tecken
			?>
        	   <div>
                   <?=$amount_n;?>: <span id="amountAuto">0</span>
                </div>
            <?php	
			}
            elseif ($dbt_type == 2) { // 2 = GSS
			?>
        	   <div>
                   <?=$amount_n;?>: <span id="amountAuto">1</span>
                </div>
            <?php 
            }
			else { // 0 = Normal (allt icke special)
				?>
          <div>
            <?=$amount_n;?>:
            <input type="text" value="1" onkeyup="itemAmount(this.value);" onblur="checkValue(this.value);" id="amountCart" autocomplete="off" />
          </div>
        <?php
			}				
			// Här slutar om man får välja antal eller annan avancerad situation
			?>
    </div><!-- order_content -->
    <div class="order_discount"><?=$lg_discount;?> (<?=$dba_amount;?>)</div>
    <div class="batch-amount">
      <div><?=$price_n[0];?></div>
      <div><?=$price_n[1];?></div>
      <div><?=$price_n[2];?></div>
      <div><?=$price_n[3];?></div>
      <div><?=$price_n[4];?></div>
    </div><!-- batch-amount -->
    <div class="progress">
      <div class="progressbar" style="width:<?php echo 20/$price_v[1]; ?>%"></div>
    </div>
    <div class="batch-price">
      <div><?=$dba_price1f;?></div>
      <div><?=$dba_price2f;?></div>
      <div><?=$dba_price3f;?></div>
      <div><?=$dba_price4f;?></div>
      <div><?=$dba_price5f;?></div>
    </div><!-- batch-price -->
    
    <?php
        // Här startar T-tecken val! Visas endast om Type är 1
        if ($dbt_type == 1) {
        ?>
    <div class="order_special"><?=$lg_chooseAmount;?> <b><?=$dba_name;?></b> (<?=$dba_amount;?>)</div>
    <div class="t_selection">
    	<ul><li
      			><div>0</div><div
            ><input type="text" id="10" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>1</div><div
            ><input type="text" id="11" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>2</div><div
            ><input type="text" id="12" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>3</div><div
            ><input type="text" id="13" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>4</div><div
            ><input type="text" id="14" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>5</div><div
            ><input type="text" id="15" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>6</div><div
            ><input type="text" id="16" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>7</div><div
            ><input type="text" id="17" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>8</div><div
            ><input type="text" id="18" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>9</div><div
            ><input type="text" id="19" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>-</div><div
            ><input type="text" id="20" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li></ul>
    	<ul><li
      			><div>A</div><div
            ><input type="text" id="21" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>B</div><div
            ><input type="text" id="22" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>C</div><div
            ><input type="text" id="23" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>D</div><div
            ><input type="text" id="24" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>E</div><div
            ><input type="text" id="25" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>F</div><div
            ><input type="text" id="26" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>G</div><div
            ><input type="text" id="27" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>H</div><div
            ><input type="text" id="28" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>I</div><div
            ><input type="text" id="29" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>J</div><div
            ><input type="text" id="30" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>K</div><div
            ><input type="text" id="31" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li></ul>
    	<ul><li
      			><div>L</div><div
            ><input type="text" id="32" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>M</div><div
            ><input type="text" id="33" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>N</div><div
            ><input type="text" id="34" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>O</div><div
            ><input type="text" id="35" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>P</div><div
            ><input type="text" id="36" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>Q</div><div
            ><input type="text" id="37" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>R</div><div
            ><input type="text" id="38" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>S</div><div
            ><input type="text" id="39" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>T</div><div
            ><input type="text" id="40" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>U</div><div
            ><input type="text" id="41" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>V</div><div
            ><input type="text" id="42" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li></ul>
    	<ul><li
      			><div>W</div><div
            ><input type="text" id="43" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>X</div><div
            ><input type="text" id="44" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>Y</div><div
            ><input type="text" id="45" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>Z</div><div
            ><input type="text" id="46" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>&Aring;</div><div
            ><input type="text" id="47" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>&Auml;</div><div
            ><input type="text" id="48" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>&Ouml;</div><div
            ><input type="text" id="49" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>&AElig;</div><div
            ><input type="text" id="50" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>&Oslash;</div><div
            ><input type="text" id="51" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div>.</div><div
            ><input type="text" id="52" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li><li
            ><div class="arrow">&nbsp;</div><div
            ><input type="text" id="53" placeholder="0" autocomplete="off" 
            		onkeyup="sheetAmount(this.value,this.id);" 
                onblur="sheetValue(this.value,this.id);" /></div></li></ul>
    </div><!-- t-selection -->
    <?php
        } // Avslutar T-tecken val (visas endast när type = 1)
        elseif ($dbt_type == 2) {
            // Type 2 är GSS
            $optZero = $optTwo = $optThree = $optFour = '';

            if ($gss_nr[2] == 1) {
                $gss_class = "small";
                $gss_digits = "01";
                $gss_zeros = "0";
                $gss_maxLength = 2;
                $optTwo = ' selected="selected"';
            }
            elseif ($gss_nr[2] == 2) {
                $gss_class = "medium";
                $gss_digits = "001";
                $gss_zeros = "00";
                $gss_maxLength = 3;
                $optThree = ' selected="selected"';
            }
            else {
                $gss_class = "large";
                $gss_digits = "0001";
                $gss_zeros = "000";
                $gss_maxLength = 4;
                $optFour = ' selected="selected"';
            }
            
            if ($gss_nr[1] == 1) {
                $gss_endDigits = substr($gss_digits,0,-2) . "10";
                $gss_sheet = 10;
            }
            elseif ($gss_nr[1] == 2) {
                $gss_endDigits = substr($gss_digits,0,-1) . "6";
                $gss_sheet = 6;
            }
            else {
                $gss_endDigits = substr($gss_digits,0,-1) . "4"; 
                $gss_sheet = 4;
            }
            
 
        ?>

      <div class="order_special"><?=$lg_createSerial;?> <b><?=$dba_name;?></b> (<?=$dba_amount;?>)</div>
      <div class="gss">
          <div class="preview"><div class="<?=$gss_class;?>" id="intPreview"><?=$gss_digits;?></div></div>
          <div class="divition"></div>
          <div class="serial"><?=$lg_startSerial;?>  
              <input type="text" id="firstValue" value="<?=$gss_digits;?>" maxlength="<?=$gss_maxLength;?>" />
               <?=$lg_endSerial;?>
              <input type="text" id="lastValue" value="<?=$gss_endDigits;?>" maxlength="<?=$gss_maxLength;?>" />*
          </div>
          <div class="divition"></div>
          <div class="jumps">
              <div>
                  <?=$lg_intervalSerial;?>:
              </div>
              <div>
                  <span class="ex" id="intSerie1"><?=$gss_zeros;?>1</span>
                  <span class="ex" id="intSerie2"><?=$gss_zeros;?>2</span>
                  <span class="ex" id="intSerie3"><?=$gss_zeros;?>3</span>
                  <span class="ex" id="intSerie4"><?=$gss_zeros;?>4</span>
                  <span class="ex" id="intSerie5"><?=$gss_zeros;?>5</span>
                  <span class="ex">...</span>
              </div>
              <div>
                  <label>
                      <input type="radio" name="skip" id="serial" checked="checked" /><span class="radio"></span> 
                      <span class="tx"><?=$lg_eachSerial;?></span>
                  </label>
                  <label>
                      <input type="radio" name="skip" id="pair" /><span class="radio"></span> 
                      <span class="tx"><?=$lg_otherSerial;?></span>
                  </label>
                  <div style="height:8px;"></div>
                  <label>
                      <input type="radio" name="skip" id="decimal" /><span class="radio"></span> 
                      <span class="tx"><?=$lg_tenthSerial;?></span>
                  </label>
                  <label>
                      <input type="radio" name="skip" id="own"/><span class="radio"></span>
                      <span class="tx"><input type="text" id="skipOwn" value="1" /></span>
                  </label>
              </div>
          </div>
          <div class="divition"></div>
          <div class="zeros">
              <?=$lg_amountZero;?>: 
              <select id="selectZeros">
                  <option value="1" <?=$optZero;?>><?=$lg_none;?></option>
                  <option value="2" <?=$optTwo;?>><?=$lg_two;?></option>
                  <?php
                    if (strlen($optThree) > 0) {
                        ?>
                            <option value="3" <?=$optThree;?>><?=$lg_three;?></option>
                        <?php
                    }
                    elseif (strlen($optFour) > 0) {
                        ?>
                            <option value="3" <?=$optThree;?>><?=$lg_three;?></option>
                            <option value="4" <?=$optFour;?>><?=$lg_four;?></option>
                        <?php
                    }
                        
            
                  ?>
              </select>
          </div>
          <div class="divition"></div>
          <div class="repeater"><?=$lg_repeatSerial;?> <input type="text" id="repeater" value="1" /> <?=$lg_amountMultiplied;?></div>
          <div class="divition"></div>
          <div class="colour"><?=$lg_selectBackground;?>:
            <select id="selectColour">
                <option value="1" selected="selected"><?=$lg_yellow;?></option>
                <option value="2"><?=$lg_white;?></option>
            </select>
          </div>
          <div class="divition"></div>
          <div class="mntxt">&bull; <?=$lg_serialWarningOne;?><br />
              &bull; <?=$lg_serialWarningTwo;?></div>
      </div>
      
      
        <?php
        } // Avslutar GSS-tecken val (visas endast när type = 2)
        elseif ($dbt_type == 3) { // Varselmäkrning
        ?>
      
          <div class="order_special">Välj din <b><?=$dba_crumb;?></b>:</div>
          <div class="varsel">
              <?php
            
                // Denna loop går igenom alla varsel som har laddats där uppe
                // och skriver ut dom lista för lista.
                // Obs! att rubrik delen är inte skapad än.
                for ($i=1; $i<=$vm_count; $i++) {
                    
                    $vm_box = "vm_box";
                    $vm_group = "vm_group_".$vma_group[$i];

                    if ($vm_id == 1) {
                        if ($vm_image == $vma_name[$i]) {
                            $vm_box = "vm_box selected";
                        }
                    }
                    

                    
                    
                    ?>
                        <div class="<?=$vm_box;?> <?=$vm_group;?>">
                            <a href="?artno=<?=$vm_artno;?>&id=<?=$vma_name[$i];?>">
                                <div><img src="/images/vm/<?=$vma_image[$i];?>" /></div>
                                <div><?=$vma_name[$i];?></div>
                            </a>
                        </div>
                    <?php                    
                }
              ?>
          </div>
      
        <?php
        } // Avslutar Varselmärkning val (visas endast när type = 3)
    ?>      
    </div><!-- order -->
  </div><!-- article -->
</div><!-- content -->

<!-- Footers -->
<!-- Footer for print -->
<div class="print_footer">
  <div class="company">Kortsystem i Gislaved AB</div>
  <div class="contact">0371-22 24 44 &bull; info@kortsystem.se</div>  
</div>
<!-- Footer for desktop and mobile -->
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
<script src="../scripts/jquery.magnific-popup.min.js" type="text/javascript"></script>
<script src="../scripts/jquery.svgmagic.js" type="text/javascript"></script>
<script>
<?php
	// Olika javascript / jquery beroende på produkt
	if ($dbt_type == 1) { // 1 = T-tecken
		?>
	
	/**** T-tecken Jquery Kod **Start** ****/
	$(document).ready(function() {
        // Nollställ allt pga T-tecken startar från 0 och inte 1.
		$("#price").html('0,00'); // 0 kartor = 0,00 kr
		$('#addCart').attr('href','#'); // Loopar länk tillbaka (kommer inte vidare till kundvagn)
        $('#addCart').css('background-color','#900'); // Vid 0 är knapp röd
        $('#addCart').html('<?=$lg_noTecken;?>'); // Vid 0 vissas Inga tecken (språk oberoende)
        $(".progressbar").css({ "width": 0,"background-color":"#f63a0f"}); // 0 på progressbar
	});
  
    /* T-tecken genomräkning */
    function sheetCount(){
        // Skapa huvud variabler
        var totItems = 0; // Total antal kartor
        var totTypes = ''; // Vilka typer som ska beställas
        var priceHTML;
        var discGroup = [0,9,49,99,199]; // T-tecken
        var proBar = [1,20,40,60,80]; // Var är procentlinjer?
        var colorBar = ['#f63a0f','#f27011','#f2b01e','#f2d31b','#86e01e']; // Progressbar färger Rött->Grönt
        var priceGroup = [<?=$dba_price1;?>,<?=$dba_price2;?>,<?=$dba_price3;?>,<?=$dba_price4;?>,<?=$dba_price5;?>]; // db priser
        var priceValue = [<?=$price_v[0];?>,<?=$price_v[1];?>,<?=$price_v[2];?>,<?=$price_v[3];?>,<?=$price_v[4];?>]; // likna discgr
        var calBar = [<?php
        
            $array01 = 20 / ($price_v[1] + 1);
            $array02 = 20 / (($price_v[2] - $price_v[1]) + 1);
            $array03 = 20 / (($price_v[3] - $price_v[2]) + 1);
            $array04 = 20 / (($price_v[4] - $price_v[3]) + 1);
            $array05 = 20 / $price_v[4];
        
            echo $array01.','.$array02.','.$array03.','.$array04.','.$array05;
            ?>]; // Procent enhet för progressbar
        
        // Loopa igenom alla T-tecken efter förändringar
        for(var i=10; i<=53; i++) { // For-loop igenom 10 till 53.
            var idValue = parseInt($("#"+i).val()); // Sparar nummerdata
            if(idValue > 0){ // Är nummer större än 1
                totItems = totItems+idValue; // Plusa för total antal
                totTypes = totTypes + i + idValue + '-'; // Bygg upp Type sträng för köp
            }
		}
        
        // Mini loop för att räkna ut vilken rabatt grupp.
		for(var i=4; i>=0; i--) { // For-loop som faller från 4 till 0
            if(totItems > discGroup[i]){
                priceHTML = totItems * priceGroup[i]; // Räkna ut priset Oformaterad
                
                // Progressbar ritning.
                var newBar = totItems - priceValue[i]; // Mellan skillnad efter rabattantal
                newBar = newBar * calBar[i]; // Skillnaden gånger procentenhet för att fylla hela 19% (ej 20)
                newBar = newBar + proBar[i]; // Vilken nivå i rabatt man är, ex om man redan är på 80%
                if(newBar > 99){ newBar = 100; } // Progressbar får inte överstiga 100%
                newBar = newBar + '%'; // Lägg in % efter siffran
                $(".progressbar").css({ "width": newBar,"background-color":colorBar[i]}); // Animera progressbar
                
                break; // Avsluta loop vid träff
            }
		}
        
        // 0 kontroll, ändrar knapp och belopp
        if(totItems > 0){ // Om det finns kartor att köpa
            $('#addCart').css('background-color','#004fa2'); // Standar blå köp knapp
            $('#addCart').html('<?=$lg_addCart;?>'); // Lägg till kundvagn (språk oberoende)
            $('#addCart').attr('href', 'cart.php?action=add&artno=<?=$dba_artno;?>&code=<?=$tm_randomID;?>&amount='+totItems+'&type='+totTypes); // Fyller knapp med länk info
            $("#price").html(priceHTML.toLocaleString('sv-SV',{minimumFractionDigits:2})); // Totalt belopp (formaterad)
        }
        else { // Nollställ vid 0
            $('#addCart').attr('href','#'); // tillåten inte lägga till kundvagn
            $('#addCart').css('background-color','#900'); // Vid 0 är knapp röd
            $('#addCart').html('<?=$lg_noTecken;?>'); // Vid 0 vissas Inga tecken (språk oberoende)
            $("#price").html('0,00'); // Vid 0 är det 0,00
            $(".progressbar").css({ "width": 0,"background-color":"#f63a0f"});
        }
        
        $("#amountAuto").html(totItems); // Visa totalt antal kartor
    }
  
	/* T-tecken kontroll 2 - När ruta förlorar fokus */
	function sheetValue(valueString,idCode){
		if(valueString.length > 0 && valueString > 0){ // Ej tom eller inte siffran noll.
			$("#"+idCode).attr('class','green'); // Får css class green
		//	$("#"+idCode).css('border-color','#76c048');
		//	$("#"+idCode).css('background-color','#ebfde8');
		}
		else { // Vid 0 eller text
			$("#"+idCode).val(''); // Rensa rutan från innehåll
			$("#"+idCode).removeAttr('class'); // Tabort class ifall den finns.
		}
        
        sheetCount(); // Räknar upp vad som finns.
	}

    /* T-tecken kontroll 1 - Efter tangent tryck */
    function sheetAmount(valueString,idCode){
        sheetValue(valueString, idCode); // Kontroll är samma som sheetValue.
    }    
    

    <?php	
	}
    elseif ($dbt_type == 2) { // GSS
        /* Eftersom GSS inte är vanlig produkt och kräver mer information så är det enklare att köra
            kontrollen direkt när sidan laddar så körs den korrekt, istället för att dubblera jobbet
            och skapa en dummy först sen ladda. */
    ?>
	
    /**** GSS Jquery Kod **Start** ****/
    $(document).ready(function() {
        gssComplete();
    });    
    
    // GSS Kontroller (de flesta kör gssComplete funktionen)
    $("#skipOwn").click(function(){ $("#own").prop("checked", true); });
    $("#serial").click(function(){ $("#skipOwn").val(1); gssComplete(); });
    $("#pair").click(function(){ $("#skipOwn").val(2); gssComplete(); });
    $("#decimal").click(function(){ $("#skipOwn").val(10); gssComplete(); });
    $("#skipOwn").keyup(function(){ gssComplete(); });
    $("#firstValue").blur(function(){ gssComplete(); });
    $("#lastValue").blur(function(){ gssComplete(); });
    $('#selectZeros').change(function(){ gssComplete(); });
    $('#selectColour').change(function(){ gssComplete(); });
    $('#repeater').keyup(function(){ gssComplete(); });
    $("#repeater").blur(function(){ 
        
        if($("#repeater").val() < 1) {
            $("#repeater").val(1)
        }
        gssComplete(); 
    });

    // Auto-systemet för att välja serie, räknar ut kartor och antal
    function gssComplete(){
        // Ta in data värden
        var inZero = parseInt($('#selectZeros').val());
        var inStart = parseInt($("#firstValue").val());
        var inEnd = parseInt($("#lastValue").val());
        var inSerie = parseInt($("#skipOwn").val());
        var inRepeat = parseInt($("#repeater").val());
        var inColour = parseInt($("#selectColour").val());
        var inSheet = <?=$gss_sheet;?>;
        
        // Extra kontroller
        if (inRepeat < 1 || isNaN(inRepeat)) {
            inRepeat = 1;
        }
        
        if (inSerie < 0 || isNaN(inSerie)) {
            inSerie = 0;
        }
        
        // Beräkna hur många nollor.
        var arZero = [0,1,10,100,1000];
        var vrZero = ["","0","00","000"];
        var lmZero = arZero[inZero];
            
        // Skapar mini-preview för serie (5 st)
        var iSt = inStart;
        var iSe = inSerie;
        var arValue = [iSt,iSt+iSe,iSt+(iSe*2),iSt+(iSe*3),iSt+(iSe*4)];        
        var arSerie = [0,0,0,0,0];
        
        for(var i=0; i<=4; i++) { // Loop - tar fram antal 0 före siffror
            if (arValue[i] < lmZero) {
                var sumLen = lmZero.toString().length - arValue[i].toString().length;
                arSerie[i] = vrZero[sumLen]+arValue[i];
            }
            else {
                arSerie[i] = arValue[i];
            }
        }
        
        // Visa preview och första värde
        $("#intPreview").html(arSerie[0]);
        $("#firstValue").val(arSerie[0]);
        
        // Visa mini-preview serien
        for(var i=0; i<=4; i++) {
            $("#intSerie"+[i+1]).html(arSerie[i]);
        }
        
        // Ändra sista värde till samma som första och serie-hopp är 0
        if (inSerie == 0) {
            inEnd = inStart;
            $("#lastValue").val(arSerie[0]);
        }
        
        // Räkna antal kartor och sista siffran 
        var sumDif = inEnd - inStart;
        var sumSheet = 1;
        var ltValue = 0;

        //-- Slut av serien får ej negativ tal.
        if (sumDif < 0) {
            sumDif = 1;
        }
        
        //-- Finns endast två möjligheter 0 eller större än 0
        if (sumDif > 0) {

            // Räkna total antal kartor (avrundar upp)
            sumSheet = (sumDif+1) / inSerie;
            sumSheet = Math.ceil(sumSheet / inSheet);
            
            // Räkna sista värdet som blir på hela kartor
            ltValue = sumSheet * inSheet;
            ltValue = ltValue * inSerie;
            ltValue = ltValue + (inStart-inSerie);
            
            // Räkna antal 0 som behövs före siffran
            if (ltValue < lmZero) {
                var ltLen = lmZero.toString().length - ltValue.toString().length;
                ltValue = vrZero[ltLen]+ltValue;
            }
            
            // Visa sista värdet
            $("#lastValue").val(ltValue);
            
            // Räkna total antal kartor inkl. repeater
            sumSheet = sumSheet * inRepeat;
        }
        else if (sumDif == 0){
            
            // Vid enskild tecken räknar vi enbart kartor (ändrar sista värdet till samma)
            sumSheet = Math.ceil(inRepeat / inSheet);
            ltValue = arSerie[0]; // ltValue måste för kundvagn
            $("#lastValue").val(ltValue);
        }
     
        // Visa total antal kartor
        $("#amountAuto").html(sumSheet);
        
        // Anropa prisräkning för antal kartor
        gssPrice(sumSheet);
        
        // Bygg information för kundvagn
        /*
            - artnr - done
            - antal - done
            vad?
            - start
            - slut
            - serie
            - nollor
            - repeat
            - färg
            
            1-4-1-4-1-1
            
            23-421-2-2-2-2
        */
        
        
        
        var sendType = arSerie[0]+'-'+ltValue+'-'+inSerie+'-'+inZero+'-'+inRepeat+'-'+inColour;
        
        // Ändrar Lägg till kundvagn knapp
        $('#addCart').attr(
            'href', 
            'cart.php?'+
            'action=add&'+
            'artno=<?=$dba_artno;?>&'+
            'code=<?=$tm_randomID;?>&'+
            'amount='+sumSheet+'&'+
            'type='+sendType); 
    }
    
    function gssPrice(sheet){
        // Ta in eller skapa variabler
        var inSheet = sheet;
        var outPrice;
        var discGroup = [0,9,49,99,199]; // GSS rabatt nivåer
        var proBar = [1,20,40,60,80]; // Var är procentlinjer?
        var colorBar = ['#f63a0f','#f27011','#f2b01e','#f2d31b','#86e01e']; // Färger för mängdrabatt
        var priceGroup = [<?=$dba_price1;?>,<?=$dba_price2;?>,<?=$dba_price3;?>,<?=$dba_price4;?>,<?=$dba_price5;?>]; // db priser
        var priceValue = [<?=$price_v[0];?>,<?=$price_v[1];?>,<?=$price_v[2];?>,<?=$price_v[3];?>,<?=$price_v[4];?>]; // discgroup
        var calBar = [<?php
        
            $array01 = 20 / ($price_v[1] + 1);
            $array02 = 20 / (($price_v[2] - $price_v[1]) + 1);
            $array03 = 20 / (($price_v[3] - $price_v[2]) + 1);
            $array04 = 20 / (($price_v[4] - $price_v[3]) + 1);
            $array05 = 20 / $price_v[4];
        
            echo $array01.','.$array02.','.$array03.','.$array04.','.$array05;
            ?>]; // Procent enhet för progressbar

        // Mini loop för att räkna ut vilken rabatt grupp (Kolla t-tecken för kommentar)
		for(var i=4; i>=0; i--) {
            if(inSheet > discGroup[i]){
                outPrice = inSheet * priceGroup[i]; // Räkna ut priset (oformaterad)
                
                // Progressbar ritning.
                var newBar = inSheet - priceValue[i];
                newBar = newBar * calBar[i];
                newBar = newBar + proBar[i];
                if(newBar > 99){ newBar = 100; }
                newBar = newBar + '%';
                $(".progressbar").css({ "width": newBar,"background-color":colorBar[i]});
                
                break;
            }
		}
        
        $("#price").html(outPrice.toLocaleString('sv-SV',{minimumFractionDigits:2})); // Totalt belopp (formaterad)
    }
    
    /**** GSS Jquery Kod **End** ****/
    <?php          
    
    }
	else { // 0 = normal/allt andra produkter
		?>
	// 5 grupper delat 20% var
	function itemAmount(inputInt) {
		
		if(inputInt > <?=$price_v[4];?>){
			// Fixar Totala belopp
			intPrice = <?=$dba_price5;?> * inputInt;
			$("#price").html(intPrice.toLocaleString('sv-SV',{minimumFractionDigits:2}));
			
			// Fyller Progress bar 80-100% [50+(20/50 = 0.4%) gäller grupp 1]
			calValue = 20/<?=$price_v[4];?>;
			newInt = inputInt - <?=$price_v[4];?>;
			newInt = newInt * calValue;
			newInt = newInt + 80;
			
			// Om det är mer än 100% blir det 100%.
			if(newInt > 99){ newInt = 100;	}
			
			newInt = newInt + '%'; // Ritar själva progress
			$(".progressbar").css({ "width": newInt,"background-color":"#86e01e"});
			
			if(inputInt > 0){ // Ändrar order knapp
				$('#addCart').attr('href', 'cart.php?action=add&artno=<?=$dba_artno;?>&code=<?=$tm_randomID;?>&amount='+inputInt+'<?=$vm_link;?>');
			}
		}
		else if(inputInt > <?=$price_v[3];?>)
		{
			intPrice =<?=$dba_price4;?> * inputInt;
			$("#price").html(intPrice.toLocaleString('sv-SV',{minimumFractionDigits:2}));
			
			// 20-49(20/30 = 0.666%) gäller grupp 1
			calValue = <?php echo $price_v[4] - $price_v[3]; ?>;
			calValue = 20/calValue;
			newInt = inputInt - <?=$price_v[3];?>;
			newInt = newInt * calValue;
			newInt = newInt + 60;
			newInt = newInt + '%';
			$(".progressbar").css({ "width": newInt,"background-color":"#f2d31b"});
			
		  if(inputInt > 0){
				$('#addCart').attr('href', 'cart.php?action=add&artno=<?=$dba_artno;?>&code=<?=$tm_randomID;?>&amount='+inputInt+'<?=$vm_link;?>');
			}
		}
		else if(inputInt > <?=$price_v[2];?>)
		{
			intPrice = <?=$dba_price3;?> * inputInt;
			$("#price").html(intPrice.toLocaleString('sv-SV',{minimumFractionDigits:2}));
			
			// 10-19(20/10 = 2%) gäller grupp 1
			calValue = <?php echo $price_v[3] - $price_v[2]; ?>;
			calValue = 20/calValue; 
			newInt = inputInt - <?=$price_v[2];?>;
			newInt = newInt * calValue;
			newInt = newInt + 40;
			newInt = newInt + '%';
			$(".progressbar").css({ "width": newInt,"background-color":"#f2b01e"});
			
		  if(inputInt > 0){
				$('#addCart').attr('href', 'cart.php?action=add&artno=<?=$dba_artno;?>&code=<?=$tm_randomID;?>&amount='+inputInt+'<?=$vm_link;?>');
			}
		}
		else if(inputInt > <?=$price_v[1];?>)
		{
			intPrice = <?=$dba_price2;?> * inputInt;
			$("#price").html(intPrice.toLocaleString('sv-SV',{minimumFractionDigits:2}));
			
			// 4-9(20/6 = 3.333%) (räknat på pris grupp 1)
			calValue = <?php echo $price_v[2] - $price_v[1]; ?>; // 9-3 = 6
			calValue = 20/calValue; 
			newInt = inputInt - <?=$price_v[1];?>;
			newInt = newInt * calValue; // 20/6 = 3.333%
			newInt = newInt + 20;
			newInt = newInt + '%';
			$(".progressbar").css({ "width": newInt,"background-color":"#f27011"});
			
		  if(inputInt > 0){
				$('#addCart').attr('href', 'cart.php?action=add&artno=<?=$dba_artno;?>&code=<?=$tm_randomID;?>&amount='+inputInt+'<?=$vm_link;?>');
			}
		}
		else if(inputInt > <?=$price_v[0];?>)
		{
			intPrice = <?=$dba_price1;?> * inputInt;
			$("#price").html(intPrice.toLocaleString('sv-SV',{minimumFractionDigits:2}));
			
			// 1-3 (20/3 = 6.666%) gäller grupp 1
			calValue = 20/<?=$price_v[1];?>;
			newInt = inputInt * calValue;
			newInt = newInt + '%';
			$(".progressbar").css({ "width": newInt,"background-color":"#f63a0f"});
			
		  if(inputInt > 0){
				$('#addCart').attr('href', 'cart.php?action=add&artno=<?=$dba_artno;?>&code=<?=$tm_randomID;?>&amount='+inputInt+'<?=$vm_link;?>');
			}
		}
		else if(inputInt == 0)
		{
			//$("#cartAmount").val(1); // Depri - en annan function har tagit över detta.
		}
	}
	
	// När antal-box tappar focus kontrollera om det är en accepterad antal
	function checkValue(inputString){
		// Slår sant på text, 0, comma osv.
		if(!(inputString > 0)) {
			// Visa en alert gör att sidan inte skickas vidare. Nollställ allt till 1.
			alert('<?=$lg_cartAlert;?>');
			$("#amountCart").val(1);
			$('#addCart').attr('href', 'cart.php?action=add&artno=<?=$dba_artno;?>&code=<?=$tm_randomID;?>&amount=1');
			itemAmount(1); // Ställer om progressbar
		}
	}	
		<?php
	} // Här slutar javascrip/jquery för vanliga produkter (aka 0)
?>	
	
	
	// ~~~~ Image zoom (start)
	$(document).ready(function() {
	$('.image_small').magnificPopup({
		delegate: 'a',
		type: 'image',
		closeOnContentClick: false,
		closeBtnInside: false,
		mainClass: 'mfp-with-zoom mfp-img-mobile',
		image: {
							verticalFit: true,
							titleSrc: function(item) {
								return item.el.attr('title');
							}
					 },
		 gallery: {
		 	 enabled: true
		 },
		 zoom: {
		   enabled: true,
		   duration: 300, // Glöm inte ändra detta i css också!
		   opener: function(element) {
		 	   return element.find('img');
		   }
		 }
	  });
  });	
	// ~~~~ Image zoom (slut)

	// Visar/Gömmer "Anpassa din produkt"
	$(document).ready(function(){
		$("#selection_toggle").click(function(){
			$("#selection_hide").toggle();
		});
	});

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
			$(".footer").css('margin-top',-30+(docHeight-footerTop)+'px'); // Beräknar mellan skillnaden (innan var 18, nu -30)
		}
    });
    
    function footCookie(){
      alert('<?=$lg_cookiesMessage;?>');
    }
  
	//~~~~ Footer **END** ~~~~//
</script>
</html>
