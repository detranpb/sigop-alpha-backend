<?php

namespace App\Entity;

use \App\Db\Database;
use \PDO;

class Usuario    {

  public $id;
  public $nome;
  public $cpf;
  public $matricula;
  public $email;
  public $senha;
  public $isSenhaModificada;

  private const TABLE_NAME = 'usuario';

/* JSONIN = {"dados":{"entidade":"usuario","operacao":"login","objeto":{"matricula":"123","cpf":"123"}}} */
/* JSONIN = {"dados":{"entidade":"usuario","operacao":"cadastro","objeto":
  {
    "nome":"PAULO PAIVA",
    "matricula":"123",
    "senha":"senha",
    "cpf":"07066322495",
    "email":"paulo@gmail.com",
    "senha":"senha123"
    }}} */

  public function __construct( array $data = null )
  {
      if ( $data )  {
          foreach ( $data as $key => $value )
              $this->$key = $value;
      }
  }

  public function setData( array $data )  {
      foreach ( $data as $key => $value )
          $this->$key = $value;
  }

  public function cadastra( array $data = null )
  {
    if ( $data != null )
         $this->setData( $data );

    $this->senha = $this->cifraSenha( $this->senha );

    //echo "-ANTESS INSERT";
    $isCadastroUsuario = true;
    $obDatabase = new Database( self::TABLE_NAME , $isCadastroUsuario );
    $this->id  = $obDatabase->insert( [ 'id'    => $this->id,
                                        'nome'  => $this->nome,
                                        'cpf'   => $this->cpf,
                                        'matricula'  => $this->matricula,
                                        'email'  => $this->email,
                                        'isSenhaModificada' => 1,
                                        'senha' => $this->senha ] );

    if ( is_numeric( $this->id ) == false )
    {
         if ( strpos($this->id, "nome") !== false )
              return ( new Response( 99, "Nome de usuario ja existente!", null ) );
         if ( strpos($this->id, "matricula") !== false )
              return ( new Response( 99, "Matricula de usuario ja existente!", null ) );
         if ( strpos($this->id, "cpf") !== false )
              return ( new Response( 99, "CPF de usuario ja existente!", null ) );
         if ( strpos($this->id, "senha") !== false )
              return ( new Response( 99, "Senha de usuario existente!", null ) );
    }    else   {
         $array = [ 'id' =>  $this->id ];
         if ( is_numeric( $this->id ) ) {
              /* $resp = new Response(0, "Novo acesso gerado com successo!", $array );
              print_r( $resp );*/
              return ( new Response(0, "Novo acesso gerado com successo!", $array ) );
        }
    }


    /*// if ( $this->id != -1 )
    if ( $this->id !=  ) return ( new Response(0, "Successo!", $array ) );
    else */

    return true;
  }

  public function novaSenha()
  {
    $obDatabase = new Database( self::TABLE_NAME );
    $this->id = $obDatabase->insert([
                                      'id'    => $this->id,
                                      'matricula'  => $this->matricula,
                                      'senha' => $this->senha
                                    ]);
    return true;
  }

  public function atualiza()
  {
    return (new Database( self::TABLE_NAME ))->update('id = '.$this->id,[
                                                      'name' => $this->name,
                                                      'password'     => $this->password
                                                      ]);
  }

  public function deleta()
  {
    return (new Database( self::TABLE_NAME ))->delete('id = '.$this->id);
  }

  public function consulta( $query )    {

      $obj = ( new Database( self::TABLE_NAME ) )->select( $query )->fetchObject(self::class);

      $dataJson = null;

      if ( $obj )
      {
           $this->id = $obj->id;
           $this->nome = $obj->nome;
           $this->cpf = $obj->cpf;
           $this->matricula = $obj->matricula;
           $this->senha = $obj->senha;
           $this->isSenhaModificada = $obj->isSenhaModificada;
           $this->email = $obj->email;
           return ( new Response(0, "Successo!", $this->toJson("Usuario") ) );
      }   else   {
          return ( new Response( 99, "Erro: Usuario nao encontrado!", null ) );
      }
  }

  public function login( $matricula, $senha )
  {

      if ( ( $matricula == null ) || ( $senha == null ) )
      {
          return ( new Response( 99, "Erro: Matricula ou senha nulo(s) !! ", null ) );

      }   else   {
          $matriculaRegex = '/^\d{5}$/';
          $cpfRegex       = '/^\d{11}$/';  // Validação de CPF

          /*if (!preg_match( $matriculaRegex, $matricula) ) return ( new Response( 99, "Erro: Matricula invalida!", null ) );
            if (!preg_match($cpfRegex, $cpf)) return ( new Response( 99, "Erro: Senha invalida!", null ) );*/
          $busca = $this->consulta( 'matricula = ' . $matricula );

        //  echo 'xxxx PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP';
        //  print_r(  $busca  );

          if ( isset( $busca ) )
          {
               // print_r( $busca->data );
               $storedHashBD = json_decode( $busca->data )->senha;
               if ( $this->decifraSenha( $senha , $storedHashBD ) )
               {
                   //echo "---->>>> SENHA OK !!!!";
                   // return $busca;
                   $this->senha = 'null';
                   //echo gettype( $this );
                   return ( new Response( 0, "Sucesso!",  json_encode( $this ) ) );
               }   else   {
                   return ( new Response( 99, "Erro: Senha invalida!", null ) );
               }
          }
      }
      // return $this->consulta( 'matricula = ' . $matricula . ' and cpf = ' . $senha );
  }

  public function cifraSenha( $senhaPlana )
  {
      $hash = password_hash( $senhaPlana, PASSWORD_DEFAULT );
      //echo " ========== cifraSenha ========== ";
      //echo $senhaPlana . "|| Hash = " . $hash;
      return $hash;
  }

  /* $senhaPlanaByUser - Input do usuário */
  public function decifraSenha( $senhaPlanaByUser , $storedHashBD )
  {
      // $storedHashBD =
      //echo " ========== decifraSenha ========== ";
      //echo $senhaPlanaByUser . "|| Hash = " . $storedHashBD;

      // Verify the hash against the password entered
      return password_verify( $senhaPlanaByUser, $storedHashBD ); // true- caso sucesso | false
  }

  public function toJson()
  {
      $objectVars = get_object_vars( $this );
      $json = json_encode( $objectVars );
      return $json;
  }

  /* This function Returns a list of Vaga instances from Database*/
  public static function getUsuarios( $where = null, $order = null, $limit = null )
  {
    return (new Database( self::TABLE_NAME ))->select( $where, $order, $limit )
                                             ->fetchAll( PDO::FETCH_CLASS,self::class );
  }

}
