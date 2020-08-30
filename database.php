<?php
/*
کانال شهر سورس مرجع انواع سورس کد های مختلف
بانک انواع سورس کد های مختلف به صورت کاملا تست شده
هر روز کلی سورس کد و اسکریپت منتظر شماست !

@ShahreSource
https://t.me/ShahreSource
*/
error_reporting(0);
include('config.php');
isset($conn->connect_error) ? die($conn->connect_error):0;
$res=$conn->query('SHOW TABLES LIKE "users_music"');
if($res->num_rows<1){
    $conn->query('CREATE TABLE users_music (
id INT(50) PRIMARY KEY ,
block VARCHAR(10) CHARACTER SET utf8mb4,
spam INT(50) ,
timee INT(50)
)');
    $conn->error ? die($conn->error):print('create success');
}else{
    print('was created');
}/*
کانال شهر سورس مرجع انواع سورس کد های مختلف
بانک انواع سورس کد های مختلف به صورت کاملا تست شده
هر روز کلی سورس کد و اسکریپت منتظر شماست !

@ShahreSource
https://t.me/ShahreSource
*/
