<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'mysql.php';
header('Content-Type: application/json');

if (isset($_GET['chatUserUSERNAME'])) {
    $input = $_GET['chatUserUSERNAME'];

    try {
        // Prüfen, ob die Eingabe direkt eine chat_user_id ist (angenommen: 6-stellig, alphanumerisch)
        if (preg_match('/^[a-f0-9]{6}$/i', $input)) {
            // Prüfen, ob diese ID existiert
            $stmt = $mysql->prepare("SELECT chat_user_id FROM accounts WHERE chat_user_id = :id");
            $stmt->bindParam(":id", $input);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                echo json_encode(['chat_user_id' => $result['chat_user_id']]);
                exit;
            }
        }

        // Falls keine gültige ID, dann als Benutzername behandeln
        $stmt = $mysql->prepare("SELECT chat_user_id FROM accounts WHERE USERNAME = :username");
        $stmt->bindParam(":username", $input);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo json_encode(['chat_user_id' => $result['chat_user_id']]);
        } else {
            echo json_encode(['chat_user_id' => null]);
        }

    } catch (Exception $e) {
        echo json_encode(['chat_user_id' => null, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['chat_user_id' => null]);
}
