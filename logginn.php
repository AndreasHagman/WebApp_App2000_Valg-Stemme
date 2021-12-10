<?php
// Kode skrevet av Waleed 
// Kode kontrollert Endre 
session_start();

include 'funksjoner.php';
//Kommentert ut for å få appen til å funke uten email-vertifisering
//include 'meldinger.php';

testOmAlleredeLoggetInn();

//Databasetilkobling - PDO
include("db_pdo.php");
$db = new myPDO();

$refresh=false;
if(isset($_COOKIE["refreshOK"])){
  setcookie("antallfeil","",time() - 3600);
  echo "<script>document.cookie ='refreshOK; expires=01 Jan 1970 00:00:00 UTC;'</script>";
  $refresh=true;
} 

if(isset($_COOKIE['antallfeil'])){
  $verdi=$_COOKIE['antallfeil'];
}else{
$verdi=0;
setcookie("antallfeil",$verdi, "/" );
}

//Coockie som er satt dersom en bruker er utestengt
if(isset($_COOKIE['utestengt'])){
  //Oppretter et datoobjekt
  $date = new DateTime();
  //gir datoen en timestampverdi(unix-format) som senere må formateres til riktig dato-format
  $date->setTimestamp($_COOKIE['utestengt']);
  //For å bruke verdien som en dato skriver vi det slik "$date->format('Y-m-d H:i:s')";
  alert("Du har forsøkt for mange ganger. Nettstedet er låst til " . $date->format('Y-m-d H:i:s'), "Rød");
}

