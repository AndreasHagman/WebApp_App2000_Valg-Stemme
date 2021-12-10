<?php
//Kode skrevet av Remi, Endre, Waleed, Andreas, Petter
//Kode kontrollert av Petter, Andreas, Waleed, Endre, Remi

//Sjekker om det finnes en melding i url
if(isset($_GET["message"])) {
//Finner så matchende medling og kjører kode
  switch ($_GET["message"]){
      case "logginnGodkjent":
        //Alert for konfirmasjon på vellykket logginn.
        //Fra minside
        alert("Du er nå logget inn", "Grønn");
        break;

      case "alleredeLoggetInn":
        alert("Du var allerede logget inn","Rød");
        break;

      case "aktivert":
        //Fra aktiver.php
        //Dersom brukeren lager en godkjent bruker
        alert("Brukeren din har blitt aktivert", "Grønn");
        $_SESSION['brukerTypeNavn'] = "Bruker";
        break;

      case "ikketilgang":
        //Dersom brukeren er logget inn, men forsøker manuelt å skrive inn for eksempel "default.php"
        //Vil da bli "kastet" hit
        //Fra minside
        alert("Du har ikke tilgang til denne siden", "Rød");
        break;

      case "registreringGodkjent":
        //Får beskjed fra registrering.php gjennom URL om at registrering er godkjent
        //Skriver ut alert til bruker
        //Fra infovalg
        alert("Registrering godkjent", "Grønn");
        break;

      case "loggetUt":
        //Alert for konfirmasjon på at bruker er logget ut.
        //Fra default.php
        alert("Du er nå logget ut","Grønn");
        unset($_SESSION['brukerID']);
        break;

      case "aktivertFeil":
        //Alert for problem ved aktivering av bruker
        alert("Aktivering av bruker feilet","Rød");
        break;

      case "fjernetStemme":
        //Fra avstemming
         //Melding som vises dersom brukeren trykker på fjernstemme
        alert ("Stemme fjernet", "Rød");
        break;

      case "stemt":
        //Fra avstemming
        //Melding som vises dersom brukeren nettopp har stemt
        alert ("Du har stemt", "Grønn");
        break;

      case "trykkPåLenke":
        alert ("Du må trykke på en lenke tilhørende en kandidat for å få tilgang til informasjon.php", "Rød");
        break;

      case "ikkeLoggetInn":
        alert("Logg inn for å få tilgang til resten av siden","Rød");
        break;
      
      //Inaktivitet
      case "nySideinaktivitet":
        alert("Du er logget ut grunnet inaktivitet", "Rød");
        break;

      case "databaseEndringInaktivitet":
        alert("Endringen ble ikke lagret grunnet inaktivitet og du er nå logget ut", "Rød");
        break;

      case "manglerBrukerRettighet":
        alert("Du mangler rettighet til å se denne siden","Rød");
        break;
      
      case "endringVellykket":
      alert("Endring vellykket","Grønn");
        break;

      case "brukertypeEndringGodkjent":
        alert("Brukertype er endret","Grønn");
      break;

      case "fjernTrukket":
        alert("Du har fjernet din reservasjon mot å bli nominert","Grønn");
      break;

      case "reservert":
        alert("Du er nå reservert mot nomineringer","Grønn");
      break;

      case "trukket":
        alert("Ditt kandidatur er trukket","Grønn");
      break;

      case "ikkeTrukket":
        alert("Ditt kandidatur er ikke trukket","Rød");
      break;
      case "brukerslettet":
        alert("Din bruker er nå slettet","Grønn");
      break;
      case "bildegodkjent":
        alert("Bilde ble lagt til","Grønn");
      break;
      case "brukertypeEndret":
        alert("Din brukertype er nå endret","Grønn");
      break;
      case "regMailSendt":
        alert("Du har registert deg og en mail med aktiveringslink er sendt.","Grønn");
      break;
      case "regMailIkkeSendt":
        alert("Mail ble ikke sent","Rød");
      break;
      case "regMailIkkeGodkjentPassord":
        alert("Ikke godkjent passord", "Rød");
      break;
      case "egMailPassordSamsvarerIkke":
        alert("Passordene er ikke like","Rød");
      break;
      case "regMailEpostFinnes":
        alert("Eposten er allerede registrert", "Rød");
      break;
      case "regMailFeilFormatEpost'":
        alert("Ugyldig epostadresse, kun registrering med en usn email aksepteres", "Rød");
      break;
      case "regMailUgyldigEpostGenerell":
        alert("Du har oppgitt en ugyldig epostadresse", "Rød");
      break;
  }
}
?>