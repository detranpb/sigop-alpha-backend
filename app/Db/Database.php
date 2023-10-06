<?php

namespace App\Db;

use \PDO;
use \PDOException;
use App\Entity\ConfigurationAPI;

class Database{

  /**
   * Host de conexão com o banco de dados
   * @var string
   */
  const HOST = 'localhost';

  /**
   * Nome do banco de dados
   * @var string
   */
  const NAME = 'detran';

  /**
   * Usuário do banco
   * @var string
   */
  const USER = 'paulo';

  /**
   * Senha de acesso ao banco de dados
   * @var string
   */
  const PASS = 'paulo';

  /**
   * Nome da tabela a ser manipulada
   * @var string
   */
  private $table;

  /**
   * Instancia de conexão com o banco de dados
   * @var PDO
   */
  private $connection;


  private $IS_DB_ERROR = false;
  private $isUserCadastro;

  // ############################## TODO: Tratar os erros do SQL NESTA CLASSE P/ RETORNAR MAIS AMIGÁVEL ########################

  /**
   * Define a tabela e instancia e conexão
   * @param string $table
   */
  public function __construct( $table = null , $isUserCadastro = false ) {
      $this->table = $table;
      $this->isUserCadastro = $isUserCadastro;
      $this->setConnection();
  }

  /**
   * Método responsável por criar uma conexão com o banco de dados
   */
  private function setConnection()    {
      try {
           $this->connection = new PDO('mysql:host='.self::HOST.';dbname='.self::NAME,self::USER,self::PASS);
           $this->connection->setAttribute( PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
      } catch ( PDOException $e ) {
          die('ERROR: '.$e->getMessage());
      }
  }

  /**
   * Método responsável por executar queries dentro do banco de dados
   * @param  string $query
   * @param  array  $params
   * @return PDOStatement
   */
  public function execute($query,$params = [])
  {
      try {
           //if ( ConfigurationAPI::$EXECUTION_MODE == 'DEBsUG')  {
                
          //}
          $statement = $this->connection->prepare($query);
          /*echo "--------- DB: execute ----------";
          echo '-- SQL Statement';
          print_r( $statement );
          echo '-- Valores';
          print_r( $params );*/

          $statement->execute( $params );
          return $statement;
      }
      catch(  PDOException $e )
      {
        // ############################## TODO: Tratar os erros do SQL NESTA CLASSE P/ RETORNAR MAIS AMIGÁVEL ########################
        // Tratamento diferente para o CASO de USUÁRIO
        if ( $this->isUserCadastro )  {
             $this->IS_DB_ERROR = true;
             return $e->getMessage();
        }
        die('ERROR: '. $e->getMessage() );
      }
  }

  /**
   * Método responsável por inserir dados no banco
   * @param  array $values [ field => value ]
   * @return integer ID inserido
   */
  public function insert( $values )   {
    //DADOS DA QUERY

    $fields = array_keys($values);
    $binds  = array_pad([],count($fields),'?');

    //MONTA A QUERY
    $query = 'INSERT INTO ' . $this->table . '(' . implode( ',' , $fields ) . ') VALUES (' . implode(',' , $binds) . ')';

    $sqlReturn = $this->execute( $query,array_values( $values ) );

    // RELANÇA A EXCEÇÃO !!!!!!
    if ( $this->IS_DB_ERROR )
         return $sqlReturn;

    //RETORNA O ID INSERIDO
    return $this->connection->lastInsertId();
  }

  /**
   * Método responsável por executar uma consulta no banco
   * @param  string $where
   * @param  string $order
   * @param  string $limit
   * @param  string $fields
   * @return PDOStatement
   */
  public function select( $where = null, $order = null, $limit = null, $fields = '*' )    {

    //DADOS DA QUERY
    $where = strlen($where) ? 'WHERE '   .$where : '';
    $order = strlen($order) ? 'ORDER BY '.$order : '';
    $limit = strlen($limit) ? 'LIMIT '   .$limit : '';
    //MONTA A QUERY
    $query = 'SELECT '.$fields.' FROM '.$this->table.' '.$where.' '.$order.' '. $limit;

    // echo "QUERY = " . $query;
    //EXECUTA A QUERY
    return $this->execute( $query );
  }

  public function selectJOIN($where = null, $order = null, $limit = null, $fields = '*', $joins = null) {
      // DADOS DA QUERY
      $where = strlen($where) ? 'WHERE '.$where : '';
      $order = strlen($order) ? 'ORDER BY '.$order : '';
      $limit = strlen($limit) ? 'LIMIT '.$limit : '';

      // MONTA A QUERY
      $query = 'SELECT '.$fields.' FROM '.$this->table;


      // ADICIONA OS JOINS, SE HOUVEREM
      if (!empty($joins)) {
          $query .= ' '.$joins;
      }

      $query .= ' '.$where.' '.$order.' '.$limit;
    //  echo 'select query ==>> ' . $query . '<br>';

      // EXECUTA A QUERY
      return $this->execute( $query );
  }

  /**
   * Método responsável por executar atualizações no banco de dados
   * @param  string $where
   * @param  array $values [ field => value ]
   * @return boolean
   */
  public function update( $where, $values )
  {
    // ---- DADOS DA QUERY ----
    $fields = array_keys( $values );
    /*echo " ----------- fields ----------- ";
    print_r( $fields );*/


    // MONTA A QUERY
    $query = 'UPDATE '.$this->table.' SET '.implode('=?,',$fields).'=? WHERE '.$where;

    // echo "Query ==>> " . $query;

    //EXECUTAR A QUERY
    $this->execute($query,array_values($values));

    //RETORNA SUCESSO
    return true;
  }

  /**
   * Método responsável por excluir dados do banco
   * @param  string $where
   * @return boolean
   */
  public function delete($where){
    //MONTA A QUERY
    $query = 'DELETE FROM '.$this->table.' WHERE '.$where;

    //EXECUTA A QUERY
    $this->execute($query);

    //RETORNA SUCESSO
    return true;
  }

}
