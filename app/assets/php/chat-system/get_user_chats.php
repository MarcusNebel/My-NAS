<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'mysql.php';

if (isset($_SESSION['id'])) {
    $sessionID = $_SESSION['id'];

    // Aktuelle chat_user_id holen
    $stmt = $mysql->prepare("SELECT chat_user_id FROM accounts WHERE ID = :id");
    $stmt->bindParam(":id", $sessionID);
    $stmt->execute();
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentChatUserID = $userRow['chat_user_id'] ?? null;

    if (!$currentChatUserID) exit;

    // Kontakte abrufen
    $stmt = $mysql->prepare("SELECT owner_id, contact_id FROM contacts WHERE owner_id = :id OR contact_id = :id");
    $stmt->bindParam(":id", $currentChatUserID);
    $stmt->execute();
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $seen = [];

    foreach ($contacts as $contact) {
        $otherUserId = ($contact['owner_id'] == $currentChatUserID) ? $contact['contact_id'] : $contact['owner_id'];
        if (in_array($otherUserId, $seen)) continue;
        $seen[] = $otherUserId;

        // Username des anderen holen
        $stmtUser = $mysql->prepare("SELECT USERNAME FROM accounts WHERE chat_user_id = :id");
        $stmtUser->bindParam(":id", $otherUserId);
        $stmtUser->execute();
        $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$userRow || !isset($userRow['USERNAME'])) continue;
        $username = htmlspecialchars($userRow['USERNAME']);

        // Letzte Nachricht zwischen den beiden holen
        $stmtLastMsg = $mysql->prepare("
            SELECT sender, message, timestamp 
            FROM messages 
            WHERE (sender = :u1 AND receiver = :u2) OR (sender = :u2 AND receiver = :u1)
            ORDER BY timestamp DESC LIMIT 1
        ");
        $stmtLastMsg->execute(['u1' => $currentChatUserID, 'u2' => $otherUserId]);
        $lastMsgRow = $stmtLastMsg->fetch(PDO::FETCH_ASSOC);

        $lastMessage = $lastMsgRow['message'] ?? '';
        $lastSender = $lastMsgRow['sender'] ?? null;
        $timestamp = $lastMsgRow['timestamp'] ?? null;

        // Formatierung der Zeit
        $formattedTime = '';
        if ($timestamp) {
            $msgTime = new DateTime($timestamp);
            $now = new DateTime();
            $yesterday = (new DateTime())->modify('-1 day');

            if ($msgTime->format('Y-m-d') === $now->format('Y-m-d')) {
                $formattedTime = $msgTime->format('H:i');
            } elseif ($msgTime->format('Y-m-d') === $yesterday->format('Y-m-d')) {
                $formattedTime = "Gestern";
            } else {
                $formattedTime = $msgTime->format('d.m.Y');
            }
        }

        // "Du:" nur anzeigen, wenn du selbst gesendet hast
        $messagePreview = ($lastSender === $currentChatUserID) ? "Du: " . htmlspecialchars($lastMessage) : htmlspecialchars($lastMessage);

        echo '
        <div class="conversation" data-username="' . $username . '" data-userid="' . $otherUserId . '">
            <div class="conversation-header">
                <div class="username">' . $username . '</div>
                <div class="last-time">' . $formattedTime . '</div>
            </div>
            <div class="last-message">' . $messagePreview . '</div>
        </div>';
    }
}
?>
