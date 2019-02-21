<?php

function formatarCnpj($cnpj_cpf) {
    if (strlen(preg_replace("/\D/", '', $cnpj_cpf)) === 11) {
        $response = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
    } else {
        $response = preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
    }
    return $response;
}


function xss($data, $problem='') {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = strip_tags($data);
    if ($problem && strlen($data) == 0){ return ($problem); }
    return $data;
}

function curl($url, $payload=null, $tipo=null, $cookies=null, $header=true, $token=null) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, $header);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_REFERER, $url); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); 
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

    if ($payload) {
        if($tipo == 'GET') {
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        } else {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    }

    if (isset($cookies)) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    }

    if(isset($token)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [ "Authorization: Bearer {$token}" ]);
    }

    $page = curl_exec($ch);
    curl_close($ch); 
    return $page;
}

function saveLogs($placa, $dados) {
    $file = "{$placa}.json";
    $arquivo = "/var/www/html/veiculo/.cache/$file";
    $fp = fopen($arquivo, "w");
    fwrite($fp, $dados);
    fclose($fp);
}

function checkCache($placa) {
    $dir = '/var/www/html/veiculo/.cache';
    $file = "{$dir}/$placa.json";
    if(file_exists($file)) {

        $file = file_get_contents($file);
        return $file;
    }
    return false;
}

function consultar($placa) {

    $dCache = checkCache($placa);

    if($dCache !== false) {
        $dados = $dCache;
        $dados = filtroConsulta($dados, 2);
        return $dados;
    } else {
        $url   = 'https://checkbusca.com/Servicos/api_system_ok.php?token=HD8G1279GD9UG29E8172097938T827308=&placa='.$placa;
        $dados = curl($url, null, null, null, false);
        
        if(stristr($dados, 'culo nao encontrad')) {
            return ['msg' => 'nada_encontrado'];
        }elseif(!stristr($dados, 'renavam')) {
            return ['msg' => 'indisponivel'];
        } else {
            return filtroConsulta($dados, 1);
        }
    }
}

function filtroConsulta($dados, $tipo=1) {

    if(is_array($dados)) {
        return $dados;
    }

    $retorno = json_decode($dados);
    if(strlen($retorno->renavam) < 4) {
        return ['msg' => 'nada_encontrado'];
    }else{

        if($tipo == 1) {
            $placa = xss($retorno->placa);
            saveLogs($placa, json_encode($retorno));
        }
        

        $doc = $retorno->doc;
        if(strlen($doc) > 10) {
            $doc = formatarCnpj($doc);
        } else {
            return ['msg' => 'nada_encontrado'];
        }

        $nome    = $retorno->dono;
        $placa   = $retorno->placa;
        $renavam = $retorno->renavam;
        $chassi  = $retorno->chassi;
        $cor     = $retorno->cor;
        $marcmod = $retorno->marcamodelo;
        $modelo  = $retorno->anofabricacao.'/'.$retorno->anomodelo;
        $categor = $retorno->categoria;
        $municip = $retorno->municipio;
        $furto     = $retorno->furto;

        if($furto == '&nbsp;') {
            $furto = 'NADA CONSTA';
        }

        $dados = [];
        $dados['dados'] = [
            'doc'     => $doc,
            'nome'    => $nome,
            'placa'   => $placa,
            'renavam' => $renavam,
            'chassi'  => $chassi,
            'cor'     => $cor,
            'marcmodelo' => $marcmod,
            'modelo'     => $modelo,
            'categoria'  => $categor,
            'municipio'  => $municip,
            'furto' => $furto
        ];

        return $dados;
    }
}
