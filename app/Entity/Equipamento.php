<?php

namespace App\Entity;

use \App\Db\Database;
use \PDO;

/*
{
"dados":{
  "entidade":"usoEquipamento",
  "operacao":"cadastrar",
  "objeto":{
      "id":null,
      "tamanho":"2023-06-19",
      "placa":"14:27",
      "nome":"123",
      "isAvariado":true,
      "numSerie":"123"
    }
    }
  }

  {
  "dados":{
    "entidade":"equipamento",
    "operacao":"consultar",
      }
    }
*/

class Equipamento
{
    public $id;
    public $numSerie;
    public $tipo;
    public $tamanho;
    public $placa;

    private $responseObj; /* Instance of a response Object */

    private const TABLE_NAME = 'equipamento';

    public function __construct(array $data = null) {
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


        $obDatabase = new Database(self::TABLE_NAME);
        $obDatabase->insert([
            'id' => $this->id,
            'numSerie' => $this->numSerie,
            'nome' => $this->tipo,
            'tamanho' => $this->tamanho,
            'placa' => $this->placa,
        ]);
        return true;
    }

    public function atualiza( array $data = null )
    {
        if ( $data != null )
          $this->setData( $data );

        return ( new Database(self::TABLE_NAME))->update('id = ' . $this->id, [
                'numSerie' => $this->numSerie,
                'nome' => $this->tipo,
                'tamanho' => $this->tamanho,
                'placa' => $this->placa,
        ]);
    }

    public function deleta()
    {
        return (new Database(self::TABLE_NAME))->delete('id = ' . $this->id);
    }

    public function getEquipamentoById( $id )
    {
        $obj = ( new Database(self::TABLE_NAME))->select('id = ' . $id )->fetchObject(self::class);

        $dataJson = null;

        if ( $obj )
        {
            $this->id = $obj->id;
            $this->numSerie = $obj->numSerie;
            $this->tipo = $obj->tipo;
            $this->tamanho = $obj->tamanho;
            $this->placa = $obj->placa;
            return ( new Response(0, "Successo!", $this->toJson() ) );
        }   else   {
            return ( new Response( 99, "Erro!", null ) );
        }
    }

    public function getEquipamento( $where )
    {
        $obj = ( new Database(self::TABLE_NAME))->select( $where )->fetchObject(self::class);

        $dataJson = null;

        if ( $obj )
        {
            $this->id = $obj->id;
            $this->numSerie = $obj->numSerie;
            $this->tipo = $obj->tipo;
            $this->tamanho = $obj->tamanho;
            $this->placa = $obj->placa;
            return ( new Response(0, "Successo!", $this->toJson() ) );
        }   else   {
            return ( new Response( 99, "Erro!", null ) );
        }
    }

    public function consulta( $param = null )
    {
        $array = $this->getEquipamentos();
        return ( new Response(0, "Successo!", $array ) );
    }

    public function getEquipamentos($where = null, $order = null, $limit = null)
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
