<?php

class Dbh {

    private $servername;
    private $username;
    private $password;
    private $dbname;
    private $charset;

    //the property or method can be accessed within the class and by classes derived from that class

     protected function connect() {
        $this->servername = "localhost";
        $this->username = "root";
        $this->password = "";
        $this->dbname = "test";
        $this->charset = "utf8mb4";
    } 

    
     /*protected function connect() {
        $this->servername = "s381.usn.no";
        $this->username = "usr_valg";
        $this->password = "pw_valg2021";
        $this->dbname = "valg2021";
        $this->charset = "utf8mb4";
    }*/
}
