<?php
//Kode skrevet av Remi, Endre, Waleed, Andreas, Petter
//Kode kontrollert av Petter, Andreas, Waleed, Endre, Remi
session_start();

include 'funksjoner.php';
include 'meldinger.php';

$bildesti = "bilder/";
testInaktivitet60min("nySide");
testOmBrukerIdErTilegnet();

//Brukeren har nå tilgang til denne siden -> oppdaterer sessionvariabel
$_SESSION['url']='minside.php';

//Databasetilkobling - PDO
include("db_pdo.php");
$db = new myPDO();

bestemBrukerTypeNavn($db);

//Kode som kjører når bytt passord knappen trykkes
if(isset($_POST['ByttPWKnapp'])) {
  testInaktivitet60min("databaseEndring");
  $passordOppdatering = 'Usant';
    $epost = $_SESSION['brukerID'];

    //Kode som legger til funksjonalitet i bytting av passord 
    //Sjekker om begge feltene er fylt ut
    if(!empty($_POST['gammeltPassord']) && !empty($_POST['nyttPassord'])){
      $pw1 = ($_POST['gammeltPassord']);
      $pw2= ($_POST['nyttPassord']);

      //Sjekker om det nye passordet innfrir følgende krav:
      $storebokstaver = preg_match(regexATilZ, $pw2);
      $smaabokstaver = preg_match(regexATilZ, $pw2);
      $nummer = preg_match(regexNullTilNi, $pw2);

      //Salter, krypterer og bytter passord om alle kravene er oppfylt
      if ($storebokstaver == 1 && $smaabokstaver == 1 && $nummer == 1) {
        $kombinert1 = $salt . $pw1;
        $kombinert2 = $salt . $pw2;
        $spw1 = sha1($kombinert1);
        $spw2 = sha1($kombinert2);
  
        $query = "SELECT * FROM bruker WHERE epost=? AND passord=?";
        $stmt = $db->prepare($query);
        $stmt->execute([$epost, $spw1]);
  
        //Setter inn brukerinfo i et array
        $bruker = $stmt->fetch(PDO::FETCH_ASSOC);

        //Da vet vi at passordet stemte overens med brukerID (som er innlogget)
        if($bruker){
            //Oppdaterer passordet til brukeren
            $query2 = "UPDATE bruker SET passord=? WHERE epost=?";
            $stmt = $db->prepare($query2);
            $stmt->execute([$spw2, $epost]);

            //Gir melding om at passord har blitt endret
            if($stmt) {
              alert("Passord endret","Grønn");
          } else {
            alert("Oppdatering feilet","Rød");
          }
          //Gir melding om at feil gammelt passord er oppgitt
        } else {
          alert("Gammelt passord er skrevet inn feil","Rød");
        }
      }
    }
    //Gir melding om at alle felt må være fylt ut
     else{
      alert("Alle felt må være fylt ut","Rød");
    }

} else {
  //Gjør ingenting
}

$sql_endreInfo = "SELECT * FROM kandidat WHERE bruker=? AND trukket='j'";
$stmt = $db->prepare($sql_endreInfo);
$stmt->execute([$_SESSION['brukerID']]);
$samleStatisk = $stmt->fetchAll();


//Prøvde å legge koden i toppen -> ikke alle valgene ville skjules
//Kode som skjuler valgene om å trekke seg eller endre sin kandidatinfo dersom valget har startet
finndato($db);
$skjul=false;
if ($_SESSION['startvalg']<date(AarMaanedDagTimeMinutterSekunder)){
   //Skjuler mulighet til å fjerne sin reservasjon mot nominering
   //Skjuler mulighet til å endre kandidatinfo
    //Skjuler mulighet til å trekke seg som kandidat og reserver seg mot en nominasjon
   $skjul=true;
}


if(isset($_POST['submit_fjernTrukket'])) {
  testInaktivitet60min("databaseEndring");
  //Kjører funksjon for å slettet et eventuelt tilhørende bilde
  slettbilde($db);

  $sql = "DELETE FROM kandidat WHERE bruker=?";
  $stmt = $db ->prepare($sql);
  $stmt->execute([$_SESSION['brukerID']]);
  header('Location: minside.php?message=fjernTrukket');
    exit();
  }
?>

