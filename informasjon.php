<?php
//Kode skrevet av Remi
//Kode kontrollert av Petter
session_start();

include 'funksjoner.php';
include 'meldinger.php';

//Databasetilkobling - PDO
include("db_pdo.php");
$db = new myPDO();

bestemBrukerTypeNavn($db);
testInaktivitet60min("nySide");
testOmBrukerIdErTilegnet();

//Sjekker om kravene for å få tilgang til siden blir godkjent -> Samme som for nominering
if ($_SESSION['startforslag']>date(AarMaanedDagTimeMinutterSekunder) || $_SESSION['sluttforslag']<date(AarMaanedDagTimeMinutterSekunder)){
  //echo "Midlertidig melding -> Ikke tilgang til denne siden";
  header("Location: {$_SESSION['url']}?message=ikketilgang");
  exit();
}

if(empty($_GET['message'])){
  header("Location: nominering.php?message=trykkPåLenke");
}


//Brukeren har nå tilgang til denne siden -> oppdaterer sessionvariabel
$_SESSION['url']='informasjon.php';

$sql = "SELECT epost, enavn FROM bruker RIGHT JOIN kandidat ON bruker=epost where trukket is null ORDER BY enavn desc limit 1";
 $stmt = $db->prepare($sql);
 $stmt->execute();
 $epost= $stmt->fetch(PDO::FETCH_ASSOC);
 $siste= $epost['epost'];

 $sql = "SELECT epost, enavn FROM bruker RIGHT JOIN kandidat ON bruker=epost where trukket is null ORDER BY enavn limit 1";
 $stmt = $db->prepare($sql);
 $stmt->execute();
 $epost= $stmt->fetch(PDO::FETCH_ASSOC);
 $første= $epost['epost'];

 $enavn = $_GET['message'];
 
 $skjulbegge=false;
 $skjulsiste= false;
 $skjulførste= false;
if($enavn == $siste && $enavn == $første){
  $skjulbegge=true;
}else if($enavn == $siste){
  $skjulsiste= true;
}else if($enavn == $første){
  $skjulførste= true;
}

//Kode for å hente forrige kandidat
if (isset($_POST['forrige'])) {  
  testInaktivitet60min("databaseEndring");
  $sql = "SELECT epost
          FROM bruker
          RIGHT JOIN kandidat ON bruker=epost
          WHERE enavn < ? AND trukket IS NULL ORDER BY enavn desc LIMIT 1";
  $stmt = $db->prepare($sql);
  $stmt->execute([$enavn]);
  $rad = $stmt->fetch(PDO::FETCH_COLUMN); 
  $epost = $rad;

  header('Location: informasjon.php?message='.$epost);
}
//Kode for å hente neste kandidat
if (isset($_POST['neste'])) { 
  testInaktivitet60min("databaseEndring");
  $sql = "SELECT epost
          FROM bruker
          RIGHT JOIN kandidat ON bruker=epost
          WHERE enavn > ? AND trukket IS NULL ORDER BY enavn";
  $stmt = $db->prepare($sql);
  $stmt->execute([$enavn]);
  $rad = $stmt->fetch(PDO::FETCH_COLUMN); 
  $epost = $rad;
  header('Location: informasjon.php?message='.$epost); 
  }

$sql = "SELECT epost, fnavn, enavn, trukket FROM kandidat, bruker WHERE kandidat.bruker=bruker.epost AND trukket is NULL ORDER BY fnavn";
$stmt = $db->prepare($sql);
$stmt->execute();
$brukere = $stmt->fetchAll();
?>

