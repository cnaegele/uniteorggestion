<?php
require 'gdt/gautentificationf5.php';
require_once '/data/dataweb/GoelandWeb/webservice/employe/clCNWSEmployeSecurite.php';
require_once 'gdt/cldbgoeland.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:  *");
header("Access-Control-Allow-Methods:  POST");
$idCaller = 0;
if (array_key_exists('empid', $_SESSION)) {
    $idCaller = $_SESSION['empid'];
}
if ($idCaller > 0) {
    $pseudoWSEmployeSecurite = new CNWSEmployeSecurite();
    if ($pseudoWSEmployeSecurite->isInGroupe($idCaller, 'GoelandManager')) {
        $jsonData = file_get_contents('php://input');
        $oData = json_decode($jsonData);
        $idUnite = $oData->id;
        $idUniteParent = $oData->idparent;
        $nom = $oData->nom;
        $nom = utf8go_decode($nom);
        $nom = str_replace("'", "''", $nom);
        $description = $oData->description;
        $description = utf8go_decode($description);
        $description = str_replace("'", "''", $description);
        $abreviation = $oData->abreviation;
        $abreviation = utf8go_decode($abreviation);
        $abreviation = str_replace("'", "''", $abreviation);
        $idTypeUnite = $oData->idtype;
        $codeOrdre = $oData->codeordre;
        $codeOrdre = utf8go_decode($codeOrdre);
        $codeOrdre = str_replace("'", "''", $codeOrdre);
        $couleur = $oData->couleur;
        $couleur = utf8go_decode($couleur);
        $couleur = str_replace("'", "''", $couleur);
        if ($oData->bactif == 'true') {
            $bactif = 1;
        } else {
            $bactif = 0;
        }
        if ($oData->bvisible == 'true') {
            $bCache = 0;
        } else {
            $bCache = 1;
        }
        $sSql = "cn_orgunit_sauve $idCaller, $idUnite, $idTypeUnite";
        if ($idUniteParent > 0) {
            $sSql .= ", $idUniteParent";
        } else {
            $sSql .= ", NULL";
        }
        $sSql .= ", '$nom'";
        if ($abreviation != '') {
            $sSql .= ", '$abreviation'";
        } else {
            $sSql .= ", NULL";
        }
        if ($description != '') {
            $sSql .= ", '$description'";
        } else {
            $sSql .= ", NULL";
        }
        if ($codeOrdre != '') {
            $sSql .= ", '$codeOrdre'";
        } else {
            $sSql .= ", NULL";
        }
        if ($couleur != '') {
            $sSql .= ", '$couleur'";
        } else {
            $sSql .= ", NULL";
        }
        $sSql .= ", $bactif, $bCache";
        $dbgo = new DBGoeland();
        $dbgo->queryRetJson2($sSql, 'W');
        echo $dbgo->resString;
        //echo "[$sSql]";
        unset($dbgo);
    } else {
        echo 'ERREUR GoelandManager requis';
    }
} else {
    echo 'ERREUR athentification F5';
}
