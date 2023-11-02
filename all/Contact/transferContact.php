<?php

class TransferDataContact {
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
        
        $checkColumnQuery = "SHOW COLUMNS FROM t_employee_recruitment_contact LIKE 'is_processed'";
        $stmtCheckColumn = $this->destinationDB->query($checkColumnQuery);
        $columnExists = $stmtCheckColumn->rowCount() > 0;

        if ($columnExists) {
            echo "<br> Table contact has already been transferred. <br>";
            return 0;
        } else {
            
            $addIsProcessedColumnQuery = "ALTER TABLE t_employee_recruitment_contact ADD COLUMN is_processed BOOLEAN DEFAULT 1";
            $this->destinationDB->exec($addIsProcessedColumnQuery);
        }

       
        $selectQuery = "SELECT id, gender, firstname, lastname, email, name, picture, mobile1, mobile2, phone1, phone2, city, birthday, number_of_campaign, status, created_at, updated_at FROM  t_employees_recruitment_contact";
        $stmtSelect = $this->sourceDB->query($selectQuery);
        $data = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

        if (empty($data)) {
            return 0;
        }

        $insertQuery = "INSERT INTO  t_employee_recruitment_contact (id, gender, firstname, lastname, email, name, picture, mobile1, mobile2, phone1, phone2, city, birthday, number_of_campaigns, status, created_at, updated_at, is_processed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtInsert = $this->destinationDB->prepare($insertQuery);

        $successfulTransactions = 0;

        foreach ($data as $row) {
            $params = [
                $row['id'],
                $row['id'],
                $row['gender'],
                $row['firstname'],
                $row['lastname'],
                $row['email'],
                $row['name'],
                $row['picture'],
                $row['mobile1'],
                $row['mobile2'],
                $row['phone1'],
                $row['phone2'],
                $row['city'],
                $row['birthday'],
                $row['number_of_campaign'],
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
            echo "OK contact <br> <br> $successfulTransactions transfer";
        }

        return $successfulTransactions;
    }
}

$transfer = new TransferDataContact();
$transfer->processDB();
?>