<!DOCTYPE html>
  <html lang="no" dir="ltr">
  <head>
    <title>Informasjon</title>
    
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>

  <body>
    <header>
      <?php navmeny(); ?>
    </header>

    <script>
      window.addEventListener('load', function(){
 
      var touchoverflate = document.getElementById('bryt2'),
          startX,
          startY,
          dist,
          threshold = 100, 
          allowedTime = 200, 
          elapsedTime,
          startTime
  
      touchoverflate.addEventListener('touchstart', function(e){
          var touchobj = e.changedTouches[0]
          dist = 0
          startX = touchobj.pageX
          startY = touchobj.pageY
          startTime = new Date().getTime() 
          e.preventDefault()
        }, false)
  
      touchoverflate.addEventListener('touchmove', function(e){
          e.preventDefault() 
        }, false)
  
      touchoverflate.addEventListener('touchend', function(e){
          var touchobj = e.changedTouches[0]
          dist = touchobj.pageX - startX 
          elapsedTime = new Date().getTime() - startTime 

          if (elapsedTime <= allowedTime && Math.abs(dist) >= threshold){
            if (dist <= 100) {
              document.getElementById("infoKnappNeste").click();
            } 
            else {
              document.getElementById("infoKnappForrige").click();
            }
          }    
          e.preventDefault()
        }, false)
      }, false) 
    </script>

    <main>
      <p class="loginsom"><?php echo $_SESSION['brukerTypeNavn'] ,": ", $_SESSION['brukerID']?> </p>
      <h1 id="informasjon_overskrift">Informasjon om kandidat</h1>

      <article id="bryt2" class="nom_artikkel">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"></form><!-- data fra form sendes til samme side -->
            
              <?php 
                $sql = "SELECT bruker, fakultet, institutt, informasjon, enavn FROM bruker RIGHT JOIN kandidat ON bruker=epost where trukket is null ORDER BY enavn";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                //Kjører igjennom rad for rad og viser resultater i tabellen
                 while($rad = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $sql = "SELECT fnavn, enavn, epost from bruker where epost=?";
                    $stmt1 = $db->prepare($sql);
                    $stmt1->execute([$rad['bruker']]);
                    $resultat = $stmt1->fetch(PDO::FETCH_ASSOC);
                    
                    $fornavn = (current($resultat));
                    $etternavn= next($resultat);
                    $navn = $fornavn . " ". $etternavn;
                    $epost = (end($resultat));
                    //echo $epost; //Denne kan vi bruke til å sjekke mot brukeren som er sendt over fra nominering.php
                    
                    //Finner bilde tilhørende hver kandidat
                    $sql = "SELECT bilde from kandidat where bruker=?";
                    $stmt2 = $db->prepare($sql);
                    $stmt2->execute([$rad['bruker']]);
                    $bildeId = $stmt2->fetch(PDO::FETCH_COLUMN);
                    //Viser standard profilbilde
                    if(empty($bildeId)){
                      $bilde = "bilder/default.jpg";
                      $alt = "Profilbilde";
                      
                      //Finner stien til gjeldende brukers bilde 
                    } else {
                      $sql = "select hvor, alt from bilde where idbilde =?";
                      $stmt3 = $db->prepare($sql);
                      $stmt3->execute([$bildeId]);
                      $resultat = $stmt3->fetch(PDO::FETCH_ASSOC);
                      $bilde1 = $resultat['hvor'];
                      $alt = $resultat['alt'];
                      $bilde_sti = "bilder/";
                      $bilde = $bilde_sti.$bilde1;
                    }
                    //Viser rad for den aktuelle kandidaten og stopper der
                    $mottatEpost = $_GET["message"];
                      if ($mottatEpost == $epost) {
                        echo "<section id='visInfo'>
                            <img src=".$bilde." alt=".$alt.">
                            <p class='infoTekst'>".$navn."</p>
                            <p class='infoTekst'>".$rad['fakultet']."</p>
                            <p class='infoTekst'>".$rad['institutt']."</p>
                            <p class='infoTekst'>".$rad['informasjon']."</p>
                            </section>";
                            
                            break;
                    } 
                }           
              ?>
            
      </article>
      <section id='knappsection'>
        <form action="informasjon.php?message=<?php echo $etternavn ?>" method="POST">
          <button type="submit" <?php if($skjulførste || $skjulbegge){echo "disabled style='visibility: hidden;'";}?> name="forrige"  id="infoKnappForrige" class="previous round">&#8249;</button>
          <button type="submit" <?php if($skjulsiste || $skjulbegge){echo "disabled style='visibility: hidden;'";}?> name="neste"  id="infoKnappNeste"  class="next round">&#8250;</button>
        </form>            
      </section>
      

    </main>
    <footer>
      <p>Copyright ©2021 Hønefoss/Norge - Rapp 20 G04</p>
    </footer>
  </body>
</html>
