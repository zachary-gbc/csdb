<?php
include($_SERVER['DOCUMENT_ROOT'] . '/other/dblogin.php');

if($mainorremote == "main" && !isset($_GET['update']))
{
    $database_ip=trim($_SERVER['REMOTE_ADDR']);
    $pushover_configured="no";
    $pushover_token="";
    $pushover_user_key="";

    $getvariables="SELECT * FROM Variables";
    if(!$rs=mysqli_query($db,$getvariables)) { echo("Unable to Run Query: $getvariables"); exit; }
    while($row = mysqli_fetch_array($rs))
    {
        if($row['Var_Name'] == "Use-Pushover") { $pushover_configured=$row['Var_Value']; }
        if($row['Var_Name'] == "Pushover-Token") { $pushover_token=$row['Var_Value']; }
        if($row['Var_Name'] == "Pushover-User-Key") { $pushover_user_key=$row['Var_Value']; }
    }

    $devices="SELECT * FROM Devices";
    if(!$rs=mysqli_query($db,$devices)) { echo("Unable to Run Query: $devices"); exit; }
    while($row = mysqli_fetch_array($rs))
    {
        $response="";
        $deviceip=$row['Dev_IP'];
        $alertip=$row['Dev_AlertIPChange'];
        $response=file_get_contents("http://$deviceip/other/updateconf.php?update=true&po=$pushover_configured&potoken$pushover_token&pouser=$pushover_user_key&alertip=$alertip");

        if($response == "UPDATED")
        {
            $now=date("Y-m-d H:i:s"); $update="UPDATE Devices SET Dev_CSDBConf='$now'";
            if(!$rs=mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }
        }
    }
}
elseif(isset($_GET['update']) && $_GET['update'] == "true")
{
    $database_ip=trim($_SERVER['REMOTE_ADDR']);
    $po=$_GET['po'];
    $potoken=$_GET['potoken'];
    $pouser=$_GET['pouser'];
    $alertip=$_GET['alertip'];
    if(trim($dbip) == trim($database_ip)) { $mainorremote="main"; } else { $mainorremote="remote"; }

    $newconf="database_ip=\"$dbip\"\npushover_configured=\"$po\"\npushover_token=\"$potoken\"\npushover_user_key=\"$pouser\"\nalert_on_ip_change=\"$alertip\"\main_or_remote=\"$mainorremote\"";

    file_put_contents($conflines,$newconf);
    echo("UPDATED");
}
?>
