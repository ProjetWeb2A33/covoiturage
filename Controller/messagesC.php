<?php

include_once(__DIR__ . '/../config.php');

class MessageC
{
    public function ListeMessages()
    {
        $db = config::getConnexion();

        try {
            $query = $db->query('SELECT * FROM messages ORDER BY created_at DESC');
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function DeleteMessage($id)
    {
        $db = config::getConnexion();

        try {
            $db->beginTransaction();

            // Vérifier si le message existe
            $checkReq = $db->prepare('SELECT id FROM messages WHERE id = :id');
            $checkReq->execute(['id' => $id]);
            if (!$checkReq->fetch()) {
                return false;
            }

            // Supprimer le message
            $delReq = $db->prepare('DELETE FROM messages WHERE id = :id');
            $delReq->execute(['id' => $id]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Error in DeleteMessage: ' . $e->getMessage());
            die('Error: ' . $e->getMessage());
            return false;
        }
    }

    public function AjouterMessage(Message $message)
    {
        $db = config::getConnexion();

        try {
            $req = $db->prepare('INSERT INTO messages (content) VALUES (:content)');
            $req->execute([
                'content' => $message->getContent()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function GetMessage($id)
    {
        $db = config::getConnexion();

        try {
            $req = $db->prepare('SELECT * FROM messages WHERE id = :id');
            $req->execute(['id' => $id]);
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function UpdateMessage(Message $message)
    {
        $db = config::getConnexion();

        try {
            $req = $db->prepare('
                UPDATE messages 
                SET content = :content
                WHERE id = :id
            ');
            $req->execute([
                'id' => $message->getID(),
                'content' => $message->getContent()
            ]);
            return true;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
            return false;
        }
    }
}

?>