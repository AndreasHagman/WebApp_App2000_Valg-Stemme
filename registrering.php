<?php 
//Kode skrevet av Petter(Html) og Remi(Php). Siste gang endret 03.06.2021
//Kode kontrollert av Remi. Siste gang 03.06.2021
session_start();
include 'funksjoner.php';
include 'meldinger.php';

//Sender bruker tilbake til forrige side hvis bruker allerede er logget inn.
testOmAlleredeLoggetInn();

if (isset($_POST['Registrering'])) {
 
  //Tester om bruker oppgir gyldig epost
  if (filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)){
    //Tester på om mail inneholder riktig slutt
    if(preg_match("~@usn\.no$~",$_POST['mail'])){

      include("db_pdo.php");
      $db = new myPDO();  
  
      //Sjekker om epost allerede er registrert
      $lmail = strtolower($_POST['mail']);
      $validermail = "select lower(epost) as mail from bruker where lower(epost)=?";
      $validert = $db->prepare($validermail);
      $validert->execute([$lmail]);
      $testmail = $validert->fetch(PDO::FETCH_ASSOC);
    

      if ($testmail == null) {
        
        //Variabler for brukerinput
        $fnavn = $_POST['fornavn'];
        $fnavn = filter_var($_POST['fornavn'], FILTER_SANITIZE_STRING);
        $enavn = $_POST['etternavn'];
        $enavn = filter_var($_POST['etternavn'], FILTER_SANITIZE_STRING);

        $mail = $_POST['mail'];
        $pw = $_POST['passord'];
        $brukertype = 1;

        //Sjekker om passordene er like
        if ($_POST['passord'] == $_POST['passord2']) {

        //Validering av passordstyrke
        $storebokstaver = preg_match(regexATilZ, $pw);
        $smaabokstaver = preg_match(regexATilZ, $pw); 
        $nummer = preg_match(regexATilZ, $pw); 

        if ($storebokstaver == 1 && $smaabokstaver == 1 && $nummer == 1 || strlen($pw) < 8) {

          //Nøkkel for salting av passord
          //$salt = "IT2_2021";
          //Kombinerer salt med passord
          $kombinert = $salt . $pw;
          $spw = sha1($kombinert);

          $_SESSION['kode'] = substr(md5(uniqid(rand(), true)), 8,8);
          $_SESSION['mail'] = $mail;
  
          
          $link = 'http://' .$_SERVER['HTTP_HOST']. '/aktiver.php?id=' . $_SESSION['kode'];
          $sendt = aktiver($link, $mail);

          if ($sendt) {
              $_SESSION['passord'] = $spw;
              $_SESSION['brukerType'] = $brukertype;
              $_SESSION['fornavn'] = $fnavn;
              $_SESSION['etternavn'] = $enavn;
              header('Location: registrering.php?message=regMailSendt');
            } else {
              header('Location: registrering.php?message=regMailIkkeSendt');
            }
          } else {
          header('Location: registrering.php?message=regMailIkkeGodkjentPassord');
          } 
        }else {
          header('Location: registrering.php?message=regMailPassordSamsvarerIkke');
        }    
      } else {
        header('Location: registrering.php?message=regMailEpostFinnes');
      }
    } else {
      header('Location: registrering.php?message=regMailFeilFormatEpost');
    }    
  } else {
    header('Location: registrering.php?message=regMailUgyldigEpostGenerell');
  }
}
?>



