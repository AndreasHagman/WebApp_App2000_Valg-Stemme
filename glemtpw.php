<?php
//Kode skrevet av Remi
//Kode kontrollert av Waleed

include "funksjoner.php";
include("db_pdo.php");
$db = new myPDO();

if (isset($_POST['submit'])) {
    
    //Tester om epost er skrevet inn
    if(!empty($_POST['epost'])){
        $epost = ($_POST['epost']);

        //Tester på om input fra bruker ekisterer i databasen
        $query = "SELECT * FROM bruker WHERE epost=?";
        $stmt = $db->prepare($query);
        $stmt->execute([$epost]);

        //Oppretter en variabel(array) med brukerinfo
        $bruker = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($bruker) {
            glemtpw($epost,$db);
        } else {
            alert("Denne brukeren finnes ikke!","Rød");
        }
    } else {
        alert("Du må skrive inn en epost!","Rød");
    }
}

?>

<!DOCTYPE html>
<html lang="no" dir="ltr">
    <head>
        <title>Glemt passord</title>
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
                <!-- <li><a href="avstemming.php">Avstemming</a></li>
                <li><a href="nominering.php">Nominering</a></li> -->
                <li><a href="registrering.php">Registrering</a></li>
                <li><a href="logginn.php">Login</a></li>
            </ul>
        </nav>
        <main>
        <!-- Lage en boks til login -->
        <form class="box" id='glemtPwBox' action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post"><!-- data i form sendes til samme side -->
            <!-- Overskrift -->
            <h3>Glemt passord</h3>
            <!-- Informasjon til bruker -->
            <p>Vennligst skriv inn registrert epost</p>
            <!-- Input for epost -->
            
            <input type="text" name="epost" id="epost" placeholder="epost" autofocus/>
            <!-- En knapp for å logge inn -->
            <input  type="submit" name="submit" id="submit"  value="Submit"/>
        </form>
        </main>
        <footer>
            <p>Copyright ©2021 Hønefoss/Norge - Rapp 20 G04</p>
        </footer>
    </body>
</html>