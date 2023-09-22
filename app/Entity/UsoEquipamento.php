<?php

namespace App\Entity;

use \App\Db\Database;
use \App\Entity\Operacao;
use \App\Entity\Equipamento;
use \App\Entity\Agente;
use \PDO;

/*
{
"dados":{
  "entidade":"usoEquipamento",
  "operacao":"cadastrar",
  "objeto":{
      "idOperacao":null,
      "dataDevolucao":"2023-06-19",
      "horaDevolucao":"14:27",
      "idEquipamento":"123",
      "isAvariado":true,
      "matriculaAgente":"123"
    }
    }
  }


{
  "dados":{
    "entidade":"usoEquipamento",
    "operacao":"cadastrar",
    "objeto":{
        "idOperacao":null,
        "dataDevolucao":"2023-06-19",
        "horaDevolucao":"14:27",
        "idEquipamento":"123",
        "isAvariado":true,
        "matriculaAgente":"123"
    }
  }
}
{"dados":{"entidade":"operacao","operacao":"atualizar","objeto":
{
  "idOperacao":290,
  "matriculaAgente":"123"
  "idEquipamento":"123",
  "dataDevolucao":"2023-06-19",
  "horaDevolucao":"14:27",
  "isAvariado":true,
  "tipoEquipamento":"VIATURA"
  "kmIni":"0",
  "kmFim":"0"
}}}
*/

class UsoEquipamento
{
    public $idOperacao;
    public $matriculaAgente;
    public $idEquipamento;
    public $dataDevolucao;
    public $horaDevolucao;
    public $isAvariado;
    public $tipoEquipamento;
    public $kmIni;
    public $kmFim;

    /* private $responseObj;  Instance of a response Object */

    private const TABLE_NAME = 'uso_equipamento';

    public function __construct(array $data = null) {
        if ( $data != null )
            $this->setData( $data );
    }

    public function setData( array $data )  {
        foreach ($data as $key => $value)
            $this->$key = $value;
    }

    public function valida( $metodoChamador )    {

        $isPlaca = false;

        if ( preg_match('/\D/', $this->idEquipamento ))
             $isPlaca = true;

        //echo "isPlaca? " . $isPlaca;
        // 1- Existe Operacao ?
        $op = new Operacao;
        $resp = $op->getOperacaoById( $this->idOperacao );
        if ( $resp->code != 0 )
             return new Response( 99, "Operacao inexistente (" . $this->idOperacao . ")", null );

        // 2- Existe sse usuário ?
        $agente = new Agente;
        $resp = $agente->getAgenteByMatricula( $this->matriculaAgente );
        if ( $resp->code != 0 )
             return new Response( 99, "Agente inexistente ( Matricula = " . $this->matriculaAgente . ")" , null );

        // 3- Existe esse equipamento ?
        $equip = new Equipamento;
        if ( $isPlaca )  {
             $resp = $equip->getEquipamento( "placa = '" . $this->idEquipamento . "'" );
        } else {
             $resp = $equip->getEquipamentoById( $this->idEquipamento );
        }
        if ( $resp->code != 0 )
             return new Response( 99, "Equipamento inexistente ( ID = " . $this->idEquipamento . ")" , null );

        // 4- Já existe usoEquipamento p/ tupla ( usuário + equipamento ) ?
        $whereClause = "idOperacao = '" . $this->idOperacao .
                       "' and matriculaAgente = '" . $this->matriculaAgente .
                       "' and idEquipamento = '" . $this->idEquipamento . "'";

        $uso = $this->usoEquipamentoExistente( $whereClause );
        if ( ( $metodoChamador == "cadastra" ) && ( $uso != null ) )
               return new Response( 99, "Uso de equipamento por agente ja cadastrado para esta operacao.", null );

        if ( ( $metodoChamador == "atualiza" ) && ( $uso == null ) )
               return new Response( 99, "Uso de equipamento por Agente nao cadastrado para esta operacao.", null );

        return new Response( 0, "Sucesso!", null );
    }