<?php
//Kode som kjører dersom bruker velger å reservere seg(trykker på "bekreft valg") mot kandudatur
if (isset($_POST['TrekkNominasjonBekreft'])){
  testInaktivitet60min("databaseEndring");

  //Henter verdi fra form med js
  $svarTrekk = $_POST['inputTrekk'];

  //Hvis bruker virkelig ønsker å reservere seg mot kandidatur blir kode kjørt.
  if ($svarTrekk == 'true') {
    $sql = "SELECT * FROM kandidat WHERE bruker =?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$_SESSION['brukerID']]);
    $funnet = $stmt->fetchAll();
    $funnet = count($funnet);
    
    //Sjekker om bruker er nominert eller ikke.
    //Hvis bruker ikke er nominert,  legger vi inn brukeren inn i kandidat-tabell, men med reservasjon mot å bli nominert.
    if ($funnet == 0){
      $trukket = "j";
      
      $sql = "insert into kandidat (bruker,trukket) VALUES (?,?)";
      $stmt = $db->prepare($sql);
      $vellykket = $stmt->execute([$_SESSION['brukerID'],$trukket]);

      if ($vellykket) {
        header('location: minside.php?message=reservert');
      }

    }else {
    $sql = "UPDATE kandidat SET trukket = 'j' WHERE bruker =?";
    $stmt = $db ->prepare($sql);
    $stmt->execute([$_SESSION['brukerID']]);
    header('location: minside.php?message=reservert');

    } 
  } 
} 
if(isset($_POST['TrekkNominasjon'])){
  testInaktivitet60min("databaseEndring");

  //Henter verdi fra form med js
  $svar = $_POST['inputSvar'];
          
      //Bruker ønsker fortsatt å trekke kandidatur
      if ($svar == 'true') {
          //Kaller på funskjon som sletter et eventuelt tilhørende bilde
          slettbilde($db);
          //Sletter kandidat
          $sql = "delete from kandidat where bruker=?";
          $stmt = $db->prepare($sql);
          $stmt->execute([$_SESSION['brukerID']]);

          header("location: minside.php?message=trukket");
                      
      }
    
      //Bruker avbryter trekking av kandidatur
      else {
        header("location: minside.php?message=ikkeTrukket");
      }   
}

if (isset($_POST['slettbrukerBTN'])){
  testInaktivitet60min("databaseEndring");
  //Sql som henter ut hvem den innloggede brukeren har stemt på
  $sql = "SELECT stemme FROM bruker where epost='".$_SESSION['brukerID']."'";
  $stmt = $db->prepare($sql);
  $stmt->execute();
  $stemme=$stmt->fetch(PDO::FETCH_COLUMN);
  if($stemme){
    //Brukeren har stemt og en stemmer må fjernes fra kandidaten som var stemt på
    $sql = "UPDATE kandidat set stemmer=stemmer-1 where bruker=?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$stemme]);
  }
  $sql = "SELECT stemmer from kandidat where epost=?";
  $stmt = $db->prepare($sql);
  $stmt->execute([$_SESSION['brukerID']]);
  $rad = $stmt->fetch(PDO::FETCH_ASSOC);

    //Henter verdi fra form med js
    $svarSlett = $_POST['inputSlett'];
    //Tester om brukeren virkelig ønsker å slette brukeren sin.
    if ($svarSlett == 'true') {
      //Sjekker om brukeren er en kandidat til valget
      $sql = "SELECT * FROM kandidat WHERE bruker =? and trukket is null";
      $stmt = $db->prepare($sql);
      $stmt->execute([$_SESSION['brukerID']]);
      $finnes = $stmt->fetchAll();
      if($finnes){
        alert("Du må fjerne ditt kandidatur før du kan slette din bruker","Rød");
      }else{
      $sql="DELETE FROM bruker where epost=?";
      $stmt = $db->prepare($sql);
      $stmt->execute([$_SESSION['brukerID']]);
      unset($_SESSION['brukerID']);
      header('Location: default.php?message=brukerslettet');
      }
    }else{
      alert("Din bruker ble ikke slettet", "Rød");
    } 
}



//Kode for å endre informasjon om kandidater
//sql som sjekker at brukeren er en kandidat
$sql_endreInfo = "SELECT * FROM kandidat WHERE bruker=? AND trukket is null";
$stmt = $db->prepare($sql_endreInfo);
$stmt->execute([$_SESSION['brukerID']]);
$samle = $stmt->fetchAll();
$samle = count($samle);

