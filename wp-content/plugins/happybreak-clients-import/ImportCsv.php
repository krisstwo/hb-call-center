<?php

/**
 * Class ImportCsv
 * class to import csv file
 * insert content in db
 * create user
 */
class ImportCsv
{
    /**
     * @var string
     */
    private $filePath;
    /**
     * @var string
     */
    private $fileName;

    function __construct()
    {
        $this->filePath = WP_CONTENT_DIR . '/uploads/import-clients/';
        $this->fileName = $this->filePath . 'customer.csv';

        // check if directory upload exist
        if (!$this->folder_exist($this->filePath)) {
            throw new Exception("{$this->filePath} => directory was not found  ");
        } // check if file exist
        else if (!file_exists($this->fileName)) {
            throw new Exception("{$this->fileName} => file  was not found");
        } else if (!is_readable($this->fileName)) {
            throw new Exception("{$this->fileName} => file not readable check permission ");
        }
        $this->runScript();

    }

    /**
     * run script import
     */
    public function runScript()
    {
        require_once(PLUGIN_PATH . 'LogFile.php');

        // instance log
        $log = new LogFile();
        $log->lfile(PLUGIN_PATH . '/log/logfile.txt');

        $currentLineIndex = 0;
        $numberUserAdded = 0;
        $numberUserNotAdded = 0;
        $errorLigneStructure = 1;
        $getArrayCsv = $this->readCSV($this->fileName);
        if (!empty($getArrayCsv)) {
            $totalLinesCount = count($getArrayCsv) - 1;
            foreach ($getArrayCsv as $row) {
                if (!empty($row)) {
                    $rowUser = $this->checkUserRow($row);

                    if ($rowUser == false) {
                        $errorLigneStructure++;
                        $errorLigne = $currentLineIndex;
                        $log->lwrite('Error structure ligne number = ' . $errorLigne);

                        echo "<span style='position: absolute;z-index:$currentLineIndex;background:#d3135a; color: #ffffff'>Error structure ligne ==>  $errorLigne</span><br><br><br>";

                    }
                    $addUser = $this->addUser($rowUser);
                    if ($addUser == false) {
                        $numberUserNotAdded++;
                        $errorInsert = $currentLineIndex;
                        $log->lwrite('Error insertion ligne ' . $currentLineIndex);
                        // show message in front
                        echo "<span style='position: absolute;z-index:$currentLineIndex;background:#602053;  color: #ffffff'>Error insertion ligne  ==>  $errorInsert</span><br><br>";
                    } else {
                        $numberUserAdded++;

                        echo "<span style='position: absolute;z-index:$currentLineIndex;background:#006505; color: #ffffff'>Ligne ajoutée (index) ==>  $numberUserAdded</span><br><br><br>";
                    }
                    $currentLineIndex++;
                    $this->outputProgress($currentLineIndex, $totalLinesCount);
                }

            }
            exit();
        }

    }

    /**
     * convert csv to array
     * @param $csvFile
     * @return array
     */
    public function readCSV($csvFile)
    {
        $file_handle = fopen($csvFile, 'r');
        fgetcsv($file_handle);
        while (!feof($file_handle)) {
            $line_of_text[] = fgetcsv($file_handle, 1024, ';');
        }
        fclose($file_handle);
        return $line_of_text;
    }

    /**
     * add user into db
     * @param $user_info
     * @return bool
     */
    private function addUser($user_info)
    {
        $user_email = $user_info['Adresse email'];
        if (empty($user_email)) {
            $user_email = $user_info['Téléphone'] . '@platform.happybreak.com';
        }

        $userID = wp_insert_user(array(
            'user_login' => $user_email,
            'user_email' => '',
            'first_name' => $user_info['Prénom'],
            'last_name' => $user_info['Nom']
        ));
        if (is_wp_error($userID)) {
            return false;
        } else {
            update_user_meta($userID, 'billing_first_name', $user_info['Prénom']);
            update_user_meta($userID, 'billing_last_name', $user_info['Nom']);
            update_user_meta($userID, 'billing_address_1', $user_info['Adresse']);
            update_user_meta($userID, 'billing_postcode', $user_info['Code postal']);
            update_user_meta($userID, 'billing_city', $user_info['Ville']);
            update_user_meta($userID, 'billing_country', 'FR');
            update_user_meta($userID, 'billing_email', $user_info['Adresse email']);
            update_user_meta($userID, 'billing_phone', $user_info['Téléphone']);

            return $userID;
        }
    }

    /**
     * add user meta
     * @param $metaInfo
     * @param $user_id
     */
    private function addUserMeta($metaInfo, $user_id)
    {

        $user_meta_fields = array('billing_first_name', 'billing_last_name', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_postcode', 'billing_country', 'billing_email');
        $arrayWithValue = array_combine($user_meta_fields, $metaInfo);

        foreach ($user_meta_fields as $user_meta_field) {

            update_user_meta($user_id, $user_meta_field, $arrayWithValue[$user_meta_field]);
        }

    }

    /**
     * @param $folder
     * @return bool|string
     */
    private function folder_exist($folder)
    {
        // Get canonicalized absolute pathname
        $path = realpath($folder);

        // If it exist, check if it's a directory
        if ($path !== false AND is_dir($path)) {
            // Return canonicalized absolute pathname
            return $path;
        }

        // Path/folder does not exist
        return false;
    }

    /**
     * show progress bar
     * @param $current
     * @param $total
     */
    private function outputProgress($current, $total)
    {
        echo "<span style='position: absolute;z-index:$current;background:#FFF;'>  Script runing  === > " . round($current / $total * 100) . "  % </span>";
        $this->myFlush();
    }

    /**
     * Flush output buffer
     */
    private function myFlush()
    {
        echo(str_repeat(' ', 256));
        if (@ob_get_contents()) {
            @ob_end_flush();
        }
        flush();
    }

    /**
     * check structure user
     * @param $row
     * @return array|bool
     */
    private function checkUserRow($row)
    {

        $structureUser = array('Civilité', 'Prénom', 'Nom', 'Téléphone', 'Adresse', 'Code postal', 'Ville', 'Pays', 'Adresse email');
        // check structure 2 table
        if (count($structureUser) != count($row)) {
            return false;
        }

        $result = array_combine($structureUser, $row);

        return $result;
    }


}