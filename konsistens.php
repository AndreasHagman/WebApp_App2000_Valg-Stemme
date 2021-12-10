<?php
//Kode skrevet av Andreas
//Kode kontrollert av Remi
session_start();

include 'funksjoner.php';
include 'meldinger.php';

//Databasetilkobling - PDO
include("db_pdo.php");
$db = new myPDO();

bestemBrukerTypeNavn($db);
testInaktivitet60min("nySide");
finndato($db);
testOmBrukerIdErTilegnet();

//Sjekker om kravene for å få tilgang til siden blir godkjent
if ($_SESSION['brukerType'] != 3 || $_SESSION['sluttvalg']>date(AarMaanedDagTimeMinutterSekunder) ) { 
  //echo "Midlertidig melding -> Ikke tilgang til denne siden";
  header("Location: {$_SESSION['url']}?message=ikketilgang");
  exit();
  }

//Brukeren har nå tilgang til denne siden -> oppdaterer sessionvariabel
$_SESSION['url']='konsistens.php';

//Publiserer valgresultatene
if(isset($_POST['publiserValgResultater'])){
  $dato = date(AarMaanedDagTimeMinutterSekunder);

  $sqlSettKontrollert ="UPDATE valg SET kontrollert=?";
  $stmtSett = $db->prepare($sqlSettKontrollert);
  $stmtSett-> execute([$dato]);

  if($stmtSett-> execute()){
    include('genererinfovalg.php');
    alert("Resultatene har blitt publisert","Grønn");
  }
  else{
    alert("Noe gikk galt","Rød");
  }
}

//Trekker valgresultatene
if(isset($_POST['trekkValgResultater'])){
  $sqlTrekkKontrollert ="UPDATE valg SET kontrollert=?";
  $stmtTrekk = $db->prepare($sqlTrekkKontrollert);
  $stmtTrekk-> execute([null]);

  if($stmtTrekk-> execute()){
    include('genererinfovalg.php');
    alert("Resultatene har blitt trukket","Grønn");
  }
  else{
    alert("Noe gikk galt","Rød");
  }
}
?>

<!DOCTYPE html>
<html lang="no" dir="ltr">

<head>
<title>Konsistens</title>
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
<?php navmeny(); ?>
<main>
<p class="loginsom"><?php echo $_SESSION['brukerTypeNavn'] ,": ", $_SESSION['brukerID']?> </p>
   <h1 id="konsistens_overskrift">Konsistens i data</h1>

<section id="konsistensMain">

  <?php 
  //sjekker hvor mange stemmer som til sammen er avgitt
  $sql = "SELECT SUM(stemmer) FROM kandidat";
  $stmt= $db->prepare($sql);
  $stmt->execute();
  $antallStemmer = $stmt->fetch(PDO::FETCH_COLUMN);

  //Sjekker hvor mange som har avgitt stemme
  $sql = "SELECT count(stemme) FROM bruker where stemme is not null";
  $stmt= $db->prepare($sql);
  $stmt->execute();
  $antallStemme = $stmt->fetch(PDO::FETCH_COLUMN);
  
  echo "<article id='stemmetelling'>";
       

  //Kode som finner epost og antall stemmer til hver kandidat
  $sql = "SELECT bruker, stemmer FROM kandidat where stemmer is not null and stemmer>0";
  $stmt= $db->prepare($sql);
  $stmt->execute();
  $stemmerPerKand = $stmt->fetchAll();

  //Tabell som viser alle kandidater med antall stemmer fra kandidattabell og brukertabell fra databasen
  //Tabelloverskrifter
  echo "<h2>Oversikt over stemmer</h2>
          <table id='stemmetabell' class='fargeDefaultBoks'>
          <tr>
          <th class='ledetekst'>Epost</th>
          <th class='ledetekst'>Antall stemmer fått</th>
          <th class='ledetekst'>Stemmer avgitt i brukertabellen</th>
          </tr>";
  //Løkke som går igjennom Kandidater og finner hvor mange som har stemt på gjeldene kandidat fra brukertabellen
  foreach($stemmerPerKand as $kandidat){
    $sql = "SELECT count(stemme) FROM bruker where stemme is not null and stemme=?";
    $stmt= $db->prepare($sql);
    $stmt->execute([$kandidat['bruker']]);
    $brukerStemmer = $stmt->fetch(PDO::FETCH_COLUMN);
    //
    echo "<tr>
          <td>".$kandidat['bruker']." </td>
          <td>".$kandidat['stemmer']." </td>
          <td> ".$brukerStemmer."</td>
          </tr>";
    
  }echo
        "<tr>
        <th>Sum</th>
        <td>".$antallStemmer." </td>
        <td> ".$antallStemme."</td>
        </table>
        </article>";
  ?>

  <?php
  //Sjekk på mailadresser
  // $sql = "SELECT sluttvalg FROM valg";
  // $stmt = $db->prepare($sql);
  // $stmt-> execute();
  // $resultat =$stmt->fetch(PDO::FETCH_ASSOC);

  echo"
  <article id='epost_sjekk'>
  <h2 style='text-align: center;'> Eposter </h2>
  <table id='epost_tabell' class='fargeDefaultBoks'>";

  $sql = "SELECT epost FROM bruker";
  $stmt = $db->prepare($sql);
  $stmt->execute();
  while($rad = $stmt->fetch(PDO::FETCH_ASSOC)){
    echo   
          "<tr>
            <td>".$rad['epost']."</td>
          </tr>";
  }echo "
      </table>
      </article>";

  echo "<article id='publiserValg'>";
  //Sjekker om det gjeldende valget allerede har blitt kontrollert
  $dato = $_SESSION['kontrollert'];
  $dato = date('H:i:s d-m-Y',strtotime($dato));

  //Hvis det ikke er kontrollert fra før av, kan resultatene publiseres
  if($_SESSION['kontrollert'] == null){
    echo "<form method='POST' action='konsistens.php'>
          <button id='publiserValgKnapp' class='button' value='publiser' name='publiserValgResultater' type='submit'>Publiser valgresultatene</button>
          </form>";
  }
  else{
    echo "<p><strong>Valgresultatene ble publisert: ",$dato,"</strong></p>";
    echo "<form method='POST' action='konsistens.php'>
          <button id='trekkValgKnapp' class='button' value='trekk' name='trekkValgResultater' type='submit'>Trekk valgresultatene</button>
          </form>";
  }
  echo "</article>";

?>
</section>
</main>
    <footer>
      <p>Copyright ©2021 Hønefoss/Norge - Rapp Gruppe 04 </p>
    </footer>
</body>

</html>