if ($samle == 0) {
  //Da er ikke brukeren registrert som kandidat, for brukeren ikke finnes i kandidat-tabellen
  //Må gjemme mulighet for å endre informasjon i kandidat-tabell
  
  } else {
      //Legge inn globalvariable som håndterer tilbakemeldinger til brukeren -->
      //Henter nåværende info om bruker for fakultet, institutt, informasjon, stemmer
      $sql_endreInfo = "SELECT * FROM kandidat WHERE bruker=?";
      $stmt = $db->prepare($sql_endreInfo);
      $stmt->execute([$_SESSION['brukerID']]);

      $bruker = $stmt->fetch(PDO::FETCH_ASSOC);
      $fakultet = $bruker['fakultet'];
      $instiutt = $bruker['institutt'];
      $informasjon = $bruker['informasjon'];

      //Endre informasjon 
      if (isset($_POST['submit_oppdatereInfo'])) {
        testInaktivitet60min("databaseEndring");
        //Sjekk på at ingen felter står tomme
        if ($_POST['fakultet'] == "" or $_POST['institutt'] == "" or $_POST['informasjon'] == "") {
          alert("Ingen felter kan stå tomme","Rød");
        } else {
          //Alle felter er fylt ut, nye verdier blir lagret i databasen
          $fakultet1 = $_POST['fakultet'];
          $institutt1 = $_POST['institutt'];
          $informasjon1 = $_POST['informasjon'];
        
          $sql_oppdatereInfo = "UPDATE kandidat SET fakultet=?, institutt=?, informasjon=? WHERE bruker=?";
          $stmt = $db->prepare($sql_oppdatereInfo);
          $stmt->execute([$fakultet1, $institutt1, $informasjon1, $_SESSION['brukerID']]);

          //Hvis endring er utført/godkjent, refreshes siden
          if ($stmt->execute()) {
            header('Location: minside.php?message=endringVellykket');
           
          } else {
            //Dersom ny data ikke blir lagret i databasen
            alert("Noe gikk galt","Rød");
          }
       } 
    }
  }
  //Kode som henter ut informasjon om valget 
  $valg = $stmt->fetch(PDO::FETCH_ASSOC);
  $startforslag = date(AarMaanedDagTimeMinutterFormattert, strtotime($_SESSION['startforslag']));
  $sluttforslag = date(AarMaanedDagTimeMinutterFormattert, strtotime($_SESSION['sluttforslag']));
  $startvalg = date(AarMaanedDagTimeMinutterFormattert, strtotime($_SESSION['startvalg']));
  $sluttvalg = date(AarMaanedDagTimeMinutterFormattert, strtotime($_SESSION['sluttvalg']));
  $tittel = $_SESSION['tittel'];

  //Legger inn den nye informasjonen i databasen
  if(isset($_POST['submit_oppdatereValgInfo'])){
    testInaktivitet60min("databaseEndring");
    //Sjekker at ingen felter står tomme
    if ($_POST['startforslag'] == "" or $_POST['sluttforslag'] == "" or $_POST['startvalg'] == "" or $_POST['sluttvalg'] == "" or $_POST['tittel'] == "") {
          alert("Ingen felter kan stå tomme","Rød");
    } else {
      //Henter ut og legger verdier fra inputfelt i variabler
      $orgStartforslag = $_POST['startforslag'];
      $startforslag1 = date(AarMaanedDagTimeMinutterSekunder, strtotime($orgStartforslag));

      $orgSluttforslag = $_POST['sluttforslag'];
      $sluttforslag1 = date(AarMaanedDagTimeMinutterSekunder, strtotime($orgSluttforslag));

      $orgStartvalg = $_POST['startvalg'];
      $startvalg1 = date(AarMaanedDagTimeMinutterSekunder, strtotime($orgStartvalg));

      $orgSluttvalg = $_POST['sluttvalg'];
      $sluttvalg1 = date(AarMaanedDagTimeMinutterSekunder, strtotime($orgSluttvalg));

      $tittel1 = $_POST['tittel'];
      //Preg_match med regular expression som sjekker om admin har fylt inn feltene med riktige verdier og fomat
      $monster = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/";
      if (preg_match($monster, $startforslag1)){
        if (preg_match($monster, $sluttforslag1)){
          if (preg_match($monster, $startvalg1)){
            if (preg_match($monster, $sluttvalg1)){
              if($startforslag1<$sluttforslag1 && $sluttforslag1<$startvalg1 && $startvalg1<$sluttvalg1){
                //Input fra alle feltene er godkjent og tabellen i databasen oppdateres
              $sql_oppdatereValgInfo = "UPDATE valg SET startforslag=?, sluttforslag=?, startvalg=?, sluttvalg=?, tittel=?";
              $stmt1 = $db->prepare($sql_oppdatereValgInfo);
              $stmt1->execute([$startforslag1, $sluttforslag1, $startvalg1, $sluttvalg1, $tittel1]);
              
                //Hvis endring er utført/godkjent, refreshes siden
                if ($stmt1->execute()) {
                  include('genererinfovalg.php');
                  header('Location: minside.php?message=endringVellykket');
                } else {
                  //Dersom ny data ikke blir lagret i databasen
                  alert("Noe gikk galt","Rød");
                }
              } else {
                  alert("Ønsket rekkefølge: startforslag < sluttforslag < startvalg < sluttvalg","Rød");
              }
            } else {
                alert("Sluttvalg har feil format/verdier","Rød");
            }
          } else {
              alert("Startvalg har feil format/verdier","Rød");
          }
        } else {
            alert("Sluttforslag har feil format/verdier","Rød");
        }
      } else {
          alert("Startforslag har feil format/verdier","Rød");
      }
      
    }
  }

