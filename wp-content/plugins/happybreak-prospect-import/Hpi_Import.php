<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

require_once 'vendor/autoload.php';

use League\Csv\Reader;
use League\Csv\Writer;
use Monolog\Logger;

class Hpi_Import
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private static $rowStructure = array(
        'civility' => 0,
        'fist_name' => 1,
        'last_name' => 2,
        'phone' => 3,
        'address' => 4,
        'zip' => 5,
        'city' => 6,
        'country' => 7,
        'email' => 8
    );

    /**
     * @var string
     */
    private $timestamp;

    function __construct($csvFilePath, $skipLines = 0)
    {

        // Setup upload dir
        if ( ! is_dir(self::getUploadDir())) {
            mkdir(self::getUploadDir());
        }

        if ( ! is_dir(self::getUploadDir())) {
            throw new Exception('Could not create upload directory, please check permissions');
        }

        // Setup timestamp for usage in file names
        $this->timestamp = date('YmdHis');

        // Steup logger
        $this->logger  = new Logger('happybreak-prospect-import');
        $logFileStream = fopen(trailingslashit(self::getUploadDir()) . $this->timestamp . '.log', 'a');
        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler($logFileStream));

        // Init csv file and read records
        $csvFile = Reader::createFromPath($csvFilePath, 'r');
        $records = $csvFile->fetchAll();

        // Skip lines
        $records = array_slice($records, $skipLines);

        $this->import($records);
    }

    private function import($records)
    {
        $currentRowIndex = 1;
        $errorsCount     = 0;
        $invalidRows     = array();

        $this->logger->info('Total rows to handle : ' . count($records));
        foreach ($records as $row) {

            try {

                // Validate line structure
                $this->validateRow($row);

                // Insert user
                $this->insertUser($row);

            } catch (Exception $e) {
                // Save row for later file creation
                $invalidRows[] = $row;
                $errorsCount++;

                // log errors
                $this->logger->error(sprintf('row #%s rejected : %s', $currentRowIndex, $e->getMessage()));
            }

            $currentRowIndex++;
        }

        // Assemble invalid rows in a file
        if ($errorsCount > 0) {
            $writer = Writer::createFromPath(trailingslashit(self::getUploadDir()) . $this->timestamp . '.csv', 'w');
            $writer->insertAll($invalidRows);
        }

        $this->logger->info(sprintf('Total rows handled : %s, total errors : %s', $currentRowIndex - 1, $errorsCount));
    }

    /**
     * @param $row
     *
     * @throws Exception
     */
    private function insertUser($row)
    {
        $email = $row[self::$rowStructure['email']];
        if (empty($email)) {
            $email = $row[self::$rowStructure['phone']] . '@platform.happybreak.com';
        }

        $userID = wp_insert_user(array(
            'user_login' => $email,
            'user_email' => '',
            'first_name' => $row[self::$rowStructure['first_name']],
            'last_name' => $row[self::$rowStructure['last_name']]
        ));

        if (is_wp_error($userID)) {
            /**
             * @var WP_Error $userID
             */
            throw new Exception($userID->get_error_messages());
        } else {
            update_user_meta($userID, 'billing_first_name', $row[self::$rowStructure['first_name']]);
            update_user_meta($userID, 'billing_last_name', $row[self::$rowStructure['last_name']]);
            update_user_meta($userID, 'billing_address_1', $row[self::$rowStructure['address']]);
            update_user_meta($userID, 'billing_postcode', $row[self::$rowStructure['zip']]);
            update_user_meta($userID, 'billing_city', $row[self::$rowStructure['city']]);
            update_user_meta($userID, 'billing_country', 'FR');
            update_user_meta($userID, 'billing_email', $row[self::$rowStructure['email']]);
            update_user_meta($userID, 'billing_phone', $row[self::$rowStructure['phone']]);
            update_user_meta($userID, 'import_date', date('Y-m-d H:i:s'));
        }
    }

    /**
     * @param $row
     *
     * @throws Exception
     */
    private function validateRow($row)
    {
        if (count(self::$rowStructure) != count($row)) {
            throw new Exception(sprintf('Row size is %s, expected %s', count($row), count(self::$rowStructure)));
        }
    }

    public static function getUploadDir()
    {
        $wpUploadDir = wp_upload_dir();

        return $wpUploadDir['basedir'] . '/happybreak-prospects-import/';
    }

    public static function generateTemplateFile()
    {
        $writer = Writer::createFromString('');
        $writer->insertOne(array_keys(self::$rowStructure));
        $writer->output('import-prospects.csv');
    }
}