<?php

#mock veiculo.

define('URLAPI', 'http://191.96.139.176/veiculo/?token=beb99570f3a3f81cf289a586e5abd89b&dados=');

function xss($data, $problem='') {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = strip_tags($data);
    if ($problem && strlen($data) == 0){ return ($problem); }
    return $data;
}

function curl($url,$cookies,$post,$referer=null,$header=1,$follow=false) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, $header);
    if ($cookies) curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow);
    if(isset($referer)){ curl_setopt($ch, CURLOPT_REFERER,$referer); }
    else{ curl_setopt($ch, CURLOPT_REFERER,$url); }
    if(strlen($post) > 5)
    {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
    }
    curl_setopt($ch,  CURLOPT_SSL_VERIFYHOST, FALSE); 
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch,  CURLOPT_TIMEOUT, 30); 
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 30);

    $res = curl_exec( $ch);

    curl_close($ch); 
    return $res;
}

function consultar($doc) {
    $url   = URLAPI . $doc;
    $dados = curl($url, null, null, null, false);
    if(stristr($dados, '{')) {
        $dados = json_decode($dados, true);
        return $dados;
    }else{
        return false;
    }
}


if(isset($_POST['dados'])) {
    header("Content-type:application/json");

    $placa = xss($_POST['dados']);
    if(strlen($placa) < 3) {
        $error = ['msg' => 'placa_invalida'];
    }elseif(strlen($placa) > 10) {
        $error = ['msg' => 'doc_invalido'];
    } else {
        $error = null;
    }

    if(!isset($error)) {
        $dados = consultar($placa);
        if(!is_array($dados)) {
            $dados = ['msg' => 'indisponivel'];
        }
        echo json_encode($dados);
        die;
    }
}else{
//    include('./tpls/checkCondut/index.html');
  //  die;
}



