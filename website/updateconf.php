<?php
include($_SERVER['DOCUMENT_ROOT'] . '/other/dblogin.php');

if($mainorremote == "main")
{
    $dbip=trim($_SERVER['SERVER_ADDR']);
    if(isset($_GET['deviceip'])) { $devip=$_GET['deviceip']; } else { $devip=""; }
    if(trim($dbip) == trim($devip)) { $mainorremote="main"; } else { $mainorremote="remote"; }
    $po="no";
    $potoken="";
    $pouser="";
    $alertip="N";

    $getvariables="SELECT * FROM Variables";
    if(!$rs=mysqli_query($db,$getvariables)) { echo("Unable to Run Query: $getvariables"); exit; }
    while($row = mysqli_fetch_array($rs))
    {
        if($row['Var_Name'] == "Use-Pushover") { $po=$row['Var_Value']; }
        if($row['Var_Name'] == "Pushover-Token") { $potoken=$row['Var_Value']; }
        if($row['Var_Name'] == "Pushover-User-Key") { $pouser=$row['Var_Value']; }
    }

    $devdetails="SELECT Dev_AlertIPChange FROM Devices WHERE (Dev_IP='$devip')";
    if(!$rs=mysqli_query($db,$devdetails)) { echo("Unable to Run Query: $devdetails"); exit; }
    while($row = mysqli_fetch_array($rs)) { $alertip=$row['Dev_AlertIPChange']; }

    $now=date("Y-m-d H:i:s"); $update="UPDATE Devices SET Dev_CSDBConf='$now' WHERE (Dev_IP='$devip')";
    if(!$rs=mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }

    $newconf="database_ip=\"$dbip\"\npushover_configured=\"$po\"\npushover_token=\"$potoken\"\npushover_user_key=\"$pouser\"\nalert_on_ip_change=\"$alertip\"\nmain_or_remote=\"$mainorremote\"\n";

    if($devip != "") { echo($newconf); }
}
?>
