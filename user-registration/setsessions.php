<?php

session_start();

require('MysqliDb.php');
$conn = new MysqliDb("localhost","vanny81","scorpio1981","weboffice_reg");

if(isset($_POST['regsession'])){
    
    $user    = stripslashes($_POST['usrname']);
    $email   = stripslashes($_POST['email']);
    $pwd     = stripslashes($_POST['pwd']);
    $fullname= stripslashes($_POST['fullname']);
    $country = stripslashes($_POST['country']);
    $gender  = stripslashes($_POST['gender']);
    $mobile  = stripslashes($_POST['mobile']);
    $company = stripslashes($_POST['company']);
    $comp_reg= stripslashes($_POST['comp_reg']);
    $address = stripslashes($_POST['address']);
    $city    = stripslashes($_POST['city']);
    $postal  = stripslashes($_POST['postal']);    
    
    $regData = array("name" => $user,
                        "email" => $email,
                        "password" => md5($pwd),
                        "mobile"   => $mobile,
                        //"gender"   => $gender,
                        "created_at" => date('Y-m-d H:i:s'),
                        "company"    => $company,
                        "onesignal_player_id" => $pwd
                        
                        );
    $conn->insert("users", $regData) or die($conn->getLastError());
    
    exit();    
}


?>