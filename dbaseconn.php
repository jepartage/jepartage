<?php //dbaseconn.php

    //this function connects to the database and also performs the query functions
    
    $dbhost = 'p50mysql391.secureserver.net';
    $dbname = 'cvdevsitenowp';
    $dbuser = 'cvdevsitenowp';
    
    $dbpass = 'Adr1an+0ple';
    mysql_connect($dbhost, $dbuser, $dbpass) or die(mysql_error());
    mysql_select_db($dbname) or die (mysql_error());
    
    function  queryMysql($query)
    {
        $result = mysql_query($query) or die(mysql_error());
        return $result;
    }
    
    ?>