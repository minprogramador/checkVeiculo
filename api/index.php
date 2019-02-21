<?php

$tokenOk = 'beb99570f3a3f81cf289a586e5abd89b';

require('functions.php');

header("Content-type:application/json");

if(isset($_REQUEST['dados'])) {
	$placa = xss($_REQUEST['dados']);
    if(strlen($placa) < 3) {
        $error = ['msg' => 'placa_invalida'];
    }elseif(strlen($placa) > 10) {
        $error = ['msg' => 'doc_invalido'];
    } else {
        $error = null;
    }
} else {
    $error = ['msg' => false];
}

if($_REQUEST['token'] != $tokenOk) {
	$dados = ['msg' => 'acesso invalido'];
} else {
	if(isset($error)) {
		$dados = $error;
	}else{
		$dados = consultar($placa);
	}
}

echo json_encode($dados);
die;