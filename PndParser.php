<?php

//unit byte *2  FF
define('BYTE', 1*2);
define('LONG', 4*2);
define('TCHAR', 2*2);
define('MTIME', 4*2);
define('DWORD', 4*2);

class PndPaser {

    public $debug = false;
    public $output=array();
    public $fileName = "PPGPOI15.DAT";
    public $fp;
    public $fileSize;
    public $address;
    public $pnd = array('HEAD','LAYER','CNT','POIS');
    public $index = 0; //address
    public $header = '';
    public $poi;
    private $defHeader = 'PPGNE_MYPOI_070531.R15';
    public $cnt =0;

    public function run($fileName = null) {

        $fileName = !is_null($fileName)  ? $fileName : $this->fileName;
        //$fileName =
        $this->fp = fopen($fileName, "rb");
        $this->fileSize = filesize($fileName);
        $this->pnd['HEAD'] = $this->getBodyParts(0, 32);//$this->_hex2ascii($this->_readByOffset($fp, 0, 32));
        $this->pnd['LAYER']  = $this->getBodyParts(32, 3200 );
        $this->pnd['CNT']  = $this->getBodyParts(3232, 4);
        $this->pnd['POIS']  = $this->getBodyParts(3236, $this->fileSize);
        $this->header = $this->_hex2ascii($this->pnd['HEAD']['val']);
        $this->cnt = hexdec($this->_chbo($this->pnd['CNT']['val']));
        $i=0;
        $beginAddr = $this->pnd['POIS']['addr'];
        $cnt = ($this->fileSize-$beginAddr)/1112;
        $pois = $this->pnd['POIS']['hex'];

        for($i=0; $i<=$cnt; $i++) {
            $this->address = $i*1112;
            $poisHex = substr($pois, $this->address, 1120);
            $this->poi[$i] = $this->_getPoi($poisHex,$i);
        }

        if($this->debug) {
            unset($this->pnd['POIS']);
            print_r($this->pnd);
            //echo $this->header."\n";
            //echo hexdec($this->pnd['CNT']['val'])."\n";
            print_r($this->poi[0]);
            die();
        }
        fclose($this->fp);
    }

    private function _tohighEdian($bin) {
        if (PHP_INT_SIZE <= 4){
            list(,$h,$l) = unpack('n*', $bin);
            return ($l + ($h*0x010000));
        }
        else{
            list(,$int) = unpack('N', $bin);
            return $int;
        }

    }

