<?php

//DB connect
require_once __DIR__ . '/dbpass.php';

//Function, that sends erorrs to admin
require_once __DIR__ . '/err_func.php';

// Functions for both WEB and SFTP parts
require_once __DIR__ . '/../../common/common_functions.php';
require_once __DIR__ . '/../../common/classes/AvoidDoubleStart.php';
require_once __DIR__ . '/../../common/constants.php';

// Variables
//SCANING DIRECTORY TO FIND FILES
$dir_root = __DIR__ . "/../../RRM/CLIENTS/";
$rrm_answer = __DIR__ . '/rrm_xml_answer/rrm_answer.php';
/*
    Common variables for document
*/
$rrmName = 'B0001064H.DE';
$today = date("Ymd");
$extenXML = ".xml";
$extenHTML = ".html";
$downLine = "_";
$version1 = "_V1";
$strt_day = START_DATE;
$end_date = END_DATE;

/**
 * Prohodim po naidenym failam i zapisyfaem v $file
 */
$schemeName = "REMITTable1_V1";
$nstdScheme = "REMITTable2_V1";
$rmt_artcl = "REMITArticle3_V1";
$elctrBid = "ElectricityBid_V1";
$elctrRights = "ElectricityRights_V1";
$elctrAll = "ElectricityTotalAllocation_V1";
$gasCap = "GasCapacity_V1";
$elctrNom = "ElectricityNomination_V1";
$gasTrnsp = "GasTransparency_V1";
$gas_nom = "GasNomination_V1";
$lng = "REMITLNG_V1";
$remit_storage = "REMITStorage_V1";
$elctr_pub = "ElectricityPublication_V1";
$elctr_conf = "ElectricityConfiguration_V1";
$elctr_gen = "ElectricityGenerationLoad_V1";
$elctr_out = "ElectricityOutage_V1";
$stdscheme2 = "REMITTable1_V2";
$stdscheme3 = "REMITTable1_V3";
$inc_path = __DIR__ . "/INCLUDE/REMITTable1.php";
$inc_path1 = __DIR__ . "/INCLUDE/REMITTable2.php";
$inc_path2 = __DIR__ . "/INCLUDE/ElectricityBid.php";
$inc_path3 = __DIR__ . "/INCLUDE/ElectricityRights.php";
$inc_path4 = __DIR__ . "/INCLUDE/ElectricityTotalAllocation.php";
$inc_path5 = __DIR__ . "/INCLUDE/GasCapacity.php";
$inc_path6 = __DIR__ . "/INCLUDE/ElectricityNomination.php";
$inc_path7 = __DIR__ . "/INCLUDE/GasTransparency.php";
$inc_path8 = __DIR__ . "/INCLUDE/GasNomination.php";
$inc_path9 = __DIR__ . "/INCLUDE/REMITLNGSchema.php";
$inc_path10 = __DIR__ . "/INCLUDE/REMITStorageSchema.php";
$inc_path11 = __DIR__ . "/INCLUDE/ElectricityPublication.php";
$inc_path12 = __DIR__ . "/INCLUDE/ElectricityConfiguration.php";
$inc_path13 = __DIR__ . "/INCLUDE/ElectricityGenerationLoad.php";
$inc_path14 = __DIR__ . "/INCLUDE/ElectricityOutage.php";
$inc_path15 = __DIR__ . "/INCLUDE/REMITArticle3.php";
$inc_path16 = __DIR__ . "/INCLUDE/REMITTable1_V2.php";
$inc_path17 = __DIR__ . "/INCLUDE/REMITTable1_V3.php";
$inc_path18 = __DIR__ . "/INCLUDE/GasCapacity_V2.php";

//Array to check schema type
$schema_arr = [
//    "schemeName" => "REMITTable1_V1",
    "nstdScheme" => "REMITTable2_V1",
    "rmt_artcl" => "REMITArticle3_V1",
    "elctrBid" => "ElectricityBid_V1",
    "elctrRights" => "ElectricityRights_V1",
    "elctrAll" => "ElectricityTotalAllocation_V1",
    "gasCap" => "GasCapacity_V1",
    "gasCap2" => SCHEMA_GAS_CAPACITY_V2,
    "elctrNom" => "ElectricityNomination_V1",
    "gasTrnsp" => "GasTransparency_V1",
    "gas_nom" => "GasNomination_V1",
    "lng" => "REMITLNG_V1",
    "remit_storage" => "REMITStorage_V1",
    "elctr_pub" => "ElectricityPublication_V1",
    "elctr_conf" => "ElectricityConfiguration_V1",
    "elctr_gen" => "ElectricityGenerationLoad_V1",
    "elctr_out" => "ElectricityOutage_V1",
    "stdscheme2" => "REMITTable1_V2",
    "stdscheme3" => "REMITTable1_V3"
];


//Functions
/*Function to check XML with XSD*/
function libxml_display_error($error)
{
    $return = "\n";
    switch ($error->level) {
        case LIBXML_ERR_WARNING:
            $return .= "<b><font size='3' color='yellow' face='Arial'>Warning $error->code</font></b>: ";
            break;
        case LIBXML_ERR_ERROR:
            $return .= "<b><font size='3' color='orange' face='Arial'>Error $error->code</font></b>: ";
            break;
        case LIBXML_ERR_FATAL:
            $return .= "<b><font size='3' color='red' face='Arial'>Fatal Error $error->code</font></b>: ";
            break;
        default:
            break;
    }
    $return .= trim($error->message);
    if ($error->file) {
        $err_fl = $error->file;
        $err_xpld = explode("/", $err_fl);
        $rslt_err = (string)$err_xpld[8];
        $return .= " in <b>$rslt_err</b>";
    }
    $return .= " on line <b>$error->line</b><br>\n";

    return $return;
}

