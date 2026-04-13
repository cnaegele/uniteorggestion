<?php
require_once 'gdt/cldbgoeland.php';
header("Access-Control-Allow-Origin: *");
$bParamsOk = false;
$prmUniteHorsVdL = '1';
if (isset($_GET['jsoncriteres'])) {
    $jsonCriteres = $_GET['jsoncriteres'];
    $oCriteres = json_decode($jsonCriteres, false);
    if (isset($oCriteres->uniteorgid)) {
        $uniteorgid = $oCriteres->uniteorgid;
        if (filter_var($uniteorgid, FILTER_VALIDATE_INT) !== false && (int)$uniteorgid > 0) {
            $bParamsOk = true;
        }
    }
}
if ($bParamsOk) {
    $sSql = "cn_orgunit_data $uniteorgid";
    $dbgo = new DBGoeland();
    $dbgo->queryRetJson2($sSql);
    echo $dbgo->resString;
    unset($dbgo);
} else {
    echo '{}';
}