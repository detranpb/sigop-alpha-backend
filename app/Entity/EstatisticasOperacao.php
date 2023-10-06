<?php
namespace App\Entity;
use \App\Db\Database;
use \PDO;
/***
{
  "dados":  {
	"entidade":"estatisticasOperacao",
	"operacao":"cadastrar",
	"objeto":  {
		"abordagensCarros":160,
		"abordagensMotos":5,
		"abordagensCiclomotor":2,
		"testesBafometro":46,
		"remocoesCarros":56,
		"remocoesMotos":66,
		"remocoesCiclomotor":86,
		"remocoesOutros":60,
		"numInfracoes":0,
		"numInfracoesAlcoolizado":1,
		"recusasComEmbriaguez":0,
		"recusasSemEmbriaguez":0,
		"numAlcoolIntervaloUm":2,
		"numAlcoolIntervaloDois":3
} } }
***/

class EstatisticasOperacao
{
    public $idStat;
    public $abordagensCarros;
    public $abordagensMotos;
    public $abordagensCiclomotor;
    public $abordagensTestesBafometro;
    public $remocoesCarros;
    public $remocoesMotos;
    public $remocoesCiclomotor;
    public $remocoesOutros;
    public $numInfracoes;
    public $numRecusasComEmbriaguez;
    public $numRecusasSemEmbriaguez;
    public $numAlcoolIntervaloUm;
    public $numAlcoolIntervaloDois;
    public $idOperacao;

    private $responseObj; /** Instance of a response Object **/
    private const TABLE_NAME = 'estatisticas_operacao';

    public function __construct( array $data = null ) {
        if ( $data != null )
             $this->setData( $data );
    }
    public function setData( array $data )            {
        // echo " ---- SET DATA ---- ";
        // print_r( $data );
        foreach( $data as $key => $value )
                 $this->$key = $value;

    }
    public function cadastra( array $data = null )
    {
      /* echo "-VAMOS CADASTRAR ?!?!";
      print_r( $data );*/

      if ( $data != null )
           $this->setData( $data );

           $obDatabase = new Database(self::TABLE_NAME);
           $obDatabase->insert([
              'idStat' => $this->idStat,
              'abordagensCarros' => $this->abordagensCarros,
              'abordagensMotos' => $this->abordagensMotos,
              'abordagensCiclomotor' => $this->abordagensCiclomotor,
              'abordagensTestesBafometro' => $this->abordagensTestesBafometro,
              'remocoesCarros' => $this->remocoesCarros,
              'remocoesMotos' => $this->remocoesMotos,
              'remocoesCiclomotor' => $this->remocoesCiclomotor,
              'remocoesOutros' => $this->remocoesOutros,
              'numInfracoes' => $this->numInfracoes,
              'numRecusasComEmbriaguez' => $this->numRecusasComEmbriaguez,
              'numRecusasSemEmbriaguez' => $this->numRecusasSemEmbriaguez,
              'numAlcoolIntervaloUm' => $this->numAlcoolIntervaloUm,
              'numAlcoolIntervaloDois' => $this->numAlcoolIntervaloDois,
              'idOperacao' => $this->idOperacao
            ]);
      return ( new Response(0, "Estatisticas salvas com sucesso!", 0 ) );
    }

    public function atualiza( array $data = null )
    {
        if ( $data != null )
          $this->setData( $data );

        return ( new Database(self::TABLE_NAME))->update(
                'idStat' . $this->idStat, [
                  'abordagensCarros' => $this->abordagensCarros,
                  'abordagensMotos' => $this->abordagensMotos,
                  'abordagensCiclomotor' => $this->abordagensCiclomotor,
                  'abordagensTestesBafometro' => $this->abordagensTestesBafometro,
                  'remocoesCarros' => $this->remocoesCarros,
                  'remocoesMotos' => $this->remocoesMotos,
                  'remocoesCiclomotor' => $this->remocoesCiclomotor,
                  'remocoesOutros' => $this->remocoesOutros,
                  'numInfracoes' => $this->numInfracoes,
                  'numRecusasComEmbriaguez' => $this->numRecusasComEmbriaguez,
                  'numRecusasSemEmbriaguez' => $this->numRecusasSemEmbriaguez,
                  'numAlcoolIntervaloUm' => $this->numAlcoolIntervaloUm,
                  'numAlcoolIntervaloDois' => $this->numAlcoolIntervaloDois,
                  'idOperacao' => $this->idOperacao
        ]);
    }
    public function deleta()      {
        return (new Database(self::TABLE_NAME))->delete('id = ' . $this->id);
    }

    public function getEquipamentoById( $id )
    {
        $obj = ( new Database( self::TABLE_NAME ) )->select('id = ' . $id )->fetchObject( self::class );
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
