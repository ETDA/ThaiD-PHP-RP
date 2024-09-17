<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<?php
$session = session();
?>
<!-- Div สำหรับแสดง ข้อมูลที่ได้มาจาก ThaID API -->
<div class="text-center">
    <div id="liveAlertPlaceholder"></div>
    <h1 class="display-4">Token Result</h1>
    <br></br>
    <img alt="ThaID Logo" src="../ThaID.png"/>
    <br></br>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Token</h5>
            <p class="card-text">Token ใช้เพื่อยืนยันตัวตน</p>
        </div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">Access Token: <?php echo $session->get('accessToken'); ?></li>
            <li class="list-group-item">Refresk Token: <?php echo $session->get('refreshToken'); ?></li>
            <li class="list-group-item">Token Type: <?php echo $session->get('tokenType'); ?></li>
            <li class="list-group-item">Expires In: <?php echo $session->get('expiresIn'); ?></li>
            <li class="list-group-item">Scope: <?php echo $session->get('scope'); ?></li>
            <li class="list-group-item">ID Token: <?php echo $session->get('idToken'); ?></li>
        </ul>
        <div class="card-body">
            <a href="#" class="card-link" id="requestid">ทดสอบขอข้อมูลจาก AS</a>
            <a href="#" class="card-link" id="validateIdToken">ทดสอบ Validate ID token</a>
            <a href="#" class="card-link" id="revoke">ทดสอบ Revoke Token</a>
            <a href="<?php echo base_url('authentication/RefreshToken');?>" class="card-link" id="revoke">ทดสอบ Refresh Token</a>
        </div>
    </div>
    <br></br>
    <hr />
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">ID Token</h5>
            <p class="card-text">ID Token ใช้เพื่อยืนยันตัวตน</p>
        </div>
        <ul class="list-group list-group-flush">
        <?php 
        $at_hash = "";
        $aud = "";
        $version = "";
        $auth_time = "";
        $iss = "";
        $iat = "";
        $exp = "";
        $sub = "";
        $pid = "";
        $name_en = "";
        $name = "";
        
        if($session->has('tokenDecode'))
        {   
            $at_hash = $session->get('tokenDecode')->at_hash;
            $aud = $session->get('tokenDecode')->aud;
            $version = $session->get('tokenDecode')->version;
            $auth_time = date("Y-m-d H:i:s", $session->get('tokenDecode')->auth_time);
            $iss = $session->get('tokenDecode')->iss;
            $iat = date("Y-m-d H:i:s", $session->get('tokenDecode')->iat);
            $exp = date("Y-m-d H:i:s", $session->get('tokenDecode')->exp);
            $sub = $session->get('tokenDecode')->sub;
            $pid = $session->get('tokenDecode')->pid;
            $name_en = $session->get('tokenDecode')->name_en;
            $name = $session->get('tokenDecode')->name;
        } ?>
            <li class="list-group-item">Access Token Hash: <?php echo $at_hash; ?></li>
            <li class="list-group-item">Audience: <?php echo $aud; ?></li>
            <li class="list-group-item">Version: <?php echo $version; ?></li>
            <li class="list-group-item">Authentication Time: <?php echo $auth_time; ?></li>
            <li class="list-group-item">Issuer: <?php echo $iss; ?></li>
            <li class="list-group-item">Issue In: <?php echo $iat; ?></li>
            <li class="list-group-item">Expires In: <?php echo $exp; ?></li>
            <li class="list-group-item">Subject Identifier: <?php echo $sub; ?></li>
            <li class="list-group-item">PID: <?php echo $pid; ?></li>
            <li class="list-group-item">Name English: <?php echo $name_en; ?></li>
            <li class="list-group-item">Name Thai: <?php echo $name; ?></li> 
        </ul>
    </div>
</div>

<script>
var alertPlaceholder = document.getElementById('liveAlertPlaceholder')

// function สำรับแสดง Alert ต้องส่ง Message ที่จะแสดง แล้วก็ type ที่เป็น Success, Warning, Danger
function alert(message, type) {
    var wrapper = document.createElement('div')
    wrapper.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible" role="alert">' + message + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>'
    alertPlaceholder.append(wrapper)
}

document.addEventListener('DOMContentLoaded', function () {
    var requestButton = document.getElementById('requestid');
    var revorkButton = document.getElementById('revoke');
    var validateButton = document.getElementById('validateIdToken');

    // function ที่ trigger จากการกด ปุ่ม ทดสอบขอข้อมูลจาก AS
    // จะส่ง Access token ไปหา ThaID เพื่อเช็คว่า Active อยู่หรือไม่
    requestButton.addEventListener('click', function () {
        $.ajax({
            url: '<?php echo base_url('authentication/TokenInspect');?>',
            type: 'GET',
            dataType:'text',
            data: {},
            success: function(response) {
                alert(response, 'success')
            },
            error: function(xhr, status, error) {
                alert(error, 'danger')
            }
        });
    });

    // function ที่ trigger จากการกด ปุ่ม ทดสอบ Revoke Token
    // จะส่ง Access token ไปหา ThaID เพื่อหยุดการใช้งาน Token
    revorkButton.addEventListener('click', function () {
        $.ajax({
            url: '<?php echo base_url('authentication/TokenRevoke');?>',
            type: 'GET',
            dataType:'text',
            data: {},
            success: function(response) {
                console.log(response);
                alert(response, 'warning')
            },
            error: function(xhr, status, error) {
                alert(error, 'danger')
            }
        });
    });

    // function ที่ trigger จากการกด ปุ่ม ทดสอบ Revoke Token
    // จะส่ง Access token ไปหา ThaID เพื่อหยุดการใช้งาน Token
    validateButton.addEventListener('click', function () {
        $.ajax({
            url: '<?php echo base_url('authentication/ValidateToken');?>',
            type: 'GET',
            dataType:'text',
            data: {},
            success: function(response) {
                console.log(response);
                alert(response, 'primary')
            },
            error: function(xhr, status, error) {
                alert(error, 'danger')
            }
        });
    });
});

</script>
