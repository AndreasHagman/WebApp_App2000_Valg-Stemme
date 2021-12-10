<?php
//Kodet skrevet av Endre
//Kode kontrollert av Petter
session_start();

include("funksjoner.php");
include('meldinger.php');


//Sender bruker tilbake til forrige side hvis bruker allerede er logget inn.
if (isset($_SESSION['brukerID'])) {
  header("Location: {$_SESSION['url']}?message=alleredeLoggetInn");
  exit();
} else {
  session_destroy();
}


  //Databasetilkobling - PDO
  include("db_pdo.php");
  $db = new myPDO();

  //Query for å hente ut informasjon om valg.
  $sql = "select * from valg";
  $stmt = $db->prepare($sql);
  $stmt->execute();
  $valget = $stmt->fetch(PDO::FETCH_ASSOC);
 
  $forslag1 = date(DagMaanedAar, strtotime($valget['startforslag']));
  $forslag2 = date(DagMaanedAar, strtotime($valget['sluttforslag']));
  $valg1 = date(DagMaanedAar, strtotime($valget['startvalg']));
  $valg2 = date(DagMaanedAar, strtotime($valget['sluttvalg']));

?>

<!DOCTYPE html>
  <html lang="no" dir="ltr">
  <head>
    <title>Applikasjonsutvikling for web</title>
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>
  <body>
    <header>
      <nav>
        <label id='navikon' for="toggle">&#9776;</label>
        <input type="checkbox" id="toggle"/>
          <ul class="menu">
            <li><a href="default.php">Forside</a></li>
            <li><a href="registrering.php">Registrering</a></li>
            <li><a href="logginn.php">Login</a></li>
          </ul>
      </nav>
    </header>
    <main>
      <h1><?php echo $valget['tittel'];?></h1>
      <article id="indexArtikkel">
        <p id="defaultInfoSpace">På de forskjellige sidene vil du finne funksjonalitet for å: 
          avgi din stemme, nominere kandidater, registrere deg som bruker og logge inn.<br>
          For å kunne ta i bruk de forskjellige funksjonene som siden tilbyr, må du <a href="registrering.php">registrere deg som bruker</a> med mindre du allerede har en bruker.
        </p>
        <p class="fargeDefaultBoks valg_info"><span class='ledetekst'>Nominering av kandidater foregår fra:</span> <br> <?php echo $forslag1; echo " til "; echo $forslag2; ?></p>
        <p class="fargeDefaultBoks valg_info"><span class='ledetekst'>Det gjeldende valget går fra:</span> <br> <?php echo $valg1; echo " til "; echo $valg2; ?></p>
      </article>
    </main>
    <footer>     
      <p>Copyright ©2021 Hønefoss/Norge - Rapp 20 G04</p>
    </footer>
  </body>
</html>