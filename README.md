# Integration of ThaID with PHP CodeIgniter 4
This project shows how to connect **ThaID** to the **PHP CodeIgniter 4 Framework** using **Open ID Connect & OAuth2 authentication**. It lets you safely log in and give users access through ThaID.
## # 📁🎛️ Settings for connecting to ThaID ##
location: `PHP/app/config/oauth2.php`

**Variables** for configuration used with ThaID data integration, such as Client ID, Client Secret, API Key, Callback URL, and Scope.
```PHP
    public $clientId     = '{Client_id}';
    public $clientSecret = '{Client_secret}';
    public $apiKey = '{api_key}';
    public $urlGetWellKnow = 'https://imauth.bora.dopa.go.th/.well-known/openid-configuration';
    public $redirectUri  = '{calback_url}';
    public $urlIntrospectToken = 'https://imauth.bora.dopa.go.th/api/v2/oauth2/introspect/';
    public $scope ='{scope}';
    public $state = '';
    public $urlAuthorize = '';
    public $urlAccessToken = '';
    public $urlGetPublicKey = '';
    public $urlRevokeToken ='';
    public $urlResourceOwnerDetails = '';
```
**Functions** to support various settings for the library.
```PHP
    public function __construct() {

        // Get config from Well Know
        $this->GetConfigWellKnow();

        // Set state random
        $this->state = $this->GenerateRandomState();

        // ประกอบร่าง url สำหรับ Authorize
        $this->urlAuthorize = $this->urlAuthorize.'?response_type=code&client_id='.$this->clientId.'&redirect_uri='.$this->redirectUri.'&scope='.$this->scope.'&state='.$this->state;
    }

    // function สำหรับ Get ค่า config จาก Well know
    public function GetConfigWellKnow(){
        try{
            $result = json_decode(file_get_contents($this->urlGetWellKnow), true);
            $this->urlAuthorize = $result['authorization_endpoint'];
            $this->urlGetPublicKey = $result['jwks_uri'];
            $this->urlAccessToken = $result['token_endpoint'];
            $this->urlRevokeToken = $result['revocation_endpoint'];  
        }
        catch(\Throwable $e){
            echo $e->getMessage();
        }
        
    }

    // function สำหรับ random state
    public function GenerateRandomState($length = 16) {
        return bin2hex(random_bytes($length));
    }

    // function สำหรับ return ค่า Authorization basic
    public function GetBasicAuthorizationCode(){
        return base64_encode($this->clientId . ':' . $this->clientSecret);
    }
```
## # 📁🚩 Configuring Routes in a Web Application ##
location: `PHP/app/config/Routes.php`

**Settings** for different paths or routes in the application.
```PHP
    // Route หน้าแรก
    $routes->get('/', 'Home::index');

    // Route กดปุ่ม login ThaID ในหน้าแรก
    $routes->get('/authentication/login', 'Home::login');

    // Route check token
    $routes->get('/authentication/TokenInspect', 'Home::TokenInspect');

    // Route ยกเลิก token
    $routes->get('/authentication/TokenRevoke', 'Home::TokenRevoke');

    // Route refresh token
    $routes->get('/authentication/RefreshToken', 'Home::RefreshToken');

    // Route validate token
    $routes->get('/authentication/ValidateToken', 'Home::ValidateToken');

    // Route สำรับ redirect ไปหน้า dashboard
    $routes->get('/Dashboard', 'Home::Dashboard');
```
**Important !!!**

When registering with DOPA, ensure the **Callback URL** matches the Route for correct **Authorization Code** delivery. Adjust this on the **RP Admin website.**
```PHP
    $routes->get('/authentication/login-callback', 'Home::login');
```
## # Authentication Function Connect to ThaID ##
location: `PHP/app/Controllers/Home.php`

Home controller is the main controller for this app, and all operations will be handled here.

**Function** for the main page of the web app.
```PHP
    public function index(): string
    {
        $this->RemoveSessionToken();
        return view('index');
    } 
```
**Function** for authenticated pages.
```PHP
    public function Dashboard(){
        return view('Authentication');
    } 
```
**Function** for the authentication process. This controller checks for an Authorization Code in the parameters.
- If there's no code, the app will redirect the user to ThaID for authentication.
- If there's a code, the app will use the API to request a token set from ThaID.
```PHP
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
```
**Function** to request a new token when the Access Token has expired or is no longer valid, by calling the API with the Refresh Token.
```PHP
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
```
**Function** to check if an Access Token is still valid by using the API Introspect Token, in cases where it's an Authorized Resource and a request for information comes from another system using ThaID.
```PHP
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
```
**Function** to request the revocation of an Access Token by calling the Revoke Token API.
```PHP
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
```
**Function** to verify if the ID Token received from ThaID complies with OpenID Connect standards.
```PHP
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
```
**Function** to create a session for storing information received from ThaID.
```PHP
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
```
**Function** to delete the session when a user logs out.
```PHP
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
```
## # 📁📄 Home Page Display for the Application ##
location: `PHP/app/Views/index.php`

## # 📁📄 Display Page for Showing Information After Authentication ##
location: `PHP/app/Views/Authentication.php`