//Kode for å endre brukertype
//For henting av alle brukernavn
$sql = "SELECT epost, fnavn, enavn, brukertype FROM bruker ORDER BY fnavn";
$stmt = $db->prepare($sql);
$stmt->execute();
$brukere = $stmt->fetchAll();

//Sjekk på om knapp blir trykket på
if(isset($_POST['submit_endreBrukerType'])){
  testInaktivitet60min("databaseEndring");

  //Henter ut hvilken bruker som er valgt (epost og brukertype)
  $valgtBrukerUE = $_POST['brukerValg'];
  $valgtBrukerE = explode(',',$valgtBrukerUE);
  $valgtBrukerEpost = $valgtBrukerE[0];
  $gammelBrukerType = $valgtBrukerE[1];
  $valgtBrukerType = $_POST['velgBrukerType'];

  //Søker etter valgt bruker i kandidattabellen
  $sql_kandidat = "SELECT bruker FROM kandidat WHERE bruker=? AND trukket IS NULL";
  $stmt= $db->prepare($sql_kandidat);
  $stmt->execute([$valgtBrukerEpost]);
  $svar = $stmt->fetch(PDO::FETCH_COLUMN);
  //Sjekker om valg bruker er en kandidat
  if($svar){
    alert("brukeren er en kandidat","Rød");
  }else {
    //Sjekker om valgt bruker har fått tildelt ny brukertype
    if($valgtBrukerType != $gammelBrukerType){
      $sql_endreBrukerType = "UPDATE bruker SET brukertype=? WHERE epost=?";
      $stmt = $db->prepare($sql_endreBrukerType);
      $stmt->execute([$valgtBrukerType, $valgtBrukerEpost]);
      if ($stmt) { header("location: minside.php?message=brukertypeEndringGodkjent");}
      if($_SESSION['brukerID'] == $valgtBrukerEpost) {
        bestemBrukerTypeNavn($db);
      }

      //Sjekker om personen har reservert seg mot nominering
      $sql = "SELECT * FROM kandidat WHERE bruker=? and trukket='j'";
      $stmt = $db->prepare($sql);
      $stmt->execute([$valgtBrukerEpost]);
      $svar = $stmt->fetch(PDO::FETCH_ASSOC);
      if($svar){
        //Sletter brukeren slik at brukeren ikke kan avreservere seg og da fortsatt være en kandidat
        $sql = "DELETE FROM kandidat WHERE bruker=?";
        $stmt = $db->prepare($sql);
        $stmt->execute($valgtBrukerEpost);
      }
    }
    else{
      alert("Brukertypen er den samme for valgte bruker","Rød");
    }
  }
}

?>
<!DOCTYPE html>
  <html lang="no" dir="ltr">
  <head>
    <title>Min side</title>
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <header>
      <?php navmeny(); ?>
      <p class="loginsom"><?php echo $_SESSION['brukerTypeNavn'] ,": ", $_SESSION['brukerID']?> </p>
      <h1 id="minside_overskrift">Min side</h1>
    </header>
  </head>
  <body>
  <main id=minsideMain>

