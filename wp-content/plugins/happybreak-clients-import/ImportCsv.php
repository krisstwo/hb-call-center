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
        require_once(CLIENT_IMPORT . 'LogFile.php');

        // instance log
        $log = new LogFile();
        $log->lfile(CLIENT_IMPORT . '/log/logfile.txt');

        $current = 0;
        $numberUserAdded = 0;
        $numberUserNotAdded = 0;
        $errorLigneStructure = 0;
        $getArrayCsv = $this->readCSV($this->fileName);
        if (!empty($getArrayCsv)) {
            $nbElement = count($getArrayCsv) - 1;
            foreach ($getArrayCsv as $row) {
                if (!empty($row)) {
                    $segmentUser = array_slice($row, 0, 6);
                    $segmentBuiling = array_slice($row, 7);

                    $rowUser = $this->checkUserRow($segmentUser);

                    if ($rowUser == false) {
                        $errorLigneStructure++;
                        $errorLigne = $current + 1;
                        $log->lwrite('Error structure ligne number = ' . $errorLigne);

                        echo "<span style='position: absolute;z-index:$current;background:#d3135a; color: #ffffff'>Error structure ligne ==>  $errorLigne</span><br><br><br>";

                    }
                    $addUser = $this->addUser($rowUser);
                    if ($addUser == false) {
                        $numberUserNotAdded++;
                        $errorInsert = $current + 1;
                        $log->lwrite('Error insertion ligne ' . $current);
                        // show message in front
                        echo "<span style='position: absolute;z-index:$current;background:#602053;  color: #ffffff'>Error insertion ligne  ==>  $errorInsert</span><br><br>";
                    } else {
                        // add info builling
                        $this->addUserMeta($segmentBuiling, $addUser);
                        $numberUserAdded++;

                        echo "<span style='position: absolute;z-index:$current;background:#006505; color: #ffffff'>Ligne ajouter  ==>  $numberUserAdded</span><br><br><br>";
                    }
                    $current++;
                    $this->outputProgress($current, $nbElement);
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

        $insert_user_result = wp_insert_user($user_info);
        if (is_wp_error($insert_user_result)) {
            return false;
        } else {
            return $insert_user_result;
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
        sleep(1);
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

        $structureUser = array('nickname', 'first_name', 'last_name', 'user_email', 'user_login', 'display_name');
        // check structure 2 table
        if (count($structureUser) != count($row)) {
            return false;
        }

        $result = array_combine($structureUser, $row);

        return $result;
    }


}