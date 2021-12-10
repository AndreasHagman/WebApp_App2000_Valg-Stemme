<?php
//Kode skrevet av Andreas 
//Kode kontrollert av Petter
session_start();
include 'funksjoner.php';
include 'meldinger.php';

testInaktivitet60min("nySide");
testOmBrukerIdErTilegnet();

//Databasetilkobling - PDO
include("db_pdo.php");
$db = new myPDO();

bestemBrukerTypeNavn($db);
finndato($db);

$counter = -1;

//Sjekker om kravene for å få tilgang til siden blir godkjent
if ($_SESSION['startforslag']>date(AarMaanedDagTimeMinutterSekunder) || $_SESSION['sluttforslag']<date(AarMaanedDagTimeMinutterSekunder)){
  //echo "Midlertidig melding -> Ikke tilgang til denne siden";
  header("Location: {$_SESSION['url']}?message=ikketilgang");
  exit();
}

//Brukeren har nå tilgang til denne siden -> oppdaterer sessionvariabel
$_SESSION['url']='nominering.php';

//SQL for å hente ut registrerte brukere og legge dem til liste for nominering.
$sql = "SELECT epost, fnavn, enavn FROM kandidat RIGHT JOIN bruker ON epost=bruker WHERE brukertype='1' AND fnavn != '' AND enavn !='' AND trukket is null ORDER BY fnavn";
$stmt = $db->prepare($sql);
$stmt->execute();
$brukere = $stmt->fetchAll();

//Registrerer info på valgt bruker
if (isset($_POST['Nominering'])){
  testInaktivitet60min("databaseEndring");
  //Opprette en sjekk på riktig tidsrom
  finndato($db);
  if ($_SESSION['startforslag'] < date(AarMaanedDagTimeMinutterSekunder) && $_SESSION['sluttforslag']>date(AarMaanedDagTimeMinutterSekunder)){
  //Sørger for at brukeren har fylt inn alle felter før noe blir registrert
    if ($_POST['fakultet'] == "" or $_POST['institutt'] == "" or $_POST['info'] == "") {
      alert("Vennligst fyll inn alle feltene før du nominerer en kandidat","Rød");
    }else{

      //Variabel som inneholder epost fra valgt bruker.
      $bruker = $_POST['valg'];
      //Query for å sjekke dobbeltlagring av nominering.
      $sql = "SELECT bruker from kandidat where bruker=?";
      $stmt = $db->prepare($sql);
      $stmt->execute([$bruker]);
      $testbruker = $stmt->fetch(PDO::FETCH_ASSOC);
      //Hvis ikke bruker er nominert fra før, legger vi inn nominasjonen i tabellen.
      if (empty($testbruker)) {
          $fakultet = $_POST['fakultet'];
          $institutt = $_POST['institutt'];
          $info = $_POST['info'];
          $sql = "INSERT into kandidat(bruker, fakultet, institutt, informasjon) VALUES(?, ?, ?, ?)";
          $stmt = $db->prepare($sql);
          $stmt->execute([$bruker,$fakultet,$institutt,$info]);
          if($stmt){
            //ikke Send mail her
            mailNominering($bruker);
          }
          //Melding om at bruker ble nominert
          alert("Nominering vellykket","Grønn");
      }
      else {
        alert("Brukeren er allerede nominert", "Rød");
      } 
    }
  } else {
    alert ("Utenfor tidsrom", "Rød");
  }
}