<?php 
  //Kode som viser bildet til innlogget kandiat
  //Sjekker først om brukeren er en kandidat
  $sql = "SELECT bruker from kandidat where bruker=? and trukket is null";
  $stmt = $db->prepare($sql);
  $stmt->execute([$_SESSION['brukerID']]);
  $testbruker = $stmt->fetch(PDO::FETCH_ASSOC);
  if (empty($testbruker)){ 
    //valg om å laste opp bilde vises ikke

  } else {//Hvis bruker er en kandidat
    echo"
    <section id='byttBilde' class='minsideFlex'>
    <h2>Gjeldende bilde:</h2>";

      //Finner bildeid til brukeren som er logget 
      $sql = "select bilde from kandidat where bruker=?";
      $stmt = $db->prepare($sql);
      $stmt->execute([$_SESSION['brukerID']]);
      $bildeId = $stmt->fetch(PDO::FETCH_COLUMN);

      //Tester om brukeren allerede har bilde eller ikke
      $ingenbilde=false;
      if(empty($bildeId)){
        $ingenbilde=true;
        //Viser et standard eksempelbilde
        //bilder/deafult.jpg
        echo "
        <img src=bilder/default.jpg width='150' height='150' alt='bilde'>
        ";
  
      } else {
      //Finner stien til bildet ved å sammenligne bildeid i tabellen bilde
      $sql = "SELECT hvor,alt from bilde where idbilde =?";
      $stmt = $db->prepare($sql);
      $stmt->execute([$bildeId]);
      $bilde = $stmt->fetch(PDO::FETCH_ASSOC);
      $alttekst=$bilde['alt'];
      $bildenavn=$bilde['hvor'];
      //henter ut filtypen på bildet uten "." 
      $filtypedeling = explode('.', $bilde['hvor']);
      $filtype = strtolower(end($filtypedeling));

      //Henter ut høyde og bredde på bildet
      //list($w,$h)=getimagesize($bildesti.$bildenavn);
      //Setter ønsket bredde og regner ut riktig høyde
      //$ønsket_bredde= 250;
      //$ønsket_høyde = (int)(($h * $ønsket_bredde)/$w);
      //Bestemmer størrelsen på bildet
      $ønsket_bredde = 250;
      $ønsket_høyde = 250;
          
      //Regner ut for å gjøre bildet til ønsket størrelse
      list($w, $h) = getimagesize($bildesti.$bildenavn);
      $ratio_orig = $w/$h;

      if($ønsket_bredde/$ønsket_høyde > $ratio_orig){
          $ønsket_bredde = $ønsket_høyde*$ratio_orig;
      }
      else{
          $ønsket_høyde = $ønsket_bredde/$ratio_orig;
      }

      $mål = imagecreatetruecolor($ønsket_bredde,$ønsket_høyde);

      //Tester hvilken mime-type bildet har og utfører deretter tilhørende kode
      if (mime_content_type($bildesti.$bildenavn) == "image/jpeg" ) {
        $source = imagecreatefromjpeg($bildesti.$bildenavn);
        imagecopyresampled($mål, $source, 0, 0, 0, 0, $ønsket_bredde, $ønsket_høyde, $w, $h);
        //Lagrer den nye versjonen av bildet over det gamle
        imagejpeg($mål, $bildesti.$bildenavn,100);
      //Tester hvilken mime-type bildet har og utfører deretter tilhørende kode
      }elseif(mime_content_type($bildesti.$bildenavn) == "image.png" ){
        $source = imagecreatefrompng($bildesti.$bildenavn);
        imagecopyresampled($mål, $source, 0, 0, 0, 0, $ønsket_bredde, $ønsket_høyde, $w, $h);
        imagepng($mål, $bildesti.$bildenavn,100);
      //Tester hvilken mime-type bildet har og utfører deretter tilhørende kode
      }elseif(mime_content_type($bildesti.$bildenavn) == "image.bmp" ){
        $source = imagecreatefromwbmp($bildesti.$bildenavn);
        imagecopyresampled($mål, $source, 0, 0, 0, 0, $ønsket_bredde, $ønsket_høyde, $w, $h);
        imagewbmp($mål, $bildesti.$bildenavn,100);
      } 

  ?>
    <!--Fremviser bildet -->
    <!-- Viser profilbildet med stien som er hentet fra php-koden -->
      <img src="bilder/<?=$bildenavn?>" alt="<?=$alttekst?>">
    <!-- Lukker else-statement -->
    <?php } ?>
    <!-- Form med inputfeltene som åpner filbehandling og knapp for å laste opp -->
    <form action="" method="post" enctype="multipart/form-data">
    <input class="lastopp_bilde" type="file" name="fil" id="fillastesopp">
    <input class="lastopp_bilde" type="submit" value="Last opp" name="lastopp" id="lastoppknapp">
    <p id='alttekst'><label for='altinput'>Alt tekst til bilde: </label></p>
    <lable id='altlabel' data-domain='Bilde av:'>
    <input type='text' id='altinput' name='altinput' required>
    </lable>
    </form>
  <?php  
  //Kode som lagrer info om bilde i database og lagrer bilde i ønsket mappe -->
  if(isset($_POST['lastopp'])) {
    testInaktivitet60min("databaseEndring");
    //Henter navn og midlertidig plasserig av bildet
    $filnavn = $_FILES["fil"]["name"];
    $src= $_FILES["fil"]["tmp_name"];
    $størrelse = $_FILES["fil"]["size"];
    $error = $_FILES["fil"]["error"];
    $mime = $_FILES["fil"]["type"];


    //henter ut filtypen på bildet uten "." 
    $filtypedeling = explode('.', $filnavn);
    $filtype = strtolower(end($filtypedeling));

    $tillat = array('jpg', 'jpeg', 'png');
    //Test om brukeren laster opp godkjent filtype
    if(in_array($mime,array('image/jpeg','image/png','image/bmp'))){
      //Sjekker etter error
      if ($error === 0) {
        //Tester på størrelse 5mb
        if ($størrelse < 5000000){
          //Lager et nytt unikt filnavn
          $nyttfilnavn = uniqid('',true).".".$filtype;
          //Bestemmer hvilken mappe bildet skal flyttes til
          $mappe = $bildesti . $nyttfilnavn;
          //Henter ut alt-teksten
          $alt=$_POST['altinput'];
          //Fyller inn ønskede felter i tabellen bilde
          $verdi = "Bilde av: " . $alt;
          $sql = "INSERT into bilde (hvor,alt) VALUES (?, ?)";
          $stmt = $db->prepare($sql);
          $stmt->execute([$nyttfilnavn, $verdi]);
          

          $sql = "SELECT bilde FROM kandidat WHERE bruker=?";
          $stmt = $db->prepare($sql);
          $stmt->execute([$_SESSION['brukerID']]);
          $gammelBildeId = $stmt->fetch(PDO::FETCH_COLUMN);


          //Henter ut den automatisk genererte bildeiden
          $sql = "SELECT idbilde from bilde where hvor=?";
          $stmt = $db->prepare($sql);
          $stmt->execute([$nyttfilnavn]);
          $bilde = $stmt->fetch(PDO::FETCH_COLUMN);
          //Skriver bildeid inn i kandidattabell på riktig kandidat
          $sql = "UPDATE kandidat set bilde=(?) where bruker=?";
          $stmt = $db->prepare($sql);
          $stmt->execute([$bilde,$_SESSION['brukerID']]);

          if(empty($gammelBildeId)){
          //Brukeren har ikke bilde
          } else {
          //Sletter et eventuelt bilde som bruker har lagt inn
          $sql = "SELECT hvor FROM bilde WHERE idbilde=?";
          $stmt = $db->prepare($sql);
          $stmt->execute([$gammelBildeId]);
          $bilde = $stmt->fetch(PDO::FETCH_COLUMN);
          //sletter bilde fra mappen
          $sti = "bilder/$bilde";
          unlink($sti);
          //bilde slettes og referanse i tabell bilde kan slettes
          $sql = "DELETE FROM bilde WHERE idbilde=?";
          $stmt = $db->prepare($sql);
          $stmt->execute([$gammelBildeId]);
          }
      
          //Flytter bildet til ønsket mappe
          if (move_uploaded_file($src, $mappe)){
            if($stmt->execute()){
              header('Location:minside.php?message=bildegodkjent');
              //Bildet ble endret
            }
          }else{
            alert("Kunne ikke flytte bildet til ønsket destinasjon","Rød");
          }

        } else {
          alert("Bildet er for stort","Rød");
        }
      } else {
        alert("Det oppstod en feil under opplasting av ditt bilde","Rød");
      }
    }else{
      alert("Du kan ikke laste opp filer av denne typen", "Rød");
    }
  }
  echo "</section>";
}

