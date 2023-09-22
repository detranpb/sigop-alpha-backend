<?php

namespace App\Entity;

use \App\Db\Database;
use \PDO;

/*$obj = array();
$op = new Operacao( $obj );
print_r( $op );*/

/*
Atualiza()

{ "dados":
  {
    "entidade":"operacao",
    "operacao":"atualizar",
    "objeto":{
      "id":"3",
      "nome":"PulinKKKK",
      "matricula":"00-00-0012",
      "cpf":"Jaguarib CARNE",
      "kmIni":"1.0",
      "kmFim":"0.0",
      "matriculaResponsavel":"123123",
      "hraIni":"54.5",
      "hraFim":null,
      "municipio":null,
      "observacoes":"Insira alguma observação."
    } } } */

class Agente
{
    public $id;
    public $nome;
    public $matricula;
    public $cpf;

    private $responseObj; /* Instance of a response Object */

    private const TABLE_NAME = 'agente';

    public function __construct( array $data = null ) {
           if ( $data != null )
               $this->setData( $data );
    }

    public function setData( array $data )  {
        foreach ($data as $key => $value)
            $this->$key = $value;
    }

    public function cadastra( array $data = null )
    {
        if ( $data != null )
             $this->setData( $data );

        $obDatabase = new Database( self::TABLE_NAME );
        $this->id = $obDatabase->insert([
            'nome' => $this->nome,
            'matricula' => $this->matricula,
            'cpf' => $this->cpf,
        ]);
        return true;
    }

    public function atualiza(  array $data = null  )
    {
        if ( $data != null )
             $this->setmatricula( $data );

        return ( new Database( self::TABLE_NAME ) )->update('id = ' . $this->id, [
            'nome' => $this->nome,
            'matricula' => $this->matricula,
            'cpf' => $this->cpf,
        ] );
    }

    public function deleta()
    {
        return (new Database(self::TABLE_NAME))->delete('id = ' . $this->id);
    }

    public function getAgenteById( $id )
    {
        $obj = (new Database(self::TABLE_NAME))->select('id = ' . $id)->fetchObject( self::class );
        $dataJson = null;
        if ( $obj )
        {
            $this->id = $obj->id;
            $this->nome = $obj->nome;
            $this->matricula = $obj->matricula;
            $this->cpf = $obj->cpf;
            return ( new Response(0, "Successo!", $this->toJson() ) );
        }   else   {
            return ( new Response( 99, "Erro!", null ) );
        }
    }

    public function getAgenteByMatricula( $matricula )
    {
        $obj = (new Database(self::TABLE_NAME))->select('matricula = ' . $matricula )->fetchObject( self::class );

        $dataJson = null;
        if ( $obj )
        {
            $this->id = $obj->id;
            $this->nome = $obj->nome;
            $this->matricula = $obj->matricula;
            $this->cpf = $obj->cpf;
            return ( new Response(0, "Successo!", $this->toJson() ) );
        }   else   {
            return ( new Response( 99, "Erro!", null ) );
        }
    }

    public function getListaMatriculasNomes()   {

        $output = []; // Create an empty array to store the filtered attributes
        $array = $this->getAgentes();

        foreach ($array as $object) {

            $object->nome = str_replace("\n", "", $object->nome);
            $filteredData = [
                'nome' => $object->nome,
                'matricula' => $object->matricula
            ];
            $output[] = $filteredData; // Add the filtered attributes to the output array
        }
        /**for( $i=0; $i < count($array); $i++ )  {
             echo $output[ $i ]['nome'] . '<br>';
        }**/
        
        return ( new Response(0, "Successo!", $output ) );
    }


    public function getAgentes( $where = null, $order = null, $limit = null )
    {
        return (new Database(self::TABLE_NAME))->select($where, $order, $limit)
            ->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public function toJson() {
        $objectVars = get_object_vars($this);
        $json = json_encode($objectVars);
        return $json;
    }


}

?>
