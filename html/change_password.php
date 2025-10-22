<?php
// require 'config.php';
// require 'helpers.php';

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $uid = $_POST['uid'];
//     $currentPassword = $_POST['currentPassword'];
//     $newPassword = $_POST['newPassword'];

//     $user_dn = "uid=$uid,$ldap_base_dn";

//     $ldapconn = ldap_connect($ldap_host, $ldap_port);
//     ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

//     if ($ldapconn && ldap_bind($ldapconn, $user_dn, $currentPassword)) {
//         $newPasswordEncoded = encodePassword($newPassword);
//         $entry = ["userPassword" => $newPasswordEncoded];

//         $result = ldap_modify($ldapconn, $user_dn, $entry);

//         if ($result) {
//             echo json_encode(["success" => true]);
//         } else {
//             echo json_encode(["success" => false, "error" => ldap_error($ldapconn)]);
//         }

//         ldap_unbind($ldapconn);
//     } else {
//         echo json_encode(["success" => false, "error" => "Autenticação falhou"]);
//     }
// }

require 'config.php';
require 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mail = $_POST['mail'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];

    $ldapconn = ldap_connect($ldap_host, $ldap_port);
    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

    if (!$ldapconn) {
        echo json_encode(["success" => false, "error" => "Não foi possível conectar ao servidor LDAP."]);
        exit;
    }

    // Conecta como admin para buscar o DN do usuário pelo e-mail
    if (!ldap_bind($ldapconn, $ldap_admin_dn, $ldap_admin_password)) {
        echo json_encode(["success" => false, "error" => "Falha na autenticação administrativa."]);
        exit;
    }

    // Procura o DN baseado no e-mail
    $search = ldap_search($ldapconn, $ldap_base_dn, "(mail=$mail)", ["dn"]);
    $entries = ldap_get_entries($ldapconn, $search);

    if ($entries["count"] == 0) {
        echo json_encode(["success" => false, "error" => "E-mail não encontrado."]);
        ldap_unbind($ldapconn);
        exit;
    }

    $user_dn = $entries[0]["dn"];

    // Agora tenta autenticar com a senha atual do usuário
    if (!@ldap_bind($ldapconn, $user_dn, $currentPassword)) {
        echo json_encode(["success" => false, "error" => "Senha atual incorreta."]);
        ldap_unbind($ldapconn);
        exit;
    }

    // Atualiza a senha
    $newPasswordEncoded = encodePassword($newPassword);
    $entry = ["userPassword" => $newPasswordEncoded];

    if (ldap_modify($ldapconn, $user_dn, $entry)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => ldap_error($ldapconn)]);
    }

    ldap_unbind($ldapconn);
}


?>
