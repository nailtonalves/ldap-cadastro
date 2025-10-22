<?php
function encodePassword($plainPassword) {
    $encoded = base64_encode($plainPassword);
    return "{SHA}" . $encoded;
}
?>