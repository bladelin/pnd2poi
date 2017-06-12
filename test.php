<?php
include("PndParser.php");

if(1) {
    $parser =new PndPaser();
    $parser->run( "PPGPOI17.DAT");
    //$parser->run( "/home/blade/www/anyway2fun_fun/srcAppServer/protected/runtime/output.dat");
    echo $parser->header."\n";
    echo $parser->cnt."\n";
    echo "Total Count:".$parser->count()."\n";
    print_r($parser->getPoi(0));
    //print_r($parser->pnd['CNT']);
}
