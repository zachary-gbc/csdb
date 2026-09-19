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
        </style>
    </head>

<body style="margin:0px">
	<div class="header">Church Pi Systems</div>
    <br><h3 style="margin: 3px 0px 0px 5px">Variables</h3><br>
    <?php
    include($_SERVER['DOCUMENT_ROOT'] . '/other/dblogin.php');
    $system="GLOBAL";
    if(isset($_POST['submit']))
    {
        $x=0;
        while(isset($_POST["id$x"]))
        {
            $id=str_replace("'","''",$_POST["id$x"]);
            $name=str_replace("'","''",$_POST["name$x"]);
            $value=str_replace("'","''",$_POST["value$x"]);

            $update="UPDATE Variables SET Var_Name='$name', Var_Value='$value' WHERE (Var_ID='$id')";
            if(!mysqli_query($db,$update)) { echo("Unable to Run Query: $update"); exit; }
            $x++;
        }

        if(isset($_POST['newvar']) && trim($_POST['newvar']) != "")
        {
            $newvar=str_replace("'","''",$_POST["newvar"]);
            $insert="INSERT INTO Variables(Var_System, Var_Name) VALUES('$system', '$newvar')";
            if(!mysqli_query($db,$insert)) { echo("Unable to Run Query: $insert"); exit; }
        }
    }

    $variables="SELECT * FROM Variables WHERE (Var_System='$system') ORDER BY Var_Name"; $table=""; $x=0;
    if(!$rs=mysqli_query($db,$variables)) { echo("Unable to Run Query: $variables"); exit; }
    while($row = mysqli_fetch_array($rs))
    {
        $table.=("<th>" . $row['Var_ID'] . "<input type='hidden' name='id$x' value=\"" . $row['Var_ID'] . "\" /></th>\n");
        $table.=("<td><input type='text' name='name$x' value=\"" . $row['Var_Name'] . "\" /></td>\n");
        $table.=("<td><input type='text' name='value$x' value=\"" . $row['Var_Value'] . "\" /></td>\n");
        $table.=("</tr>\n"); $x++;
    }

    echo("<form method='post' action=''>\n<table>\n<tr>\n<th>ID</th>\n<th>Name</th>\n<th>Value</th>\n</tr>\n$table</table>\n");
    echo("<br>New Variable Name: <input type='text' name='newvar' />\n <br><br><input type='submit' name='submit' value='Submit Changes' />\n</form>\n");

    mysqli_close($db);
    ?>
</body>
</html>
