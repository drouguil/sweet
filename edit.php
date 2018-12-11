<?php 

	ob_start();

	include("header.php");

	$buffer = ob_get_contents();

	ob_end_clean();

	if($_SESSION["login"] == NULL)
	{

		$buffer=str_replace("%TITLE%","Accès réservé - L'annuaire des portails",$buffer);

		echo $buffer;?>
		<main id='login' class='container'>
			<h2 class="center" style="margin:0 auto;">
				<a class="btn btn-info" href="/register">Inscrivez-vous</a><br/>
				 et/ou <br/>
				<a class="btn btn-info" href="/login">Connectez-vous</a> <br/>
				 pour mettre à jour les positions des portails
			</h2>
		</main>

	<?php } 
	else 
	{
		
		$sql = "SELECT * FROM ip_banned
				WHERE `ip` = '" . $_SERVER['REMOTE_ADDR'] . "' OR `user` = " . $_SESSION["user_id"];

		$req = $dbh->prepare($sql);
		$req->execute();
		$ip = $req->fetchAll();

		$buffer=str_replace("%TITLE%","Modifier la position du portail - L'annuaire des portails",$buffer);

		echo $buffer;

		if (isset($_POST["posX"]) && ($_POST["posX"] >= -100) && ($_POST["posX"] <= 100) && isset($_POST["posY"]) && ($_POST["posY"] >= -100) && ($_POST["posY"] <= 100) && isset($_POST["number_utilisations"]) && ($_POST["number_utilisations"] >= 1) && ($_POST["number_utilisations"] <= 128) && isset($_POST["modificateur"]) && ($_POST["modificateur"] >= 1) && ($_POST["modificateur"] <= 25) && isset($_POST["portal_id"]))
		{
	
			$sql = "SELECT * FROM portals
				WHERE id = " . $_POST["portal_id"];

			$req = $dbh->prepare($sql);
			$req->execute();
			$portal = $req->fetch();
			
			if($portal == false)
			{
				header('Location: /serveurs');
			}
				
				$sql = "SELECT * FROM cycle_modificateurs
					WHERE id = " . $portal["cycle"];

				$req = $dbh->prepare($sql);
				$req->execute();
				$cycle = $req->fetch();
				
				if($_POST["modificateur"] == $cycle["modificateur_1"] || $_POST["modificateur"] == $cycle["modificateur_2"] || $_POST["modificateur"] == $cycle["modificateur_3"] || $_POST["modificateur"] == $cycle["modificateur_4"] || $_POST["modificateur"] == $cycle["modificateur_5"] || $_POST["modificateur"] == $cycle["modificateur_6"] || $_POST["modificateur"] == $cycle["modificateur_7"] || $_POST["modificateur"] == $cycle["modificateur_8"] || $_POST["modificateur"] == $cycle["modificateur_9"] || $_POST["modificateur"] == $cycle["modificateur_10"]) 
				{	
					if($_POST["unknowing"] == 'on')
					{
						$unknowing = 1;
					}
					else
					{
						$unknowing = 0;
					}
					
					if($_POST["canopee"] == 'on')
					{
						$canopee = 1;
					}
					else
					{
						$canopee = 0;
					}
					
					$sql = "UPDATE portals
							SET `posX` = " . $_POST["posX"] .
							" , `posY` = " . $_POST["posY"] .
							" , `number_utilisations` = " . $_POST["number_utilisations"] .
							" , `current_modificateur` = " . $_POST["modificateur"] .
							" , `canopee` = " . $canopee . 
							" , `unknowing` = " . $unknowing . 
							" , date = NOW() WHERE `id` = " . $_POST["portal_id"];

					$req = $dbh->prepare($sql);					
					$req->execute();			
					
					$sql = "UPDATE users
							SET `portals_number` = `portals_number` + 1
							WHERE `id` = " . $_SESSION["user_id"];
							
					$req = $dbh->prepare($sql);					
					$req->execute();
					
					if($portal["posX"] != $_POST["posX"] || $portal["posY"] != $_POST["posY"] || $portal["unknowing"] != $unknowing)
					{	
						$userUpdate = $_SESSION["user_id"];
						if($unknowing == 0)
							{
								$sql = "SELECT * FROM historique WHERE `posX` = " . $_POST["posX"] . " AND `posY` = " . $_POST["posY"] . " AND `unknowing` = 0 AND `portal_id` = " . $_POST["portal_id"];
							
								$sqlTest = $sql;
							
								$req = $dbh->prepare($sql);
								$req->execute();
								$historiqueFound = $req->fetch();
								
								if(!empty($historiqueFound))
								{
									$userUpdate = $historiqueFound["user"];
								}
							}
						$sql= "INSERT INTO `historique`(`portal_id`, `posX`, `posY`, `canopee`, `unknowing`, `number_utilisations`, `current_modificateur`, `date`, `user`, `votes_number`, `reports_number`) 
							   VALUES (" . $portal["id"] . 
							   "," . $_POST["posX"] . 
							   "," . $_POST["posY"] . 
							   "," . $canopee . 
							   "," . $unknowing . 
							   "," . $_POST["number_utilisations"] . 
							   "," . $_POST["modificateur"] . 
							   ", NOW()," . $userUpdate . 
							   ",0,0)";
							   
						$req = $dbh->prepare($sql);					
						$req->execute();
						
						$sql = "DELETE FROM `historique` WHERE `portal_Id` = " . $_POST["portal_id"] . " AND `date` <= '" . date('Y-m-d', strtotime('-3 day')) . "'";

						$req = $dbh->prepare($sql);
						$req->execute();
				
						$sql = "DELETE FROM `votes` WHERE `portal` = " . $_POST["portal_id"];

						$req = $dbh->prepare($sql);					
						$req->execute();
						
						$sql = "UPDATE portals
							SET `user` = " . $userUpdate .
							", `ip` = '" . $_SERVER['REMOTE_ADDR'] . 
							"', `votes_number` = 0, `reports_number` = 0 
							WHERE `id` = " . $_POST["portal_id"];

						$req = $dbh->prepare($sql);
						$req->execute();
					}
					else
					{
						if($_POST["number_utilisations"] != $portal["number_utilisations"] || $_POST["modificateur"] != $portal["current_modificateur"])
						{
							$sql= "INSERT INTO `historique`(`portal_id`, `posX`, `posY`, `canopee`, `unknowing`, `number_utilisations`, `current_modificateur`, `date`, `user`, `votes_number`, `reports_number`) 
							   VALUES (" . $portal["id"] . 
							   "," . $_POST["posX"] . 
							   "," . $_POST["posY"] . 
							   "," . $canopee . 
							   "," . $unknowing . 
							   "," . $_POST["number_utilisations"] . 
							   "," . $_POST["modificateur"] . 
							   ", NOW()," . $portal["user"] . 
							   ",0,0)";
							   
							$req = $dbh->prepare($sql);					
							$req->execute();
						}
					}
					
					if($_POST["number_utilisations"] != $portal["number_utilisations"] && $portal["user"] != $_SESSION["user_id"])
					{
						header('Location: /confirmation/'.$portal["id"]);
					}
					else
					{
						header('Location: /portails/'.$portal["server"]);
					}
				}
		}

		if(isset($_GET["id"]))
		{
			$_GET["id"] = intval($_GET["id"]);

			$sql = "SELECT * FROM portals
				WHERE id = " . $_GET["id"];

			$req = $dbh->prepare($sql);
			$req->execute();
			$portal_test = $req->fetch();
			
			if ($portal_test != false)
			{
				$sql = "SELECT * FROM portals
						WHERE id = " . $_GET["id"];

				$req = $dbh->prepare($sql);		
				$req->execute();			
				$portal = $req->fetch();
		
				$sql = "SELECT * FROM modificateurs
					   WHERE id = " . $portal["current_modificateur"];

				$req = $dbh->prepare($sql);			
				$req->execute();		
				$current_modificateur = $req->fetch();
			
				$current_modificateur_name = str_replace("é","e",$current_modificateur["name"]);
				$current_modificateur_name = str_replace("ê","e",$current_modificateur_name);
				$current_modificateur_name = str_replace("è","e",$current_modificateur_name);
				$current_modificateur_name = str_replace("'","_",$current_modificateur_name);
				$current_modificateur_name = str_replace("-","_",$current_modificateur_name);
				$current_modificateur_name = str_replace(" ","_",$current_modificateur_name); 
				
				if(empty($ip) && $_SESSION["rigths"] != 0)
				{?>
				<main id="edit">
				<div class="pub">
				<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
				<!-- Pub edit haut -->
				<ins class="adsbygoogle"
					 style="display:inline-block;width:728px;height:90px"
					 data-ad-client="ca-pub-9064407582480322"
					 data-ad-slot="3764739292"></ins>
				<script>
				(adsbygoogle = window.adsbygoogle || []).push({});
				</script>
				</div>
					<div class='vertical'>

						<div class='vertical-center'>

							<div class="divtitle">
								<h2>Cervelles de iops et enutrofs séniles s'abstenir lors du renseignement des informations</h2>
							</div>

							<form method="post" action="edit.php">

								<input type="hidden" name="portal_id" value="<?php echo $portal["id"]?>"/>

								<table class='table'>

								   <tbody>

									   <tr>
										   <td>Dimension</td>
										   <td>Position</td>
										   <td id="unknowing_head">Nombre d'utilisations</td>
										   <td>Modificateur actuel</td>
										   <td>Validation de la modification</td>
									   </tr>

									   <tr>
										   <td><h2><?php echo $portal["name"]; ?></h2><img src="../images/portals/<?php echo $portal["name"]; ?>.png" height="173"></td>
										   <td id="unknowing_body" ><h2 class="line">[<input type="number" name="posX" min="-100" max="100" step="1" value="<?php echo $portal["posX"]; ?>">,<input type="number" name="posY" min="-100" max="100" step="1" value="<?php echo $portal["posY"]; ?>">]</h2>
										   <h2><p class="line" ><input id="check_canopee" type="checkbox" name="canopee" <?php if($portal["canopee"] == 1) { echo 'checked'; } ?> /> <label for="check_canopee">Village de la Canopée</label></p></h2>
										   <h2><p class="line"><input id="unknowing_input_one" type="checkbox" onclick="change(1);" name="unknowing" /> <label for="unknowing_input_one">Position inconnue</label><p></td></h2>
										   <td><h2>
										   <input id="unknowing_input" type="number" name="number_utilisations" min="1" max="128" step="1" value="<?php echo $portal["number_utilisations"]; ?>">
										   <p id="unknowing_input_bis" style="display: none;" class="line"><input id="unknowing_input_two" type="checkbox" onclick="change(0);" name="unknowing" /> <label for="unknowing_input_two">Position inconnue</label></p>
										   </h2></td>
										   
										   <td>

											   <select name="modificateur">

												<?php 

													$sql = "SELECT *
														FROM cycle_modificateurs
														WHERE id = " . $portal["cycle"];

													$req = $dbh->prepare($sql);
													$req->execute();
													$cycle = $req->fetch();

													for($i = 1 ; $i < 11 ; $i++)
													{
														$sql = "SELECT * FROM modificateurs
														   WHERE id = " . $cycle["modificateur_".$i];
													   
														$req = $dbh->prepare($sql);
														$req->execute();
														$modificateur = $req->fetch();
													
														$modificateur_name = str_replace("é","e",$modificateur["name"]);
														$modificateur_name = str_replace("ê","e",$modificateur_name);
														$modificateur_name = str_replace("è","e",$modificateur_name);
														$modificateur_name = str_replace("'","_",$modificateur_name);
														$modificateur_name = str_replace(" ","_",$modificateur_name);
														$modificateur_name = str_replace("-","_",$modificateur_name); ?>

														<option value="<?php echo $modificateur["id"]; ?>" title="../images/modificateurs/<?php echo $modificateur_name; ?>.png" <?php if($modificateur["id"] == $portal["current_modificateur"]){ echo 'selected'; }?>><?php echo $modificateur["name"]; ?></option>
														   
													<?php } ?>

												</select>

											</td>

										   <td>
										   
										   <input class="btn" value="Valider" type="submit">
										   <br>
										   <a href="/portails/<?php echo $portal["server"]; ?>" class="btn" >Annuler</a>
										   
										   </td>
										</tr>
									</tbody>

								</table>

							</form>

						</div>

					</div>
				
				<div class="pub">
				<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
				<!-- Pub edit bas -->
				<ins class="adsbygoogle"
					 style="display:inline-block;width:728px;height:90px"
					 data-ad-client="ca-pub-9064407582480322"
					 data-ad-slot="5241472491"></ins>
				<script>
				(adsbygoogle = window.adsbygoogle || []).push({});
				</script>
								</div>
				</main>
			<?php
			}
			else
			{?>
			<main id="edit">
			<div class='vertical'>

						<div class='vertical-center'>
								
								<h1>Vous avez été banni du site pour avoir troll, si vous voulez demander un deban veuillez nous contacter à <b>sweetovh@gmail.com</b></h1>
			
						</div>
			
			</div>
			</main>
			<?php
			}
			}
			else 
			{
				$sql = "SELECT * FROM portals
				WHERE id = " . $_GET["id"];

				$req = $dbh->prepare($sql);
				$req->execute();
				$portal_test = $req->fetch();
				
				if($portal_test == false)
				{
					header('Location: /serveurs');
				}
				
				if($_POST["number_utilisations"] != $portal["number_utilisations"] && $portal["user"] != $_SESSION["user_id"])
				{
					header('Location: /confirmation/'.$portal["id"]);
				}
				else
				{
					header('Location: /portails/'.$portal["server"]);
				}
			}
		}
		else 
		{
			header('Location: /serveurs');
		}
	}

include("footer.php"); ?>

<script language="javascript">

	$(document).ready(function(e) {
		$("body select").msDropDown();
	});

</script>

<script language="javascript">

			function change(knowing) {
				if(!knowing)
				{
					$('#unknowing_head').removeAttr( 'style' );
					$('#unknowing_body').removeAttr( 'style' );
					$('#unknowing_input').removeAttr( 'style' );
					$('#unknowing_input_bis').css('display', 'none');
					$('#unknowing_input_one').prop('checked', false);
					$('#unknowing_input_two').prop('checked', false);
				}
				else
				{
					$('#unknowing_head').css('display', 'none');
					$('#unknowing_body').css('display', 'none');
					$('#unknowing_input').css('display', 'none');
					$('#unknowing_input_bis').removeAttr( 'style' );
					$('#unknowing_input_one').prop('checked', true);
					$('#unknowing_input_two').prop('checked', true);
				}
			}

</script>

<script language="javascript">
	change(<?= $portal["unknowing"] ?>);
</script>