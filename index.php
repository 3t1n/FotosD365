<?php
include 'd365.php';

//f787b828-0542-eb11-a812-000d3ac166cd
$guid = $_GET['guid'];

if(isset($_GET["guid"]) && !is_null($guid)){
    $query = "annotations(".$guid.")";
    $d365 = new D365("057e915e-649e-4c0c-86a5-97d349f622d1", "4VOcrrkF-_~aMzLrXY6FwIwx_14vT0~gnb", "crmservice@cdhucrm.onmicrosoft.com", "Pass@word1","https://cdhucrm.api.crm2.dynamics.com");
    $response = $d365->queryOdata($query);
    //baixa as imagens
    header('Content-Description: File Transfer');
    header("Content-type: application/octet-stream");
    header("Content-disposition: attachment; filename= Image.jpg");
    exit(base64_decode($response->documentbody));
}else{
    echo("Guid não informado");
}


?>