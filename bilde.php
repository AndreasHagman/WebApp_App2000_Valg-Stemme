<?php
//Kode skrevet av Endre 
//Kode kontrollert av Waleed

//Resizer bilder til thumbnails som blir brukt på nominering.php
//Finner bildet og mime-typen til bildet
$bilde = $_GET['bilde'];
$mime_type = mime_content_type($bilde);

//Bestemmer størrelsen på thumbnailen
$width = 50;
$height = 50;
    
//Regner ut for å gjøre bildet til ønsket størrelse
list($width_orig, $height_orig) = getimagesize($bilde);
$ratio_orig = $width_orig/$height_orig;

if($width/$height > $ratio_orig){
    $width = $height*$ratio_orig;
}
else{
    $height = $width/$ratio_orig;
}

$bilde_p = imagecreatetruecolor($width, $height);

//Bestemmer hva som skjer alt etter mime typen
if ($mime_type == "image/png"){
    $bilde_v = imagecreatefrompng($bilde);
    $header_content = "Content-Type: image/png";
}
elseif ($mime_type == "image/jpeg"){
    $bilde_v = imagecreatefromjpeg($bilde);
    $header_content = "Content-Type: image/jpeg";
}
elseif ($mime_type == "image/bmp"){
    $bilde_v = imagecreatefromwbmp($bilde);
    $header_content = "Content-Type: image/bmp";
}

//Resizer bildet og bestemmer header header type basert på mime-typen
imagecopyresampled($bilde_p, $bilde_v, 0, 0, 0, 0, $width,$height,$width_orig,$height_orig);
header($header_content);

//Printer ut bildet
if ($mime_type == "image/png"){
    imagepng($bilde_p);
}
elseif ($mime_type == "image/jpeg"){
    imagejpeg($bilde_p);
}
elseif ($mime_type == "image/bmp"){
    imagewbmp($bilde_p);
}

imagedestroy($bilde_p);
?>