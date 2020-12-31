<?php
session_start();
include "../connexion.php";

$choix=$_POST['choix'];
$lead=$_POST['lead'];
$traitre=$_POST['traitre'];
$id=$_SESSION['id'];

if ($choix!="") {


$stmt = $db->prepare("UPDATE hrpg SET vote=$choix WHERE id=$id");
  $stmt->execute();

  if ($lead==1) {$stmt = $db->prepare("UPDATE hrpg SET leader='2' WHERE id=$id");
  $stmt->execute();}

    if ($traitre==1) {$stmt = $db->prepare("UPDATE hrpg SET traitre='2' WHERE id=$id");
  $stmt->execute();}

}

$stmt = $db->prepare("SELECT id,nom,hf,str,mind,hp,leader,vote,tag1,tag2,tag3,traitre FROM hrpg WHERE id=$id");
$stmt->execute();
$row=$stmt->fetch();
   $id=$row[0];
   $nom=utf8_encode($row[1]);
   $hf=$row[2];
   $str=$row[3];
   $mind=$row[4];
   $hp=$row[5];
   $leader=$row[6];
   $vote=$row[7];
   $tag1=$row[8];
   $tag2=$row[9];
   $tag3=$row[10];
   $traitre=$row[11];


?>

<html>
<head>
<title>AT RPG</title>
  <link rel="stylesheet" type="text/css"
          href="https://fonts.googleapis.com/css?family=Libre+Baskerville">
</head>
<body>
<div align="center">

<font style="font-family: 'Libre Baskerville', Black;font-size: 50px;">Votre aventurier</font>
<br><br><br><br>
<table align="center" border=1 width=500 cellpadding=5>
  <tr>
    <td>
      <font style="font-family: 'Libre Baskerville', Black;font-size: 20px;">
<?php


$genre="Homme";
if ($hf==1) {$genre="Femme";}
if ($hf==3) {$genre="Non Binaire";}
if ($leader==1) {$lead="Vous êtes actuellement <b>Leader</b> !<br><br>";}
if ($traitre==1) {$trai="Vous êtes actuellement <b>Traitre</b> !<br><br>";}

if ($tag1!="" && $tag1!=" ") {$genre.="<br>".$tag1;}
if ($tag2!="" && $tag2!=" ") {$genre.="<br>".$tag2;}
if ($tag3!="" && $tag3!=" ") {$genre.="<br>".$tag3;}

print "
<b><u>$nom</u></b><br>
$genre<br><br>
<br>Force : <b>$str</b><br>
Intelligence : <b>$mind</b><br><br>
Points de vie : <b>$hp</b><br><br>
$lead $trai";

if ($hp>0) { 

$stmt = $db->prepare("SELECT choix,c1,c2,c3,c4,c5,c6,c7,c8,c9,c10,choixtag FROM sondage");
$stmt->execute();
$row=$stmt->fetch();
$choix=utf8_encode($row[0]);
$c1=utf8_encode($row[1]);
$c2=utf8_encode($row[2]);
$c3=utf8_encode($row[3]);
$c4=utf8_encode($row[4]);
$c5=utf8_encode($row[5]);
$c6=utf8_encode($row[6]);
$c7=utf8_encode($row[7]);
$c8=utf8_encode($row[8]);
$c9=utf8_encode($row[9]);
$c10=utf8_encode($row[10]);
$choixtag=utf8_encode($row[11]);

if ($choix!="" && $vote==0 && ($choixtag=="" || ($tag1==$choixtag || $tag2==$choixtag || $tag3==$choixtag))) {

  print "Décision en cours :<br><b>$choix</b><br><br>
<form action=\"main.php\" method=\"post\">";

if ($c1!="") {print "<input type=\"radio\" name=\"choix\" value=\"1\"> $c1<br>";}
if ($c2!="") {print "<input type=\"radio\" name=\"choix\" value=\"2\"> $c2<br>";}
if ($c3!="") {print "<input type=\"radio\" name=\"choix\" value=\"3\"> $c3<br>";}
if ($c4!="") {print "<input type=\"radio\" name=\"choix\" value=\"4\"> $c4<br>";}
if ($c5!="") {print "<input type=\"radio\" name=\"choix\" value=\"5\"> $c5<br>";}
if ($c6!="") {print "<input type=\"radio\" name=\"choix\" value=\"6\"> $c6<br>";}
if ($c7!="") {print "<input type=\"radio\" name=\"choix\" value=\"7\"> $c7<br>";}
if ($c8!="") {print "<input type=\"radio\" name=\"choix\" value=\"8\"> $c8<br>";}
if ($c9!="") {print "<input type=\"radio\" name=\"choix\" value=\"9\"> $c9<br>";}
if ($c10!="") {print "<input type=\"radio\" name=\"choix\" value=\"10\"> $c10<br><br>";}

if ($leader==1) {print "<input type=checkbox name=lead value=1> Utiliser mon pouvoir de leader<br><br>";}

if ($traitre==1) {print "<input type=checkbox name=traitre value=1> Utiliser mon pouvoir de traitre et annuler le vote choisi<br><br>";}

print "<input type=\"submit\" value=\"Mon choix est irrévocable\"></form>";
} elseif ($vote!=0) {

?>
(Votre vote a bien été pris en compte)
<?php
} else {

?>
(Pas de décision en cours)
<?php
}
}
?>

<br><br>
<b>Possessions :</b><br>


<?php
$stmt = $db->prepare("SELECT quoi FROM loot WHERE idh=$id ORDER BY id DESC");
  $stmt->execute();
 foreach($stmt->fetchAll() as $key => $row){
   $quoi=$row[0];

print "<br>- $quoi";

}

?>
</font>
</td>
</tr>
</table>

<br>
<br>

<table align="center" border=1 width=500 cellpadding=5>
  <tr>
    <td>
      <font style="font-family: 'Libre Baskerville', Black;font-size: 20px;">
          <a href="main.php" style="text-decoration: none;color: #FF6600">CLIQUEZ ICI POUR ACCEDER AU SONDAGE</a>
</font>
</td>
</tr>
</table>
<br>
<br>

  </font>
</body>
</html>
