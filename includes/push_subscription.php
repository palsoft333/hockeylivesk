<?php
session_start();
include("db.php");

$sub = file_get_contents('php://input');
$subscription = json_decode($sub, true);

if (!isset($subscription['endpoint'])) {
    echo 'Error: not a subscription';
    return;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // create a new subscription entry in your database (endpoint is unique)
        $q = mysqli_query($link, "UPDATE e_xoops_users SET push_id='".mysqli_real_escape_string($link, $sub)."' WHERE uid='".$_SESSION["logged"]."'");
        break;
    case 'PUT':
        // update the key and token of subscription corresponding to the endpoint
        $q = mysqli_query($link, "UPDATE e_xoops_users SET push_id='".mysqli_real_escape_string($link, $sub)."' WHERE uid='".$_SESSION["logged"]."'");
        break;
    case 'DELETE':
        // delete the subscription corresponding to the endpoint
        $q = mysqli_query($link, "UPDATE e_xoops_users SET push_id=NULL WHERE uid='".$_SESSION["logged"]."'");
        break;
    default:
        echo "Error: method not handled";
        return;
}