    public function saveFile($filePath) {

        //print_r($poiData);
        $poiData = array();
        $header = $this->defHeader;

        $fp = fopen($filePath, "wb");
        //$data = $this->_asc2bin($header);

        //$data = $this->_asc2hex($header);
        //$data  = pack("H*" ,  $data );
        //$data .=  $this->_fillZeros(10);

        $layer ='';
        $cnt = 1854;
        $cnt = sprintf("%08s",dechex($cnt));
        //echo $cnt."\n";
        //$data ="0000073e";
        //echo $this->_chbo($cnt);


        $data =  $this->_fillZeros($header, 32, 'ascii');
        $data .=  $this->_fillZeros($layer, 3200, 'ascii');
        $data .=  $this->_fillZeros($cnt, 4, 'chbo');


        /*
        $arry['gps'] = $this->_getAddrData($poisHex , $pos, 2*LONG);
        $arry['name'] = $this->_getAddrData($poisHex, $pos, TCHAR*64 , true);
        $arry['tel'] = $this->_getAddrData($poisHex, $pos, TCHAR*16, true);
        $arry['addr'] = $this->_getAddrData($poisHex, $pos, TCHAR*64, true );
        $arry['note'] = $this->_getAddrData($poisHex, $pos, TCHAR*64);
        $arry['photo'] = $this->_getAddrData($poisHex, $pos, TCHAR*64);

        foreach($arry as $key => $val)
            $arry[$key]['val'] = $this->_getStr($val['hex']);

        $arry['layer'] = $this->_getAddrData($poisHex, $pos, BYTE);
        $arry['count'] = $this->_getAddrData($poisHex,$pos, BYTE);
        $arry['lock'] = $this->_getAddrData($poisHex, $pos, BYTE);
        $arry['time'] = $this->_getAddrData($poisHex, $pos, MTIME);

        $pos=0;
        $arry['x'] = $this->_getAddrData($arry['gps']['hex'], $pos, 8);
        $arry['y'] = $this->_getAddrData($arry['gps']['hex'], $pos, 8);
        $arry['x']['val'] = hexdec($this->_chbo($arry['x']['hex']))/1000000;
        $arry['y']['val'] = hexdec($this->_chbo($arry['y']['hex']))/1000000;
        $arry['time']['val'] = date("Y-m-d H:i:s",hexdec($this->_chbo($arry['time']['hex'])));
        

        foreach($poiData as $key => $poi) {
            foreach($tags as $tag => $length) {
                if(isset($poi->{$tag}) && $poi->{$tag}!='')) {
                    $v = $poi->{$tag};
                    $poiBinary[$key] =  $this->_fillZeros($v, $length, 'chbo');  
                }
            }
        }
        */


        $tags = array(
            'x' => 2 *LONG,
            'y' => 2 *LONG,
            'name' => TCHAR*64 ,
            'tel' => 2 *LONG,
            'addr' => 2 *LONG,
            'note' => 2 *LONG,
            'photo' => 2 *LONG,
            'time' => 2 *LONG,
        );
        
        $poi['x'] = sprintf("%08s",dechex(121571384));
        $poi['y'] = sprintf("%08s",dechex(25079752));
        //$poi['name'] = '\x'.dechex();

        //$array = preg_split('//u', '研');
//1478
        $character =  '研勤';

        echo $this->UTF_to_Unicode($character);//'&#','');
        die();
        $poi['name'] = dechex(str_replace($this->UTF_to_Unicode($character),'&#',''));
     

        $poiBinary = '';
        foreach($tags as $tag => $length) {
            if(isset($poi[$tag])) {
                $v = $poi[$tag];
                $poiBinary .= $this->_fillZeros($v, $length, 'chbo');  
            }
        }

        if($poiBinary && $poiBinary != '') {
            //print_r($poiBinary);
            $data .= $poiBinary;
        }
      
        //$data .=  $this->_fillZeros($ds,1112,'chbo');
        //}
        fwrite($fp, $data);
        fclose($fp);
    }

    private function _fillZeros($input, $length,$type) {
        $multipiler = $length - strlen($input);
        if($type=='ascii')
            $hexData = $this->_asc2hex($input);
        else {
            return pack("H*",$this->_chbo($input));
        }
        return pack("H*",$hexData.str_repeat('00', $multipiler));
    }

    private function _packPoi() {
        return '00';
    }

    private function _asc2hex ($temp) {
       $len = strlen($temp);
       $data='';
       for ($i=0; $i<$len; $i++) $data.=sprintf("%02x",ord(substr($temp,$i,1)));
       return $data;
    }

    public function count() {
        return count($this->poi);
    }

    public function getPoi($i) {
        return $this->poi[$i];
    }

    public function getBodyParts($pos,$length) {

        $val = $this->_readByOffset($this->fp, $pos, $length);
        return array('hex' => $val,
                     'addr'=> $pos,
                     'val' => $val,
        );
    }

