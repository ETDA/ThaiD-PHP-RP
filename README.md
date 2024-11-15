# 1. คำแนะนำเพื่อใช้งานแอปพลิเคชัน ThaIDAuthenExample เพื่อทดสอบการเชื่อมต่อระบบ ThaID ด้วยภาษา PHP 8.12 + CodeIgniter 4

แอปพลิเคชันเป็นตัวอย่างเพื่อแสดงวิธีการเชื่อมต่อ **ThaID** โดยใช้ภาษา **PHP 8.12** ร่วมกับเฟรมเวิร์ก **CodeIgniter 4** โดยใช้การยืนยันตัวตนด้วยมาตรฐาน **OpenID Connect & OAuth2**

## # 📁 library ในโปรเจกต์

1. **leagur/oauth2-client** เป็น library สำหรับจัดการ การเรียกใช้งาน OAuth2
2. **firebase/php-jwt** เป็น library สำหรับจัดการ Token หลังจากเข้าใช้งาน OAuth2

## # 📁 Runtime

1. **PHP 8.12** (รวมอยู่ในชุดจัดการเครื่องแม่ข่ายเว็บ **XAMPP**)

## # 🎛️ การติดตั้ง และตั้งค่าโปรแกรม XAMPP สำหรับใช้งานแอปพลิเคชัน

**XAMPP** เป็นโปรแกรมที่ช่วยในการ **จัดการเครื่องแม่ข่ายเว็บ** ในเครื่องคอมพิวเตอร์ ประกอบด้วย **Apache HTTP Server, MySQL database**, และโปรแกรมภาษา **PHP** ช่วยให้นักพัฒนาเว็บสามารถทดสอบและพัฒนาเว็บไซต์ด้วยภาษา **PHP**

1. ไปที่ เว็บไซต์ [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html) เพื่อทำการดาวน์โหลด PHP
2. ดาวน์โหลด version 8.2.12 / PHP 8.2.12
3. ติดตั้ง **XAMPP** และเลือกใช้งาน **Apache** เพื่อรันเว็บไซต์ สามารถเลือกบริการที่ต้องการได้
4. เลือก **Path** ที่ต้องการติดตั้ง XAMPP ได้ตามความสะดวกของผู้ใช้งาน เช่น **C:\xampp หรือ D:\xampp** ตามที่ผู้ใช้งานต้องการ
5. คัดลอกไฟล์และโฟล์เดอร์ภายในโฟลเดอร์ **ThaIDAuthenExample** และนำไปวางที่ Path XAMPP ที่ตั้งค่าไว้
   **ตัวอย่าง** วางโปรเจต์ PHP ไว้ที่ `C:\Users\User\xampp\htdocs`
6. ตั้งค่าสำหรับการเชื่อมต่อ **ThaID** ตามรายละเอียดดังนี้

---

location: `PHP/app/Config/oauth2.php`
แก่ไขค่าในตัวแปรสำหรับการเชื่อมโยงข้อมูล **ThaID** ได้แก่ **client id, client secret, API Key, Callback URL, Scope** ตามตัวอย่างในไฟล์

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

---

🚨**Important**🚨 ถ้า **XAMPP module Apache** กำลังทำงานอยู่ ให้ทำการ **หยุด (stop)** ก่อนที่จะทำการ **แก้ไข** config

7. คลิกที่ปุ่ม **Config** ที่อยู่ข้าง Apache จากนั้นเลือก **httpd.conf** เพื่อแก้ไขคอนฟิกของ Apache
8. ค้นหา **DocumentRoot** ในไฟล์ `httpd.conf` ถ้าเจอให้แก้ไข **Path** ของ DocumentRoot เป็น Path เช่น `C:\Users\User\xampp\htdocs\ThaID\PHP\public` ที่เป็นตำแหน่งของ **โปรเจกต์ public** แล้วคลิก **Save** หรือ **Ctrl + S** เพื่อ **บันทึก**
9. คลิกที่ปุ่ม **Config** ที่อยู่ข้าง Apache จากนั้นเลือก **httpd-ssl.conf** เพื่อแก้ไขคอนฟิกของ Apache
10. ค้นหา **General setup for the virtual host** ในไฟล์ `httpd-ssl.conf` แล้วเจอ ให้แก้ไขเป็น Path เช่น `C:\Users\User\xampp\htdocs\ThaID\PHP\public` ที่เป็นตำแหน่งของ **โปรเจกต์ public** แล้วคลิก **Save** หรือ **Ctrl + S** เพื่อ **บันทึก**
11. คลิกที่ปุ่ม **Config** ที่อยู่ข้าง Apache จากนั้นเลือก **PHP (php.ini)** เพื่อแก้ไขคอนฟิกของ PHP
12. ค้นหา **;extension=intl** ในไฟล์ `php.ini` ลบ **เซมิโคลอน** ออก **(;)** แล้วคลิก **Save** หรือ **Ctrl + S** เพื่อ **บันทึก**
13. กดปุ่ม **Start** ที่อยู่ข้าง Apache
14. เปิด **Browser** และไปที่ **URL** `http://localhost:8080/` หรือ `http://localhost:` ตามด้วย port ที่แสดงบน XAMPP

