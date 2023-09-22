<?php
require_once 'cors.php';

require_once __DIR__ . '/vendor/autoload.php';
use App\Entity\Operacao;
use App\Entity\UsoEquipamento;
use App\Entity\Agente;
use App\Entity\Response;
use App\Entity\Equipamento;
use App\Entity\Usuario;
use App\Entity\OperacaoDetalhada;
use App\Entity\ConfigurationAPI;

/*"dados: {
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
}"
{ 'dados': {
        'entidade': 'usuario',
        'operacao': 'consultar'
        'objeto':{}
}
}"

{ "dados":
  {
    "entidade":"operacao",
    "operacao":"cadastrar",
    "objeto":{
      "nomeOperacao":"Insira nome",
      "data":"00-00-0000",
      "bairro":null,
      "kmIni":"1.0",
      "kmFim":"0.0",
      "matriculaResponsavel":"00000",
      "hraIni":null,
      "hraFim":null,
      "municipio":null,
      "observacoes":"Insira alguma observação."
    } } }

    { "dados":
      {
        "entidade":"operacao",
        "operacao":"atualizar",
        "objeto":{
          "id": 1,
          "nomeOperacao":"Insira nome",
          "data":"00-00-0000",
          "bairro":null,
          "kmIni":"1.0",
          "kmFim":"0.0",
          "matriculaResponsavel":"00000",
          "hraIni":null,
          "hraFim":null,
          "municipio":null,
          "observacoes":"Insira alguma observação."
        } } }
*/

/* $obj = new Usuario;
$hash = "$2y$10$TbXzt155A/H8.Lf0S3fcTuGGV8ka9o7w1b7nUkibE6RcxGMGqXjQO"; // $obj->cifraSenha( "paulo" );
echo '----- ';
$obj->decifraSenha( "senha" , $hash );
exit;*/

$current_time = microtime( true ); // Get the current time with milliseconds
$SERVER_FORMATTED_TIME = date( 'H:i:s.' ) . sprintf("%03d", ( $current_time - floor( $current_time ) ) * 1000 ); // Format the time with milliseconds

/********************************************************************************************************************************************

      JSON -> {   "dados":{"entidade":"operacaoDetalhada","operacao":"consultar","objeto":{"data":"2023-04-25"} } }

********************************************************************************************************************************************/
function generateLog( $data )
{
      // Get the current month
      $currentMonth = date('Y-m');

      // Create the log file name
      $logFileName = 'log-' . $currentMonth . '.txt';
      $message = $data;

      $file = fopen($logFileName, 'a'); // MODO DE APPEND

      if ($file) {
          fwrite($file, $message . PHP_EOL);
          fclose($file);
      } else {
          // Handle the case when the file could not be opened
          echo 'Unable to open the log file.';
      }

}

if ( $_SERVER[ 'REQUEST_METHOD' ] === 'POST' )
{
     // if ( ConfigurationAPI::$EXECUTION_MODE == 'DEBUG')
     // print_r( file_get_contents('php://input') );
     $jsonStrIn = file_get_contents( 'php://input' );
     $data = json_decode( $jsonStrIn, true );
     $logIn = "[ " . $SERVER_FORMATTED_TIME . " ] IN >> " . $jsonStrIn;
     generateLog( $logIn );

     if ( ( $data === null ) && ( json_last_error() !== JSON_ERROR_NONE ) )     {
          echo "Invalid JSON error: " . json_last_error_msg();
          exit;
     }
     $entityObj = null;
     $response  = null;

     $DATA_ENTITY = $data['dados']['entidade'];
     if ( isset( $data['dados']['objeto'] ) )
          $DATA_OBJECT = $data['dados']['objeto'];

     $DATA_OPERATION = $data['dados']['operacao'];

     switch ( $DATA_ENTITY )
     {
       case 'operacao' :
             $entityObj = new Operacao;
             break;

       case 'usoEquipamento' :
             $entityObj = new UsoEquipamento;
             break;

       case 'equipamento' :
             $entityObj = new Equipamento;
             break;

       case 'usuario' :
             $entityObj = new Usuario;
             break;

       case 'agente' :
             $entityObj = new Agente;
             break;

       case 'operacaoDetalhada' :
             $entityObj = new OperacaoDetalhada;
             break;
     }

    switch ( $DATA_OPERATION )
    {
        case 'cadastrar':
              if ( ConfigurationAPI::$EXECUTION_MODE == 'DEBUG')  {
                   print_r( $data['dados']['objeto'] );
                   echo 'Cadastra response ------- <br>';
              }
              $response = $entityObj->cadastra( $DATA_OBJECT );
              break;

        case 'atualizar':
              //print_r( $data['dados']['objeto'] );
              $response = $entityObj->atualiza( $DATA_OBJECT );
              break;

        /*** Usado Somente para USUARIO ***/
        case 'novaSenha':
              // print_r( $data['dados']['objeto'] );
              // $response = $entityObj->atualiza( $DATA_OBJECT );
              break;

        case 'consultar':
              if ( $DATA_ENTITY == 'agente'  )  {
                   echo file_get_contents('agentes.json') ;
                   exit;
              }
              if ( $DATA_ENTITY == 'equipamento' )
                   $response = $entityObj->consulta();
              else {
                  // code...
                  $response = $entityObj->consulta( $data['dados']['objeto'] );
              }

              break;

        case 'login':
              //echo 'login';
              //print_r( $DATA_OBJECT );
              $response = $entityObj->login( $DATA_OBJECT['matricula'], $DATA_OBJECT['senha'] );
              break;
    }
    // echo "LOGIN: " . $response->toJson();
    // print_r(  $response  );
    $json = null;

    /** echo 'Entity?' . $DATA_ENTITY;
    echo "<br>Response é objeto? " . is_object( $response );
    **/

    /* 1- Objetos com JSON */
    if ( is_object( $response ) )
    {
         // ***** Equip consulta *****
         if ( method_exists( $response, 'toJson' ) )    {
              $json = stripslashes( $response->toJson() );
              echo $json;
              generateLog( "[ " . $SERVER_FORMATTED_TIME . " ] OUT << " . $json );
              exit;
         } else if ( ( $DATA_ENTITY == 'equipamento' ) && ( $DATA_OPERATION == 'consultar' ) ) {
              $json = substr_replace( $json, ']', -1, 0 );
              echo $json;
              generateLog( "[ " . $SERVER_FORMATTED_TIME . " ] OUT << " . $json );
              exit;
         }
    }
    $json = stripslashes( json_encode( $response ) );
    echo $json;
    generateLog( "[ " . $SERVER_FORMATTED_TIME . " ] OUT << " . $json );
 }

// {"dados":{"entidade":"operacao","operacao":"cadastrar","objeto":{"nomeOperacao":"teste","data":"00-00-0000","bairro":"jaguaribe","kmIni":"23","kmFim":"26","matriculaResponsavel":"123","hraIni":"9:50","hraFim":"9:50","municipio":"Mangabeira","observacoes":"Insira alguma observação."}}}


?>
