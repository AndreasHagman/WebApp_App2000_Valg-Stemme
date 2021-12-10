<?php
	//Kode skrevet av Remi
	//Kode kontrollert av Waleed

	//Kode for aktivering av bruker etter registrering
    
	session_start();
	include("db_pdo.php");
	include("funksjoner.php");
	$db = new myPDO(); 
    
	if(!empty($_GET["id"])) {
		if($_SESSION['kode']==$_GET["id"]){
			$sql = "insert into bruker(epost, passord, enavn, fnavn, brukertype) VALUES('" . $_SESSION['mail'] . "','" . 
			$_SESSION['passord'] . "','" . $_SESSION['etternavn'] . "','" . $_SESSION['fornavn'] . "','" . $_SESSION['brukerType'] . "')";
			$stmt = $db->prepare($sql);
			$stmt->execute();

				if(!empty($stmt)) {
					$_SESSION['brukerID'] = $_SESSION['mail'];
					unset($_SESSION['mail']);
					unset($_SESSION['passord']);
					opprettInaktivitetTid();
					header('Location: minside.php?message=aktivert');
					exit();
				} else {
					unset($_SESSION['passord']);
					header('Location: default.php?message=aktivertFeil');
					exit();
				}
			}
		}
?>