<?php

class TransferDataAttachmentDB {
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
        
        $checkColumnQuery = "SHOW COLUMNS FROM t_employee_recruitment_mailbox_email_attachment LIKE 'is_processed'";
        $stmtCheckColumn = $this->destinationDB->query($checkColumnQuery);
        $columnExists = $stmtCheckColumn->rowCount() > 0;

        if ($columnExists) {
            echo "<br> Table attachmentdb has already been transferred. <br>";
            return 0;
        } else {
            
            $addIsProcessedColumnQuery = "ALTER TABLE t_employee_recruitment_mailbox_email_attachment ADD COLUMN is_processed BOOLEAN DEFAULT 1";
            $this->destinationDB->exec($addIsProcessedColumnQuery);
        }

       
        $selectQuery = "SELECT id, filename, ext, size, message_id, status, created_at, updated_at FROM t_recruitment_mailbox_email_attachment";
        $stmtSelect = $this->sourceDB->query($selectQuery);
        $data = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

        if (empty($data)) {
            return 0;
        }

        $insertQuery = "INSERT INTO t_employee_recruitment_mailbox_email_attachment (id, extension, file, size, message_id, status, created_at, updated_at, is_processed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtInsert = $this->destinationDB->prepare($insertQuery);


        $successfulTransactions = 0;

        foreach ($data as $row) {
            $params = [
                $row['id'],
                $row['ext'],
                $row['filename'],
                $row['size'],
                $row['message_id'],
                $row['status'],
                $row['created_at'],
                $row['updated_at']
            ];

            if ($stmtInsert->execute($params)) {
                $successfulTransactions++;
            } else {
                $this->errors[] = $stmtInsert->errorInfo();
            }
        }

        if ($successfulTransactions > 0) {
            echo "OK Attachement <br> <br> $successfulTransactions transfer";
        }

        return $successfulTransactions;
    }
}

$transfer = new TransferDataAttachmentDB();
$transfer->processDB();
?>
