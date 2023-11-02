<?php

class TransferDataMessage {
    private $sourceDB;
    private $destinationDB;
    private $errors = [];

    public function __construct()
    {
        $this->sourceDB = new PDO('mysql:host=localhost;dbname=site_rh_transfer', 'root', '');
        $this->destinationDB = new PDO('mysql:host=localhost;dbname=site_portal', 'root', '');
    }

    public function processDB()
    {
        
        $checkColumnQuery = "SHOW COLUMNS FROM t_employee_recruitment_mailbox_email_message LIKE 'is_processed'";
        $stmtCheckColumn = $this->destinationDB->query($checkColumnQuery);
        $columnExists = $stmtCheckColumn->rowCount() > 0;

        if ($columnExists) {
            echo "<br> Table Message has already been transferred. <br>";
            return 0;
        } else {
            
            $addIsProcessedColumnQuery = "ALTER TABLE t_employee_recruitment_mailbox_email_message ADD COLUMN is_processed BOOLEAN DEFAULT 1";
            $this->destinationDB->exec($addIsProcessedColumnQuery);
        }

       
        $selectQuery = "SELECT id, mailbox_id, contact_id, uid, subject, email_to, email_from, email_replyto, body, date, is_affected, is_archived, archive_error, status, created_at, updated_at FROM  t_recruitment_mailbox_email_message";
        $stmtSelect = $this->sourceDB->query($selectQuery);
        $data = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

        if (empty($data)) {
            return 0;
        }

        $insertQuery = "INSERT INTO  t_employee_recruitment_mailbox_email_message (id, mailbox_id, contact_id, uid, subject, email_to, email_from, email_replyto, body, date, is_affected, is_archived, archive_error, status, created_at, updated_at ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtInsert = $this->destinationDB->prepare($insertQuery);

        $successfulTransactions = 0;

        foreach ($data as $row) {
            $params = [
                $row['id'],
                $row['mailbox_id'],
                $row['contact_id'],
                $row['uid'],
                $row['subject'],
                $row['email_to'],
                $row['email_from'],
                $row['email_replyto'],
                $row['body'],
                $row['date'],
                $row['is_affected'],
                $row['is_archived'],
                $row['archive_error'],
                $row['status'],
                $row['created_at'],
            ];
            
            if ($stmtInsert->execute($params)) {
                $successfulTransactions++;
            } else {
                $this->errors[] = $stmtInsert->errorInfo();
            }
        }

        if ($successfulTransactions > 0) {
            echo "OK Message <br> <br> $successfulTransactions transfer";
        }

        return $successfulTransactions;
    }
}

$transfer = new TransferDataMessage();
$transfer->processDB();
?>
