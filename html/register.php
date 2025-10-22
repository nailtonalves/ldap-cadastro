<?php
require 'config.php';
require 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // echo json_encode(array('data' => "teste", 'status' => 'error'), JSON_UNESCAPED_UNICODE);
    // exit;
    $uid = $_POST['uid'];
    $givenName = $_POST['givenName'];
    $sn = $_POST['sn'];
    $mail = $_POST['mail'];
    $cpf = $_POST['cpf'];
    $dob = $_POST['dob']; // formato: YYYYMMDD
    $password = $_POST['password'];

    $cn = strtoupper($givenName);
    $displayName = "$givenName $sn";

    $ldapconn = ldap_connect($ldap_host, $ldap_port);
    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

    if ($ldapconn && ldap_bind($ldapconn, $ldap_admin_dn, $ldap_admin_password)) {
        $dn = "uid=$uid,$ldap_base_dn";

        $entry = [
            "objectClass" => ["brPerson", "eduPerson", "inetOrgPerson", "person", "schacPersonalCharacteristics"],
            "cn" => $cn,
            "sn" => $sn,
            "brPersonCPF" => $cpf,
            "displayName" => $displayName,
            "eduPersonPrincipalName" => "$uid@uesb.edu.br",
            "eduPersonScopedAffiliation" => "staff@uesb.edu.br",
            "givenName" => $givenName,
            "mail" => $mail,
            "schacCountryOfCitizenship" => "Brazil",
            "schacDateOfBirth" => $dob,
            "uid" => $uid,
            "userPassword" => encodePassword($password),
        ];


        try {

            $result = @ldap_add($ldapconn, $dn, $entry);
            if (!$result) {
                echo json_encode(array(
                    'data' => ldap_error($ldapconn),
                    'status' => 'error'
                ), JSON_UNESCAPED_UNICODE);
                ldap_close($ldapconn);
                exit;
            }
            echo json_encode(array('data' => "CADASTRO", 'status' => 'error'), JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Throwable $th) {
            echo json_encode(array('data' => "ERRO", 'status' => 'error'), JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode(array('data' => "TESTE", 'status' => 'error'), JSON_UNESCAPED_UNICODE);
        exit;
        if ($result) {
            echo json_encode(array('data' => "teste", 'status' => 'error'), JSON_UNESCAPED_UNICODE);
            exit;
            // echo json_encode(["success" => true]);
            echo json_encode(array('data' =>  $entry, 'status' => 'success'), JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(array('data' => "teste", 'status' => 'error'), JSON_UNESCAPED_UNICODE);
            exit;
            // echo json_encode(["success" => false, "error" => ldap_error($ldapconn)]);
            echo json_encode(['success' => false, 'error' => ldap_error($ldapconn)], JSON_UNESCAPED_UNICODE);
        }

        ldap_unbind($ldapconn);
    } else {
        echo json_encode(array('data' => "teste", 'status' => 'error'), JSON_UNESCAPED_UNICODE);
        exit;
        echo json_encode(['success' => false, 'error' => 'Falha na conexão LDAP']);
        echo json_encode(['success' => false, 'error' => 'Falha na conexão LDAP'], JSON_UNESCAPED_UNICODE);
    }
}
