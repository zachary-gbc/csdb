<?php
  include('dblogin.php');

  $type=""; $device=""; $devname="rpi-xx"; $devip=""; $update=""; $dbupdate=""; $now=date("Y-m-d H:i:s");
  if(isset($_GET['type'])) { $type=$_GET['type']; }
  if(isset($_GET['device'])) { $device=$_GET['device']; }
  if(isset($_GET['devname'])) { $devname=$_GET['devname']; }
  if(isset($_GET['lanip'])) { $devip=$_GET['lanip']; }
  
  if($device != "")
  {
    switch($type)
    {
      case "new":
        $insert="INSERT INTO Devices(Dev_Type, Dev_MAC, Dev_IP) VALUES('Pi', '$device', '$devip')";
        if(!mysqli_query($db,$insert)) { echo("Unable to Run Query: $insert"); exit; }
        break;

        case "ghupdate":
        $update="UPDATE Devices SET Dev_CSDBGHUpdate='$now' WHERE (Dev_MAC='$device')";
        break;

      case "ipchange":
        $ipaddress=$_GET['ipaddress'];
        $update="UPDATE Devices SET Dev_IP='$devip' WHERE (Dev_MAC='$device')";
        break;

      case "pushover":
        $title=$_GET['title'];
        $response=$_GET['response'];
        $update="INSERT INTO PushoverLog (PO_Device, PO_Title, PO_Response, PO_DateTime) VALUES((SELECT Dev_ID FROM Devices WHERE (Dev_MAC='$device')), '$title', '$response', '$now')";
        break;
    }

    if($update != "")
    {
      if(!mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }
      echo("MESSAGE " . date("Y-m-d H:i:s") . ": Database Updated Successfully ($type)\n");
    }
  }
?>
