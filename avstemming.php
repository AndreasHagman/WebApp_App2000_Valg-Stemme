<?php
//Kode skrevet av Remi
//Kode kontrollert av Petter
session_start();

include 'funksjoner.php';
include 'meldinger.php';

testInaktivitet60min("nySide");
//Sjekker om bruker er logget inn
testOmBrukerIdErTilegnet();

//Databasetilkobling - PDO
include("db_pdo.php");
$db = new myPDO();

finndato($db);
bestemBrukerTypeNavn($db);


if ($_SESSION['startvalg']>date(AarMaanedDagTimeMinutterSekunder) || $_SESSION['sluttvalg']<date(AarMaanedDagTimeMinutterSekunder)){ 
  //echo "Midlertidig melding -> Ikke tilgang til denne siden";
  header("Location: {$_SESSION['url']}?message=ikketilgang");
  exit();
}

//Brukeren har nå tilgang til denne siden -> oppdaterer sessionvariabel
$_SESSION['url']='avstemming.php';

//Henter ut alle kandidater som kan stemmes på
$sql = "SELECT epost, fnavn, enavn FROM kandidat, bruker WHERE kandidat.bruker=bruker.epost AND trukket is NULL ORDER BY fnavn";
$stmt = $db->prepare($sql);
$stmt->execute();
$brukere = $stmt->fetchAll();
 
//Sql som henter ut hvem den innloggede brukeren har stemt på
$sql = "SELECT stemme FROM bruker where epost='".$_SESSION['brukerID']."'";
$stmt = $db->prepare($sql);
$stmt->execute();
$stemme=$stmt->fetch(PDO::FETCH_COLUMN);
//oppretter global variabel som holder epost til inlogget bruker
$bruker= $_SESSION['brukerID'];
//Henter ut info om brukeren som blir valgt i avstemmingsmenyen

//Sjekk på om bruker har avgitt stemme eller ikke
$avgitt_stemme=true;
if (empty($stemme)){
  $avgitt_stemme=false;
} else {
  //brukeren har avgitt stemme -> henter ut fornavn og etternavn fra databasen
  $sql = "SELECT fnavn, enavn FROM bruker WHERE epost=?";
  $stmt = $db->prepare($sql);
  $stmt->execute([$stemme]);
  $navn=$stmt->fetch(PDO::FETCH_ASSOC);
  $stemmeprint= $navn['fnavn']. " ". $navn['enavn'];
}

//Trykker på avgi stemme knapp.
if(isset($_POST['avgiStemme'])){
  $stemtPå= $_POST['stemmerPå'];
  $sql = "UPDATE bruker SET stemme=? WHERE epost=?";
  $stmt = $db->prepare($sql);
  $stmt->execute([$stemtPå,$bruker]);

  //Oppdaterer stemmer i tabellen med +1 dersom stemmen ble registrert i databasen
  if($stmt){
    //Sql som sjekker om brukeren har stemmer
    $sql = "SELECT stemmer FROM kandidat where bruker=?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$stemtPå]);
    $stemmer=$stmt->fetch(PDO::FETCH_COLUMN);
    
    if($stemmer){
      //Hvis brukeren har stemmer fra før oppdateres tabellen med pluss 1
      $sql = "UPDATE kandidat set stemmer=stemmer+1 where bruker=?";
      $stmt = $db->prepare($sql);
      $stmt->execute([$stemtPå]);
    } else {
      //Brukeren har ingen stemmer fra før -> setter verdien i stemmer til 1
      $sql = "UPDATE kandidat set stemmer=1 where bruker=?";
      $stmt = $db->prepare($sql);
      $stmt->execute([$stemtPå]);
    }
    //Fjerner 1 stemme fra forrige kandidat dersom brukeren allerede hadde stemt
    if (empty($stemme)){
      //Brukeren hadde ikke stemt fra før, gjør ingenting
    } else {
      //Brukeren hadde avgitt stemme, må ta -1 stemme på gammel kandidat
      $sql = "UPDATE kandidat set stemmer=stemmer-1 where bruker=?";
      $stmt = $db->prepare($sql);
      $stmt->execute([$stemme]);
    }
  }
  //Refresher siden med melding om at brukeren har avgitt en stemme
  header('Location: avstemming.php?message=stemt');
  exit();
}

