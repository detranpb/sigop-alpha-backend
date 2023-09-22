<?php

namespace App\Entity;

use \App\Db\Database;
use \App\Entity\Operacao;
use \App\Entity\UsoEquipamento;
use \PDO;

/*** *** *** {
"dados":{
    "entidade":"usoEquipamento",
    "operacao":"cadastrar",
    "objeto":{  "data":"12-12-2020" }
    }
} *** *** ***/

/*
'{
"code": "0",
"message": "Sucesso!",
"data": {
  "dadosOperacao": {
    "id":"1",
    "nomeOperacao":"operacao 1",
    "data":"2023-01-01",
    "bairro":"Jaguaribe",
    "municipio":"Joao Pessoa",
    "kmIni":"45.9",
    "kmFim":"55.9",
    "matriculaResponsavel":"123",
    "observacao":"observacao de teste"
  },
  "lista": [
      {
        'idOperacao': '1',
        'matriculaAgente' : '41416',
        'idEquipamento' : '3',
        'dataDevolucao' : '2023-06-20',
        'horaDevolucao' : '16:03',
        'isAvariado' : 1
      },
      {
        'idOperacao': '1',
        'matriculaAgente' : '41416',
        'idEquipamento' : '3',
        'dataDevolucao' : '2023-06-20',
        'horaDevolucao' : '16:03',
        'isAvariado' : 1
      }
  ]
}
}'

{
  "code":"0",
  "message":"Sucesso!",
  "data":
  {
    "dadosOperacao":  {
      "id":"1","nomeOperacao":"operacao 1","data":"2023-01-01","bairro":"Jaguaribe","municipio":"Jo\u00e3o
Pessoa","kmIni":"45.9","kmFim":"55.9","matriculaResponsavel":"123","observacao":"observacao de teste" },
    "lista": [
      {"idOperacao":"1","matriculaAgente":"123","idEquipamento":"123","dataDevolucao":"2023-01-01","horaDevolucao":"14:27:00","isAvariado":"1"},
      {"idOperacao":"1","matriculaAgente":"41416","idEquipamento":"1","dataDevolucao":"2023-06-19","horaDevolucao":"14:28:00","isAvariado":"1"},
      {"idOperacao":"1","matriculaAgente":"41416","idEquipamento":"2","dataDevolucao":"2023-06-20","horaDevolucao":"16:03:00","isAvariado":"1"},
      {"idOperacao":"1","matriculaAgente":"41416","idEquipamento":"3","dataDevolucao":"2023-06-20","horaDevolucao":"16:03:00","isAvariado":"1"}
    ] } }
***/
class OperacaoDetalhada
{
    private const TABLE_NAME_I  = 'operacao';
    private const TABLE_NAME_II = 'uso_equipamento';

    public function valida()
    {
        // 1- Existe Operacao ?
        $op   = new Operacao;
        $resp = $op->getOperacaoById( $this->idOperacao );
        if ( $resp->code != 0 )
             return new Response( 99, "Operacao inexistente (" . $this->idOperacao . ")", null );

        // 2- Existe sse usuário ?
        $agente = new Agente;
        $resp   = $agente->getAgenteByMatricula( $this->matriculaAgente );
        if ( $resp->code != 0 )
             return new Response( 99, "Agente inexistente ( Matricula = " . $this->matriculaAgente . ")" , null );

        // 3- Existe esse equipamento ?
        $equip = new Equipamento;
        $resp  = $equip->getEquipamentoById( $this->idEquipamento );
        if ( $resp->code != 0 )
             return new Response( 99, "Equipamento inexistente ( ID = " . $this->idEquipamento . ")" , null );

        // 4- Já existe usoEquipamento p/ tupla ( usuário + equipamento ) ?
        $uso = $this->validaUsoEquipamentos(" matriculaAgente = '" . $this->matriculaAgente . "' and idOperacao = '" . $this->idOperacao .
                                          "' and idEquipamento = '"  . $this->idEquipamento . "'");
        // print_r( $uso );
        if  ( $uso != null )
              return new Response( 99, "Equipamento/agente ja cadastrados para esta operacao.", null );

        return new Response( 0, "Sucesso!", null );
    }

    public function consulta( $param )
    {
        $op = new Operacao;
        $resp = null;

        // 1- Busca dados Operação
        if ( isset( $param['data'] ) )  /* Busca operação por data ou */
        {
             // echo 'Data = ' . $param['data'];
             $resp = $op->getOperacaoByData( $param['data'] );
        } else if ( isset( $param['id'] ) ) {
             $resp = $op->getOperacaoById( $param['id'] );
        }
        $operacao = $resp->data;

        if ( $resp->code != 0 ) {
             return new Response( 99, "Operacao inexistente para esta data.", null );
        }
        // 2- Busca ocorrências de UsoEquipamento
        $opData = json_decode( $resp->data, true);
        $uso = new UsoEquipamento;
        $listaEquipamentos = $uso->getUsoEquipamentos( "idOperacao = '" . $opData['id'] . "'");
        $numElems = count( $listaEquipamentos );

        $data = [
                 "dadosOperacao" => json_decode( $operacao ), // Convert JSON back to PHP object
                 "lista" => $listaEquipamentos
        ];
        // Create the final associative array
        $result = [
                "code" => "0",
                "message" => "Sucesso!",
                "data" => $data
        ];
        // $jsonData = json_encode( $result );
        return $result;
    }

    public function getUsoEquipamentos( $where = null, $order = null, $limit = null )       {
        return ( new Database( self::TABLE_NAME ) )->select( $where, $order, $limit )
                                                  ->fetchAll( PDO::FETCH_CLASS, self::class );
    }

    public static function getUsoEquipamentoByIdOperacao($idOperacao)
    {
        return (new Database(self::TABLE_NAME))->select( 'idOperacao = ' . $idOperacao )
            ->fetchObject(self::class);
    }

    public function toJson() {
        $objectVars = get_object_vars($this);
        $json = json_encode($objectVars);
        return $json;
    }

}
?>
