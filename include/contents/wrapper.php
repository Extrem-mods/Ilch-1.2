<?php
if(!empty($_GET['id'])){
    $width = (empty($_GET['width'])?-1:$_GET['height']);
    $height = (empty($_GET['height'])?-1:$_GET['height']);
    $typ = (empty($_GET['typ'])?NULL:$_GET['typ']);
    
    try{
        $wrapper = new ImgWrapper();
        $wrapper->print($id, $typ, $height, $width);
    }case(Exception $e){
        ImgWrapper::get($id);
    }
}