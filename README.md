# pnd2poi
Convert binary code in PHP
```
<?php
include("PndParser.php");
if(1) {
    $parser =new PndPaser();
    $parser->run( "PPGPOI17.DAT");
    echo $parser->header."\n";
    echo $parser->cnt."\n";
    echo "Total Count:".$parser->count()."\n";
    print_r($parser->getPoi(0));
}
```
