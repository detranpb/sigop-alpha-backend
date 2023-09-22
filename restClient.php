<?php

$url = 'http://localhost/policiamento/app/policiamento-services.php'; // Replace with the API endpoint URL

/*$data = array(
    'name' => 'John Doe',
    'email' => 'johndoe@example.com'
);*/

$jsonIn = "
  dados: {
      'entidade': 'uso-equipamento',
      'operacao': 'cadastrar',
      'objeto': {
          idOperacao = 1,
          matriculaAgente = 41416,
          idEquipamento = 1,
          dataDevolucao = '01-01-2012',
          horaDevolucao = '01-01-2012',
          isAvariado  = true
      }
}";

$options = array(
    'http' => array(
        'header'  => "Content-type: application/json",
        'method'  => 'POST',
        'content' => $jsonIn //json_encode($data)
    )
);

$context  = stream_context_create( $options );
$response = file_get_contents( $url, false, $context );

if ($response === false) {
    // Error handling
    die('Error occurred while making the API request.');
}

$responseData = json_decode( $response, true );
// echo 'Ok ==' . $responseData;
// Process the API response
// ...

?>
