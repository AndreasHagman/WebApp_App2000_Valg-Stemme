<?php
// Kode skrevet av Petter 
// Kode kontrollert av Andreas 

//Setter begresning for hvor lenge et script for lov til å kjøre i loop
set_time_limit(30);

//Konstanter og variabler som gjenbrukes
define("AarMaanedDagTimeMinutterSekunder", "Y-m-d H:i:s");
define("AarMaanedDagTimeMinutterFormattert", "Y-m-d\TH:i");
define("DagMaanedAar", "d-m-Y");
define("regexATilZ", "@[A-Z]@");
define("regexNullTilNi", "@[0-9]@");
date_default_timezone_set('Europe/Oslo');
$salt = "IT2_2021";

//Funksjon for å forhindre å måtte skrive alerter manuelt
function alert($melding,$farge){
    echo "<section id=\"alert$farge\">
    <span class=\"closebtn\" onclick=\"this.parentElement.style.display='none';\">&times;</span>
    $melding </section>";
    }

//Henter ut brukertypen til innlogget bruker og setter tittel tilhørende brukertype
function bestemBrukerTypeNavn($db){
    //Query som henter ut brukertype
    $sql = "SELECT brukertype FROM bruker WHERE epost=?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$_SESSION['brukerID']]);
    $resultat = $stmt->fetch(PDO::FETCH_ASSOC);

    if($_SESSION['brukerType']!=$resultat['brukertype']){
        $_SESSION['brukerType']=$resultat['brukertype'];
        //Switch som bestemmer riktig tittel avhengig av brukertype
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
        header('Location: minside.php?message=brukertypeEndret');
    }
}

function slettbilde($db){
//Sletter tilhørende bilde hvis brukeren har bilde
    $sql = "SELECT bilde FROM kandidat WHERE bruker=?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$_SESSION['brukerID']]);
    $bildeId = $stmt->fetch(PDO::FETCH_COLUMN);
    //Sjekker om brukeren har et bilde
    if(empty($bildeId)){
    //Brukeren har ikke bilde
    } else {
        echo 'her er bildeid: '.$bildeId;
    //Sletter et eventuelt bilde som bruker har lagt inn
    $sql = "SELECT hvor FROM bilde WHERE idbilde=?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$bildeId]);
    $bilde = $stmt->fetch(PDO::FETCH_COLUMN);
    echo "her er bildesti:". $bilde;

    $sti = "bilder/$bilde";
    unlink($sti);
    //bilde slettes og referanse i tabell bilde kan slettes
    $sql = "DELETE FROM bilde WHERE idbilde=?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$bildeId]);
    }
}

function finndato($db){
    $sql = "SELECT * FROM valg";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $resultat =$stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['startforslag']= $resultat['startforslag'];
    $_SESSION['sluttforslag']= $resultat['sluttforslag'];
    $_SESSION['startvalg']= $resultat['startvalg'];
    $_SESSION['sluttvalg']= $resultat['sluttvalg'];
    $_SESSION['tittel']= $resultat['tittel'];
    $_SESSION['kontrollert']= $resultat['kontrollert'];
}

function glemtpw($epost,$db){

    $nyttpw = lagpassord(8);
    $salt = "IT2_2021";

    $kombinert = $salt . $nyttpw;
    $spw = sha1($kombinert);

    $sql = "UPDATE bruker SET passord =? WHERE epost=?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$spw,$epost]);

    nyttpwMail($epost,$nyttpw);

}

function lagpassord($lengde){
  
    $gyldigChar = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
    $gyldigCharNr = strlen($gyldigChar);
    $resultat = "";
    for ($i = 0; $i < $lengde; $i++){
        $indeks = mt_rand(0, $gyldigCharNr - 1);
        $resultat .= ($gyldigChar)[$indeks];
    } return $resultat;
}

