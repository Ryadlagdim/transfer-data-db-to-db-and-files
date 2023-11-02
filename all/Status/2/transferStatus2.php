<?php

class TransferDataStatusTwo {
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
        
        $checkColumnQuery = "SHOW COLUMNS FROM t_employee_recruitment_mailbox_message_campaign_state_i18n LIKE 'is_processed'";
        $stmtCheckColumn = $this->destinationDB->query($checkColumnQuery);
        $columnExists = $stmtCheckColumn->rowCount() > 0;

        if ($columnExists) {
            echo "<br> Table status 2 has already been transferred. <br><br>";
            return 0;
        } else {
            
            $addIsProcessedColumnQuery = "ALTER TABLE t_employee_recruitment_mailbox_message_campaign_state_i18n ADD COLUMN is_processed BOOLEAN DEFAULT 1";
            $this->destinationDB->exec($addIsProcessedColumnQuery);
        }

       
        $selectQuery = "SELECT id, status_id, lang, value, created_at, updated_at  FROM  t_recruitment_email_message_campaign_status_i18n";
        $stmtSelect = $this->sourceDB->query($selectQuery);
        $data = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

        if (empty($data)) {
            return 0;
        }

        $insertQuery = "INSERT INTO  t_employee_recruitment_mailbox_message_campaign_state_i18n ( id ,state_id, lang, value, created_at, updated_at  ) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtInsert = $this->destinationDB->prepare($insertQuery);

        $successfulTransactions = 0;

        foreach ($data as $row) {
            $params = [
                $row['id'],
                $row['status_id'],
                $row['lang'],
                $row['value'],
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
            echo "OK StatusTwo <br> <br> $successfulTransactions transfer";
        }

        return $successfulTransactions;
    }
}

$transfer = new TransferDataStatusTwo();
$transfer->processDB();
?>