    private function _getPoi($poisHex,$i=0) {

        $pos=0;
        $arry['gps'] = $this->_getAddrData($poisHex , $pos, 2*LONG);
        $arry['name'] = $this->_getAddrData($poisHex, $pos, TCHAR*64 , true);
        $arry['tel'] =$this->_getAddrData($poisHex, $pos, TCHAR*16, true);
        $arry['addr'] =$this->_getAddrData($poisHex, $pos, TCHAR*64, true );
        $arry['note'] = $this->_getAddrData($poisHex, $pos, TCHAR*64);
        $arry['photo'] = $this->_getAddrData($poisHex, $pos, TCHAR*64);

        foreach($arry as $key => $val)
            $arry[$key]['val'] = $this->_getStr($val['hex']);

        $arry['layer'] = $this->_getAddrData($poisHex, $pos, BYTE);
        $arry['count'] = $this->_getAddrData($poisHex,$pos, BYTE);
        $arry['lock'] = $this->_getAddrData($poisHex, $pos, BYTE);
        $arry['time'] = $this->_getAddrData($poisHex, $pos, MTIME);

        $pos=0;
        $arry['x'] = $this->_getAddrData($arry['gps']['hex'], $pos, 8);
        $arry['y'] = $this->_getAddrData($arry['gps']['hex'], $pos, 8);
        $arry['x']['val'] = hexdec($this->_chbo($arry['x']['hex']))/1000000;
        $arry['y']['val'] = hexdec($this->_chbo($arry['y']['hex']))/1000000;
        $arry['time']['val'] = date("Y-m-d H:i:s",hexdec($this->_chbo($arry['time']['hex'])));

        $outputArr =array('x','y','name','tel','addr','note','photo','time');
        foreach($outputArr as $v) {
           $this->output[$i][$v] = $arry[$v]['val'];
        }
        return $arry;
    }

    private function _getAddrData($res, &$pos, $len , $dymanic = false) {

        $str =substr($res, $pos, $len);
        $pos = $pos+$len;
        if($dymanic) {
            $newStr = '';
            $i=0;
            while($i<=$len) {
                $new = substr($str,$i,4);
                //echo $i.$new."\n";
                $newStr .= $new;
                if($new == "0000")
                    break;
                $i = $i+4;
            }
            $str = $newStr;
        }
        return array(
            'hex'=>$str,
            'addr' => $this->_getAddr($pos,$len),
        );
    }

    private function _getAddr($pos, $length) {

        $pos = $this->pnd['POIS']['addr']+ $this->address+$pos;
        return '0x'.sprintf("%04s", dechex($pos)).' - 0x'. sprintf("%04s",dechex($pos+$length));
    }

    private function _getStr($arr,$len = 4) {

        $out = '';
        for($i = 0; $i < strlen($arr) / $len; $i++) {
            $str = substr($arr,$len * $i,$len);
            if($str != '') {
                $str = '\u'.$this->_chbo($str);
                $out .= $this->_unescape_utf16($str);
            }
        }
        return $out;
    }

