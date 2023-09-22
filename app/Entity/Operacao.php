<?php

namespace App\Entity;

use \App\Db\Database;
use \PDO;

/*$obj = array();
$op = new Operacao( $obj );
print_r( $op );*/

/*
Atualiza()
{ "dados":{
    "entidade":"operacao",
    "operacao":"atualizar",
    "objeto":{
      "id":"3",
      "nomeOperacao":"PulinKKKK",
      "data":"00-00-0012",
      "bairro":"Jaguarib CARNE",
      "kmIni":"1.0",
      "kmFim":"0.0",
      "matriculaResponsavel":"123123",
      "hraIni":"54.5",
      "hraFim":null,
      "municipio":null,
      "observacoes":"Insira alguma observação."
    } } }
*/

class Operacao
{
    public $id;
    public $nomeOperacao;
    public $data;
    public $bairro;
    public $municipio;
    public $kmIni;
    public $kmFim;
    public $matriculaResponsavel;
    public $observacao;

    private const TABLE_NAME = 'operacao';

    public function __construct( array $data = null )
    {
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
        $this->id = -1;
        $this->id = $obDatabase->insert( [
            'nomeOperacao' => $this->nomeOperacao,
            'data' => $this->data,
            'bairro' => $this->bairro,
            'municipio' => $this->municipio,
            'kmIni' => $this->kmIni,
            'kmFim' => $this->kmFim,
            'matriculaResponsavel' => $this->matriculaResponsavel,
            'observacao' => $this->observacao,
        ] );

        $array = [
          'id' =>  $this->id
        ];
        // if ( $this->id != -1 ) return ( new Response(0, "Successo!", "{ \"id\" : " . $this->id . "}" ) );
        if ( $this->id != -1 ) return ( new Response(0, "Successo!", $array ) );
        else return ( new Response( 99, "Erro: Usuario nao encontrado!", null ) );
    }

    public function atualiza(  array $data = null  )
    {
        if ( $data != null )
             $this->setData( $data );

        //echo " --------------- atualiza aqui (data) --------------- ";
        //print_r( $data );

        $resp = ( new Database( self::TABLE_NAME ) )->update('id = ' . $this->id, [
            'nomeOperacao' => $this->nomeOperacao,
            'data' => $this->data,
            'bairro' => $this->bairro,
            'municipio' => $this->municipio,
            'kmIni' => $this->kmIni,
            'kmFim' => $this->kmFim,
            'matriculaResponsavel' => $this->matriculaResponsavel,
            'observacao' => $this->observacao,
        ]);
        $array = [
          "id" => $this->id
        ];

        if (   $resp == 1 )
             return ( new Response(0, "Operacao atualizada com successo!", $array ) );
        else
            return ( new Response( 99, "Erro de atualização!", null ) );
    }

    public function deleta()
    {
        return ( new Database( self::TABLE_NAME ) )->delete( 'id = ' . $this->id );
    }

    public function getOperacaoById( $id )
    {
        $obj = ( new Database( self::TABLE_NAME ) )->select( 'id = ' . $id )->fetchObject( self::class );
        $dataJson    = null;

        if ( $obj )
        {
            $this->id = $obj->id;
            $this->nomeOperacao = $obj->nomeOperacao;
            $this->data = $obj->data;
            $this->bairro = $obj->bairro;
            $this->municipio = $obj->municipio;
            $this->kmIni = $obj->kmIni;
            $this->kmFim = $obj->kmFim;
            $this->matriculaResponsavel = $obj->matriculaResponsavel;
            $this->observacao = $obj->observacao;
            return ( new Response(0, "Successo!", $this->toJson() ) );
        }   else   {
            return ( new Response( 99, "Erro!", null ) );
        }
    }

    public function getOperacaoByData( $data )
    {
        $obj = ( new Database( self::TABLE_NAME ) )->select( "data = '" . $data . "'" )->fetchObject( self::class );
        $dataJson    = null;

        if ( $obj )
        {
            $this->id = $obj->id;
            $this->nomeOperacao = $obj->nomeOperacao;
            $this->data = $obj->data;
            $this->bairro = $obj->bairro;
            $this->municipio = $obj->municipio;
            $this->kmIni = $obj->kmIni;
            $this->kmFim = $obj->kmFim;
            $this->matriculaResponsavel = $obj->matriculaResponsavel;
            $this->observacao = $obj->observacao;
            return ( new Response(0, "Successo!", $this->toJson() ) );
        }   else   {
            return ( new Response( 99, "Erro!", null ) );
        }
    }

    public function consulta( $param )
    {
        $resp = null;
        $filteredArray = null;

        // 1- Busca dados Operação
        if ( isset( $param['data'] ) )      {
             $resp = $this->getOperacoes( "data = '" . $param['data'] . "'");
        }
        else if ( isset( $param['id'] ) )   {
            $resp = $this->getOperacoes( "id = '" . $param['id'] . "'");
        }
        else if ( isset( $param['matriculaResponsavel'] ) )   {
            $resp = $this->getOperacoes( "matriculaResponsavel = '" . $param['matriculaResponsavel'] . "'");
        }

        /* Realiza a filtragem dos dados a serem retornados */
        if ( isset( $param['filtro'] ) && ( $param['filtro'] ) )    {
             $originalArray = $resp;
             $filteredArray = array_map( function( $object ) {
                 return (object) [
                     'id'           => $object->id,
                     'nomeOperacao' => $object->nomeOperacao,
                     'data'         => $object->data,
                     'matriculaResponsavel' => $object->matriculaResponsavel,
                 ];
             }, $originalArray );
             $resp = $filteredArray;
        }



       if ( ConfigurationAPI::$EXECUTION_MODE == 'DEBUG')  {
             print_r( $filteredArray );
             echo 'Array de operações filtrado <br>';
             print_r( $resp );
        }
        if ( $resp != null )   {
              return ( new Response(0, "Successo!", $resp ) );
        }   else      {
              return ( new Response( 99, "Erro!", null ) );
        }
      }


    public static function getOperacoes( $where = null, $order = null, $limit = null )
    {
        return (new Database(self::TABLE_NAME))->select( $where, $order, $limit )
               ->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public function toJson() {
        $objectVars = get_object_vars($this);
        $json = json_encode($objectVars);
        return $json;
    }


}

?>
