<?php

namespace App\Entity;

use \App\Db\Database;
use \PDO;

class Response
{
    public $code;
    public $message;
    public $data;

    public function __construct($code, $message, $data) {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    public function toJson( $objCaller = null )
    {
      $objectVars = get_object_vars($this);
      $json = json_encode($objectVars);

      /* Caso o retorno tenha CPF === é A CONSULTA DE login !!!!! Tem q marretar p/ remover aspas*/
      if ( strpos( $json, "cpf" ) !== false ) 
           return $this->removeAspasDuplas( $json );
      else
          return $json; //
    }

    private function removeAspasDuplas( $json )
    {
        $json = str_replace('"{', '{', $json);
        $json = rtrim($json, '"');

        // Remove o penúltimo aspas caso o data seja nulo "
        if ( $this->data != null )  {
            $length = strlen( $json );
            if ( $length >= 2 )
                 $json = substr( $json, 0, $length - 2 ) . substr( $json, -1 );
        }
        // echo $json;
        return $json;
    }
}
?>
