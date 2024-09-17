<?php

namespace App\Controllers;
require 'vendor/autoload.php';
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use GuzzleHttp\Client;
use Config\OAuth2;


use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

class Home extends BaseController
{

     // provider ThaID
    protected $provider;

    // oauth2 config
    protected $oauthConfig;

    // เอาไว้เก็บ session ต่างๆ
    protected $session;

    public function __construct(){
        // เอา oauth2.php มาแปลงเป็น variable
        $this->oauthConfig = new OAuth2();
        
        // สำหรับ set ค่า provider ของ ThaID
        $this->provider = new GenericProvider([
            'clientId'                =>  $this->oauthConfig->clientId,
            'clientSecret'            =>  $this->oauthConfig->clientSecret,
            'redirectUri'             =>  $this->oauthConfig->redirectUri,
            'urlAuthorize'            =>  $this->oauthConfig->urlAuthorize,
            'urlAccessToken'          =>  $this->oauthConfig->urlAccessToken,
            'urlResourceOwnerDetails' =>  $this->oauthConfig->urlResourceOwnerDetails,
        ]);

        // สร้าง ตัวแปร Session
        $this->session = session();
    }

    /* 
    จะ แสดงหน้าแรกของ web
    */
    public function index(): string
    {
        $this->RemoveSessionToken();
        return view('index');
    } 

    /* 
    จะ redirect ไปที่ file Authentication.php
    สำหรับผู้ใช้งานที่ไม่อยากให้เห็น URL ที่คืนค่ามาจากหลังการ login
    */
    public function Dashboard(){
        return view('Authentication');
    }

    /* 
    จะ redirect ไปที่หน้า login ของ ThaID
    หลังจาก user login ระบบ จะเอา Code ที่มากลับ call back url มาหา Access token มาเก็บไว้ใน Session
    แล้วแสดงหน้าจอ ที่มาจาก file Authentication.php ใน Folder Views
    */
    public function login(){ 
        if (!isset($_GET['code'])) { // เข้า if ถ้าไม่มี code มากับ path จะ redirect ไปที่หน้า Login ของ ThaID
            $authorizationUrl = $this->provider->getAuthorizationUrl();
            header('Location: ' . $authorizationUrl);
            exit;
        } else {  // เข้า else ถ้ามี code มากับ path (Login ThaID สำเร็จ)
            try {
                $resultToken = $this->provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code'],
                ]);
                $this->CreateSessionToken($resultToken);
            } 
            catch (IdentityProviderException $e) {
                // ถ้า Get access token แล้ว error จะแสดง error message บนหน้าจอ
                $errorResponse = $e->getResponseBody();
                if (is_array($errorResponse) && isset($errorResponse['error_description'])) {
                    echo 'Error Description: ' . $errorResponse['error_description'];
                } else {
                    echo 'Something went wrong: ' . $e->getMessage();
                }
                $this->RemoveSessionToken();
            }
            catch(\Throwable $e){
                echo $e->getMessage();
            }
        }
        return redirect()->to('/Dashboard');
    }

    /* 
    จะเอา Refresh token มาจาก Session มา get token ใหม่
    หลังจาก refresh เสร็จ จะเอา Access token มาเก็บไว้ใน Session
    แล้วแสดงหน้าจอ ที่มาจาก file Authentication.php ใน Folder Views
    */
    public function RefreshToken (){
        if($this->session->get('refreshToken') != null){
            try {
                // get access token
                $resultToken = $this->provider->getAccessToken('refresh_token', [
                    'refresh_token' => $this->session->get('refreshToken')
                ]);

                // เอา access token ไปเก็บใน Session
                $this->CreateSessionToken($resultToken);
            } 
            catch (IdentityProviderException $e){
                // ถ้า Get access token แล้ว error จะแสดง error message บนหน้าจอ
                $errorResponse = $e->getResponseBody();
                if (is_array($errorResponse) && isset($errorResponse['error_description'])) {
                    echo 'Error Description: ' . $errorResponse['error_description'];
                } else {
                    echo 'Something went wrong: ' . $e->getMessage();
                }
            }
        }
        return redirect()->to('/Dashboard');
    }

    /*
    จะเอา access token มาจาก Session มา check กับ ThaID ว่า Token ถูกต้อง และ สามารถใช้งานได้ไหม
    โดยจะใช้วิธี Call api แบบ Post แบบธรรมดา
    แล้วจะ return ข้อมูลที่ได้กลับมา ไปแสดงบนหน้าจอ
    */
    public function TokenInspect(){
        $url = $this->oauthConfig->urlIntrospectToken;
        $curl = curl_init($url);
        $token = $this->session->get('accessToken');
        $dataBody = array(
            'token' => "Bearer $token"
        );

        $basicBase64 = $this->oauthConfig->GetBasicAuthorizationCode();
        $dataString = http_build_query($dataBody);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic '.$basicBase64,
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);

        $result = curl_exec($curl);
        return $result;
    }

    /*
    จะเอา access token มาจาก Session มา ยกเลิกการใช้งาน กับ ThaID แล้วจะ return ผมกลับไปหา หน้าจอ
    */
    public function TokenRevoke(){
        $url = $this->oauthConfig->urlRevokeToken;
        $curl = curl_init($url);
        $token = $this->session->get('accessToken');
        $dataBody = array(
            'token' => "$token"
        );
        
        $basicBase64 = $this->oauthConfig->GetBasicAuthorizationCode();
        $dataString = http_build_query($dataBody);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic '.$basicBase64,
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);

        $result = curl_exec($curl);
        return $result;
    }

    /*
    จะดึง public key จาก jwks มา validate id token
    */
    public function ValidateToken(){
        try{
            $idToken = $this->session->get('idToken');
            $jwks = json_decode(file_get_contents($this->oauthConfig->urlGetPublicKey), true);
            $resultValidate = JWT::decode($idToken, JWK::parseKeySet($jwks));
            echo "id token ok";
            echo "<br/>";
            echo "<br/>";
            print_r($resultValidate);
        }
        catch(\Throwable $e){
            return $e->getMessage();
        }
    }

    /*
    จะเอาข้อมูล Access token, Refresh token, Expire, ID token  มาเก็บไว้ใน Session
    แล้วนำ ID Token มา decode แล้วเอาข้อมูลเพิ่มเติมไปเก็บใน Session 
    */
    public function CreateSessionToken($Token){

        // Set session token
        session()->set('accessToken', $Token->getToken());
        session()->set('refreshToken', $Token->getRefreshToken());
        session()->set('expiresIn', $Token->getExpires());
        session()->set('idToken', $Token->getValues()['id_token']);
        session()->set('scope', $Token->getValues()['scope']);
        session()->set('tokenType', $Token->getValues()['token_type']);
        
        $jwks = json_decode(file_get_contents($this->oauthConfig->urlGetPublicKey), true);
        $resultValidate = JWT::decode($Token->getValues()['id_token'], JWK::parseKeySet($jwks));
        $this->session->set('tokenDecode', $resultValidate);
    }

    /*
    ลบ Session ทั้งหมด
    */
    public function RemoveSessionToken(){
        $this->session->remove('accessToken');
        $this->session->remove('refreshToken');
        $this->session->remove('tokenType');
        $this->session->remove('expiresIn');
        $this->session->remove('scope');
        $this->session->remove('idToken');
        $this->session->remove('tokenDecode');
        $this->session->destroy();
    }  
}
