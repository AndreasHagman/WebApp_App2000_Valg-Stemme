<?php
//Kode skrevet av Remi
//Kode kontrollert av Endre

session_start();
include "funksjoner.php";
$GLOBALS['bekreftFeil'] = FALSE;
testOmAlleredeLoggetInn();

//Databasetilkobling - PDO
include("db_pdo.php");
$db = new myPDO();


if(!empty($_GET["id"])){
    $_SESSION['epost'] = $_GET["id"];
    $_SESSION['bekreftelsesKode'] = bekreftKode($_SESSION['epost']);
}


if(isset($_POST['videre'])){
    $inputKode = ($_POST['kode']);
    echo $bekreftelsesKode; 

   
    if($inputKode == $_SESSION['bekreftelsesKode']) {

        $query = "SELECT * FROM bruker WHERE epost=?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['epost']]);
        $bruker = $stmt->fetch(PDO::FETCH_ASSOC);

        if($bruker){
        //Bruker har skrevet inn riktig bekreftelseskode og vi logger inn.
        $_SESSION['brukerID'] = $_SESSION['epost'];
        $_SESSION['brukerType'] = (int)$bruker['brukertype'];
        $_SESSION['brukerTypeNavn'] = "Bruker";
        
        if($_SESSION['brukerType'] == 2){
            $_SESSION['videreSendt'] = true;
            header("Location: minside.php?message=logginnGodkjent"); //Hvis admin bruker logger inn
            } 
    } else {
        alert("Du har oppgitt feil kode, vennligst prøv igjen","Rød");
    }
    } else {
        $bekreftFeil = TRUE;
    }
}



?>
<!DOCTYPE html>
<html lang="no" dir="ltr">
<head>
    <title>Logg inn</title>
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    

</head>
<body>
    <nav>
        <label id='navikon' for="toggle">&#9776;</label>
        <input type="checkbox" id="toggle"/>
        <ul class="menu">
            <li><a href="default.php">Forside</a></li>
            <li><a href="registrering.php">Registrering</a></li>
            <li><a href="logginn.php">Login</a></li>
        </ul>
      </nav>

      <main>

        <h1>Login for brukere</h1>

        <form class="bekreft" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                <!-- Overskrift -->
                <h2>LOGG INN</h2>
                <!-- Informasjon til bruker -->
                <p>Vennligst skriv inn bekreftelseskode</p>
                <!-- Input for kode -->
                <input type="text" name="kode" id="kode" placeholder="Kode" autofocus/>
                <!-- En knapp for å registrere input -->
                <input  type="submit" name="videre" id="videre"  value="Videre"/>
                <section id="bekreftFeil">
                <?php if($bekreftFeil){ ?>
                <p id="bekreftFeil1">Feil kode, prøv på nytt</p>
                <?php } ?>
                </section>
            </form>
    </main>

      <footer>
        <p>Copyright ©2021 Hønefoss/Norge - Rapp 20 G04</p>
      </footer>
</body>
</html>