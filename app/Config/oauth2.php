<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class OAuth2 extends BaseConfig
{
    // clientId คือ ระบุค่า client identifier ที่กรมการปกครองออกให้กับ RP ที่ผ่านการลงทะเบียนแล้ว
    public $clientId     = '';

    // clientSecret คือ ค่าที่จะได้มาพร้อมกับ clientId ที่กรมการปกครองออกให้ จำเป็นต้องใช้เพื่อไปเรียกใช้งาน API ต่างๆ
    public $clientSecret = '';

    // apiKey คือสตริงที่ไม่ซ้ำกันซึ่งใช้เพื่อระบุตัวตนและรับรองสิทธิ์ของผู้ใช้หรือแอปพลิเคชันที่เรียกใช้ API
    public $apiKey = '';

    // urlGetWellKnow เป็น url สำหรับรับค่า config oauth2 ของ ThaID
    public $urlGetWellKnow = 'https://imauth.bora.dopa.go.th/.well-known/openid-configuration';

    // redirectUri เป็น url ที่หลังจาก login เสร็จ ตัว ThaID จะ redirect ไป และมีค่า code และ state กลับไปให้
    public $redirectUri  = ''; // ต้องไม่มี slash ปิดท้าย

    // urlIntrospectToken คือ url ที่ใช้ในการตรวจสอบยืนยันข้อมูลเกี่ยวกับ token ในระบบการยืนยันตัวตนและการให้สิทธิ์
    public $urlIntrospectToken = 'https://imauth.bora.dopa.go.th/api/v2/oauth2/introspect/';

    // scope เป็นพารามิเตอร์ที่ระบุขอบเขตของสิทธิ์การเข้าถึงที่แอปพลิเคชันต้องการ
    public $scope ='pid openid name_en name birthdate address given_name middle_name family_name given_name_en middle_name_en family_name_en gender smartcard_code title title_en ial date_of_issuance date_of_expiry';

    // state เป็นพารามิเตอร์ที่ใช้เพื่อป้องกันการโจมตีแบบ Cross-Site Request Forgery (CSRF) และเพื่อรักษาสถานะของเซสชันระหว่างคำขอรับรอง
    public $state = '';

    // urlAuthorize เป็น url เพื่อการยืนยันตัวตนกับระบบของกรมการปกครอง
    public $urlAuthorize = '';

    // urlAccessToken เป็น url ที่ไว้ไปขอ Access token
    public $urlAccessToken = '';

    // urlAuthorize เป็น url สำหรับรับค่า publict key JWKS มา validate id token
    public $urlGetPublicKey = '';

    // urlRevokeToken คือ url ที่ใช้ในการยกเลิก token
    public $urlRevokeToken ='';

    // urlResourceOwnerDetails เป็น URL ที่ใช้ในการเข้าถึงข้อมูลของเจ้าของทรัพยากร
    public $urlResourceOwnerDetails = '';

    // construct
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
}
?>