    private function _unescape_utf16($string) {
        //return $string;
        /* go for possible surrogate pairs first */
        $string = preg_replace_callback('/\\\\u(D[89ab][0-9a-f]{2})\\\\u(D[c-f][0-9a-f]{2})/i',
            function ($matches) {
                $d = pack("H*", $matches[1].$matches[2]);
                return mb_convert_encoding($d, "UTF-8", "UTF-16BE");
            }, $string);
        /* now the rest */
        $string = preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
            function ($matches) {
                $d = pack("H*", $matches[1]);
                return mb_convert_encoding($d, "UTF-8", "UTF-16BE");
            }, $string);
        return $string;
    }

    private function _readByOffset(&$fp,$offset,$length) {
        fseek($fp, $offset);
        return bin2hex(fread($fp, $length));
    }


    private function _ascii2hex($str) {
        $p = '';
        for ($i=0; $i < strlen($str); $i=$i+2) {
            $p .= chr(substr($str, $i, 1));
        }
        return $p;
    }

    private function _hex2ascii($str) {

        $p = '';
        for ($i=0; $i < strlen($str); $i=$i+2) {
            $p .= chr(hexdec(substr($str, $i, 2)));
        }
        return $p;
    }

    private function _chbo($num,$type="h") {
        //if($type=='n') $data = dechex($num);
        $data = $num;
        if (strlen($data) <= 2)
            return $num;
        $u = unpack("H*", strrev(pack("H*", $data)));
        if($type=='n')
            return hexdec($u[1]);
        return $u[1];
    }

    function UTF_to_Unicode($input, $array=False) {

    $value = '';
    $val   = array();

    for($i=0; $i< strlen( $input ); $i++){
 
        $ints = ord ( $input[$i] );
 
        $z     = ord ( $input[$i] );
        if(isset($input[$i+1]))
            $y     = ord ( $input[$i+1] ) - 128;
        if(isset($input[$i+2]))
            $x     = ord ( $input[$i+2] ) - 128;
        if(isset($input[$i+3]))
            $w     = ord ( $input[$i+3] ) - 128;
        if(isset($input[$i+4]))
            $v     = ord ( $input[$i+4] ) - 128;
        if(isset($input[$i+5]))
            $u     = ord ( $input[$i+5] ) - 128;
        
        /* Encoding 1 bit
        @@@@@@@@@@@@@@@@@@@@@@@@@@*/
        if( $ints >= 0 && $ints <= 127 ){
            // 1 bit
            $value[] = $z;
            $value1[]= dechex($z);
            //$val[]  = $value; 
        }
        
        /* Encoding 2 bit
        @@@@@@@@@@@@@@@@@@@@@@@@@@*/
        if( $ints >= 192 && $ints <= 223 ){
        // 2 bit
            //$value[] = $temp = ($z-192) * 64 + $y;
            $value[] = $temp = ($z-192) * 64 + $y;
            $value1[]= dechex($temp);
            //$val[]  = $value;
        }  
          
        /* Encoding 3 bit
        @@@@@@@@@@@@@@@@@@@@@@@@@@*/
        if( $ints >= 224 && $ints <= 239 ){
            // 3 bit
            $value[] = $temp = ($z-224) * 4096 + $y * 64 + $x;
            $value1[]= dechex($temp);
            //$val[]  = $value;
        } 
        
        /* Encoding 4 bit
        @@@@@@@@@@@@@@@@@@@@@@@@@@*/    
        if( $ints >= 240 && $ints <= 247 ){
            // 4 bit
            $value[] = $temp = ($z-240) * 262144 + $y * 4096 + $x * 64 + $w;
            $value1[]= dechex($temp);
        } 
         
        /* Encoding 5 bit
        @@@@@@@@@@@@@@@@@@@@@@@@@@*/   
        if( $ints >= 248 && $ints <= 251 ){
            // 5 bit
            $value[] = $temp = ($z-248) * 16777216 + $y * 262144 + $x * 4096 + $w * 64 + $v;
            $value1[]= dechex($temp);
        }
        
        /* Encoding 6 bit
        @@@@@@@@@@@@@@@@@@@@@@@@@@*/
        if( $ints == 252 || $ints == 253 ){
            // 6 bit
            $value[] = $temp = ($z-252) * 1073741824 + $y * 16777216 + $x * 262144 + $w * 4096 + $v * 64 + $u;
            $value1[]= dechex($temp);
        }
        
        /* Wrong Ord!
        @@@@@@@@@@@@@@@@@@@@@@@@@@*/
        if( $ints == 254 || $ints == 255 ){
            echo 'Wrong Result!<br>';
        }
     
    }
 
    if( $array === False ){
        $unicode = '';
        foreach($value as $value){
               $unicode .= '&#'.$value.';';
        
        }
        //return str_replace(array('&#', ';'), '', $unicode);
        return $unicode;
        
    }
    if($array === True ){
       return $value;
    }
 
}

}

if(0) {
    $parser =new PndPaser();
    $parser->run( "/home/blade/www/anyway2fun_fun/srcAppServer/protected/runtime/output.dat");
    echo $parser->header."\n";
    echo $parser->cnt."\n";
    echo "Total Count:".$parser->count()."\n";
    $item = $parser->getPoi(689);
}else {
    $parser =new PndPaser();
    $parser->saveFile("./output.dat");
}
//echo strlen($item)."\n";
//print_r($parser->output);

