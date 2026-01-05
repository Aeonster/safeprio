<?php
	// Skapa en koppling till databas
	// Localhost
	$db = new mysqli("localhost","root","","kortsystem_se");
	
	// Kortsystem.se (Loopia 2020~)
	//$db = new mysqli("mysql683.loopia.se","ks_user@k266201","df65voh1","kortsystem_se");

	// Kortsystem.se (Telia ~2019)
	//$db = new mysqli("kortsystem-108030.mysql.webhosting.telia.com","108030_ug80700","F1reIc32684","108030-kortsystem");
	
	// Telia vs. Localhost
	/*
		Telia är nojig när det gäller UTF8
		För att undvika ÅÄÖ problem samt databas problem måste du
		innan du flyttar över filer till telia.
		1. Kommentera bort Localhost raden och avkommentera Kortsyste.se här uppe.
		2. dbs.php -> Gör samma som 1, men du måste också kommentera bort de 2 queries på botten.
		3. search.php (includes) -> Rad 35 cirka, avkommentera och kommentera bort för telia vs localhost.
			 I Telia raden ska "utf8_decode" finnas med. Det ska inte vara med i localhost raden.
	*/

	mysqli_query($db, "SET NAMES 'utf8'"); 
	mysqli_query($db, "SET CHARACTER SET 'utf8'");
?>