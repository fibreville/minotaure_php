<?php
session_start();
include "connexion.php";
$id = $_SESSION['id'];

if ($id == 1) {
    $action = $_GET['action'];
    $sequence = $_POST['sequence'];
    $choix = $_POST['choix'];
    $c1 = $_POST['c1'];
    $c2 = $_POST['c2'];
    $c3 = $_POST['c3'];
    $c4 = $_POST['c4'];
    $c5 = $_POST['c5'];
    $c6 = $_POST['c6'];
    $c7 = $_POST['c7'];
    $c8 = $_POST['c8'];
    $c9 = $_POST['c9'];
    $c10 = $_POST['c10'];
    $choixtag = $_POST['choixtag'];

    $choixrandom = $_POST['choixrandom'];

    $loot = $_POST['loot'];
    $propriete = $_POST['propriete'];
    $qui = $_POST['qui'];
    $victime = $_POST['victime'];
    $victimetag = $_POST['victimetag'];
    $type = $_POST['type'];
    $difficulte = $_POST['difficulte'];
    $penalite = $_POST['penalite'];
    $sanction = $_POST['sanction'];
    $tag1 = $_POST['tag1'];
    $tag2 = $_POST['tag2'];
    $tag3 = $_POST['tag3'];

    if ($tag1 != "") {
        $z = substr_count($tag1, ",");
        $travail = explode(",", $tag1);

        $stmt = $db->prepare("SELECT id FROM hrpg WHERE hp>0 ORDER BY RAND()");
        $stmt->execute();
        foreach ($stmt->fetchAll() as $key => $row) {
            $id_joueur = $row[0];
            $k = rand(0, $z);
            $item = $travail[$k];

            $stmt2 = $db->prepare("UPDATE hrpg SET tag1='$item' WHERE id='$id_joueur'");
            $stmt2->execute();
        }
    }

    if ($tag2 != "") {
        $z = substr_count($tag2, ",");
        $travail = explode(",", $tag2);

        $stmt = $db->prepare("SELECT id FROM hrpg WHERE hp>0 ORDER BY RAND()");
        $stmt->execute();
        foreach ($stmt->fetchAll() as $key => $row) {
            $id_joueur = $row[0];
            $k = rand(0, $z);
            $item = $travail[$k];

            $stmt2 = $db->prepare("UPDATE hrpg SET tag2='$item' WHERE id=$id_joueur");
            $stmt2->execute();

        }
    }

    if ($tag3 != "") {
        $z = substr_count($tag3, ",");
        $travail = explode(",", $tag3);

        $stmt = $db->prepare("SELECT id FROM hrpg WHERE hp>0 ORDER BY RAND()");
        $stmt->execute();
        foreach ($stmt->fetchAll() as $key => $row) {
            $id_joueur = $row[0];
            $k = rand(0, $z);
            $item = $travail[$k];

            $stmt2 = $db->prepare("UPDATE hrpg SET tag3='$item' WHERE id=$id_joueur");
            $stmt2->execute();
        }
    }


    if ($type != "") {
        $travail = explode(",", $victime);
        if ($type == "m") {
            $type = "mind";
        }
        if ($type == "s") {
            $type = "str";
        }
        $modif = substr($penalite, 0, 2);
        $modifquoi = substr($penalite, 2, 1);

        if ($modifquoi == "h") {
            $modifquoi = "hp";
        }
        if ($modifquoi == "m") {
            $modifquoi = "mind";
        }
        if ($modifquoi == "s") {
            $modifquoi = "str";
        }

        $modif = $modifquoi . $modif;

        if ($travail[1] != "") {
            $i = 0;

            while ($travail[$i] > 0) {
                $id_joueur = $travail[$i];
                $stmt = $db->prepare("SELECT $type,nom,id FROM hrpg WHERE id=$id_joueur");
                $stmt->execute();
                foreach ($stmt->fetchAll() as $key => $row) {
                    $valeur = $row[0];
                    $nom = utf8_encode($row[1]);
                    $id_joueur = $row[2];
                    if (($valeur + rand(1, 6)) < ($difficulte + rand(1, 6))) {
                        // defaite
                        $sanction .= "<font color=red>$nom a échoué !</font><br>";
                        $k++;
                        $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$modif WHERE id=$id_joueur");
                        $stmt->execute();
                    } else {
                        $sanction .= "<font color=009900>$nom a réussi !</font><br>";
                        $l++;
                    }
                }
                $i++;
            }

            $sanction .= "<br><b>$l</b> victoires pour <b>$k</b> défaites.";
        } elseif ($victime == "*") {
            $stmt = $db->prepare("SELECT $type,nom,id FROM hrpg WHERE hp>0");
            $stmt->execute();
            foreach ($stmt->fetchAll() as $key => $row) {
                $valeur = $row[0];
                $nom = utf8_encode($row[1]);
                $id_joueur = $row[2];

                if (($valeur + rand(1, 6)) < ($difficulte + rand(1, 6))) {
                    // defaite
                    $sanction .= "<font color=red>$nom a échoué !</font><br>";
                    $k++;

                    $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$modif WHERE id=$id_joueur");
                    $stmt->execute();
                } else {
                    $sanction .= "<font color=009900>$nom a réussi !</font><br>";
                    $l++;
                }
            }
            $sanction .= "<br><b>$l</b> victoires pour <b>$k</b> défaites.";
        } elseif ($victime == "m") {
            $stmt = $db->prepare("SELECT $type,nom,id FROM hrpg WHERE hp>0 && mind>=str");
            $stmt->execute();
            foreach ($stmt->fetchAll() as $key => $row) {
                $valeur = $row[0];
                $nom = utf8_encode($row[1]);
                $id_joueur = $row[2];

                if (($valeur + rand(1, 6)) < ($difficulte + rand(1, 6))) {
                    // defaite
                    $sanction .= "<font color=red>$nom a échoué !</font><br>";
                    $k++;
                    $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$modif WHERE id=$id_joueur");
                    $stmt->execute();
                } else {
                    $sanction .= "<font color=009900>$nom a réussi !</font><br>";
                    $l++;
                }
            }
            $sanction .= "$l victoires pour $k défaites.";
        } elseif ($victime == "s") {
            $stmt = $db->prepare("SELECT $type,nom,id FROM hrpg WHERE hp>0 AND str>mind");
            $stmt->execute();
            foreach ($stmt->fetchAll() as $key => $row) {
                $valeur = $row[0];
                $nom = utf8_encode($row[1]);
                $id_joueur = $row[2];

                if (($valeur + rand(1, 6)) < ($difficulte + rand(1, 6))) {
                    // defaite
                    $sanction .= "<font color=red>$nom a échoué !</font><br>";
                    $k++;
                    $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$modif WHERE id=$id_joueur");
                    $stmt->execute();
                } else {
                    $sanction .= "<font color=009900>$nom a réussi !</font><br>";
                    $l++;
                }
            }
            $sanction .= "$l victoires pour $k défaites.";
        } else {
            $idh = $victime;
            $stmt = $db->prepare("SELECT $type,nom FROM hrpg WHERE id=$idh");
            $stmt->execute();
            $row = $stmt->fetch();
            $valeur = $row[0];
            $nom = utf8_encode($row[1]);

            if (($valeur + rand(1, 6)) < ($difficulte + rand(1, 6))) {
                $sanction .= "<font color=red>$nom a échoué !</font>";
                $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$modif WHERE id=$idh");
                $stmt->execute();
            } else {
                $sanction .= "<font color=009900>$nom a réussi !</font>";
            }
        }
    }

    if ($victimetag != "") {
        $stmt = $db->prepare("SELECT $type,nom,id FROM hrpg WHERE hp>0 && (tag1='$victimetag' || tag2='$victimetag' || tag3='$victimetag')");
        $stmt->execute();
        foreach ($stmt->fetchAll() as $key => $row) {
            $valeur = $row[0];
            $nom = utf8_encode($row[1]);
            $id_joueur = $row[2];

            if (($valeur + rand(1, 6)) < ($difficulte + rand(1, 6))) {
                // defaite
                $sanction .= "<font color=red>$nom a échoué !</font><br>";
                $k++;
                $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$modif WHERE id=$id_joueur");
                $stmt->execute();
            } else {
                $sanction .= "<font color=009900>$nom a réussi !</font><br>";
                $l++;
            }
        }
        $sanction .= "<br><b>$l</b> victoires pour <b>$k</b> défaites pour le groupe $victimetag.";
    }

    if ($loot != "") {
        $travail = explode(",", $qui);
        if ($travail[1] != "") {
            $modif = substr($propriete, 0, 2);
            $modifquoi = substr($propriete, 2, 1);

            if ($modifquoi == "h") {
                $modifquoi = "hp";
            }
            if ($modifquoi == "m") {
                $modifquoi = "mind";
            }
            if ($modifquoi == "s") {
                $modifquoi = "str";
            }

            $modif = $modifquoi . $modif;

            $i = 0;
            while ($travail[$i] > 0) {
                $id_joueur = $travail[$i];
                $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$modif WHERE id=$id_joueur");
                $stmt->execute();
                $stmt = $db->prepare("INSERT INTO loot(idh,quoi) VALUES (:idh,:loot)");
                $stmt->execute([
                    ':idh' => $id_joueur,
                    ':loot' => $loot,
                ]);

                $i++;
            }
        } elseif ($qui == "*") {
            $modif = substr($propriete, 0, 2);
            $modifquoi = substr($propriete, 2, 1);

            if ($modifquoi == "h") {
                $modifquoi = "hp";
            }
            if ($modifquoi == "m") {
                $modifquoi = "mind";
            }
            if ($modifquoi == "s") {
                $modifquoi = "str";
            }

            $modif = $modifquoi . $modif;

            $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$modif WHERE hp>0");
            $stmt->execute();

            $stmt = $db->prepare("SELECT id FROM hrpg WHERE hp>0");
            $stmt->execute();
            foreach ($stmt->fetchAll() as $key => $row) {
                $id_joueur = $row[0];

                $stmt = $db->prepare("INSERT INTO loot(idh,quoi) VALUES (:idh,:loot)");
                $stmt->execute([
                    ':idh' => $id_joueur,
                    ':loot' => $loot,
                ]);
            }
        } elseif ($qui == "m") {
            $modif = substr($propriete, 0, 2);
            $modifquoi = substr($propriete, 2, 1);

            if ($modifquoi == "h") {
                $modifquoi = "hp";
            }
            if ($modifquoi == "m") {
                $modifquoi = "mind";
            }
            if ($modifquoi == "s") {
                $modifquoi = "str";
            }

            $modif = $modifquoi . $modif;

            $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$modif WHERE hp>0 AND mind>=str");
            $stmt->execute();

            $stmt = $db->prepare("SELECT id FROM hrpg WHERE hp>0 AND mind>=str");
            $stmt->execute();
            foreach ($stmt->fetchAll() as $key => $row) {
                $id_joueur = $row[0];

                $stmt = $db->prepare("INSERT INTO loot(idh,quoi) VALUES (:idh,:loot)");
                $stmt->execute([
                    ':idh' => $id_joueur,
                    ':loot' => $loot,
                ]);
            }
        } elseif ($qui == "s") {
            $modif = substr($propriete, 0, 2);
            $modifquoi = substr($propriete, 2, 1);

            if ($modifquoi == "h") {
                $modifquoi = "hp";
            }
            if ($modifquoi == "m") {
                $modifquoi = "mind";
            }
            if ($modifquoi == "s") {
                $modifquoi = "str";
            }

            $modif = $modifquoi . $modif;

            $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$modif WHERE hp>0 AND mind<str");
            $stmt->execute();

            $stmt = $db->prepare("SELECT id FROM hrpg WHERE hp>0 AND mind<str");
            $stmt->execute();
            foreach ($stmt->fetchAll() as $key => $row) {
                $id_joueur = $row[0];

                $stmt = $db->prepare("INSERT INTO loot(idh,quoi) VALUES (:idh,:loot)");
                $stmt->execute([
                    ':idh' => $id_joueur,
                    ':loot' => $loot,
                ]);

            }
        } else {
            $idh = $qui;
            $stmt = $db->prepare("INSERT INTO loot(idh,quoi) VALUES (:idh,:loot)");
            $stmt->execute([
                ':idh' => $idh,
                ':loot' => $loot,
            ]);

            // propriete = +1h / -1m / +1s

            $modif = substr($propriete, 0, 2);
            $modifquoi = substr($propriete, 2, 1);

            if ($modifquoi == "h") {
                $modifquoi = "hp";
            }
            if ($modifquoi == "m") {
                $modifquoi = "mind";
            }
            if ($modifquoi == "s") {
                $modifquoi = "str";
            }

            $modif = $modifquoi . $modif;

            $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$modif WHERE id=$idh");
            $stmt->execute();
        }
    }

    if ($action == "clean") {
        $stmt = $db->prepare("UPDATE sondage SET choix=''");
        $stmt->execute();
        $stmt = $db->prepare("UPDATE hrpg SET vote='0'");
        $stmt->execute();
        $stmt = $db->prepare("UPDATE hrpg SET traitre='1' WHERE traitre='2'");
        $stmt->execute();
        $stmt = $db->prepare("UPDATE hrpg SET leader='1' WHERE leader='2'");
        $stmt->execute();
    }

    if ($choix != "") {
        try {
            $stmt = $db->prepare("UPDATE sondage SET choix=:choix,c1=:c1,c2=:c2,c3=:c3,c4=:c4,c5=:c5,c6=:c6,c7=:c7,c8=:c8,c9=:c9,c10=:c10,choixtag=:choixtag");
            $stmt->execute([
                ':choix' => $choix,
                ':c1' => $c1,
                ':c2' => $c2,
                ':c3' => $c3,
                ':c4' => $c4,
                ':c5' => $c5,
                ':c6' => $c6,
                ':c7' => $c7,
                ':c8' => $c8,
                ':c9' => $c9,
                ':c10' => $c10,
                ':choixtag' => $choixtag,
            ]);
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    if ($sequence != "") {
        $stmt = $db->prepare("INSERT INTO epopee(text) VALUES (:sequence)");
        $stmt->execute([
            ':sequence' => $sequence,
        ]);
    }

    $designe = "(pas de tirage en cours)";

    if ($action == "random") {
        $stmt = $db->prepare("SELECT nom,id FROM hrpg WHERE hp>0 ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch();
        $designe = utf8_encode($row[0]) . " (#" . $row[1] . ")";
    }
    if ($action == "randomm") {
        $stmt = $db->prepare("SELECT nom,id FROM hrpg WHERE hp>0 AND mind>=str ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch();
        $designe = utf8_encode($row[0]) . " (#" . $row[1] . ")";
    }

    if ($choixrandom != "") {
        $stmt = $db->prepare("SELECT nom,id FROM hrpg WHERE hp>0 AND (tag1 LIKE '$choixrandom' || tag2 LIKE '$choixrandom' || tag3 LIKE '$choixrandom') ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch();
        $designe = utf8_encode($row[0]) . " (#" . $row[1] . ")";
    }

    if ($action == "randoms") {
        $stmt = $db->prepare("SELECT nom,id FROM hrpg WHERE hp>0 AND mind<str ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch();
        $designe = utf8_encode($row[0]) . " (#" . $row[1] . ")";
    }

    if ($action == "leader") {
        $stmt = $db->prepare("UPDATE hrpg SET leader=0");
        $stmt->execute();
        $stmt = $db->prepare("SELECT id FROM hrpg WHERE hp>0 ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch();
        $id_leader = $row[0];
        $stmt = $db->prepare("UPDATE hrpg SET leader=1 WHERE id='$id_leader'");
        $stmt->execute();
    }

    if ($action == "traitre") {
        $stmt = $db->prepare("UPDATE hrpg SET traitre=0");
        $stmt->execute();
        $stmt = $db->prepare("SELECT id FROM hrpg WHERE hp>0 ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch();
        $id_traitre = $row[0];
        $stmt = $db->prepare("UPDATE hrpg SET traitre=1 WHERE id='$id_traitre'");
        $stmt->execute();
    }

    $nbhv = $db->query("SELECT COUNT(*) FROM hrpg WHERE hp>0")->fetchColumn();
    $nbhm = $db->query("SELECT COUNT(*) FROM hrpg WHERE hp<1")->fetchColumn();
    $nbhma = $db->query("SELECT COUNT(*) FROM hrpg WHERE mind>=str")
        ->fetchColumn();
    $nbhstr = $db->query("SELECT COUNT(*) FROM hrpg WHERE mind<str")
        ->fetchColumn();
    $stmt = $db->prepare("SELECT nom,leader,vote FROM hrpg WHERE leader>0 AND hp>0");
    $stmt->execute();
    $row = $stmt->fetch();
    $leader = utf8_encode($row[0]);
    $leadvalue = utf8_encode($row[1]);
    $leadvote = utf8_encode($row[2]);

    $stmt = $db->prepare("SELECT nom,traitre,vote FROM hrpg WHERE traitre>0 AND hp>0");
    $stmt->execute();
    $row = $stmt->fetch();
    $traitre = utf8_encode($row[0]);
    $traitrevalue = utf8_encode($row[1]);
    $traitrevote = utf8_encode($row[2]);
    ?>

  <html>
  <head>
    <title>AT RPG</title>
    <link rel="stylesheet" type="text/css"
          href="https://fonts.googleapis.com/css?family=Libre+Baskerville">
  </head>
  <body bgcolor="white">
  <font style="font-family: 'Libre Baskerville', Black;font-size: 30px;">Notre
    Aventure<br><font
            style="font-family: 'Libre Baskerville', Black;font-size: 20px;">Rejoindre
      l'Aventure : maspero.blue/rpg/ ou taper !aventure</font></font>
  <br><br>

  <table border=0>
    <tr valign="top">
      <td valign="top">
        <font style="font-family: 'Libre Baskerville', Black;font-size: 15px;">
          Leader du groupe : <b><?php print "$leader"; ?></b><br>
          Traître du groupe : <b><?php print "$traitre"; ?></b><br><br>
          Joueurs encore en vie : <b><?php print "$nbhv"; ?></b><br>
          <br>
          <table border=0 valign=top cellspacing=2>
            <tr>
                <?php
                $stmt = $db->prepare("SELECT id,nom,hf,str,mind,hp,tag1,tag2,tag3 FROM hrpg WHERE hp>0 ORDER BY nom");
                $stmt->execute();
                foreach ($stmt->fetchAll() as $key => $row) {
                    $id_joueur = $row[0];
                    $nom = utf8_encode($row[1]);
                    $hf = $row[2];
                    $str = $row[3];
                    $mind = $row[4];
                    $hp = $row[5];
                    $tag1 = $row[6];
                    $tag2 = $row[7];
                    $tag3 = $row[8];

                    $color = "000000";
                    $femme = "Homme";
                    if ($hf == 1) {
                        $color = "990000";
                        $femme = "Femme";
                    }
                    if ($hf == 3) {
                        $color = "990000";
                        $femme = "Non Binaire";
                    }

                    if ($tag1 != "" && $tag1 != " ") {
                        $femme .= "<br>" . $tag1;
                    }
                    if ($tag2 != "" && $tag2 != " ") {
                        $femme .= "<br>" . $tag2;
                    }
                    if ($tag3 != "" && $tag3 != " ") {
                        $femme .= "<br>" . $tag3;
                    }

                    if ($str < $mind) {
                        $aptitude = "malin ($mind/6)";
                    }
                    if ($str > $mind) {
                        $aptitude = "fort ($str/6)";
                    }
                    if ($str == $mind) {
                        $aptitude = "malin et fort ($str/6)";
                    }

                    print "<td width=80><table border=0 cellspacing=1 bgcolor=black width=100% height=100%><tr><td bgcolor=white>
<font style=\"font-family: 'Libre Baskerville', Black;font-size: 12px;\"><b><font color=$color><font style=\"font-family: 'Libre Baskerville', Black;font-size: 10px;\">$nom<br></b>$femme</font></font><br>Esprit : $mind<br>Corps : $str<br>Vie : $hp<br>Joueur #$id_joueur</font></td></tr></table>
</td>";
                    $i++;

                    if ($i % 10 == 0) {
                        print "</tr><tr>";
                    }
                }

                $p = 10 - ($i % 10);

                while ($p > 0) {
                    print "<td> </td>";
                    $p--;
                }
                ?>
            </tr>
          </table>
          <br>Joueurs morts : <?php print "$nbhm"; ?><br><br>
          <table border=0 valign=top cellspacing=2>
            <tr>
                <?php
                $i = 0;
                $stmt = $db->prepare("SELECT id,nom,hf,str,mind,hp,tag1,tag2,tag3 FROM hrpg WHERE hp<1 ORDER BY nom");
                $stmt->execute();
                foreach ($stmt->fetchAll() as $key => $row) {
                    $id_joueur = $row[0];
                    $nom = utf8_encode($row[1]);
                    $hf = $row[2];
                    $str = $row[3];
                    $mind = $row[4];
                    $hp = $row[5];
                    $tag1 = $row[6];
                    $tag2 = $row[7];
                    $tag3 = $row[8];

                    $color = "000000";
                    $femme = "Homme";
                    if ($hf == 1) {
                        $color = "990000";
                        $femme = "Femme";
                    }
                    if ($hf == 3) {
                        $color = "990000";
                        $femme = "Non Binaire";
                    }

                    if ($tag1 != "" && $tag1 != " ") {
                        $femme .= "<br>" . $tag1;
                    }
                    if ($tag2 != "" && $tag2 != " ") {
                        $femme .= "<br>" . $tag2;
                    }
                    if ($tag3 != "" && $tag3 != " ") {
                        $femme .= "<br>" . $tag3;
                    }

                    if ($str < $mind) {
                        $aptitude = "malin ($mind/6)";
                    }
                    if ($str > $mind) {
                        $aptitude = "fort ($str/6)";
                    }
                    if ($str == $mind) {
                        $aptitude = "malin et fort ($str/6)";
                    }

                    // TODO : define OR
                    print "
                  <td width=80>
                    <table border=0 cellspacing=1 bgcolor=black width=100% height=100%>
                      <tr>
                      <td bgcolor=white>
                      <font style=\"font-family: 'Libre Baskerville', Black;font-size: 12px;\"><b>
                      <font color=$color style=\"font-family: 'Libre Baskerville', Black;font-size: 10px;\">
                        $nom<br></b>$femme
                        <br>Esprit : $mind
                        <br>Corps : $str
                        <br>Or : 
                        <br>Vie : $hp
                        <br>Joueur #$id_joueur
                      </font>
                      </td></tr>
                    </table>
                  </td>
                ";

                    $i++;
                    if ($i % 10 == 0) {
                        print "</tr><tr>";
                    }

                }

                $p = 10 - ($i % 10);
                while ($p > 0) {
                    print "<td> </td>";
                    $p--;
                }
                ?>
            </tr>
          </table>
          <br><br><font
                  style="font-family: 'Libre Baskerville', Black;font-size: 12px;"><?php print $sanction; ?></font>
      </td>
      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      </td>
      <td>
        <font style="font-family: 'Libre Baskerville', Black;font-size: 12px;">
            <?php

            /*
            <b><a href=ecran.php?action=event>Évenement en cours :</a></b><i>
            <br>
            <?php

            if ($action=="event") {

                $stmt = $db->prepare("UPDATE event SET actif=2 WHERE actif=1");
                $stmt->execute();
                $stmt = $db->prepare("SELECT id FROM event WHERE actif=0 ORDER BY RAND()");
                $stmt->execute();
                $row=$stmt->fetch();
                $id_event=utf8_encode($row[0]);
                $stmt = $db->prepare("UPDATE event SET actif=1 WHERE id=$id_event");
                $stmt->execute();

            }

            $stmt = $db->prepare("SELECT event,type FROM event WHERE actif=1");
            $stmt->execute();
            $row=$stmt->fetch();
            $event=utf8_encode($row[0]);
            $type=$row[1];

            if ($type==0) {$typo="quête principale";}
            if ($type==1) {$typo="aventure collective";}
            if ($type==2) {$typo="aventure individuelle";}

            print "$event<br><font size=2>$typo</font>";


            print "</i><br>";

            */

            ?>
          <b>Nominations :</b><br><br>

          <a href="ecran.php?action=leader">Nommer un nouveau leader</a><br>
          <a href="ecran.php?action=traitre">Nommer un nouveau traitre</a><br>
          <a href="ecran.php?action=random">Designer quelqu'un au hasard</a><br>
          <a href="ecran.php?action=randomm">Designer un malin au hasard</a><br>
          <a href="ecran.php?action=randoms">Designer un fort au hasard</a><br>
          <form method=post action=ecran.php>
            Designer un tag au hasard : <input type="text" name="choixrandom"
                                               size="15"><input type="submit"
                                                                value="OK">
          </form>
          <br>
          <b>Le joueur désigné est :</b> <?php print "$designe"; ?><br><br><br>
          <b>Actions :</b>
          <br>
          <br>
            <?php
            $stmt = $db->prepare("SELECT choix,c1,c2,c3,c4,c5,c6,c7,c8,c9,c10,choixtag FROM sondage");
            $stmt->execute();
            $row = $stmt->fetch();
            $choix = utf8_encode($row[0]);
            $c1 = utf8_encode($row[1]);
            $c2 = utf8_encode($row[2]);
            $c3 = utf8_encode($row[3]);
            $c4 = utf8_encode($row[4]);
            $c5 = utf8_encode($row[5]);
            $c6 = utf8_encode($row[6]);
            $c7 = utf8_encode($row[7]);
            $c8 = utf8_encode($row[8]);
            $c9 = utf8_encode($row[9]);
            $c10 = utf8_encode($row[10]);
            $choixtag = utf8_encode($row[11]);

            if ($choix != "") {

                print "$choix<br><br><table>";

                if ($c1 != "") {
                    $nbc1 = $db->query("SELECT COUNT(id) FROM hrpg WHERE vote=1")
                        ->fetchColumn();
                    $pc1 = round(($nbc1 * 100 / $nbhv), 2);
                    print "<tr><td>$c1 : </td><td>$nbc1 / $nbhv soit $pc1 %</td></tr>";
                }

                if ($c2 != "") {
                    $nbc2 = $db->query("SELECT COUNT(id) FROM hrpg WHERE vote=2")
                        ->fetchColumn();
                    $pc2 = round(($nbc2 * 100 / $nbhv), 2);
                    print "<tr><td>$c2 : </td><td>$nbc2 / $nbhv soit $pc2 %</td></tr>";
                }
                if ($c3 != "") {
                    $nbc3 = $db->query("SELECT COUNT(id) FROM hrpg WHERE vote=3")
                        ->fetchColumn();
                    $pc3 = round(($nbc3 * 100 / $nbhv), 2);
                    print "<tr><td>$c3 : </td><td>$nbc3 / $nbhv soit $pc3 %</td></tr>";
                }
                if ($c4 != "") {
                    $nbc4 = $db->query("SELECT COUNT(id) FROM hrpg WHERE vote=4")
                        ->fetchColumn();
                    $pc4 = round(($nbc4 * 100 / $nbhv), 2);
                    print "<tr><td>$c4 : </td><td>$nbc4 / $nbhv soit $pc4 %</td></tr>";
                }
                if ($c5 != "") {
                    $nbc5 = $db->query("SELECT COUNT(id) FROM hrpg WHERE vote=5")
                        ->fetchColumn();
                    $pc5 = round(($nbc5 * 100 / $nbhv), 2);
                    print "<tr><td>$c5 : </td><td>$nbc5 / $nbhv soit $pc5 %</td></tr>";
                }
                if ($c6 != "") {
                    $nbc6 = $db->query("SELECT COUNT(id) FROM hrpg WHERE vote=6")
                        ->fetchColumn();
                    $pc6 = round(($nbc6 * 100 / $nbhv), 2);
                    print "<tr><td>$c6 : </td><td>$nbc6 / $nbhv soit $pc6 %</td></tr>";
                }
                if ($c7 != "") {
                    $nbc7 = $db->query("SELECT COUNT(id) FROM hrpg WHERE vote=7")
                        ->fetchColumn();
                    $pc7 = round(($nbc7 * 100 / $nbhv), 2);
                    print "<tr><td>$c7 : </td><td>$nbc7 / $nbhv soit $pc7 %</td></tr>";
                }
                if ($c8 != "") {
                    $nbc8 = $db->query("SELECT COUNT(id) FROM hrpg WHERE vote=8")
                        ->fetchColumn();
                    $pc8 = round(($nbc8 * 100 / $nbhv), 2);
                    print "<tr><td>$c8 : </td><td>$nbc8 / $nbhv soit $pc8 %</td></tr>";
                }
                if ($c9 != "") {
                    $nbc9 = $db->query("SELECT COUNT(id) FROM hrpg WHERE vote=9")
                        ->fetchColumn();
                    $pc9 = round(($nbc9 * 100 / $nbhv), 2);
                    print "<tr><td>$c9 : </td><td>$nbc9 / $nbhv soit $pc9 %</td></tr>";
                }
                if ($c10 != "") {
                    $nbc10 = $db->query("SELECT COUNT(id) FROM hrpg WHERE vote=10")
                        ->fetchColumn();
                    $pc10 = round(($nbc10 * 100 / $nbhv), 2);
                    print "<tr><td>$c10 : </td><td>$nbc10 / $nbhv soit $pc10 %</td></tr>";
                }

                print "</table>";

                $pctot = $pc1 + $pc2 + $pc3 + $pc4 + $pc5 + $pc6 + $pc7 + $pc8 + $pc9 + $pc10;
                print "Total votants : $pctot %<br>";

                if ($choixtag != "") {
                    print "(vote limité à $choixtag)<br>";
                }

                if ($leadvalue == 2) {
                    print "<br><b>Le leader $leader a utilisé son pouvoir et choisi le choix $leadvote !</b><br>";
                }

                if ($traitrevalue == 2) {
                    print "<br><b>Le traitre $traitre a utilisé son pouvoir et choisi d'annuler le choix $traitrevote !</b><br>";
                }

                print "<br><a href=ecran.php>Rafraichir</a><br><a href=ecran.php?action=clean>Clean</a>";
            } else {
                ?>
              <form method=post action=ecran.php>
                <input type="text" name="choix" size="30"
                       value="Intitulé des choix"><br>
                <input type="text" name="c1" size="30"><br>
                <input type="text" name="c2" size="30"><br>
                <input type="text" name="c3" size="30"><br>
                <input type="text" name="c4" size="30"><br>
                <input type="text" name="c5" size="30"><br>
                <input type="text" name="c6" size="30"><br>
                <input type="text" name="c7" size="30"><br>
                <input type="text" name="c8" size="30"><br>
                <input type="text" name="c9" size="30"><br>
                <input type="text" name="c10" size="30"><br>
                Limiter à : <input type="text" name="choixtag" size="15"><br>
                <input type="submit" value="DÉLIBERER">
              </form>
                <?php
            }
            ?>

          <br>
          <br><br>
          <b>Épreuves :</b><br><br>
          <form method=post action=ecran.php>
            <input type="text" name="type" size="10">Type<br>
            <input type="text" name="difficulte" size="10">Difficulté<br>
            <input type="text" name="penalite" size="10">Penalité<br>
            <input type="text" name="victime" size="10">Qui<br>
            <input type="text" name="victimetag" size="10">Tag<br>
            <input type="submit" value="DONNER">
            <br>
            <br><br>
            <b>Loot :</b><br><br>
            <form method=post action=ecran.php>
              <input type="text" name="loot" size="30">Quoi<br>
              <input type="text" name="propriete" size="10">Pouvoir <br>
              <input type="text" name="qui" size="10">À qui<br>
              <input type="submit" value="DONNER">
              <br><br>
              <b>Tags :</b><br><br>
              <form method=post action=ecran.php>
                <input type="text" name="tag1" size="30"><br>
                <input type="text" name="tag2" size="30"><br>
                <input type="text" name="tag3" size="30"><br>
                <input type="submit" value="DONNER">
      </td>
      <td>
      </td>
    </tr>

  </table>
  </font>
  </body>
  </html>
    <?php
}
?>