function nyttpwMail($epost,$nyttpw){
    $til = $epost;
    $emne = "Nytt passord";

    $bound_text = "----*%$!$%*";
    $bound = "--".$bound_text."\r\n";
    $bound_last = "--".$bound_text."--\r\n";

    $headers = "From: applikasjon@usn.no\r\n";
    $headers .= "MIME-Version: 1.0\r\n" .
            "Content-Type: multipart/mixed; boundary=\"$bound_text\""."\r\n" ;

    $melding = " \r\n".
            $bound;

            $melding .=
            'Content-Type: text/html; charset=UTF-8'."\r\n".
            'Content-Transfer-Encoding: 7bit'."\r\n\r\n".
            '
       
                    <BODY BGCOLOR="White">
                    <body>
                    
        
                    <font size="3" color="#000000" style="text-decoration:none;font-family:Lato light">
                    <div class="info" Style="align:left;">
        
                    <p>Hei! <br>
                    Her kommer ditt nye etterspurte passord. <br>
                    Du kan endre ditt passord under "min side". <br>
                    <br>
                    Hvis du ikke har etterspørt nytt passord, kan du se bort ifra denne eposten.</p>
        
                    <br>
        
                    <p>Nytt passord:  '.$nyttpw.'</p>
        
                    
        
                    </br>
                    <p>-----------------------------------------------------------------------------------------------------------------</p>
                    </br>
                    <p>( Dette er en autogenerert melding, vennligst ikke svar på denne meldingen, hvis du har noen videre spørsmål, kontakt applikasjon@usn.no )</p>
                    </font>
                    </div>
                    </body>
                '."\n\n".
                                                                            $bound_last;
        
            $sendt = mail($til, $emne, $melding, $headers);
            
            if($sendt){
                alert("Nytt passord sendt til din epost","Grønn");
            } else {
                alert("ERROR! Nytt passord ble ikke sendt","Rød");
                }
}

function navmeny(){
    echo "<nav>
      <label id='navikon' for='toggle'>&#9776;</label>
      <input type='checkbox' id='toggle'/>
      <!-- Elementer til hamburgermeny -->
        <ul class='menu'>"; 
           echo "<li><a href='generert/infovalg.html'>Forside</a></li>";
           if ($_SESSION['brukerType'] != 1 && $_SESSION['kontrollert']==null && $_SESSION['startvalg']<date("Y-m-d H:i:s")){ echo"<li><a href='resultat.php'>Resultat</a></li>"; }
           if ($_SESSION['brukerType'] == 3 && $_SESSION['sluttvalg']<date("Y-m-d H:i:s") ) { echo "<li><a href='konsistens.php'>Konsistens</a></li>";}
           if ($_SESSION['startvalg']<date("Y-m-d H:i:s") && $_SESSION['sluttvalg']>date("Y-m-d H:i:s")){ echo" <li><a href='avstemming.php'>Avstemming</a></li>";} 
           if ($_SESSION['startforslag']<date("Y-m-d H:i:s") && $_SESSION['sluttforslag']>date("Y-m-d H:i:s")){ echo"<li><a href='nominering.php'>Nominering</a></li>";} 
          echo "<li><a href='minside.php'>Min side</a></li>
          <li><a href='default.php?message=loggetUt'>Logg ut</a></li>
        </ul>
    </nav>";
}

function aktiver($link, $mail){
    $til = $mail;
    $emne = "Aktiver bruker";

    $bound_text = "----*%$!$%*";
    $bound = "--".$bound_text."\r\n";
    $bound_last = "--".$bound_text."--\r\n";

    $headers = "From: applikasjon@usn.no\r\n";
    $headers .= "MIME-Version: 1.0\r\n" .
            "Content-Type: multipart/mixed; boundary=\"$bound_text\""."\r\n" ;

    $melding = " \r\n".
            $bound;

            $melding .=
            'Content-Type: text/html; charset=UTF-8'."\r\n".
            'Content-Transfer-Encoding: 7bit'."\r\n\r\n".
            '
       
                    <BODY BGCOLOR="White">
                    <body>
                    
        
                    <font size="3" color="#000000" style="text-decoration:none;font-family:Lato light">
                    <div class="info" Style="align:left;">
        
                    <p>Hei! <br>
                    Dette er en epost for å aktivere din bruker. <br>
                    Aktiver din bruker ved å følge lenken under. <br>
                    <br>
                    Hvis du ikke har opprettet en bruker, kan du se bort ifra denne eposten.</p>
        
                    <br>
        
                    <a href="'.$link.'">Link</a>
        
                    
        
                    </br>
                    <p>-----------------------------------------------------------------------------------------------------------------</p>
                    </br>
                    <p>( Dette er en autogenerert melding, vennligst ikke svar på denne meldingen, hvis du har noen videre spørsmål, kontakt applikasjon@usn.no )</p>
                    </font>
                    </div>
                    </body>
                '."\n\n".
                                                                            $bound_last;
        
            $sendt = mail($til, $emne, $melding, $headers);
            

            
            if($sendt){
                return true;
            } else {
                alert("ERROR! Feil med registrering","Rød");
                return false;
                }

}

