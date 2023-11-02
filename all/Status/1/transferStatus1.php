<?php

class TransferDataStatusOne {
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
        
        $checkColumnQuery = "SHOW COLUMNS FROM t_employee_recruitment_mailbox_message_campaign_state LIKE 'is_processed'";
        $stmtCheckColumn = $this->destinationDB->query($checkColumnQuery);
        $columnExists = $stmtCheckColumn->rowCount() > 0;

        if ($columnExists) {
            echo "<br> Table status 1 has already been transferred. <br>";
            return 0;
        } else {
            
            $addIsProcessedColumnQuery = "ALTER TABLE t_employee_recruitment_mailbox_message_campaign_state ADD COLUMN is_processed BOOLEAN DEFAULT 1";
            $this->destinationDB->exec($addIsProcessedColumnQuery);
        }

       
        $selectQuery = "SELECT id, name, color, icon  FROM   t_recruitment_email_message_campaign_status";
        $stmtSelect = $this->sourceDB->query($selectQuery);
        $data = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

        if (empty($data)) {
            return 0;
        }

        $insertQuery = "INSERT INTO  t_employee_recruitment_mailbox_message_campaign_state (id, name, color, icon  ) VALUES (?, ?, ?, ?)";
        $stmtInsert = $this->destinationDB->prepare($insertQuery);

        $successfulTransactions = 0;

        foreach ($data as $row) {
            $params = [
                $row['id'],
                $row['name'],
                $row['color'],
                $row['icon']
            ];

            if ($stmtInsert->execute($params)) {
                $successfulTransactions++;
            } else {
                $this->errors[] = $stmtInsert->errorInfo();
            }
        }

        if ($successfulTransactions > 0) {
            echo "OK StatusOne <br> <br> $successfulTransactions transfer";
        }

        return $successfulTransactions;
    }
}

$transfer = new TransferDataStatusOne();
$transfer->processDB();
?>
