<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'mysql.php';

$chatUserId = "Wird geladen...";

if (isset($_SESSION['id'])) {
    $stmt = $mysql->prepare("SELECT chat_user_id FROM accounts WHERE ID = :id");
    $stmt->bindParam(":id", $_SESSION['id']);
    $stmt->execute();
    $row = $stmt->fetch();

    if ($row) {
        $chatUserId = $row['chat_user_id'];

        // Wenn chat_user_id NULL ist, generiere eine neue eindeutige ID
        if ($chatUserId === null) {

            function generateUniqueChatUserId($mysql) {
                do {
                    $randomId = substr(bin2hex(random_bytes(3)), 0, 6);

                    $checkStmt = $mysql->prepare("SELECT COUNT(*) FROM accounts WHERE chat_user_id = :id");
                    $checkStmt->bindParam(":id", $randomId);
                    $checkStmt->execute();
                    $exists = $checkStmt->fetchColumn();

                } while ($exists > 0); // Wiederholen, falls ID bereits existiert

                return $randomId;
            }

            $generatedChatUserID = generateUniqueChatUserId($mysql);

            $stmt = $mysql->prepare("UPDATE accounts SET chat_user_id = :random_id WHERE ID = :id");
            $stmt->bindParam(":random_id", $generatedChatUserID);
            $stmt->bindParam(":id", $_SESSION['id']);
            $stmt->execute();

            $chatUserId = $generatedChatUserID;
        }
    }
}
?>