---

<br/><br/><br/>

# 2. องค์ประกอบของแอปพลิเคชันภายในโซลูชัน

## 📁 ThaIDAuthenExample

## # 📁🎛️ การตั้งค่าสำหรับการเชื่อมต่อกับ ThaID

location: `PHP/app/Config/oauth2.php`

**Functions** เพื่อรองรับการตั้งค่าที่หลากหลายสำหรับ library

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

## # 📁🚩 การตั้งค่าเส้นทางต่างๆในเว็บไซต์

location: `PHP/app/Config/Routes.php`

**Settings** สำหรับเส้นทางต่าง ๆ ในแอปพลิเคชัน

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

เมื่อทำการลงทะเบียนกับ DOPA ต้องแน่ใจว่า **Callback URL** จะตรงกับ routes เพื่อใช้งาน **Authorization Code** ที่ถูกต้อง สามารถปรับแต่งสิ่งนี้ใน เว็บไซต์ผู้ดูแล **RP Admin website**

```PHP
    $routes->get('/authentication/login-callback', 'Home::login');
```

## # ฟังก์ชันการยืนยันตัวตนเชื่อมต่อกับ ThaID

location: `PHP/app/Controllers/Home.php`

Home Controller เป็นตัวควบคุมหลักของแอปนี้ และจะจัดการทุกอย่างที่นี่

**ฟังก์ชัน** สำหรับหน้าแรกของเว็บแอป

```PHP
    public function index(): string
    {
        $this->RemoveSessionToken();
        return view('index');
    }
```

**ฟังก์ชัน** สำหรับหน้าที่ต้องยืนยันตัวตน

```PHP
    public function Dashboard(){
        return view('Authentication');
    }
```

**ฟังก์ชัน** สำหรับการตรวจสอบสิทธิ์ ฟังก์ชันนี้จะดูว่ามี Authorization Code หรือไม่

- หากไม่มีรหัส แอปจะพาผู้ใช้ไปยัง ThaID เพื่อยืนยันตัวตน
- หากมีรหัส แอปจะใช้ API เพื่อขอรับโทเค็นจาก ThaID

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

**ฟังก์ชัน** สำหรับขอรับโทเค็นใหม่เมื่อ Access Token หมดอายุหรือใช้ไม่ได้ โดยการเรียก API ด้วย Refresh Token.

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

**ฟังก์ชัน** สำหรับตรวจสอบว่า Access Token ยังคงใช้งานได้หรือไม่ โดยการใช้ API Introspect Token ในกรณีที่เป็นทรัพยากรที่ได้รับอนุญาตและมีการขอข้อมูลจากระบบที่ใช้ ThaID

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

**ฟังก์ชัน** สำหรับขอให้ยกเลิก Access Token โดยการเรียก API Revoke Token

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

**ฟังก์ชัน** สำหรับตรวจสอบว่า ID Token ที่ได้จาก ThaID ตรงตามมาตรฐาน OpenID Connect หรือเปล่า

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

**ฟังก์ชัน** สำหรับสร้างเซสชันสำหรับเก็บข้อมูลจาก ThaID

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

**ฟังก์ชัน** สำหรับลบเซสชันเมื่อผู้ใช้ออกจากระบบ

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

## # 📁📄 แสดงผลหน้าแรกของแอปพลิเคชัน

location: `PHP/app/Views/index.php`

## # 📁📄 หน้าจอสำหรับแสดงข้อมูลหลังจากยืนยันตัวตน

location: `PHP/app/Views/Authentication.php`