function libxml_display_errors()
{
    $errors = libxml_get_errors();
    foreach ($errors as $error) {

        print libxml_display_error($error);
        $error1 = libxml_display_error($error);
        /*Creates tmp file tmp.html with errors codes*/
        $f = fopen(__DIR__ . "/TMP/tmp.html", "a+");
        fwrite($f, print_r($error1, true));
        fclose($f);
    }
    libxml_clear_errors();
}

// Main Code.

$lock = new AvoidDoubleStart(__FILE__);

if (!$lock->isFileBusy()) {
    $lock->setBusy();
} else {

    if ($lock->isScriptBusyTooLong()) {
        mailLog("Script has been set free because it is busy too long. ", __FILE__ . __LINE__, LOCK_SCRIPT_ERROR);
        $lock->setFree();
    }
    exit;
}

// Enable user error handling
libxml_use_internal_errors(true);

$files_clnt = array_diff(scandir($dir_root, 1), array('..', '.'));
echo "dir_root:\n";
print_r($files_clnt);
echo "\n";

/*
Remove unnecessary rows
*/
natsort($files_clnt);
echo "\n";
print_r($files_clnt);

/*
Take file explode and save variable to $id_office
*/
foreach ($files_clnt as $file_clnt) {

    echo "\n";
    echo $file_clnt;
    echo "\n";
    $expld_id_office = explode("_", $file_clnt);
    $id_office = (int)$expld_id_office[0];
    echo "\n $id_office \n";

    /*
    Find the path to the client from DB
    */
    $sql_clnt = "SELECT sftp_path FROM `OFFICES` WHERE `ID_OFFICE` = '$id_office'";
    $row_clnt = oneRow($sql_clnt, __FILE__);

    $path_to_clnt = (string)$row_clnt['sftp_path'];
    echo "\n Path to client folder:  " . $path_to_clnt . "\n";

    //SCANING CLIENT DIRECTORY TO FIND THE FILES
    $cust_sftp_in_full_path = "$path_to_clnt/CUST_SFTP_IN/";
    echo "$cust_sftp_in_full_path\n";

    $customers_files = array_diff(scandir($cust_sftp_in_full_path, 1), array('..', '.'));
    print_r($customers_files);

    /*
    Remove unnecessary rows
    */
    natsort($customers_files);
    echo "customers_files:\n";
    print_r($customers_files);
    echo "\n";

    //Checks what schemas client can download
    $sql_opt = "SELECT * FROM options WHERE office_id = '$id_office'";
    $rslt_opt = querySql($sql_opt, __FILE__);
    //Fetch assoc array
    $option_schema = [];
    foreach ($rslt_opt as $rows_opt) {
        echo $rows_opt['key'] . "\n";
        $option_schema[] = $rows_opt['key'];
    }

    foreach ($customers_files as $file) {
        echo "\n$file\n";
        $expl = explode("_", $file);

        //date of file -> change to any integer
        $schema_date = $first_part_file_name = $expl[0];
        $schema_name = (string)$expl[1];
        $schema_version = (string)$expl[2];
        $fileScheme = $schema_name . "_" . $schema_version;

        //Explode with dot and find last element of array
        $dot_expld = explode(".", $file);
        //Last element of array
        $file_ext = array_pop($dot_expld);

        //Vars for RRM_XML_ANSWER (Receipt)
        $path_to_rrm_resp = $path_to_clnt . "/RRM_RESPONSE/";
        $path_to_rrm_out = $path_to_clnt . "/CUST_SFTP_OUT/";
        $xml_name = "ReceiptRRM_" . $file;
        $schema_for_answr = $schema_name;
        $rrm_status = "Rejected_RRM";
        $dom_attr = 'http://www.acer.europa.eu/REMIT/REMITReceiptSchema_V1.xsd';
        $validation = "technical_rrm";

        //Checks file Extention
        if ($file_ext == "xml") {
            // Duplicated file
            if (!uniqueXml($file, $id_office)) {
                $id_errorcode = 4;
                require $rrm_answer;
                //Transfer file to  folder
                renameWithMaillogIfError("${path_to_clnt}/CUST_SFTP_IN/", $file, "${path_to_clnt}/DUPLICATED_FILES/", __FILE__, __LINE__);
                echo PHP_EOL . "==============DUPLICATED_FILE==============" . PHP_EOL;
            } else {

                //Check first part of file name
                if (is_numeric($first_part_file_name)) {

                    echo "\nSchema: " . $fileScheme . "\n";
                    echo "\nSchema name: " . $schema_name . "\n";
                    console_debug($schema_arr, 'ALL SCHEMA');
                    console_debug($option_schema, 'OPTION SCHEMA FOR OFFICE ' . $id_office);

                    //Check the schema name in filename
                    if (in_array($fileScheme, $schema_arr)) {

                        if (in_array($schema_name, $option_schema)) {

                            echo "\n" . "Scheme name: " . $fileScheme . "\n";
                            $istrue = "true";
                            $sql7 = "SELECT if(COUNT(*)>0,'false','true') AS my_bool FROM `ACER_XML` where fileName = '$file' and ID_OFFICE = '$id_office'";
                            $result7 = querySql($sql7, __FILE__);

                            foreach ($result7 as $row) {

                                $bl = (string)$row['my_bool'];
                                echo "\nBool is: $bl\n";
                            }

                            /** If: office have  option ONLY_ELCOM */
                            if (hasOption($id_office, ONLY_ELCOM, __FILE__)) {
                                $bl = uniqueXmlElcomTbl($file, $id_office, __FILE__);
                            }

                            //VARs
                            $filename_with_full_path = $cust_sftp_in_full_path . $file;
                            $rrm_id_err = 0;
                            $eic_id = 0;
                            $delivery_point = 0;
                            $details = '';
                            $add_details = '';
                            $transportation_ceremp_error = 0;

                            //Require script that checks allow Market Participant to this office AND reportingEntityID
                            if (($fileScheme == "REMITTable1_V1") || ($fileScheme == "REMITTable1_V2") || ($fileScheme == "REMITTable1_V3")) {

                                //If office has this option not RRM  ID or EIC ID
                                if (!hasOption($id_office, DISABLE_CHECK_RRMID, __FILE__)) {
                                    echo "\n ***** CHECK RRM ID in REMITTable1_V1 ****** \n";
                                    require __DIR__ . '/validation/rrm_id/remittable1.php';
                                }

                                //If office has this option not check delivery point or zone
                                if (!hasOption($id_office, DISABLE_CHECK_DELIVERY_PZ, __FILE__)) {
                                    echo "\n ***** CHECK DELIVERY POINT OR ZONE in REMITTable1_V1 ****** \n";
                                    require __DIR__ . '/validation/eic_code/remittable1.php';
                                }

                                if (!hasOption($id_office, DISABLE_CHECK_CEREMP, __FILE__)) {

                                    // check if client EIC is registered in CEREMP
                                    echo "\n ***** Checking CEREMP ****** \n";
                                    $eics_array = getCodesFromFileForCerempValidation($filename_with_full_path, $fileScheme);
                                    $ceremp_data = validateCodesByCerempRules($eics_array, $fileScheme);

                                    echo "EICS in file - \n";

                                    $ceremp_error = $ceremp_data['error_status'];
                                    $ceremp_err_arr = $ceremp_data['error_codes'];

                                    echo "*** REMITTable1_V1 ceremp_error = " . $ceremp_error . " *** \n";
                                }
                            }

                            //Require script that checks allow Market Participant to this office AND reportingEntityID
                            if ($fileScheme == "REMITTable2_V1") {

                                //If office has this option not RRM  ID or EIC ID
                                if (!hasOption($id_office, DISABLE_CHECK_RRMID, __FILE__)) {
                                    echo "\n ***** CHECK RRM ID in REMITTable2_V1 ****** \n";
                                    require __DIR__ . '/validation/rrm_id/remittable2.php';
                                }

                                //If office has this option not check delivery point or zone
                                if (!hasOption($id_office, DISABLE_CHECK_DELIVERY_PZ, __FILE__)) {
                                    echo "\n ***** CHECK DELIVERY POINT OR ZONE in REMITTable2_V1 ****** \n";
                                    require __DIR__ . '/validation/eic_code/remittable2.php';
                                }

                                if (!hasOption($id_office, DISABLE_CHECK_CEREMP, __FILE__)) {

                                    $eics_array = getCodesFromFileForCerempValidation($filename_with_full_path, $fileScheme);
                                    $ceremp_data = validateCodesByCerempRules($eics_array, $fileScheme);

                                    $ceremp_error = $ceremp_data['error_status'];
                                    $ceremp_err_arr = $ceremp_data['error_codes'];
                                }
                            }

                            //Require script that checks RRMID in REMITStorage_V1
                            if ($fileScheme == "REMITStorage_V1") {

                                //If office has this option not RRM  ID or EIC ID
                                if (!hasOption($id_office, DISABLE_CHECK_RRMID, __FILE__)) {
                                    echo "\n ***** CHECK RRM ID in REMITStorage_V1 ****** \n";
                                    require __DIR__ . '/validation/rrm_id/remitstorage.php';
                                }
                            }

                            //Require script that checks EIC ID in GasNomination_V1
                            if ($fileScheme == "GasNomination_V1") {

                                //If office has this option not RRM  ID or EIC ID
                                if (!hasOption($id_office, DISABLE_CHECK_RRMID, __FILE__)) {
                                    echo "\n ***** CHECK EIC ID in GasNomination_V1 ****** \n";
                                    require __DIR__ . '/validation/rrm_id/gas_nomination.php';
                                }
                            }

                            //Require script that checks EIC ID in ElectricityNomination_V1
                            if ($fileScheme == "ElectricityNomination_V1") {

                                //If office has this option not RRM  ID or EIC ID
                                if (!hasOption($id_office, DISABLE_CHECK_RRMID, __FILE__)) {
                                    echo "\n ***** CHECK EIC ID in ElectricityNomination_V1 ****** \n";
                                    require __DIR__ . '/validation/rrm_id/electr_nomination.php';
                                }
                            }

                            // Gas Capacity section validation
                            if ($fileScheme == SCHEMA_GAS_CAPACITY_V1 || $fileScheme == SCHEMA_GAS_CAPACITY_V2) {

                                // script that checks WEBWARE EIC ID in GasCapacity_V1
                                //If office has this option not RRM  ID or EIC ID
                                if (!hasOption($id_office, DISABLE_CHECK_RRMID, __FILE__)) {
                                    echo "\n ***** CHECK EIC ID in GasCapacity_V1 ****** \n";
                                    require __DIR__ . '/validation/rrm_id/gas_capacity.php';
                                }

                                if (!hasOption($id_office, DISABLE_CHECK_CEREMP, __FILE__)) {

                                    // check if client EIC is registered in CEREMP
                                    echo "\n ***** Checking CEREMP  ****** \n";

                                    $eics_array = getCodesFromFileForCerempValidation($filename_with_full_path, $fileScheme);
                                    $ceremp_data = validateCodesByCerempRules($eics_array, $fileScheme);

                                    $ceremp_error = $ceremp_data['error_status'];
                                    $ceremp_err_arr = $ceremp_data['error_codes'];

                                    if (!isset($ceremp_error)) {
                                        $ceremp_error = 0;
                                    }
                                }
                            }

                            // errors
                            if ($rrm_id_err == 1) {

                                $id_errorcode = 15;
                                require $rrm_answer;

                                if (!copy("${path_to_clnt}/CUST_SFTP_IN/${file}", "${path_to_clnt}/ORIG_XML/${file}")) {
                                    mailLog("Can't copy file!<br>File name: ${file}<br>From: ${path_to_clnt}/CUST_SFTP_IN/<br/>To:${path_to_clnt}/ORIG_XML/", __FILE__ . __LINE__, CEREMP_INFO);
                                    echo "Can't copy ${file}..." . PHP_EOL;
                                }

                                //Transfer file to  folder
                                renameWithMaillogIfError($path_to_clnt . "/CUST_SFTP_IN/", $file, $path_to_clnt . "/INVALID_RRM_CODE/", __FILE__, __LINE__);
                            }

                            if ($eic_id == 1) {

                                $id_errorcode = 16;
                                require $rrm_answer;

                                if (!copy("${path_to_clnt}/CUST_SFTP_IN/${file}", "${path_to_clnt}/ORIG_XML/${file}")) {
                                    mailLog("Can't copy file!<br>File name: ${file}<br>From: ${path_to_clnt}/CUST_SFTP_IN/<br/>To:${path_to_clnt}/ORIG_XML/", __FILE__ . __LINE__, CEREMP_INFO);
                                    echo "Can't copy ${file}..." . PHP_EOL;
                                }

                                //Transfer file to INVALID_SCHEMA folder
                                renameWithMaillogIfError($path_to_clnt . "/CUST_SFTP_IN/", $file, $path_to_clnt . "/INVALID_RRM_CODE/", __FILE__, __LINE__);
                            }

                            if ($delivery_point == 1) {

                                $error_codes = implode("\n", $delivery_pz_err);
                                $error_codes_br = implode("<br>", $delivery_pz_err);
                                $error_codes_n = implode("\n", $delivery_pz_err);


                                $log_text = '
									Error codes:
									' . $error_codes_br . '<br>
									Filename: ' . $file;

                                createLog(2, $log_text, 2, __FILE__, $id_office);

                                /**
                                 * Generate additional info for user
                                 */

                                $add_details = "\n" . "Error codes:" . "\n";
                                $add_details .= $error_codes_n;
                                $add_details = ''; // Comment this line , when after test period

                                $id_errorcode = 17;
                                require $rrm_answer;

                                if (!copy("${path_to_clnt}/CUST_SFTP_IN/${file}", "${path_to_clnt}/ORIG_XML/${file}")) {
                                    mailLog("Can't copy file!<br>File name: ${file}<br>From: ${path_to_clnt}/CUST_SFTP_IN/<br/>To:${path_to_clnt}/ORIG_XML/", __FILE__ . __LINE__, CEREMP_INFO);
                                    echo "Can't copy ${file}..." . PHP_EOL;
                                }

                                //Transfer file to INVALID_SCHEMA folder
                                renameWithMaillogIfError($path_to_clnt . "/CUST_SFTP_IN/", $file, $path_to_clnt . "/INVALID_RRM_CODE/", __FILE__, __LINE__);
                            }

                            if ($ceremp_error == 1) {

                                echo "\n ***** CEREMP Error == $ceremp_error ****** \n";
                                echo "\n ***** WRONG EICS:  ****** \n";

                                /**
                                 * Generate additional info for user
                                 */
                                $add_details = "\n" . "Error codes:" . "\n";

                                $ceremp_error_codes_br = "";
                                $codes = [];
                                $error_true = 0;
                                $warning_true = 1;
                                foreach ($ceremp_err_arr as $key => $field) {
                                    if ($field['status'] == 1) $error_true = 1;
                                    if ($field['status'] == 2) $warning_true = 0;
                                    echo "field => ${key} - ${field['type']}";
                                    foreach ($field['codes'] as $wrong_eic) {
                                        echo "\n $wrong_eic \n";
                                        $add_details .= $wrong_eic . PHP_EOL;
                                        $ceremp_error_codes_br .= $wrong_eic . PHP_EOL;
                                        $codes[] = $wrong_eic;
                                    }
                                }

                                echo "error_true => ";
                                var_dump($error_true);
                                echo "warning_true => ";
                                var_dump($warning_true);
                                $ceremp_error = !(bool)$warning_true && !(bool)$error_true ? 0 : 1;
                                echo "ceremp_error => ";
                                var_dump($ceremp_error);

                                if ($ceremp_error == 1) {
                                    $id_errorcode = 23;
                                    require $rrm_answer;

                                    if (!copy("${path_to_clnt}/CUST_SFTP_IN/${file}", "${path_to_clnt}/ORIG_XML/${file}")) {
                                        mailLog("Can't copy file!<br>File name: ${file}<br>From: ${path_to_clnt}/CUST_SFTP_IN/<br/>To:${path_to_clnt}/ORIG_XML/", __FILE__ . __LINE__, CEREMP_INFO);
                                        echo "Can't copy ${file}..." . PHP_EOL;
                                    }

                                    renameWithMaillogIfError($path_to_clnt . "/CUST_SFTP_IN/", $file, $path_to_clnt . "/INVALID_MP/", __FILE__, __LINE__);

                                    $path = "$path_to_clnt/ORIG_XML/";
                                    $timestamp = date('Y-m-d G:i:s');
                                    $timestamp1 = date('Y-m-d G:i:s');

                                    $idStep = 2;

                                    $progressStatus = "RRMrejected";
                                    $fileNoExt = substr($file, 0, -4);
                                    $fileNew = $fileNoExt . $downLine . $progressStatus . $extenHTML;
                                    $pathRJCTfile = "$path_to_clnt/RRM_RESPONSE/";

                                    /*Query to Write to ACER_XML table*/
                                    $sqlRj = "INSERT INTO ACER_XML (ID_OFFICE, fileName, path, receivedTime, schemaType, ID_STEP, progressStatus, progressTime)
                                          VALUES ('$id_office', '$file', '$path', '$timestamp', '$fileScheme', '$idStep', '$progressStatus', '$timestamp1')";
                                    $resultRj = mysqli_query($dbconn, $sqlRj) or die(mailLog(mysqli_error($dbconn) . "<br>" . "SQL: " . $sqlRj, __FILE__ . __LINE__, MYSQL_ERROR));

                                    /*Query to Write to FILE_HIST table*/
                                    $sqlRj1 = "INSERT INTO FILE_HIST (ID_LOAD, ID_STEP, SCHEME, NAME, URL, STATUS, CREATED_TIME)
                                            VALUES ((SELECT max(ID_LOAD) FROM ACER_XML WHERE fileName = '$file' and ID_OFFICE = '$id_office'),
                                          '$idStep',  '$fileScheme', '$xml_name', '$pathRJCTfile', '$progressStatus', '$timestamp')";
                                    $resultRj1 = mysqli_query($dbconn, $sqlRj1) or die(mailLog(mysqli_error($dbconn) . "<br>" . "SQL: " . $sqlRj1, __FILE__ . __LINE__, MYSQL_ERROR));

                                    //Insert into ERROR_DETAIL information , why RRM reject file
                                    $sql_ins_det = "INSERT INTO ERROR_DETAILS (ID_FILE_HIST, STATUS, ERRORCODE, ERRORDESCRIPTION)
                  VALUES ((select min(ID_FILE_HIST) from FILE_HIST where ID_LOAD = (select max(ID_LOAD) from ACER_XML where fileName = '$file' and ID_OFFICE = '$id_office')), 
                            '$progressStatus', '$id_errorcode', '$description')";
                                    $rslt_ins_det = insertSql($sql_ins_det, __FILE__ . __LINE__);

                                    $log_text = 'Error codes: ' . $ceremp_error_codes_br . '<br>Filename: ' . $file;
                                    createLog(9, $log_text, 2, __FILE__ . __LINE__, $id_office);

                                    // Info for Admin, send to mail
                                    $ceremp_info = "<p>office_id:${id_office}</p>";
                                    $ceremp_info .= "<p>codes: $ceremp_error_codes_br</p>";
                                    mailLog($ceremp_info, __FILE__ . __LINE__, CEREMP_INFO);

                                    $ceremp_log_data = ['office_id' => $id_office, 'codes' => $codes, 'file_name' => $file];
                                    if ($fileScheme == SCHEMA_GAS_CAPACITY_V1 || $fileScheme == SCHEMA_GAS_CAPACITY_V2) cerempLog($ceremp_log_data);
                                }
                            }

                            //For delivery point
                            if ($rrm_id_err == 0 && $eic_id == 0 && $delivery_point == 0 && $ceremp_error == 0) {

                                if ($istrue == $bl) { // The filename is unique

                                    switch ($fileScheme) {
                                        case $schemeName:
                                            require($inc_path);
                                            break;
                                        case $nstdScheme:
                                            require($inc_path1);
                                            break;
                                        case $elctrBid:
                                            require($inc_path2);
                                            break;
                                        case $elctrRights:
                                            require($inc_path3);
                                            break;
                                        case $elctrAll:
                                            require($inc_path4);
                                            break;
                                        case $gasCap:
                                            require($inc_path5);
                                            break;
                                        case $elctrNom:
                                            require($inc_path6);
                                            break;
                                        case $gasTrnsp:
                                            require($inc_path7);
                                            break;
                                        case $gas_nom:
                                            require($inc_path8);
                                            break;
                                        case $lng:
                                            require($inc_path9);
                                            break;
                                        case $remit_storage:
                                            require($inc_path10);
                                            break;
                                        case $elctr_pub:
                                            require($inc_path11);
                                            break;
                                        case $elctr_conf:
                                            require($inc_path12);
                                            break;
                                        case $elctr_gen:
                                            require($inc_path13);
                                            break;
                                        case $elctr_out:
                                            require($inc_path14);
                                            break;
                                        case $rmt_artcl:
                                            require($inc_path15);
                                            break;
                                        case $stdscheme2:
                                            require($inc_path16);
                                            break;
                                        case SCHEMA_REMIT_TABLE1_V3:
                                            require($inc_path17);
                                            break;
                                        case SCHEMA_GAS_CAPACITY_V2:
                                            require($inc_path18);
                                            break;
                                    }
                                } else { // Duplicated filename

                                    echo "\n Error!!! Duplicated filename! \n";

                                    switch ($fileScheme) {
                                        case $schemeName:
                                            $schema_for_answr = SCHEMA_REMIT_TABLE1;
                                            break;
                                        case $nstdScheme:
                                            $schema_for_answr = "REMITTable2";
                                            break;
                                        case $elctrBid:
                                            $schema_for_answr = "ElectricityBid";
                                            break;
                                        case $elctrRights:
                                            $schema_for_answr = "ElectricityRights";
                                            break;
                                        case $elctrAll:
                                            $schema_for_answr = "ElectricityTotalAllocation";
                                            break;
                                        case $gasCap:
                                            $schema_for_answr = "GasCapacity";
                                            break;
                                        case $elctrNom:
                                            $schema_for_answr = "ElectricityNomination";
                                            break;
                                        case $gasTrnsp:
                                            $schema_for_answr = "GasTransparency";
                                            break;
                                        case $gas_nom:
                                            $schema_for_answr = "GasNomination";
                                            break;
                                        case $lng:
                                            $schema_for_answr = "REMITLNG";
                                            break;
                                        case $remit_storage:
                                            $schema_for_answr = "REMITStorage";
                                            break;
                                        case $elctr_pub:
                                            $schema_for_answr = "ElectricityPublication";
                                            break;
                                        case $elctr_conf:
                                            //Vars for RRM_XML_ANSWER
                                            $schema_for_answr = "ElectricityConfiguration";
                                            break;
                                        case $elctr_gen:
                                            $schema_for_answr = "ElectricityGenerationLoad";
                                            break;
                                        case $elctr_out:
                                            $schema_for_answr = "ElectricityOutage";
                                            break;
                                        case $rmt_artcl:
                                            $schema_for_answr = "REMITArticle3";
                                            break;
                                        case $stdscheme2:
                                            $schema_for_answr = SCHEMA_REMIT_TABLE1;
                                            break;
                                        case SCHEMA_REMIT_TABLE1_V3:
                                            $schema_for_answr = SCHEMA_REMIT_TABLE1;
                                            break;
                                        case SCHEMA_GAS_CAPACITY_V2:
                                            $schema_for_answr = SCHEMA_GAS_CAPACITY;
                                            break;
                                    }

                                    $id_errorcode = 4;
                                    require $rrm_answer;

                                    $duplicated_file_name = $file . ".Duplicated";

                                    $check_if_duplicated_twice_sql = "SELECT ID_LOAD FROM ACER_XML WHERE fileName = '$duplicated_file_name' and ID_OFFICE = '$id_office'";

                                    echo $check_if_duplicated_twice_sql . "\n";

                                    if (rowsCount($check_if_duplicated_twice_sql, __FILE__ . __LINE__) == 0) {

                                        $description = 'Duplicated filename';

                                        $path = "$path_to_clnt/ORIG_XML/";
                                        $timestamp = date('Y-m-d G:i:s');
                                        $timestamp1 = date('Y-m-d G:i:s');

                                        $idStep = 2;

                                        $progressStatus = "RRMrejected";
                                        $fileNoExt = substr($file, 0, -4);
                                        $fileNew = $fileNoExt . $downLine . $progressStatus . $extenHTML;
                                        $pathRJCTfile = "$path_to_clnt/RRM_RESPONSE/";


                                        /*Query to Write to ACER_XML table*/
                                        $sqlRj = "INSERT INTO ACER_XML (ID_OFFICE, fileName, path, receivedTime, schemaType, ID_STEP, progressStatus, progressTime)
                                          VALUES ('$id_office', '$duplicated_file_name', '$path', '$timestamp', '$fileScheme', '$idStep', '$progressStatus', '$timestamp1')";
                                        $resultRj = mysqli_query($dbconn, $sqlRj) or die(mailLog(mysqli_error($dbconn) . "<br>" . "SQL: " . $sqlRj, __FILE__ . __LINE__, MYSQL_ERROR));

                                        /*Query to Write to FILE_HIST table*/
                                        $sqlRj1 = "INSERT INTO FILE_HIST (ID_LOAD, ID_STEP, SCHEME, NAME, URL, STATUS, CREATED_TIME)
                                            VALUES ((SELECT max(ID_LOAD) FROM ACER_XML WHERE fileName = '$duplicated_file_name' and ID_OFFICE = '$id_office'),
                                          '$idStep',  '$fileScheme', '$xml_name', '$pathRJCTfile', '$progressStatus', '$timestamp')";
                                        $resultRj1 = mysqli_query($dbconn, $sqlRj1) or die(mailLog(mysqli_error($dbconn) . "<br>" . "SQL: " . $sqlRj1, __FILE__ . __LINE__, MYSQL_ERROR));

                                        //Insert into ERROR_DETAIL information , why RRM reject file
                                        $sql_ins_det = "INSERT INTO ERROR_DETAILS (ID_FILE_HIST, STATUS, ERRORCODE, ERRORDESCRIPTION)
                  VALUES ((select min(ID_FILE_HIST) from FILE_HIST where ID_LOAD = (select ID_LOAD from ACER_XML where fileName = '$duplicated_file_name' and ID_OFFICE = '$id_office')), 
                            '$progressStatus', '$id_errorcode', '$description')";
                                        $rslt_ins_det = insertSql($sql_ins_det, __FILE__);
                                    }

                                    echo "\nCan't load file! File: $file exist!!! \n";

                                    if (rename("$cust_sftp_in_full_path$file", "$path_to_clnt/DUPLICATED_FILES/$file.Duplicated")) {
                                        echo "\n" . "File: $file.Duplicated transfered from CUST_SFTP_IN -> DUPLICATED_FILES" . "\n";
                                    } else {
                                        echo "\n" . "Can't transfer Duplicated $file!!!" . "\n";
                                        mailLog("Can't transfer file!" . "<br>" . $cust_sftp_in_full_path . $file . " to " . "$path_to_clnt/DUPLICATED_FILES/$file.Duplicated", __FILE__, MOVE_FILE_ERROR . "; Error code: " . $id_errorcode);
                                    }
                                } // Duplicated filename

                            } else {
                                // todo save to DB RRM_ID, DELIVERY_POINT, EIC_ACCEPTED, CEREMP errors
                            } // RRM_ID, DELIVERY_POINT, EIC_ACCEPTED, CEREMP errors  checking

                        } else { //No permission to use format (if in array schema)

                            $id_errorcode = 7;
                            require $rrm_answer;

                            $path = "$path_to_clnt/ORIG_XML/";
                            $timestamp = date('Y-m-d G:i:s');
                            $timestamp1 = date('Y-m-d G:i:s');

                            $idStep = 2;

                            $progressStatus = "RRMrejected";
                            $fileNoExt = substr($file, 0, -4);
                            $fileNew = $fileNoExt . $downLine . $progressStatus . $extenHTML;
                            $pathRJCTfile = "$path_to_clnt/RRM_RESPONSE/";

                            /*Query to Write to ACER_XML table*/
                            $sqlRj = "INSERT INTO ACER_XML (ID_OFFICE, fileName, path, receivedTime, schemaType, ID_STEP, progressStatus, progressTime)
                                          VALUES ('$id_office', '$file', '$path', '$timestamp', '$fileScheme', '$idStep', '$progressStatus', '$timestamp1')";
                            $resultRj = mysqli_query($dbconn, $sqlRj) or die(mailLog(mysqli_error($dbconn) . "<br>" . "SQL: " . $sqlRj, __FILE__ . __LINE__, MYSQL_ERROR));

                            /*Query to Write to FILE_HIST table*/
                            $sqlRj1 = "INSERT INTO FILE_HIST (ID_LOAD, ID_STEP, SCHEME, NAME, URL, STATUS, CREATED_TIME)
                                            VALUES ((SELECT max(ID_LOAD) FROM ACER_XML WHERE fileName = '$file' and ID_OFFICE = '$id_office'),
                                          '$idStep',  '$fileScheme', '$xml_name', '$pathRJCTfile', '$progressStatus', '$timestamp')";
                            $resultRj1 = mysqli_query($dbconn, $sqlRj1) or die(mailLog(mysqli_error($dbconn) . "<br>" . "SQL: " . $sqlRj1, __FILE__ . __LINE__, MYSQL_ERROR));


                            //Insert into ERROR_DETAIL information , why RRM reject file
                            $sql_ins_det = "INSERT INTO ERROR_DETAILS (ID_FILE_HIST, STATUS, ERRORCODE, ERRORDESCRIPTION)
                  VALUES ((select min(ID_FILE_HIST) from FILE_HIST where ID_LOAD = (select max(ID_LOAD) from ACER_XML where fileName = '$file' and ID_OFFICE = '$id_office')), 
                            '$progressStatus', '$id_errorcode', '$description')";
                            $rslt_ins_det = insertSql($sql_ins_det, __FILE__ . __LINE__);

                            if (!copy("${path_to_clnt}/CUST_SFTP_IN/${file}", "${path_to_clnt}/ORIG_XML/${file}")) {
                                mailLog("Can't copy file!<br>File name: ${file}<br>From: ${path_to_clnt}/CUST_SFTP_IN/<br/>To:${path_to_clnt}/ORIG_XML/", __FILE__ . __LINE__, CEREMP_INFO);
                                echo "Can't copy ${file}..." . PHP_EOL;
                            }

                            //Transfer file to INVALID_SCHEMA folder
                            renameWithMaillogIfError($path_to_clnt . "/CUST_SFTP_IN/", $file, $path_to_clnt . "/INVALID_SCHEMA/", __FILE__, __LINE__);
                        } // This office is not permitted to use this schemeto use format (if in array schema)

                    } else { //Ivalid schema_name

                        $id_errorcode = 2;
                        require $rrm_answer;

                        $path = "$path_to_clnt/ORIG_XML/";
                        $timestamp = date('Y-m-d G:i:s');
                        $timestamp1 = date('Y-m-d G:i:s');

                        $idStep = 2;

                        $progressStatus = "RRMrejected";
                        $fileNoExt = substr($file, 0, -4);
                        $fileNew = $fileNoExt . $downLine . $progressStatus . $extenHTML;
                        $pathRJCTfile = "$path_to_clnt/RRM_RESPONSE/";

                        /*Query to Write to ACER_XML table*/
                        $sqlRj = "INSERT INTO ACER_XML (ID_OFFICE, fileName, path, receivedTime, schemaType, ID_STEP, progressStatus, progressTime)
                                          VALUES ('$id_office', '$file', '$path', '$timestamp', '$fileScheme', '$idStep', '$progressStatus', '$timestamp1')";
                        $resultRj = mysqli_query($dbconn, $sqlRj) or die(mailLog(mysqli_error($dbconn) . "<br>" . "SQL: " . $sqlRj, __FILE__ . __LINE__, MYSQL_ERROR));

                        /*Query to Write to FILE_HIST table*/
                        $sqlRj1 = "INSERT INTO FILE_HIST (ID_LOAD, ID_STEP, SCHEME, NAME, URL, STATUS, CREATED_TIME)
                                            VALUES ((SELECT max(ID_LOAD) FROM ACER_XML WHERE fileName = '$file' and ID_OFFICE = '$id_office'),
                                          '$idStep',  '$fileScheme', '$xml_name', '$pathRJCTfile', '$progressStatus', '$timestamp')";
                        $resultRj1 = mysqli_query($dbconn, $sqlRj1) or die(mailLog(mysqli_error($dbconn) . "<br>" . "SQL: " . $sqlRj1, __FILE__ . __LINE__, MYSQL_ERROR));


                        //Insert into ERROR_DETAIL information , why RRM reject file
                        $sql_ins_det = "INSERT INTO ERROR_DETAILS (ID_FILE_HIST, STATUS, ERRORCODE, ERRORDESCRIPTION)
                  VALUES ((select min(ID_FILE_HIST) from FILE_HIST where ID_LOAD = (select max(ID_LOAD) from ACER_XML where fileName = '$file' and ID_OFFICE = '$id_office')), 
                            '$progressStatus', '$id_errorcode', '$description')";
                        $rslt_ins_det = insertSql($sql_ins_det, __FILE__ . __LINE__);

                        if (!copy("${path_to_clnt}/CUST_SFTP_IN/${file}", "${path_to_clnt}/ORIG_XML/${file}")) {
                            mailLog("Can't copy file!<br>File name: ${file}<br>From: ${path_to_clnt}/CUST_SFTP_IN/<br/>To:${path_to_clnt}/ORIG_XML/", __FILE__ . __LINE__, CEREMP_INFO);
                            echo "Can't copy ${file}..." . PHP_EOL;
                        }

                        //Transfer file to INVALID_SCHEMA folder
                        renameWithMaillogIfError($path_to_clnt . "/CUST_SFTP_IN/", $file, $path_to_clnt . "/INVALID_SCHEMA/", __FILE__, __LINE__);
                    } // Ivalid schema_name for our rrm


                } else { //Check first part of file name


                    //       Todo DELETE DATE CHECK -> ELSE!!!!
                    $id_errorcode = 3;
                    require $rrm_answer;

                    $path = "$path_to_clnt/ORIG_XML/";
                    $timestamp = date('Y-m-d G:i:s');
                    $timestamp1 = date('Y-m-d G:i:s');

                    $idStep = 2;

                    $progressStatus = "RRMrejected";
                    $fileNoExt = substr($file, 0, -4);
                    $fileNew = $fileNoExt . $downLine . $progressStatus . $extenHTML;
                    $pathRJCTfile = "$path_to_clnt/RRM_RESPONSE/";

                    /*Query to Write to ACER_XML table*/
                    $sqlRj = "INSERT INTO ACER_XML (ID_OFFICE, fileName, path, receivedTime, schemaType, ID_STEP, progressStatus, progressTime)
                                          VALUES ('$id_office', '$file', '$path', '$timestamp', '$fileScheme', '$idStep', '$progressStatus', '$timestamp1')";
                    $resultRj = mysqli_query($dbconn, $sqlRj) or die(mailLog(mysqli_error($dbconn) . "<br>" . "SQL: " . $sqlRj, __FILE__ . __LINE__, MYSQL_ERROR));

                    /*Query to Write to FILE_HIST table*/
                    $sqlRj1 = "INSERT INTO FILE_HIST (ID_LOAD, ID_STEP, SCHEME, NAME, URL, STATUS, CREATED_TIME)
                                            VALUES ((SELECT max(ID_LOAD) FROM ACER_XML WHERE fileName = '$file' and ID_OFFICE = '$id_office'),
                                          '$idStep',  '$fileScheme', '$xml_name', '$pathRJCTfile', '$progressStatus', '$timestamp')";
                    $resultRj1 = mysqli_query($dbconn, $sqlRj1) or die(mailLog(mysqli_error($dbconn) . "<br>" . "SQL: " . $sqlRj1, __FILE__ . __LINE__, MYSQL_ERROR));


                    //Insert into ERROR_DETAIL information , why RRM reject file
                    $sql_ins_det = "INSERT INTO ERROR_DETAILS (ID_FILE_HIST, STATUS, ERRORCODE, ERRORDESCRIPTION)
                  VALUES ((select min(ID_FILE_HIST) from FILE_HIST where ID_LOAD = (select max(ID_LOAD) from ACER_XML where fileName = '$file' and ID_OFFICE = '$id_office')), 
                            '$progressStatus', '$id_errorcode', '$description')";
                    $rslt_ins_det = insertSql($sql_ins_det, __FILE__ . __LINE__);

                    if (!copy("${path_to_clnt}/CUST_SFTP_IN/${file}", "${path_to_clnt}/ORIG_XML/${file}")) {
                        mailLog("Can't copy file!<br>File name: ${file}<br>From: ${path_to_clnt}/CUST_SFTP_IN/<br/>To:${path_to_clnt}/ORIG_XML/", __FILE__ . __LINE__, CEREMP_INFO);
                        echo "Can't copy ${file}..." . PHP_EOL;
                    }

                    //Transfer file to INVALID_DATE folder
                    renameWithMaillogIfError($path_to_clnt . "/CUST_SFTP_IN/", $file, $path_to_clnt . "/INVALID_DATE/", __FILE__, __LINE__);
                } // Check first part of file name


            }
        } else { //Check extension of file (xml)

            $id_errorcode = 5;
            require $rrm_answer;

            $path = "$path_to_clnt/ORIG_XML/";
            $timestamp = date('Y-m-d G:i:s');
            $timestamp1 = date('Y-m-d G:i:s');

            $idStep = 2;

            $progressStatus = "RRMrejected";
            $fileNoExt = substr($file, 0, -4);
            $fileNew = $fileNoExt . $downLine . $progressStatus . $extenHTML;
            $pathRJCTfile = "$path_to_clnt/RRM_RESPONSE/";

            /*Query to Write to ACER_XML table*/
            $sqlRj = "INSERT INTO ACER_XML (ID_OFFICE, fileName, path, receivedTime, schemaType, ID_STEP, progressStatus, progressTime)
                                          VALUES ('$id_office', '$file', '$path', '$timestamp', '$fileScheme', '$idStep', '$progressStatus', '$timestamp1')";
            $resultRj = mysqli_query($dbconn, $sqlRj) or die(mailLog(mysqli_error($dbconn) . "<br>" . "SQL: " . $sqlRj, __FILE__ . __LINE__, MYSQL_ERROR));

            /*Query to Write to FILE_HIST table*/
            $sqlRj1 = "INSERT INTO FILE_HIST (ID_LOAD, ID_STEP, SCHEME, NAME, URL, STATUS, CREATED_TIME)
                                            VALUES ((SELECT max(ID_LOAD) FROM ACER_XML WHERE fileName = '$file' and ID_OFFICE = '$id_office'),
                                          '$idStep',  '$fileScheme', '$xml_name', '$pathRJCTfile', '$progressStatus', '$timestamp')";
            $resultRj1 = mysqli_query($dbconn, $sqlRj1) or die(mailLog(mysqli_error($dbconn) . "<br>" . "SQL: " . $sqlRj1, __FILE__ . __LINE__, MYSQL_ERROR));

            //Insert into ERROR_DETAIL information , why RRM reject file
            $sql_ins_det = "INSERT INTO ERROR_DETAILS (ID_FILE_HIST, STATUS, ERRORCODE, ERRORDESCRIPTION)
                  VALUES ((select min(ID_FILE_HIST) from FILE_HIST where ID_LOAD = (select max(ID_LOAD) from ACER_XML where fileName = '$file' and ID_OFFICE = '$id_office')), 
                            '$progressStatus', '$id_errorcode', '$description')";
            $rslt_ins_det = insertSql($sql_ins_det, __FILE__ . __LINE__);

            if (!copy("${path_to_clnt}/CUST_SFTP_IN/${file}", "${path_to_clnt}/ORIG_XML/${file}")) {
                mailLog("Can't copy file!<br>File name: ${file}<br>From: ${path_to_clnt}/CUST_SFTP_IN/<br/>To:${path_to_clnt}/ORIG_XML/", __FILE__ . __LINE__, CEREMP_INFO);
                echo "Can't copy ${file}..." . PHP_EOL;
            }

            //Transfer file to INVALID_EXT folder
            renameWithMaillogIfError($path_to_clnt . "/CUST_SFTP_IN/", $file, $path_to_clnt . "/INVALID_EXT/", __FILE__, __LINE__);
        } // Check extension of file (xml)
    }
}

$lock->setFree();