//Trykker på fjern stemme knapp
if(isset($_POST['fjernstemme'])){
  //Fjerner referansen til kandidaten fra brukertabellen
  $sql = "UPDATE bruker SET stemme=NULL WHERE epost=?";
  $stmt = $db->prepare($sql);
  $stmt->execute([$bruker]);
  //Fjerner en stemme fra kandidaten
  $sql = "UPDATE kandidat set stemmer=stemmer-1 where bruker=?";
  $stmt = $db->prepare($sql);
  $stmt->execute([$stemme]);
  //Refresher siden med melding om at stemmen ble fjernet
  header('Location: avstemming.php?message=fjernetStemme');
  exit();
}
?>

<!DOCTYPE html>
  <html lang="no" dir="ltr">
  <head>
    <title>Avstemming</title>
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>

  <body>
    <header>
    <?php navmeny(); ?>
    </header>

    <main>
      <p class="loginsom"><?php echo $_SESSION['brukerTypeNavn'] ,": ", $_SESSION['brukerID']?> </p>

      <h1 id="overstkift_avtesmming">Avstemming</h1>

      <?php if($brukere != null){
        echo "<form method='post' action='";htmlspecialchars($_SERVER['PHP_SELF']);echo"'>
              <section class='testsentrer'>
                <label for='valgboks'>Velg kandidat</label><br><select id='valgboks' name='stemmerPå' onchange='Endrefunksjon();'>";
                foreach($brukere as $bruker) :
                  echo "<option value='{$bruker['epost']}'> {$bruker['fnavn']} {$bruker['enavn']}</option>";
                endforeach;
                echo "</select>
              </section>
        
              <section class='testsentrer'>
                <button type='submit' id='avstemKnapp' class='button' name='avgiStemme'>Avgi stemme</button>
                <button type='submit' id='seMer' class='button' name='seMer'>Se mer</button>
              </section>
              <section id='stemmesvar' class='border'>";
                if($avgitt_stemme==false){echo "<p id='duharstemt'>Du har ikke stemt</p>";}
                else{echo "<p id='duharstemt'>Du har stemt: $stemmeprint </p>";}
              echo "</section>";
              if($avgitt_stemme==true){
              echo "<section class='testsentrer' id = fjernStemme>
                <button type='submit' id='fjernStemmeKnapp' class='button' name='fjernstemme'>Fjern stemme</button>
              </section>";
              }
              echo "</form>
              
              <script type='text/javascript'>
              function Endrefunksjon() {
              var valgboks = document.getElementById('valgboks');
              var selectedValue = valgboks.options[valgboks.selectedIndex].value;
              
              }
            </script>";
      }
      else {
        echo "<p id='tomResultat'>Det var ingen som ble nominert</p>";
      }
      ?>
     
      <?php
      //Trykker på knapp for å se mer info om valgt kandidat
      if(isset($_POST['seMer'])){
        $stemtPå= $_POST['stemmerPå'];
        $sql = "Select fnavn, enavn, bilde, fakultet, institutt, informasjon 
                from kandidat, bruker 
                where kandidat.bruker=bruker.epost AND bruker=?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$stemtPå]);
        $rad = $stmt->fetch(PDO::FETCH_ASSOC);
        $navn = $rad['fnavn']. " ". $rad['enavn'];

        if(empty($rad['bilde'])){
          $bilde = "bilder/default.jpg";
          $alt = "Profilbilde";
        } else {
        //Finner bildesti til kandidatens bilde
        $sql = "select hvor, alt from bilde where idbilde =?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$rad['bilde']]);
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
        $bilde1 = $resultat['hvor'];
        $alt = $resultat['alt'];
        $bilde_sti = "bilder/";
        $bilde = $bilde_sti.$bilde1;
        
        } 
        echo "<section id='visInfoAvstem' class='border'>
              <img src=".$bilde." alt=".$alt.">
              <p class='infoTekst'>".$navn."</p>
              <p class='infoTekst'>".$rad['fakultet']."</p>
              <p class='infoTekst'>".$rad['institutt']."</p>
              <p class='infoTekst'>".$rad['informasjon']."</p>
              </section>";
      }
      

        ?>
    </main>
    <footer>
      <p>Copyright ©2021 Hønefoss/Norge - Rapp 20 G04</p>
    </footer>
  </body>
</html>