?>
<!DOCTYPE html>
<html lang="no" dir="ltr">
  <head>
    <title>Nominering</title>
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>

  <body>
    <?php navmeny(); ?>
      <header id="nom_header">
      <p class="loginsom"><?php echo $_SESSION['brukerTypeNavn'] ,": ", $_SESSION['brukerID']?> </p>
        <h1>Nominasjon</h1>
      </header>
      <main id="nom_main">

        <!-- Form til mobil-visning -->
        <article class="nom_artikkel">
          <h2 id="nom_h2">Nominer ny kandidat</h2>
          <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"><!-- data i form sendes til samme side -->
          <table id="ny_kandidat">
            <tr>
              <th>
                <label for='nom_select'>Velg en kandidat</label>
              </th>
            <tr>
              <td>
                <select name="valg" id="nom_select">
                  <?php foreach($brukere as $bruker) : ?>
                    <option value="<?= $bruker['epost']; ?>"><?= $bruker['fnavn'];?><?php echo " ";?><?= $bruker['enavn'];?></option>
                  <?php endforeach; ?>
                </select>
              </td>
            </tr>
            </tr>
            <tr>
              <th><label for='fakultet'>Fakultet</label></th>
            </tr>
            <tr>
              <td><input type="text" id="fakultet" name="fakultet" class="nom_input1"></td>
            </tr>
            <tr>
              <th><label for='institutt'>Institutt</label></th>
            </tr>
            <tr>
              <td><input type="text" id="institutt" name="institutt" class="nom_input1"></td>
            </tr>
            <tr>
              <th><label for='info'>Informasjon</label></th>
            </tr>
            <tr>
              <td>
                <textarea name="info" id="info" class="nom_input" maxlength="250"></textarea>
              </td>
            </tr>
            <tr>
              <td>
                <button id="reg_knapp" class="button" name="Nominering" type="submit">Nominer</button>
              </td>
            </tr>
          </table>
          </form>
          
          <!-- form til desktop visning -->
          <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> <!-- data i form sendes til samme side -->
          <table id="ny_kandidat2">
            <tr>
              <th><label for='nom_select2'>Velg en kandidat</label></th>
              <td>
                <select name="valg" id="nom_select2">
                <?php foreach($brukere as $bruker) : ?>
                  <option value="<?= $bruker['epost']; ?>"><?= $bruker['fnavn'];?><?php echo " ";?><?= $bruker['enavn'];?></option>
                <?php endforeach; ?>
                </select>
              </td>
            </tr>
            <tr>
              <th><label for='fakultet2'>Fakultet</label></th>
              <td><input type="text" id="fakultet2" name="fakultet" class="nom_input2"></td>
            </tr>
            <tr>
              <th><label for='institutt2'>Institutt</label></th>
              <td><input type="text" id="institutt2" name="institutt" class="nom_input2"></td>
            </tr>
            <tr>
              <th><label for='info2'>Informasjon</label></th>
              <td><textarea name="info" id="info2" class="nom_input" maxlength="250"></textarea></td>
            </tr>
            <tr> 
              <td></td>
              <td><button id="reg_knapp2" class="button" name="Nominering" type="submit">Nominer</button></td>
          </tr>    
          </table>
      </article>

      <article id="bryt2" class="nom_artikkel">
        <h2>Nominerte Kandidater</h2>
        <form method="POST" action="nominering.php"></form><!-- data fra form sendes til samme side -->
          

              <?php 
              //sjekker om det finnes registrerte kandidater
              $sql = "SELECT bruker, fakultet, institutt, informasjon from kandidat where trukket is null";
              $stmt = $db->prepare($sql);
              $stmt->execute();
              $rad = $stmt->fetch(PDO::FETCH_ASSOC);
              //Hvis kandidat eksisterer
              if($rad){
                ?>
              <table id="registrert_kan">
                <tr>
                  <th>Bilde</th>
                  <th>Navn</th>
                  <th>Informasjon</th>
                </tr> 
                <?php
                //$sql = "SELECT bruker, fakultet, institutt, informasjon from kandidat where trukket is null";
                $sql = "SELECT bruker, enavn FROM bruker RIGHT JOIN kandidat ON bruker=epost where trukket is null ORDER BY enavn";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                //Kjører igjennom rad for rad og viser resultater i tabellen
                while($rad = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $alttekst="standardbilde";
                    $sql = "SELECT fnavn, enavn, epost from bruker where epost=?";
                    $stmt1 = $db->prepare($sql);
                    $stmt1->execute([$rad['bruker']]);
                    $resultat = $stmt1->fetch(PDO::FETCH_ASSOC);
                    $fornavn = (current($resultat));
                    $etternavn = next($resultat);
                    $etternavn = (current($resultat));
                    $navn = $fornavn . " ". $etternavn;
                    $epost = (end($resultat));
                    
                    //Finner bilde tilhørende hver kandidat
                    $sql = "SELECT bilde from kandidat where bruker=?";
                    $stmt2 = $db->prepare($sql);
                    $stmt2->execute([$rad['bruker']]);
                    $bildeId = $stmt2->fetch(PDO::FETCH_COLUMN);
                    //Viser standard profilbilde
                    if(empty($bildeId)){
                      $bilde = "bilder/default.jpg";
                      
                      //Finner stien til gjeldende brukers bilde 
                    } else {
                      $sql = "SELECT hvor,alt from bilde where idbilde =?";
                      $stmt3 = $db->prepare($sql);
                      $stmt3->execute([$bildeId]);
                      $bresultat = $stmt3->fetch(PDO::FETCH_ASSOC);
                      $bilde_sti = "bilder/";
                      $bilde = $bilde_sti.$bresultat['hvor'];
                      $alttekst=$bresultat['alt'];
                    }

                    $counter = $counter + 1;

                    //For hver bruker som ligger lagret i kandidat vises en ny rad i nominerte kandidater tabellen
                    echo "<tr>
                          <td><img class='mini_bilde' src=\"bilde.php?bilde=".$bilde."\" alt=\"$alttekst\"></td>
                          <td>".$navn."</td>
                          <td>". "<a href='informasjon.php?message=$epost' targetLink='$counter' alt='Informasjon'>Ytterligere info</a>". "</td>
                    </tr>";
                }
              } 
              else{
                echo "<h3 id='ingenNominerte'>Det finnes ingen nominerte kandidater </h3>";
                
              }
              ?>
          </table>   
        </form>
      </article>
      
    </main>
    <footer>
        <p>Copyright ©2021 Hønefoss/Norge - Rapp 20 G04</p>
    </footer>
  </body>
</html>