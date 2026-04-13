<?php
require_once 'gdt/cldbgoeland.php';
header("Access-Control-Allow-Origin: *");
$bParamsOk = true;
$prmUniteHorsVdL = '1';
if (isset($_GET['jsoncriteres'])) {
    $jsonCriteres = $_GET['jsoncriteres'];
    $oCriteres = json_decode($jsonCriteres, false);
    if (isset($oCriteres->unitehorsvdl)) {
        $bUniteHorsVdL = $oCriteres->unitehorsvdl;
        if (!$bUniteHorsVdL) {
            $prmUniteHorsVdL = '0';
        }
    }
}
if ($bParamsOk) {
    $sSql = "cn_orgunit_liste $prmUniteHorsVdL";
    $dbgo = new DBGoeland();
    $dbgo->queryRetJson2($sSql);
    echo $dbgo->resString;
    unset($dbgo);
} else {
    echo '{}';
}