if($skjul==true || $samle == 0){
  //Ikke vis dette innholdet
} else {
    //vis dette innholdet
    echo "<section id='kandidatInfoHide' class='minsideFlex'>
        <article id='kandidatInfo'>
        <h2>Endre din kandidatinfo</h2>
        <form method='POST' action="; htmlspecialchars($_SERVER['PHP_SELF']);echo">
     <table id='endreInfoTabell'>
        <tr>
          <th><label for='fakultetE'>Fakultet</label></th>
        </tr>
        <tr>
          <td><input type='text' id='fakultetE' name='fakultet' value='$fakultet'></td>
        </tr>
        <tr>
          <th><label for='instituttE'>Institutt</label></th>
        </tr>
        <tr>
          <td><input type='text' id='instituttE' name='institutt' value='$instiutt'></td>
        </tr>
        <tr>
          <th><label for='infoE'>Informasjon</label></th>
        </tr>
        <tr>
          <td>
            <textarea name='informasjon' id='infoE' maxlength='250'>$informasjon</textarea>
          </td>
        </tr>
        <tr>
          <td><button id='endre_knapp' class='button' name='submit_oppdatereInfo' value='Endre' type='submit'>Oppdater info</button></td>
        </tr>
      </table>
      </form>
</article>
</section>";   
}

