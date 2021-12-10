<?php
  class myPDO extends PDO {
    public function __construct() {
      $settings = parse_ini_file('setting.ini',TRUE);


      $drv = $settings['database']['driver'];
      $hst = $settings['database']['host'];
      $sch = $settings['database']['schema'];
      $usr = $settings['database']['username'];
      $prt = $settings['database']['port'];
      $pwd = $settings['database']['password'];
      $dns = $drv . ':host=' . $hst . ';dbname=' . $sch . ';port=' .$prt;
      parent::__construct($dns,$usr,$pwd);
    }

  }
?>
