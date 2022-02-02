<!--Starts session to DB to store in session vars-->
<?php
  session_start();
  //require "includes/dbh.inc.php";
?>

<DOCTYPE html>
<!DOCTYPE HTML>

<html lang="en">

    <head>

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name=viewport content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="webStyles.css">

        <title>DB Order Webpage</title>

    </head>

    <body>
        <header>
            <nav class="navHeaderMain">
                <a class="headerLogo" href="newOrderInput.php">
                    <img src="img/logo2.png" alt="web logo">
                </a>
                <ul>
                    <li><a href="newOrderInput.php">Home/Input Order</a></li>
                </ul>
            </nav>        
        </header>
    </body>
</html>