    public function cadastra( array $data = null )
    {
       if ( $data != null )
           $this->setData( $data );

        $response = $this->valida("cadastra") ;

        if ( $response->code != 0 ) // CASO INSUCESSO DE VALIDAÇÃO
             return $response;

        $obDatabase = new Database(self::TABLE_NAME);
        $obDatabase->insert( [
            'idOperacao' => $this->idOperacao,
            'matriculaAgente' => $this->matriculaAgente,
            'idEquipamento' => $this->idEquipamento,
            'dataDevolucao' => $this->dataDevolucao,
            'horaDevolucao' => $this->horaDevolucao,
            'isAvariado' => $this->isAvariado,
            'tipoEquipamento' => $this->tipoEquipamento,
            'kmIni' => $this->kmIni,
            'kmFim' => $this->kmFim,
        ] );
        return ( new Response(0, "Cadastro realizado com sucesso !", null ) );
    }

    public function atualiza( array $data = null )
    {
           if ( $data != null )
                $this->setData( $data );

           $response = $this->valida("atualiza") ;
           if ( $response->code != 0 ) // CASO INSUCESSO DE VALIDAÇÃO
                return $response;

        $whereClause = "idOperacao = '" . $this->idOperacao .
                       "' and matriculaAgente = '" . $this->matriculaAgente .
                       "' and idEquipamento = '" . $this->idEquipamento . "'";

          // Atualiza where 'idOperacao = ' . $this->idOperacao
        $resp = (new Database(self::TABLE_NAME))->update( $whereClause, [
            'matriculaAgente' => $this->matriculaAgente,
            'idEquipamento' => $this->idEquipamento,
            'dataDevolucao' => $this->dataDevolucao,
            'horaDevolucao' => $this->horaDevolucao,
            'isAvariado' => $this->isAvariado,
            'tipoEquipamento' => $this->tipoEquipamento,
            'kmIni' => $this->kmIni,
            'kmFim' => $this->kmFim,
        ]);
        // $this->deleta();

        if ( $resp == 1 )
             return ( new Response(0, "Uso equipamento atualizado com successo!", "" ) );
        else
            return ( new Response( 99, "Erro de atualização!", null ) );
    }

    public function deleta()
    {
        /*echo "-idOp  ==>> " . $this->idOperacao;
        echo "-idEq  ==>> " . $this->idEquipamento;
        echo "-matri ==>> " . $this->matriculaAgente;*/

        return (new Database(self::TABLE_NAME))->delete("'idOperacao' = '" . $this->idOperacao .
                                                        "' and idEquipamento = '"  .  $this->idEquipamento .
                                                        "' and matriculaAgente = '" . $this->matriculaAgente .
                                                        "' and isAvariado = 'true' " );
    }

    private function usoEquipamentoExistente( $where = null )
    {
        return ( new Database(self::TABLE_NAME))->select( $where )->fetchObject( self::class );
    }

    public function selectByIdOperacao($idOperacao)
    {
        $obj = ( new Database(self::TABLE_NAME))->select('idOperacao = ' . $idOperacao )->fetchObject(self::class);

        $dataJson = null;

        if ( $obj )
        {
            $this->idOperacao = $obj->idOperacao;
            $this->matriculaAgente = $obj->matriculaAgente;
            $this->idEquipamento = $obj->idEquipamento;
            $this->dataDevolucao = $obj->dataDevolucao;
            $this->horaDevolucao = $obj->horaDevolucao;
            $this->isAvariado = $obj->isAvariado;
            $this->tipoEquipamento = $obj->tipoEquipamento;
            $this->kmIni = $obj->kmIni;
            $this->kmFim = $obj->kmFim;
            $dataJson = $this->toJson();
        }   else    {
            /* In case not founding data */
            $dataJson = json_encode( ['error' => 'Object not found in database'] );
        }
        return ( new Response( 0, "Success!", $dataJson ) );
    }

    public function getUsoEquipamentos($where = null, $order = null, $limit = null)
    {
        return (new Database(self::TABLE_NAME))->select($where, $order, $limit)
            ->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function getUsoEquipamentoByIdOperacao($idOperacao)
    {
        return (new Database(self::TABLE_NAME))->select('idOperacao = ' . $idOperacao)
            ->fetchObject(self::class);
    }

    public function toJson()
    {
        $objectVars = get_object_vars($this);
        $json = json_encode($objectVars);
        return $json;
    }

}
?>
