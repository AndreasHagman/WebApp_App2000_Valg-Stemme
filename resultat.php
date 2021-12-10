<?php

//Kode skrevet av Endre og Andreas
//Kode kontrollert av Petter
session_start();

//Databasetilkobling - PDO
include("db_pdo.php");
$db = new myPDO();

include 'funksjoner.php';
include 'meldinger.php';

bestemBrukerTypeNavn($db);
testInaktivitet60min("nySide");
testOmBrukerIdErTilegnet();

//Sjekker om kravene for å få tilgang til siden blir godkjent
if ($_SESSION['brukerType'] == 1 || $_SESSION['kontrollert']!=null){
  //echo "Midlertidig melding -> Ikke tilgang til denne siden";
  header("Location: {$_SESSION['url']}?message=ikketilgang");
  exit();
}

//Brukeren har nå tilgang til denne siden -> oppdaterer sessionvariabel
$_SESSION['url']='resultat.php';
?>

  <!DOCTYPE html>
  <html lang="no" dir="ltr">
  
  <head>
  <title>Valgresultat</title>
      <link rel="stylesheet" type="text/css" href="css/index.css">
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>
  
  <body>
  <?php navmeny(); ?>
  <main>
  <p class="loginsom"><?php echo $_SESSION['brukerTypeNavn'] ,": ", $_SESSION['brukerID']?> </p>
     <h1 id="resultat_overskrift">Valgresultat</h1>
     <h2>Her ser du resultatlisten så langt i valget:<h2>

        <?php

          $sql = "SELECT bruker, stemmer FROM kandidat where trukket is null ORDER BY stemmer desc";
          $stmt = $db->prepare($sql);
          $stmt->execute();
          
          $kandidater=$stmt->fetchAll();

          if($kandidater!= null){
          //Da kan resultater vises
          echo "
              <section id='resFremvisning' class='border'>
              <table id='fremvisningTabell' class='fargeDefaultBoks'>
              ";
          
              $sql = "SELECT bruker, stemmer FROM kandidat where trukket is null ORDER BY stemmer desc";
              $stmt = $db->prepare($sql);
              $stmt->execute();

         
            $plassering="0";
            foreach ($kandidater as $kandidat){
              $plassering ++;
              $sql = "SELECT fnavn, enavn FROM bruker WHERE epost=?";
              $stmt = $db->prepare($sql);
              $stmt-> execute([$kandidat['bruker']]);
              $navn = $stmt->fetch(PDO::FETCH_ASSOC);
              if ($kandidat['stemmer']==null){
                $kandidat['stemmer']="0";
              }

              echo "<tr>
                    <td>".$plassering.". ".$navn['fnavn']." ".$navn['enavn']."</td>
                    <td class='stemmer'>".$kandidat['stemmer']."</td>
                    </tr>";
            }
             echo "
                </table>
                </section>";
          } else {
            echo "<h3 id='tomResultat'>Resultatlisten er tom</h3>";
          }
        ?>

        </main>

    <footer>     
        <p>Copyright ©2021 Hønefoss/Norge - Rapp 20 G04</p>
    </footer>
    </body>
</html>