//Sql for å sjekke om brukeren i det hele tatt kan bli en kandidat.
$sql = "SELECT * FROM bruker WHERE epost=? AND (fnavn='' OR enavn='')";
$stmt = $db->prepare($sql);
$stmt->execute([$_SESSION['brukerID']]);
$sjekk = $stmt->fetchAll();
$sjekk = count($sjekk);

  //Sql for å sjekke om bruker er en kandidat
  $sql_sjekkReservasjon = "SELECT * FROM kandidat WHERE bruker=? AND trukket is not null";
  $stmt = $db->prepare($sql_sjekkReservasjon);
  $stmt->execute([$_SESSION['brukerID']]);
  $samle3 = $stmt->fetchAll();
  $samle3 = count($samle3);
  
  
  $nominert= true;
  if ($samle3 == 1 || $_SESSION['brukerType'] >= 2 ) {
    //Da har brukeren reservert seg mot å bli nominert.
    //Vi fjerner derfor mulighet for brukeren å se funksjon for reservasjon og trekk av kandidatur.
    $nominert= false;
  } else {
    //ikke gjør noe
  }


if($skjul == true || $sjekk == 1 || $nominert == false){}
else{
  echo "<section id='trekkNominasjon' class='minsideFlex'>

    <form method='POST' action="; htmlspecialchars($_SERVER['PHP_SELF']);echo">
    <p id='trekknom'><label for='trekkNominasjonBTN'>Reservasjon mot kandidatur/trekk kandidatur</label></p>
    
    <span id=trekknomspan>
    <input type='checkbox' id='trekkNominasjonBTN' value='TrekkNominasjon' required style='margin-left: 10px;'>
    <button onclick='trekk()' type='='inputTsubmit' class='button' id='trekkNominasjonBekreft' name='TrekkNominasjonBekreft' style='margin-left: 10px;'>Bekreft valg</button>
    <input type='hidden' id='inputTrekk' value='' name='inputTrekk'>
    </span>
    
    <!-- Huske å endre navn på variabler/funksjoner -->
    <script>
    document.getElementById('trekkNominasjonBekreft').onclick = function() {trekk()};

    function trekk() {
      var svarTrekk = confirm('Ønsker du virkelig å reservere deg mot å bli nominert?')

      document.getElementById('inputTrekk').value = svarTrekk;
    }
    </script>
    </form>
    </section>";
}
?>

<!-- Tabell i en form for å bytte passord -->
<section id="byttPassordSec" class="minsideFlex">
<h2>Bytt passord</h2>
<form id="byttPassord" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"><!-- data i form sendes til samme side -->
<table id="byttPassordTB">
<tr>
    <th><label for='gammeltPassord'>Gammelt passord</label></th>
</tr>
<tr>
    <td><input type="password" name="gammeltPassord" id="gammeltPassord"/></td>
</tr>
<tr>
    <th><label for='nyttPassord'>Nytt passord</label></th>
</tr>
<tr>
    <td><input type="password" name="nyttPassord" id="nyttPassord" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Minimum 8 tegn, 1 tall, samt 1 stor og 1 liten bokstav."></td>
</tr>
</tr>
    <td id="caps_melding"></td>
</tr>
<tr>
  <td id='vis_passord'><label for='inputpassord'>Vis Passord</label><input id='inputpassord' type="checkbox" onclick="visPassord()"></td>
</tr>
<tr>
    <td><input type="submit" name="ByttPWKnapp" id="ByttPWKnapp" class="button" value="Bytt Passord"></td>
</tr>
</table>
</form>
<script>
  //Funkjon som sier ifra om caps-lock er på
  var input = document.getElementById("gammeltPassord");
      var text = document.getElementById("caps_melding");
      input.addEventListener("keyup", function(event) {
      if (event.getModifierState("CapsLock")) {
        caps_melding.textContent = "Caps-lock er på";
      } else {
        caps_melding.textContent = ""
      }
      });
      var input2 = document.getElementById("nyttPassord");
      input2.addEventListener("keyup", function(event) {
      if (event.getModifierState("CapsLock")) {
        caps_melding.textContent = "Caps-lock er på";
      } else {
        caps_melding.textContent = ""
      }
      });
//Funkjson for å vise eller skjule passord
function visPassord() {
        var x1 = document.getElementById("gammeltPassord");
        var x2 = document.getElementById("nyttPassord");
        if (x1.type === "password" && x1.type === "password") {
          x1.type = "text";
          x2.type = "text";
        } else {
          x1.type = "password";
          x2.type = "password";
        }
      }
</script>
</section>

<?php
if($_SESSION['brukerType'] != 2){}
else{
  echo "<section id='utnevnBrukerType' class='minsideFlex'>
  <h2>Endre brukertype</h2>
  
  <p>Velg en bruker og dens ønskede brukertype</p>
  <form method='POST' action="; htmlspecialchars($_SERVER['PHP_SELF']);echo">
  <article id='brukerTypeForm'>
  <select name='brukerValg' id='brukerValg'>";
  foreach($brukere as $bruker){
  echo "<option value='{$bruker['epost']},{$bruker['brukertype']}'>{$bruker['brukertype']} | {$bruker['epost']}</option>";}
  echo "</select>
  
  <select name='velgBrukerType' id='velgBrukerType'>
  <option value='1'>1 | Bruker</option>
  <option value='2'>2 | Administrator</option>
  <option value='3'>3 | Kontrollør</option>
  </select>
  
  <button id='endreBrukerType_knapp' class='button' name='submit_endreBrukerType' value='Endre' type='submit'>Oppdater info</button>
  </article>
  </form>
  </section>";
}


