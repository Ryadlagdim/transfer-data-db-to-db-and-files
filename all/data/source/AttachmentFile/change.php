<?php


$destinationDir = 'new/admin/data/employee/recruitment/servers/1/mailboxes/1/messages/attachments/';

if (!is_dir($destinationDir)) {
    if (mkdir($destinationDir, 0755, true)) { 
        echo "Created destination directory: $destinationDir.<br>";
    } else {
        echo "Error creating destination directory: $destinationDir.<br>";
        exit;
    }

}

$successCount = 0;
$errorCount = 0;
$skippedCount = 0;

for ($i = 306; $i <= 389; $i++) {
    if ($i == 369 || $i == 342) {
        $skippedCount++;
        continue;
    }

    $oldPath = './data/destination/frontend/view/messages/attachments/' . $i;
    $newPath = $destinationDir . $i;

    if (is_dir($oldPath)) {
        if (!is_dir($newPath)) {
            if (mkdir($newPath, 0755, true)) { 
                $successCount++;
            } else {
                echo "Error creating folder '$i' in the destination directory.<br>";
                $errorCount++;
                continue;
            }

            $files = glob($oldPath . '/*');
            foreach ($files as $file) {
                $fileInfo = pathinfo($file);
                $newFile = $newPath . '/' . $fileInfo['basename'];
                if (rename($file, $newFile)) {
                    echo "Moved file: " . $fileInfo['basename'] . " to $newFile.<br>";
                    $successCount++;
                } else {
                    echo "Error moving file: " . $fileInfo['basename'] . " to $newFile.<br>";
                    $errorCount++;
                }
            }
        } else {
            echo "Folder '$i' already exists in the destination directory.<br>";
            $skippedCount++;
        }
    } else {
        echo "Source folder '$i' does not exist.<br>";
        $skippedCount++;
    }
}

echo "Summary: $successCount items successfully transferred, $errorCount errors, $skippedCount items skipped.";
