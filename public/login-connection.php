<?php
header('Location: dashboard.php');
exit;

/*
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    try {
        $auth = ConnectionDB::getInstance();
        //if ($auth->check_login($username, $password, $error)) {
        $_SESSION['logged_in'] = true;
        unset($_SESSION['from_index']);
        
        exit;
        //}
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
*/
?>