if($_SESSION['brukerType'] != 2){
  //Ikke vis innhold
}else{
  //Vis innhold
  echo" <section id='endreValg' class='minsideFlex'>
<form method='POST' action='minside.php'>
  <article id ='endrevalgform'>  
    <table id='endreValgInfoTabell'>
        <tr>
          <th><h2>Endre info om valg</h2></th>
        </tr>
        <tr>
          <th>Startforslag</th>
        </tr>
        <tr>
          <td><input type='datetime-local' id='startforslagE' class='endreValgInfo' name='startforslag' value='$startforslag'></td>
        </tr>
        <tr>
          <th>Sluttforslag</th>
        </tr>
        <tr>
          <td><input type='datetime-local' id='sluttforslagE' class='endreValgInfo' name='sluttforslag' value='$sluttforslag'></td>
        </tr>
        <tr>
          <th>Startvalg</th>
        </tr>
        <tr>
          <td>
            <input type='datetime-local' name='startvalg' class='endreValgInfo' id='startvalgE' value='$startvalg'>
          </td>
        </tr>
        <tr>
          <th>Sluttvalg</th>
        </tr>
        <tr>
          <td>
            <input type='datetime-local' name='sluttvalg' class='endreValgInfo' id='sluttvalgE' value='$sluttvalg'>
          </td>
        </tr>
        <tr>
        <th><label for='tittelE'>Tittel</label></th>
        </tr>
        <tr>
          <td>
            <input type='text' name='tittel' id='tittelE' value='$tittel'>
          </td>
        </tr>
        <tr>
          <td><button id='endre_knapp' class='button' name='submit_oppdatereValgInfo' value='Endre' type='submit'>Oppdater info</button></td>
        </tr>
      </table>
  </article>
      </form>
</section>";
}

if(empty($samleStatisk) || $skjul==true){
  //Ikke vis innhold
}else{
  //vis innhold
  echo" <section id='fjernTrukket' class='minsideFlex'>
<form action='minside.php' method='post'>
<p>Trykk her for å fjerne din reservasjon mot å bli nominert</p>
<button id='submit_fjernTrukket' class='button' name='submit_fjernTrukket' type='submit'>Fjern reservasjon</button>
</form>
</section>";
}

$sql="SELECT bruker from kandidat where bruker=? and trukket is null";
$stmt = $db->prepare($sql);
$stmt->execute([$_SESSION['brukerID']]);
$resultat = $stmt->fetchAll();
$kandidat=false;

if($resultat){
  $kandidat=true;
}

$brukertype=2;
$sql="SELECT brukertype from bruker where brukertype=?";
$stmt = $db->prepare($sql);
$stmt->execute([$brukertype]);
$antallAdmin = $stmt->rowCount();
$enadmin=false;
if($antallAdmin > 1) {
  $enadmin = false;
} else {
  //Admin kan ikke slettes
  $enadmin = true;
}

//Vises ikke dersom du er en kandidat og valget har startet 
if($_SESSION['startvalg']<date(AarMaanedDagTimeMinutterSekunder) && $kandidat==true || $enadmin==true && $_SESSION['brukerType']==2){
  //Skal ikke vises
}else{
  echo "<section id='slettbrukerseksjon' class='minsideFlex'>

    <form method='POST' action="; htmlspecialchars($_SERVER['PHP_SELF']);echo">
    <p>Trykk her for å slette din bruker</p>
    
    <span id=slettbruker>
    <button onclick='slett()' type='='inputTsubmit' class='button' id='slettbrukerBTN' name='slettbrukerBTN' style='margin-left: 10px;'>Slett bruker</button>
    <input type='hidden' id='inputSlett' value='' name='inputSlett'>
    </span>
    
    <!-- Huske å endre navn på variabler/funksjoner -->
    <script>
    document.getElementById('slettbrukerBTN').onclick = function() {slett()};

    function slett() {
      var svarSlett = confirm('Ønsker du virkelig å slette din bruker?')

      document.getElementById('inputSlett').value = svarSlett;
    }
    </script>
    </form>
    </section>";
  }
?>
  </main>
    <!-- Footer -->
    <footer>
      <p>Copyright ©2021 Hønefoss/Norge - Rapp 20 G04</p>
    </footer>
  </body>
</html> 