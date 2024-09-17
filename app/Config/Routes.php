<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

//สำหรับ set Route

/// Route หน้าแรก
$routes->get('/', 'Home::index');

/// Route หลังจาก login ThaID ตัวระบบจะ redirect มา route นี้
$routes->get('/authentication/login-callback', 'Home::login');

/// Route กดปุ่ม login ThaID ในหน้าแรก
$routes->get('/authentication/login', 'Home::login');

/// Route check token
$routes->get('/authentication/TokenInspect', 'Home::TokenInspect');

/// Route ยกเลิก token
$routes->get('/authentication/TokenRevoke', 'Home::TokenRevoke');

/// Route refresh token
$routes->get('/authentication/RefreshToken', 'Home::RefreshToken');

/// Route validate token
$routes->get('/authentication/ValidateToken', 'Home::ValidateToken');

/// Route สำรับ redirect ไปหน้า dashboard
$routes->get('/Dashboard', 'Home::Dashboard');