if(isset($_POST['logginn'])) {
  //Sjekker om brukeren har en utestengelse
  if(!isset($_COOKIE['utestengt'])){
    //Brukeren har ikke en utestengelse
    //Tester om begge feltene er fylt inn
    if(!empty($_POST['epost']) && !empty($_POST['passord'])){
      $epost = ($_POST['epost']);
      $passord= ($_POST['passord']);
      $kombinert = $salt . $passord;
      $spw = sha1($kombinert);
    
      //Sjekker om input fra bruker eksisterer i databasen
      $query = "SELECT * FROM bruker WHERE epost=? AND passord=?";
      $stmt = $db->prepare($query);
      $stmt->execute([$epost, $spw]);

      //Oppretter en variabel(array) med brukerinfo
      $bruker = $stmt->fetch(PDO::FETCH_ASSOC);

      //Tester om bruker er satt, arrayet $bruker vil gi False om det ikke inneholder data
      if($bruker){
        opprettInaktivitetTid();
        setcookie("antallfeil","",time() - 3600);
          if((int)$bruker['brukertype'] == 2){
            //For å få appen til å funke uten Sms-vertifisering

              //header('Location: bekreft.php?id='.$epost.'');

              $_SESSION['brukerID'] = $epost;
              $_SESSION['brukerType'] = (int)$bruker['brukertype'];
              $_SESSION['brukerTypeNavn'] = "Bruker";
              header("Location: minside.php?message=logginnGodkjent");
              
          } else {
              //Vellykket log inn
              $_SESSION['brukerID'] = $epost;
              $_SESSION['brukerType'] = (int)$bruker['brukertype'];
              switch($_SESSION['brukerType']){
                case 1:
                    $_SESSION['brukerTypeNavn'] = "Bruker";
                    break;
                case 2:
                    $_SESSION['brukerTypeNavn'] = "Administrator";
                    break;
                case 3:
                    $_SESSION['brukerTypeNavn'] = "Kontrollør";
                    break;
                default:
                    $_SESSION['brukerTypeNavn'] = null;
            }
              header("Location: minside.php?message=logginnGodkjent");
              exit();
          }  
      }
      else{
        //Feilet log inn
        $verdi+=1;
        setcookie("antallfeil",$verdi, "/" );
        echo $_COOKIE['antallfeil'];
        if ($_COOKIE['antallfeil']==4){setcookie("utestengt",time() + 180, time() + 180, "/" );}
        header("Location: logginn.php?message=loginn_feilet");
      }
    }
    else{ //Inputfeltene er tomme
        $verdi+=1;
        setcookie("antallfeil",$verdi, "/" );
        echo $_COOKIE['antallfeil'];
        if ($_COOKIE['antallfeil']==4){setcookie("utestengt",time() + 180, time() + 180, "/" );}
        header("Location: logginn.php?message=loginn_feilet");
    }
  }else { alert("Du er utestengt","Rød"); }
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

        <!-- <h1 id="loginn_overskrift">Login for brukere</h1> -->

      <!-- Lage en boks til login -->
      <form class="box" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post"><!-- data i form sendes til samme side -->
        <!-- Overskrift -->
        <h2>LOGG INN</h2>
        <!-- Informasjon til bruker -->
        <p>Vennligst skriv inn epost og passord</p>
        <!-- Input for brukernavn(epost) -->
        <input type="text" name="epost" id="epost" placeholder="epost" autofocus/>
        <!-- Input for passord -->
        <input type="password" name="passord" id="passord" placeholder="passord"/>
        <!-- Et felt som inneholder tilbakemelding dersom caps-lock er på -->
        <aside id="caps_melding"></aside>
        <!--Checkbox for å krysse av på om du vil se passord -->
        <label for="vis_passord">Vis passord</label><input type="checkbox" id="vis_passord" onclick="visPassord()"><br>
        <!-- En knapp for å logge inn -->
        <input  type="submit" name="logginn" id="logginn" class="button" <?php if(isset($_COOKIE['utestengt'])){?><?php echo 'disabled' ?> style="background-color: grey;  border: 3px solid grey; cursor: default;"<?php } ?> value="logg inn"/>
        <section id="feiletLoggInn">
          <?php if(isset($_COOKIE['antallfeil'])){
            if($_COOKIE['antallfeil']>0 && $refresh==false){ ?>
          <p id="logginnFeilet1" style="color:red;">Logg inn feilet <?php echo $_COOKIE['antallfeil'] ?> av 5, prøv på nytt</p>
          <?php }
          } ?>
          <p id="nedtelling" style="color:red;"></p>
        </section>
        <!-- Lenke til hvis man har glemt passord -->
        <p> <a id="glemtpassord" href="glemtpw.php">Glemt passord?</a> </p>
      </form>
      <!-- Script som sjekker om Caps-lock er aktivert eller ei -->
      <script>
      var input = document.getElementById("passord");
      var text = document.getElementById("caps_melding");
      input.addEventListener("keyup", function(event) {

      if (event.getModifierState("CapsLock")) {
        caps_melding.textContent = "Caps-lock er på";
      } else {
        caps_melding.textContent = ""
      }
      });

      //Funkjson for å vise eller skjule passord
      function visPassord() {
        var x = document.getElementById("passord");
        if (x.type === "password") {
          x.type = "text";
        } else {
          x.type = "password";
        }
      }

      //Funksjon som teller ned antall sekunder til bruker kan logge inn
      function nedtelling(sekunder){
      var timeleft = sekunder;
      var downloadTimer = setInterval(function(){
        if(timeleft <= 0){
          clearInterval(downloadTimer);
          document.getElementById("nedtelling").innerHTML = "Refresh nettsiden for å prøve igjen";
          document.cookie ="refreshOK";
        } else {
          document.getElementById("nedtelling").innerHTML = timeleft + " seconds remaining";
        }
        timeleft -= 1;
      }, 1000);
    }
      </script>
      <?php 
      //Beregner hvor mange sekunder som er igjen til brukeren kan logge inn
      if(isset($_COOKIE['utestengt'])){
        $sekunder=strtotime($date->format(AarMaanedDagTimeMinutterSekunder))-strtotime(date(AarMaanedDagTimeMinutterSekunder));
        echo"<script type='text/JavaScript'>nedtelling({$sekunder});</script>";}
       ?>
    </main>

      <footer>
        <p>Copyright ©2021 Hønefoss/Norge - Rapp 20 G04<p>
      </footer>
</body>
</html>