<!DOCTYPE html>
  <html lang="no" dir="ltr">
  <head>
    <title>Registrering</title>
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

        <article id="vis2">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <table class="tabellRegistrering">
              <tr>
                <th style="text-align: center; margin-top:5px;"><h1>Registrering av nye brukere</h1></th>
              </tr>
              <tr>
                <th class="kolonne1"><span class="fornavnEtternavnRegistrering">&#9432;</span>&nbsp;&nbsp;Fornavn</th>
              </tr>
              <tr>
                <td class="tdRegistrering"><input type="text" id="fornavn" name="fornavn"  class="inputKnapp" placeholder="Bokstaver fra a-å" pattern="[a-zA-Z-æøåÆØÅ]+$" title="Format: Kun bokstaver fra A-Å" value="<?php echo isset($_POST["fornavn"]) ? $_POST["fornavn"] : ''; ?>"></td>
              </tr>
              <tr>
                <th class="kolonne1"><span class="fornavnEtternavnRegistrering">&#9432;</span>&nbsp;&nbsp;Etternavn</th>
              </tr>
              <tr>
                <td class="tdRegistrering"><input type="text" id="etternavn" name="etternavn" class="inputKnapp" placeholder="Bokstaver fra a-å"  pattern="[a-zA-Z-æøåÆØÅ]+$" title="Format: Kun bokstaver fra A-Å" value="<?php echo isset($_POST["etternavn"]) ? $_POST["etternavn"] : ''; ?>"></td>
              </tr>
              <tr>
                <th class="kolonne1"><span class="requiredDot">* </span>Epost</th>
              </tr>
              <tr>
                <td class="tdRegistrering"><input type="text" id="mail" name="mail" class="inputKnapp" placeholder="test@usn.no (kun usn-mailadresse)" required pattern="[a-z0-9._%+-]+@[a-z0-9.-](?=.*[A-Z])+\.[A-Z]{2,}$" title="Format: test@usn.no"></td>
              </tr>
              <tr>
                <th class="kolonne1"><span class="requiredDot">* </span>Passord</th>
              </tr>
              <tr>
                <td class="tdRegistrering" id="reg_passord"><input type="password" id="passord" name="passord" class="inputKnapp" placeholder="Minimum 8 tegn, 1 tall, 1 stor og 1 liten bokstav" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Minimum 8 tegn, 1 tall, samt 1 stor og 1 liten bokstav." value="<?php echo isset($_POST["passord"]) ? $_POST["passord"] : ''; ?>"></td>
              </tr>
              </tr>
                <td class="tdRegistrering" id="caps_melding"></td>
              </tr>
              <tr>
                <td class="tdRegistrering" id="reg_passord2"><input type="password" id="passord2" name="passord2" class="inputKnapp" placeholder="Bekreft passord" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Minimum 8 tegn, 1 tall, samt 1 stor og 1 liten bokstav." value="<?php echo isset($_POST["passord2"]) ? $_POST["passord2"] : ''; ?>"></td>
              </tr>
              <tr>
                <td class="tdRegistrering"><label for="vis_passord">Vis passord</label><input type="checkbox" id="vis_passord" onclick="visPassord()"></td>
              </tr>             
              <tr>
              <td class="tdRegistrering"><label for="checkbox">Har lest og godkjenner</label> <span id="vilkår" 
                onclick="alert('1. Vi får behandle dine personopplysninger' + '\n' + '2. Vi lagrer og bruker info om deg' + '\n' 
                          + '3. Du kan trekke tilbake samtykke ved å slette bruker senere' + '\n' + '4. Vi deler ikke dine personopplysninger med andre')">
                <u>vilkår</u></span><input type="checkbox" id="checkbox" name="" required></td>
              </tr>
              <tr>
                <td class="tdRegistrering" id="registreringKnapp">
                  <button type="submit" name="Registrering" id="regbtn" class="button">Registrer bruker</button>  
                </td>
              </tr>
              <tr>
                <td class="tdRegistrering"><p id="alleredeRegistrert">Har bruker? Klikk <a href="logginn.php">her</a></p></td>
              </tr>
            </table>
            <p id="PNK"><span class="requiredDot">*</span> Nødvendig/Må fylles inn </p>
            <p id="PNK2" class="fornavnEtternavnRegistrering">&#9432; Nødvendig hvis du vil bli en kandidat</p>
            <p id="PNK3"> NB! Registrering må skje på samme nettleser og enhet</p>

          </form>
        </article>
      <script>
        //Script som sjekker om Caps-lock er aktivert eller ei
      var input = document.getElementById("passord");
      var text = document.getElementById("caps_melding");
      input.addEventListener("keyup", function(event) {
      if (event.getModifierState("CapsLock")) {
        caps_melding.textContent = "Caps-lock er på";
      } else {
        caps_melding.textContent = ""
      }
      });
      var input2 = document.getElementById("passord2");
      input2.addEventListener("keyup", function(event) {
      if (event.getModifierState("CapsLock")) {
        caps_melding.textContent = "Caps-lock er på";
      } else {
        caps_melding.textContent = ""
      }
      });

      function function1() {
        document.getElementById('hide').style.display = block;
      }   

       //Funksjon for å vise eller skjule passord
       function visPassord() {
        var x = document.getElementById("passord");
        var x2 = document.getElementById("passord2");
        if (x.type === "password" && x2.type === "password") {
          x.type = "text";
          x2.type = "text";
        } else {
          x.type = "password";
          x2.type = "password";
        }
      }

      //Funksjon for å sjekke likhet på passord
      var pass1 = document.getElementById("passord");
      var pass2 = document.getElementById("passord2");

      function sjekkPassord() {
        if (pass1.value != pass2.value) {
          document.getElementById("passord2").style.color = "red";
        } else{
          document.getElementById("passord2").style.color = "black";
        }
      }

      pass1.addEventListener('keyup', () => {
        if (pass2.value.length != 0) sjekkPassord();
      })
      
      pass2.addEventListener('keyup', sjekkPassord);
      </script>
    </main>

      <script>
        // On mouse-over, execute myFunction
        function vilkår() {
          document.getElementById("vilkår").click(); // Click on the checkbox
        }
      </script>

    <footer>     
      <p>Copyright ©2021 Hønefoss/Norge - Rapp 20 G04</p>
    </footer>  
  </body>
</html>