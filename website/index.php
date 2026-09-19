<!DOCTYPE html>
<html>
	<head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
        <meta name="author" content="Zachary Flight" />
        <title>Church Pi Systems</title>
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
                margin: 0px;
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
    <?php
    $systems=array("cec"=>"Calendar Entry Checker"); $installed=""; $notinstalled="";

    foreach($systems as $system => $fullname)
    {
        if(file_exists("$system/index.php")) { $installed.="<h3><a href='$system'>$fullname</a></h3>\n"; }
        else { $notinstalled.="<h3><a href='https://github.com/zachary-gbc/$system'>$fullname</a></h3>\n"; }
    }

    if($installed != "") { echo("<br><h2>Installed Systems:</h2>\n$installed<br>"); }
    if($notinstalled != "") { echo("<br><h2>Systems Not Installed:</h2>\n$notinstalled"); }
    ?>
</body>
</html>
