<?php
//Classe responsável pela conexão e consultas com o CRM Dynamics 365
//Escrita por Tadeu Mansi

class D365
{
    //atributos de configuracao
    private $_client_id;
    private $_user;
    private $_password;
    private $_secret;
    private $_crm_org;
    private $_crm_api;

    //atributos do token do usuario
    public $_access_token;
    public $_refresh_token;
    public $_expires_in;
    public $_expires_on;


    //contrutor setando as configuracoes
    function __construct($client_id,$secret,$user,$password,$crm_org)
    {
        $this->_client_id = $client_id;
        $this->_user = $user;
        $this->_password = $password;
        $this->_secret = $secret;
        $this->_crm_org = $crm_org;
        $this->_crm_api = $crm_org . "/api/data/v9.1/";
        $this->setToken();
    }

    private function requestPost($url, array $body,$json,$auth)
    {

        //mandando a requisicoo
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);


        //se o request vai enviar o body em json e precisa de autenticacao
        if($json && $auth)
        {
            //formatando para json
            $postString = json_encode($body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
                'Authorization: Bearer '.$this->_access_token.'' ));
        }
        //se o request vai enviar o body em json
        elseif($json){
            //formatando para json
            $postString = json_encode($body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        }
        //precisa de autenticacao
        elseif($auth)
        {
            //formatando para data string
            $postString = http_build_query($body, '', '&');
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer '.$this->_access_token.'' ));
        }
        else
        {
            //formatando para data string
            $postString = http_build_query($body, '', '&');
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //pegar resposta
        $response = curl_exec($ch);
        $obj = json_decode($response);
        //termina a execução caso de algum erro
        if (curl_errno($ch))
        {
            return false;
            curl_close($ch);
        }
        else
        {
            //setando os atributos
            curl_close($ch);
            return $obj;
        }
    }
    //helper para requests get
    private function requestGet($url,$auth)
    {
        //mandando a requisição
        $ch = curl_init($url);
        //se precisa de autenticacao
        if($auth){
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer '.$this->_access_token.'' ));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //pegar resposta
        $response = curl_exec($ch);
        $obj = json_decode($response);
        //termina a execução caso de algum erro
        if (curl_errno($ch))
        {
            return false;
            curl_close($ch);
        }
        else
        {
            //setando os atributos
            curl_close($ch);
            return $obj;
        }
    }
    //atualiza o token de acesso do usuario
    public function refreshToken()
    {
        //Essa função é responsável por atualizar o token de acesso para a api do dynamics
        //endpoint e corpo da requisição
        $url = "https://login.microsoftonline.com/common/oauth2/token";
        $body = array(
            "refresh_token" => $this->_refresh_token,
            "client_id" => $this->_client_id,
            "grant_type" => "refresh_token",
            "resource" => "https://graph.microsoft.com/",
            "client_secret" => $this->_secret
        );
        $obj = $this->requestPost($url, $body, false,false);
        //setando os atributos
        if($obj)
        {
            $this->_access_token = $obj->access_token;
            $this->_refresh_token = $obj->refresh_token;
            $this->_expires_in = $obj->expires_in;
            $this->_expires_on = $obj->expires_on;
        }

    }
    //seta o token de acesso do usuario
    private function setToken()
    {
        //Essa função é responsável por adquirir o token de acesso para a api do dynamics
        //endpoint e corpo da requisição
        $url = "https://login.microsoftonline.com/common/oauth2/token";

        $body = array(
            "client_id" => $this->_client_id,
            "grant_type" => "password",
            "resource" => $this->_crm_org,
            "username" => $this->_user,
            "password" => $this->_password,
            "client_secret" => $this->_secret
        );
        $obj = $this->requestPost($url, $body,false,false);
        if($obj)
        {
            //setando os atributos
            $this->_access_token = $obj->access_token;
            $this->_refresh_token = $obj->refresh_token;
            $this->_expires_in = $obj->expires_in;
            $this->_expires_on = $obj->expires_on;
        }
    }

    public function queryOdata($query){
        $url = $this->_crm_api . $query ;
        $obj = $this->requestGet($url,true);
        return $obj;
    }
}

?>