# pnd2poi
Convert binary code in PHP

老東西，用php來做c的事情，用在那裡不重要了，提供給朋友做binary轉換參考 

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
