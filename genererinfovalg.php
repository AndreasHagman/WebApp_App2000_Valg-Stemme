<?php

//Kode skrevet av Endre og Andreas
//Kode kontrollert av Petter

//Fil for å generere en html-versjon av index for fremvising til brukere
//session_start();
bestemBrukerTypeNavn($db);
testInaktivitet60min("nySide");

//Henter ut datoer for sjekk på menyelementer
finndato($db);
    
$forslag1 = date(DagMaanedAar, strtotime($_SESSION['startforslag']));
$forslag2 = date(DagMaanedAar, strtotime($_SESSION['sluttforslag'])); 
$valg1 = date(DagMaanedAar, strtotime($_SESSION['startvalg']));
$valg2 = date(DagMaanedAar, strtotime($_SESSION['sluttvalg']));

$path = realpath(".");  //  get server path
$save_path=$path;   //  define target path
$tittel=$_SESSION['tittel'];
$navn=$_SESSION['brukerTypeNavn'];
$id=$_SESSION['brukerID'];

$viskonsistens="";
$visavstemming="";
$visnominering="";
//Vil ikke oppdatere seg selv, krever at noen oppdaterer databasen for at menyelementene skal oppdateres

//Kode for å hente ut antallstemmer per kandidat til tabellvisning
$sql = "SELECT bruker, stemmer FROM kandidat where trukket is null ORDER BY stemmer desc";
$stmt = $db->prepare($sql);
$stmt->execute();

//Henter verdien av kontrollert i databasen
finndato($db);
//Deklarerer variabler som brukes til tabellen
$starttabell="";
$sluttabell="";
$streng = "";
//Tester på om valget er kontrollert, bare da vil resultater vises
if($_SESSION['kontrollert'] != null){
  $starttabell="<section id='resFremvisning'>
                <h2 class='fargeDefaultBoks'><span class='ledetekst'>Valgresultat</span></h2>
                <table id='fremvisningTabell' class='fargeDefaultBoks'>";
  $sluttabell="</table>
               </section>";

  //Henter ut alle kandidater fra databasen
  if ($kandidater=$stmt->fetchAll()){
    $plassering="0";
    //Itererer igjennom kandidat for kandidat
    foreach ($kandidater as $kandidat){
      $plassering ++;
      $sql = "SELECT fnavn, enavn FROM bruker WHERE epost=?";
      $stmt = $db->prepare($sql);
      $stmt-> execute([$kandidat['bruker']]);
      $navn1 = $stmt->fetch(PDO::FETCH_ASSOC);
      $fornavn= $navn1['fnavn'];
      $etternavn=$navn1['enavn'];
      $antallstemmer=$kandidat['stemmer'];
      
      if ( $antallstemmer===null){
        $antallstemmer="0";
      }
      $streng.= "<tr><td>".$plassering.". ".$fornavn." ". $etternavn."</td><td class='stemmer'>". $antallstemmer."</td></tr>\n";
    }
  }
}

  $data="<!DOCTYPE html>
  <html lang='no' dir='ltr'>
  <head>
    <title>Applikasjonsutvikling for web</title>
    <link rel='stylesheet' type='text/css' href='../css/index.css'>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  </head>
  <body>
    <header>
      <nav>
        <label id='navikon' for='toggle'>&#9776;</label>
        <input type='checkbox' id='toggle'/>
        <ul class='menu'>
          <li><a href='infovalg.html'>Forside</a></li>
          <li><a href='../minside.php'>Min side</a></li>
          <li><a href='../default.php?message=loggetUt'>Logg ut</a></li>
        </ul>
      </nav>
    </header>
    <main>

    <h1>$tittel</h1>

    <article id='indexArtikkel'>
        <p>På de forskjellige sidene vil du finne funksjonalitet for å legge av din stemme, nominere kandidater, og gjøre endringer relatert til brukeren på min side.<br></p>
        <p class='valg_info fargeDefaultBoks'><span class='ledetekst'>Nominering av kandidater foregår fra:</span> <br> $forslag1 til $forslag2</p>
        <p class='valg_info fargeDefaultBoks'><span class='ledetekst'>Det gjeldende valget går fra:</span> <br> $valg1 til $valg2 </p>
      </article>

      $starttabell
          $streng
        $sluttabell

    </main>

    <footer>     
      <p>Copyright ©2021 Hønefoss/Norge - Rapp 20 G04</p>
    </footer>
  </body>
</html>";

	$dest="generert/infovalg.html";             //  define final destination target name

	$fp = fopen($save_path.'\\'.$dest, "w", 0);		//  open for writing
	$ant = fwrite($fp, $data);						  // write all of $data to our opened file
	fclose($fp);									  //  close the file
?>

