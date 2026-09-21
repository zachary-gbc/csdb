<!DOCTYPE html>
<html>
	<head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
        <meta name="author" content="Zachary Flight" />
        <title>Variables</title>
        <style>
            html, body {
                background-color: #FFFFEE;
                padding: 2px 25px 2px 25px;
                font-size: 1.1em;
            }
            .header {
                color: blue;
                font-family: "Lato", "Open Sans", Arial, sans-serif;
                font-size: 2em;
                font-weight: bold;
                padding-left: 15px;
            }
            h1, h2, h3, h4, h5, h6 {
                color: blue;
            }
            table {
                border: 2px solid #0033CC;
            }
            td {
               padding: 3px 3px 0px 3px;
            }
            .tr_even {
                background-color: #AAFFFF;
            }
            .tr_odd {
                background-color: #CCFFFF;
            }
        </style>
    </head>

<body style="margin:0px">
	<div class="header">Church Pi Systems</div>
    <br><h3 style="margin: 3px 0px 0px 5px">Devices</h3><br>
    <?php
    include($_SERVER['DOCUMENT_ROOT'] . '/other/dblogin.php');
    if(isset($_POST['submit']))
    {
        $x=1;
        while(isset($_POST["id$x"]))
        {
            $id=str_replace("'","''",$_POST["id$x"]);
            $name=str_replace("'","''",$_POST["name$x"]);
            $type=str_replace("'","''",$_POST["type$x"]);
            $mac=str_replace("'","''",$_POST["mac$x"]);
            $ip=str_replace("'","''",$_POST["ip$x"]);
            if(isset($_POST["alertip$x"])) { $alertip="Y"; } else { $alertip="N"; }

            $update="UPDATE Devices SET Dev_Name='$name', Dev_Type='$type', Dev_MAC='$mac', Dev_IP='$ip', Dev_AlertIPChange='$alertip' WHERE (Dev_ID='$id')";
            if(!mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }
            if(isset($_POST["delete$x"])) { $delete="DELETE FROM Devices WHERE (Dev_ID='$id')"; if(!mysqli_query($db,$delete)) { echo("Unable to Run Query: $delete"); exit; } }
            $x++;
        }

        if(isset($_POST['newdev']) && trim($_POST['newdev']) != "")
        {
            $newdev=str_replace("'","''",$_POST["newdev"]);
            $insert="INSERT INTO Devices(Dev_Name, Dev_Type) VALUES('$newdev', 'Unknown')";
            if(!mysqli_query($db,$insert)) { echo("Unable to Add New Device"); exit; }
        }
    }

    $devices="SELECT * FROM Devices ORDER BY Dev_Name"; $table=""; $x=0;
    if(!$rs=mysqli_query($db,$devices)) { echo("Unable to Run Query: $devices"); exit; }
    while($row = mysqli_fetch_array($rs))
    {
        if(($x%2) == 0) { $table.=("<tr class='tr_odd'>\n"); } else { $table.=("<tr class='tr_even'>\n"); } $x++;
        if($row['Dev_AlertIPChange'] == "Y") { $alertchecked="checked='checked'"; } else { $alertchecked=""; }

        $table.=("<th>" . $row['Dev_ID'] . "<input type='hidden' name='id$x' value=\"" . $row['Dev_ID'] . "\" /></th>\n");
        $table.=("<td><input type='text' name='name$x' value=\"" . $row['Dev_Name'] . "\" /></td>\n");
        $table.=("<td><input type='text' name='type$x' value=\"" . $row['Dev_Type'] . "\" /></td>\n");
        $table.=("<td><input type='text' name='mac$x' value=\"" . $row['Dev_MAC'] . "\" /></td>\n");
        $table.=("<td><input type='text' name='ip$x' value=\"" . $row['Dev_IP'] . "\" /></td>\n");
        $table.=("<td><input type='checkbox' name='alertip$x' $alertchecked /></td>\n");
        $table.=("<td>" . date("m/d/Y h:i a",strtotime($row['Dev_CSDBGHUpdate'])) . "</td>\n");
        $table.=("<td>" . date("m/d/Y h:i a",strtotime($row['Dev_CSDBConf'])) . "</td>\n");
        $table.=("<td><input type='checkbox' name='delete$x' /></td>\n");
        $table.=("</tr>\n");
    }

    echo("<form method='post' action=''>\n<table>\n<tr>\n<th>ID</th>\n<th>Name</th>\n<th>Type</th>\n<th>MAC Address</th>\n");
    echo("<th>IP Address</th>\n<th>Alert on IP Change</th>\n<th>GitHub Update</th>\n<th>Conf Update</th>\n<th>Delete</th>\n</tr>\n$table</table>\n");
    echo("<br>New Device Name: <input type='text' name='newdev' />\n <br><br><input type='submit' name='submit' value='Submit Changes' />\n</form>\n");
    mysqli_close($db);
    ?>
</body>
</html>