function bekreftKode($mail){
    $kode = substr(md5(uniqid(rand(), true)), 8,8);

    $til = $mail;
    $emne = "Bekreft bruker";

    $bound_text = "----*%$!$%*";
    $bound = "--".$bound_text."\r\n";
    $bound_last = "--".$bound_text."--\r\n";

    $headers = "From: applikasjon@usn.no\r\n";
    $headers .= "MIME-Version: 1.0\r\n" .
            "Content-Type: multipart/mixed; boundary=\"$bound_text\""."\r\n";

    $melding = " \r\n".
            $bound;

            $melding .=
            'Content-Type: text/html; charset=UTF-8'."\r\n".
            'Content-Transfer-Encoding: 7bit'."\r\n\r\n".
            '
       
                    <BODY BGCOLOR="White">
                    <body>
                    
        
                    <font size="3" color="#000000" style="text-decoration:none;font-family:Lato light">
                    <div class="info" Style="align:left;">
        
                    <p>Hei! <br>
                    Dette er en epost for å logge inn på din bruker. <br>
                    Logg inn ved å taste inn bekreftelseskoden under. <br>
                    <br>
                    Hvis du ikke har prøvd å logge inn, kan du se bort ifra denne eposten.</p>
        
                    <br>
        
                    <p>Kode: '.$kode.'</p>
        
                    
        
                    </br>
                    <p>-----------------------------------------------------------------------------------------------------------------</p>
                    </br>
                    <p>( Dette er en autogenerert melding, vennligst ikke svar på denne meldingen, hvis du har noen videre spørsmål, kontakt applikasjon@usn.no )</p>
                    </font>
                    </div>
                    </body>
                '."\n\n".
                                                                            $bound_last;
        
            $sendt = mail($til, $emne, $melding, $headers);

            if($sendt){
                alert("En epost med en bekreftelseskode er sendt til din innboks","Grønn");
                return $kode;
            } else {
                alert("ERROR! Feil ved sending av bekreftelseskode","Rød");
                }

}

function mailNominering($mail){

    $til = $mail;
    $emne = "Nominert";

    $bound_text = "----*%$!$%*";
    $bound = "--".$bound_text."\r\n";
    $bound_last = "--".$bound_text."--\r\n";

    $headers = "From: applikasjon@usn.no\r\n";
    $headers .= "MIME-Version: 1.0\r\n" .
            "Content-Type: multipart/mixed; boundary=\"$bound_text\""."\r\n";

    $melding = " \r\n".
            $bound;

            $melding .=
            'Content-Type: text/html; charset=UTF-8'."\r\n".
            'Content-Transfer-Encoding: 7bit'."\r\n\r\n".
            '
       
                    <BODY BGCOLOR="White">
                    <body>
                    
        
                    <font size="3" color="#000000" style="text-decoration:none;font-family:Lato light">
                    <div class="info" Style="align:left;">
        
                    <p>Hei! <br>
                    Dette er en epost for å informere om at din bruker har blitt nominert. <br>
                    Hvis du ikke har nominert det selv, og/eller ønsker å fjerne nominasjonen, <br>
                    kan du gå inn på "Min Side", og slette nomineringen.</p>    
        
                    </br>
                    <p>-----------------------------------------------------------------------------------------------------------------</p>
                    </br>
                    <p>( Dette er en autogenerert melding, vennligst ikke svar på denne meldingen, hvis du har noen videre spørsmål, kontakt applikasjon@usn.no )</p>
                    </font>
                    </div>
                    </body>
                '."\n\n".
                                                                            $bound_last;
        
            mail($til, $emne, $melding, $headers);

}


function opprettInaktivitetTid() {
    $_SESSION["last_login_timestamp"] = time();
}

function testInaktivitet60min($endring) {
    if((time() - $_SESSION['last_login_timestamp']) > 1800) { //1800 sekunder = 30min
        if ($endring == "nySide") {
            session_destroy();
            header('location: default.php?message=nySideinaktivitet');  
        } else if ($endring == "databaseEndring") {
            session_destroy();
            header('location: default.php?message=databaseEndringInaktivitet');
        } 
    } else {
        opprettInaktivitetTid();
    }
}

//Tester om brukerId er satt 
function testOmBrukerIdErTilegnet() {
    if (!isset($_SESSION['brukerID'])) {
        header("Location: default.php?message=ikkeLoggetInn");
        exit();
      }
}

//Sender bruker tilbake til forrige side hvis bruker allerede er logget inn.
function testOmAlleredeLoggetInn() {
if (isset($_SESSION['brukerID'])) {
    header("Location: {$_SESSION['url']}?message=alleredeLoggetInn");
    exit();
  }
}

//Hindrer bruker å inspirere og endre kode.
echo "
            <script type=\"text/javascript\">

            
          
                document.onkeydown = function(e) {
              if(event.keyCode == 123) {
                 return false;
              }
              if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) {
                 return false;
              }
              if(e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) {
                 return false;
              }
              if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) {
                 return false;
              }
              if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) {
                 return false;
              }
            }

            </script>
    ";
?>