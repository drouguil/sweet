<?php

	ob_start();

	include("header.php");

	$buffer = ob_get_contents();

	ob_end_clean();

	$buffer = str_replace("%TITLE%","Sweet.ovh - Le site de recensement des portails",$buffer);

	echo $buffer;
	
	if(!isset($_POST["g-recaptcha-response"])) {
?>

	<form method="POST" action="captcha.php">
		<div class="g-recaptcha" data-sitekey="6LcExjcUAAAAAEdlqsObyM40i_XTeLPlTsIgjIJX"></div>
		<input type="submit" value="Envoyer le formulaire">
	</form>

<?php 	}

	else {
		
		$data = array("secret" => "6LcExjcUAAAAABKy3WfIihoxvZV_KYYbZMf6taMF","response" => $_POST["g-recaptcha-response"]);


$data_url = http_build_query ($data);

// Cr�ation d'un flux
$opts = array(
  'http'=>array(
    'method'=>"POST",
    'content'=>$data_url
  )
);

$context = stream_context_create($opts);

// Acc�s � un fichier HTTP avec les ent�tes HTTP indiqu�s ci-dessus
$file = json_decode(file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context), true);

var_dump($file["success"]);

	}

	include("footer.php");

?>