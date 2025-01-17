<?php
include "functions.php";
if (!isset($_SESSION['user_id'])) { header("Location: ./login.php"); exit; }

if (isset($_POST["submit_user"])) {
    if (isset($_POST["edit"])) {
        $rArray = getUser($_POST["edit"]);
        unset($rArray["id"]);
    } else {
        $rArray = Array("member_id" => 0, "username" => "", "password" => "", "exp_date" => null, "admin_enabled" => 1, "enabled" => 1, "admin_notes" => "", "reseller_notes" => "", "bouquet" => Array(), "max_connections" => 1, "is_restreamer" => 0, "allowed_ips" => Array(), "allowed_ua" => Array(), "created_at" => time(), "created_by" => -1, "is_mag" => 0, "is_e2" => 0, "force_server_id" => 0, "is_isplock" => 0, "isp_desc" => "", "forced_country" => "", "is_stalker" => 0, "bypass_ua" => 0, "play_token" => "");
    }
    foreach (Array("max_connections", "enabled", "admin_enabled") as $rSelection) {
        if (isset($_POST[$rSelection])) {
            $rArray[$rSelection] = intval($_POST[$rSelection]);
            unset($_POST[$rSelection]);
        } else {
            $rArray[$rSelection] = 1;
        }
    }
    foreach (Array("is_stalker", "is_e2", "is_mag", "is_restreamer", "is_trial") as $rSelection) {
        if (isset($_POST[$rSelection])) {
            $rArray[$rSelection] = 1;
            unset($_POST[$rSelection]);
        } else {
            $rArray[$rSelection] = 0;
        }
    }
    $rArray["bouquet"] = Array();
    if (isset($_POST["bouquet"])) {
        foreach ($_POST["bouquet"] as $rBouquetID) {
            $rArray["bouquet"][] = intval($rBouquetID);
        }
        $rArray["bouquet"] = "[".join(",", $rArray["bouquet"])."]";
        unset($_POST["bouquet"]);
    }
    if ((isset($_POST["exp_date"])) && (!isset($_POST["no_expire"]))) {
        if ((strlen($_POST["exp_date"]) > 0) AND ($_POST["exp_date"] <> "1970-01-01")) {
            try {
                $rDate = new DateTime($_POST["exp_date"]);
                $rArray["exp_date"] = $rDate->format("U");
            } catch (Exception $e) {
                echo "Incorrect date.";
                $_STATUS = 1;
            }
        }
        unset($_POST["exp_date"]);
    } else {
        $rArray["exp_date"] = null;
    }
    if( empty($_POST['username']) || empty($_POST['password']) ){
        $_STATUS = 3; // Username or Password field are empty
    }
    if (!isset($_STATUS)) {
        foreach($_POST as $rKey => $rValue) {
            if (isset($rArray[$rKey])) {
                $rArray[$rKey] = $rValue;
            }
        }
        $rArray["created_by"] = $rArray["member_id"];
        $rCols = "`".implode('`,`', array_keys($rArray))."`";
        foreach (array_values($rArray) as $rValue) {
            isset($rValues) ? $rValues .= ',' : $rValues = '';
            if (is_array($rValue)) {
                $rValue = json_encode($rValue);
            }
            if (is_null($rValue)) {
                $rValues .= 'NULL';
            } else {
                $rValues .= '\''.$db->real_escape_string($rValue).'\'';
            }
        }
        if (isset($_POST["edit"])) {
            $rCols = "`id`,".$rCols;
            $rValues = $_POST["edit"].",".$rValues;
        }
        $rQuery = "REPLACE INTO `users`(".$rCols.") VALUES(".$rValues.");";
        if ($db->query($rQuery)) {
            if (isset($_POST["edit"])) {
                $rInsertID = intval($_POST["edit"]);
            } else {
                $rInsertID = $db->insert_id;
            }
            if ((isset($rInsertID)) && (isset($_POST["access_output"]))) {
                $db->query("DELETE FROM `user_output` WHERE `user_id` = ".intval($rInsertID).";");
                foreach ($_POST["access_output"] as $rOutputID) {
                    $db->query("INSERT INTO `user_output`(`user_id`, `access_output_id`) VALUES(".intval($rInsertID).", ".intval($rOutputID).");");
                }
            }
            $_STATUS = 0;
        } else {
            $_STATUS = 2;
        }
        if (!isset($_GET["id"])) {
            $_GET["id"] = $rInsertID;
        }
    }
}

if (isset($_GET["id"])) {
    $rUser = getUser($_GET["id"]);
    if (!$rUser) {
        exit;
    }
    $rUser["outputs"] = getOutputs($rUser["id"]);
}
$rRegisteredUsers = getRegisteredUsers();
$rCountries = Array(Array("id" => "", "name" => "Off"), Array("id" => "A1", "name" => "Anonymous Proxy"), Array("id" => "A2", "name" => "Satellite Provider"), Array("id" => "O1", "name" => "Other Country"), Array("id" => "AF", "name" => "Afghanistan"), Array("id" => "AX", "name" => "Aland Islands"), Array("id" => "AL", "name" => "Albania"), Array("id" => "DZ", "name" => "Algeria"), Array("id" => "AS", "name" => "American Samoa"), Array("id" => "AD", "name" => "Andorra"), Array("id" => "AO", "name" => "Angola"), Array("id" => "AI", "name" => "Anguilla"), Array("id" => "AQ", "name" => "Antarctica"), Array("id" => "AG", "name" => "Antigua And Barbuda"), Array("id" => "AR", "name" => "Argentina"), Array("id" => "AM", "name" => "Armenia"), Array("id" => "AW", "name" => "Aruba"), Array("id" => "AU", "name" => "Australia"), Array("id" => "AT", "name" => "Austria"), Array("id" => "AZ", "name" => "Azerbaijan"), Array("id" => "BS", "name" => "Bahamas"), Array("id" => "BH", "name" => "Bahrain"), Array("id" => "BD", "name" => "Bangladesh"), Array("id" => "BB", "name" => "Barbados"), Array("id" => "BY", "name" => "Belarus"), Array("id" => "BE", "name" => "Belgium"), Array("id" => "BZ", "name" => "Belize"), Array("id" => "BJ", "name" => "Benin"), Array("id" => "BM", "name" => "Bermuda"), Array("id" => "BT", "name" => "Bhutan"), Array("id" => "BO", "name" => "Bolivia"), Array("id" => "BA", "name" => "Bosnia And Herzegovina"), Array("id" => "BW", "name" => "Botswana"), Array("id" => "BV", "name" => "Bouvet Island"), Array("id" => "BR", "name" => "Brazil"), Array("id" => "IO", "name" => "British Indian Ocean Territory"), Array("id" => "BN", "name" => "Brunei Darussalam"), Array("id" => "BG", "name" => "Bulgaria"), Array("id" => "BF", "name" => "Burkina Faso"), Array("id" => "BI", "name" => "Burundi"), Array("id" => "KH", "name" => "Cambodia"), Array("id" => "CM", "name" => "Cameroon"), Array("id" => "CA", "name" => "Canada"), Array("id" => "CV", "name" => "Cape Verde"), Array("id" => "KY", "name" => "Cayman Islands"), Array("id" => "CF", "name" => "Central African Republic"), Array("id" => "TD", "name" => "Chad"), Array("id" => "CL", "name" => "Chile"), Array("id" => "CN", "name" => "China"), Array("id" => "CX", "name" => "Christmas Island"), Array("id" => "CC", "name" => "Cocos (Keeling) Islands"), Array("id" => "CO", "name" => "Colombia"), Array("id" => "KM", "name" => "Comoros"), Array("id" => "CG", "name" => "Congo"), Array("id" => "CD", "name" => "Congo, Democratic Republic"), Array("id" => "CK", "name" => "Cook Islands"), Array("id" => "CR", "name" => "Costa Rica"), Array("id" => "CI", "name" => "Cote D'Ivoire"), Array("id" => "HR", "name" => "Croatia"), Array("id" => "CU", "name" => "Cuba"), Array("id" => "CY", "name" => "Cyprus"), Array("id" => "CZ", "name" => "Czech Republic"), Array("id" => "DK", "name" => "Denmark"), Array("id" => "DJ", "name" => "Djibouti"), Array("id" => "DM", "name" => "Dominica"), Array("id" => "DO", "name" => "Dominican Republic"), Array("id" => "EC", "name" => "Ecuador"), Array("id" => "EG", "name" => "Egypt"), Array("id" => "SV", "name" => "El Salvador"), Array("id" => "GQ", "name" => "Equatorial Guinea"), Array("id" => "ER", "name" => "Eritrea"), Array("id" => "EE", "name" => "Estonia"), Array("id" => "ET", "name" => "Ethiopia"), Array("id" => "FK", "name" => "Falkland Islands (Malvinas)"), Array("id" => "FO", "name" => "Faroe Islands"), Array("id" => "FJ", "name" => "Fiji"), Array("id" => "FI", "name" => "Finland"), Array("id" => "FR", "name" => "France"), Array("id" => "GF", "name" => "French Guiana"), Array("id" => "PF", "name" => "French Polynesia"), Array("id" => "TF", "name" => "French Southern Territories"), Array("id" => "MK", "name" => "Fyrom"), Array("id" => "GA", "name" => "Gabon"), Array("id" => "GM", "name" => "Gambia"), Array("id" => "GE", "name" => "Georgia"), Array("id" => "DE", "name" => "Germany"), Array("id" => "GH", "name" => "Ghana"), Array("id" => "GI", "name" => "Gibraltar"), Array("id" => "GR", "name" => "Greece"), Array("id" => "GL", "name" => "Greenland"), Array("id" => "GD", "name" => "Grenada"), Array("id" => "GP", "name" => "Guadeloupe"), Array("id" => "GU", "name" => "Guam"), Array("id" => "GT", "name" => "Guatemala"), Array("id" => "GG", "name" => "Guernsey"), Array("id" => "GN", "name" => "Guinea"), Array("id" => "GW", "name" => "Guinea-Bissau"), Array("id" => "GY", "name" => "Guyana"), Array("id" => "HT", "name" => "Haiti"), Array("id" => "HM", "name" => "Heard Island & Mcdonald Islands"), Array("id" => "VA", "name" => "Holy See (Vatican City State)"), Array("id" => "HN", "name" => "Honduras"), Array("id" => "HK", "name" => "Hong Kong"), Array("id" => "HU", "name" => "Hungary"), Array("id" => "IS", "name" => "Iceland"), Array("id" => "IN", "name" => "India"), Array("id" => "ID", "name" => "Indonesia"), Array("id" => "IR", "name" => "Iran, Islamic Republic Of"), Array("id" => "IQ", "name" => "Iraq"), Array("id" => "IE", "name" => "Ireland"), Array("id" => "IM", "name" => "Isle Of Man"), Array("id" => "IL", "name" => "Israel"), Array("id" => "IT", "name" => "Italy"), Array("id" => "JM", "name" => "Jamaica"), Array("id" => "JP", "name" => "Japan"), Array("id" => "JE", "name" => "Jersey"), Array("id" => "JO", "name" => "Jordan"), Array("id" => "KZ", "name" => "Kazakhstan"), Array("id" => "KE", "name" => "Kenya"), Array("id" => "KI", "name" => "Kiribati"), Array("id" => "KR", "name" => "Korea"), Array("id" => "KW", "name" => "Kuwait"), Array("id" => "KG", "name" => "Kyrgyzstan"), Array("id" => "LA", "name" => "Lao People's Democratic Republic"), Array("id" => "LV", "name" => "Latvia"), Array("id" => "LB", "name" => "Lebanon"), Array("id" => "LS", "name" => "Lesotho"), Array("id" => "LR", "name" => "Liberia"), Array("id" => "LY", "name" => "Libyan Arab Jamahiriya"), Array("id" => "LI", "name" => "Liechtenstein"), Array("id" => "LT", "name" => "Lithuania"), Array("id" => "LU", "name" => "Luxembourg"), Array("id" => "MO", "name" => "Macao"), Array("id" => "MG", "name" => "Madagascar"), Array("id" => "MW", "name" => "Malawi"), Array("id" => "MY", "name" => "Malaysia"), Array("id" => "MV", "name" => "Maldives"), Array("id" => "ML", "name" => "Mali"), Array("id" => "MT", "name" => "Malta"), Array("id" => "MH", "name" => "Marshall Islands"), Array("id" => "MQ", "name" => "Martinique"), Array("id" => "MR", "name" => "Mauritania"), Array("id" => "MU", "name" => "Mauritius"), Array("id" => "YT", "name" => "Mayotte"), Array("id" => "MX", "name" => "Mexico"), Array("id" => "FM", "name" => "Micronesia, Federated States Of"), Array("id" => "MD", "name" => "Moldova"), Array("id" => "MC", "name" => "Monaco"), Array("id" => "MN", "name" => "Mongolia"), Array("id" => "ME", "name" => "Montenegro"), Array("id" => "MS", "name" => "Montserrat"), Array("id" => "MA", "name" => "Morocco"), Array("id" => "MZ", "name" => "Mozambique"), Array("id" => "MM", "name" => "Myanmar"), Array("id" => "NA", "name" => "Namibia"), Array("id" => "NR", "name" => "Nauru"), Array("id" => "NP", "name" => "Nepal"), Array("id" => "NL", "name" => "Netherlands"), Array("id" => "AN", "name" => "Netherlands Antilles"), Array("id" => "NC", "name" => "New Caledonia"), Array("id" => "NZ", "name" => "New Zealand"), Array("id" => "NI", "name" => "Nicaragua"), Array("id" => "NE", "name" => "Niger"), Array("id" => "NG", "name" => "Nigeria"), Array("id" => "NU", "name" => "Niue"), Array("id" => "NF", "name" => "Norfolk Island"), Array("id" => "MP", "name" => "Northern Mariana Islands"), Array("id" => "NO", "name" => "Norway"), Array("id" => "OM", "name" => "Oman"), Array("id" => "PK", "name" => "Pakistan"), Array("id" => "PW", "name" => "Palau"), Array("id" => "PS", "name" => "Palestinian Territory, Occupied"), Array("id" => "PA", "name" => "Panama"), Array("id" => "PG", "name" => "Papua New Guinea"), Array("id" => "PY", "name" => "Paraguay"), Array("id" => "PE", "name" => "Peru"), Array("id" => "PH", "name" => "Philippines"), Array("id" => "PN", "name" => "Pitcairn"), Array("id" => "PL", "name" => "Poland"), Array("id" => "PT", "name" => "Portugal"), Array("id" => "PR", "name" => "Puerto Rico"), Array("id" => "QA", "name" => "Qatar"), Array("id" => "RE", "name" => "Reunion"), Array("id" => "RO", "name" => "Romania"), Array("id" => "RU", "name" => "Russian Federation"), Array("id" => "RW", "name" => "Rwanda"), Array("id" => "BL", "name" => "Saint Barthelemy"), Array("id" => "SH", "name" => "Saint Helena"), Array("id" => "KN", "name" => "Saint Kitts And Nevis"), Array("id" => "LC", "name" => "Saint Lucia"), Array("id" => "MF", "name" => "Saint Martin"), Array("id" => "PM", "name" => "Saint Pierre And Miquelon"), Array("id" => "VC", "name" => "Saint Vincent And Grenadines"), Array("id" => "WS", "name" => "Samoa"), Array("id" => "SM", "name" => "San Marino"), Array("id" => "ST", "name" => "Sao Tome And Principe"), Array("id" => "SA", "name" => "Saudi Arabia"), Array("id" => "SN", "name" => "Senegal"), Array("id" => "RS", "name" => "Serbia"), Array("id" => "SC", "name" => "Seychelles"), Array("id" => "SL", "name" => "Sierra Leone"), Array("id" => "SG", "name" => "Singapore"), Array("id" => "SK", "name" => "Slovakia"), Array("id" => "SI", "name" => "Slovenia"), Array("id" => "SB", "name" => "Solomon Islands"), Array("id" => "SO", "name" => "Somalia"), Array("id" => "ZA", "name" => "South Africa"), Array("id" => "GS", "name" => "South Georgia And Sandwich Isl."), Array("id" => "ES", "name" => "Spain"), Array("id" => "LK", "name" => "Sri Lanka"), Array("id" => "SD", "name" => "Sudan"), Array("id" => "SR", "name" => "Suriname"), Array("id" => "SJ", "name" => "Svalbard And Jan Mayen"), Array("id" => "SZ", "name" => "Swaziland"), Array("id" => "SE", "name" => "Sweden"), Array("id" => "CH", "name" => "Switzerland"), Array("id" => "SY", "name" => "Syrian Arab Republic"), Array("id" => "TW", "name" => "Taiwan"), Array("id" => "TJ", "name" => "Tajikistan"), Array("id" => "TZ", "name" => "Tanzania"), Array("id" => "TH", "name" => "Thailand"), Array("id" => "TL", "name" => "Timor-Leste"), Array("id" => "TG", "name" => "Togo"), Array("id" => "TK", "name" => "Tokelau"), Array("id" => "TO", "name" => "Tonga"), Array("id" => "TT", "name" => "Trinidad And Tobago"), Array("id" => "TN", "name" => "Tunisia"), Array("id" => "TR", "name" => "Turkey"), Array("id" => "TM", "name" => "Turkmenistan"), Array("id" => "TC", "name" => "Turks And Caicos Islands"), Array("id" => "TV", "name" => "Tuvalu"), Array("id" => "UG", "name" => "Uganda"), Array("id" => "UA", "name" => "Ukraine"), Array("id" => "AE", "name" => "United Arab Emirates"), Array("id" => "GB", "name" => "United Kingdom"), Array("id" => "US", "name" => "United States"), Array("id" => "UM", "name" => "United States Outlying Islands"), Array("id" => "UY", "name" => "Uruguay"), Array("id" => "UZ", "name" => "Uzbekistan"), Array("id" => "VU", "name" => "Vanuatu"), Array("id" => "VE", "name" => "Venezuela"), Array("id" => "VN", "name" => "Viet Nam"), Array("id" => "VG", "name" => "Virgin Islands, British"), Array("id" => "VI", "name" => "Virgin Islands, U.S."), Array("id" => "WF", "name" => "Wallis And Futuna"), Array("id" => "EH", "name" => "Western Sahara"), Array("id" => "YE", "name" => "Yemen"), Array("id" => "ZM", "name" => "Zambia"), Array("id" => "ZW", "name" => "Zimbabwe"));

include "header.php"; ?>
        <div class="wrapper boxed-layout">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <a href="./users.php"><li class="breadcrumb-item"><i class="mdi mdi-backspace"></i> Back to Users</li></a>
                                </ol>
                            </div>
                            <h4 class="page-title"><?php if (isset($rUser)) { echo "Edit"; } else { echo "Add"; } ?> User</h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-xl-12">
                        <?php if ((isset($_STATUS)) && ($_STATUS == 0)) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            User operation was completed successfully.
                        </div>
                        <?php } else if ((isset($_STATUS)) && ($_STATUS > 0)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            There was an error performing this operation! Please check the form entry and try again.
                        </div>
                        <?php } ?>
                        <div class="card">
                            <div class="card-body">
                                <form action="./user.php<?php if (isset($_GET["id"])) { echo "?id=".$_GET["id"]; } ?>" method="POST" id="user_form">
                                    <?php if (isset($rUser)) { ?>
                                    <input type="hidden" name="edit" value="<?=$rUser["id"]?>" />
                                    <input type="hidden" name="admin_enabled" value="<?=$rUser["admin_enabled"]?>" />
                                    <input type="hidden" name="enabled" value="<?=$rUser["enabled"]?>" />
                                    <?php } ?>
                                    <div id="basicwizard">
                                        <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                            <li class="nav-item">
                                                <a href="#user-details" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                    <i class="mdi mdi-account-card-details-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline">Details</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#advanced-options" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-folder-alert-outline mr-1"></i>
                                                    <span class="d-none d-sm-inline">Advanced</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#bouquets" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                                    <i class="mdi mdi-flower-tulip mr-1"></i>
                                                    <span class="d-none d-sm-inline">Bouquets</span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content b-0 mb-0 pt-0">
                                            <div class="tab-pane" id="user-details">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="username">Username</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="username" name="username" value="<?php if (isset($rUser)) { echo $rUser["username"]; } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="password">Password</label>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control" id="password" name="password" value="<?php if (isset($rUser)) { echo $rUser["password"]; } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="member_id">Member ID</label>
                                                            <div class="col-md-8">
                                                                <select name="member_id" id="member_id" class="form-control select2" data-toggle="select2">
                                                                    <?php foreach ($rRegisteredUsers as $rRegisteredUser) { ?>
                                                                    <option <?php if (isset($rUser)) { if (intval($rUser["member_id"]) == intval($rRegisteredUser["id"])) { echo "selected "; } } ?>value="<?=$rRegisteredUser["id"]?>"><?=$rRegisteredUser["username"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="max_connections">Max Connections</label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control" id="max_connections" name="max_connections" value="<?php if (isset($rUser)) { echo $rUser["max_connections"]; } else { echo "1"; } ?>">
                                                            </div>
                                                            <label class="col-md-2 col-form-label" for="exp_date">Expiry <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Leave blank for unlimited." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control text-center date" id="exp_date" name="exp_date" value="<?php if (isset($rUser)) { if (!is_null($rUser["exp_date"])) { echo date("Y-m-d", $rUser["exp_date"]); } else { echo "\" disabled=\"disabled"; } } ?>" data-toggle="date-picker" data-single-date-picker="true">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <div class="custom-control custom-checkbox mt-1">
                                                                    <input type="checkbox" class="custom-control-input" id="no_expire" name="no_expire"<?php if(isset($rUser)) { if (is_null($rUser["exp_date"])) { echo " checked"; } } ?>>
                                                                    <label class="custom-control-label" for="no_expire">Never</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="admin_notes">Admin Notes</label>
                                                            <div class="col-md-8">
                                                                <textarea id="admin_notes" name="admin_notes" class="form-control" rows="3" placeholder=""><?php if (isset($rUser)) { echo $rUser["admin_notes"]; } ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="reseller_notes">Reseller Notes</label>
                                                            <div class="col-md-8">
                                                                <textarea id="reseller_notes" name="reseller_notes" class="form-control" rows="3" placeholder=""><?php if (isset($rUser)) { echo $rUser["reseller_notes"]; } ?></textarea>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Next</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="advanced-options">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="force_server_id">Forced Connection <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Force this user to connect to a specific server. Otherwise, the server with the lowest load will be selected." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-8">
                                                                <select name="force_server_id" id="force_server_id" class="form-control select2" data-toggle="select2">
                                                                    <option <?php if (isset($rUser)) { if (intval($rUser["force_server_id"]) == 0) { echo "selected "; } } ?>value="0">Disabled</option>
                                                                    <?php foreach ($rServers as $rServer) { ?>
                                                                    <option <?php if (isset($rUser)) { if (intval($rUser["force_server_id"]) == intval($rServer["id"])) { echo "selected "; } } ?>value="<?=$rServer["id"]?>"><?=$rServer["server_name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="is_stalker">Ministra Portal <i data-toggle="tooltip" data-placement="top" title="" data-original-title="Select this option if you intend to use this account with your Ministra / Stalker portal. Output formats, expiration and connections below will be ignored. Only MPEG-TS output is allowed." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="is_stalker" id="is_stalker" type="checkbox" <?php if (isset($rUser)) { if ($rUser["is_stalker"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="is_mag">MAG Device <i data-toggle="tooltip" data-placement="top" title="" data-original-title="This option will be selected if this device is a MAG set top box. This will be a sub account and should not be modified directly." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="is_mag" id="is_mag" type="checkbox" <?php if (isset($rUser)) { if ($rUser["is_mag"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="is_e2">Enigma Device <i data-toggle="tooltip" data-placement="top" title="" data-original-title="This option will be selected if this device is a Enigma set top box. This will be a sub account and should not be modified directly." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="is_e2" id="is_e2" type="checkbox" <?php if (isset($rUser)) { if ($rUser["is_e2"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                            <label class="col-md-4 col-form-label" for="is_restreamer">Restreamer <i data-toggle="tooltip" data-placement="top" title="" data-original-title="If selected, this user will not be blocked for restreaming channels." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-2">
                                                                <input name="is_restreamer" id="is_restreamer" type="checkbox" <?php if (isset($rUser)) { if ($rUser["is_restreamer"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="is_trial">Trial Account</label>
                                                            <div class="col-md-2">
                                                                <input name="is_trial" id="is_trial" type="checkbox" <?php if (isset($rUser)) { if ($rUser["is_trial"] == 1) { echo "checked "; } } ?>data-plugin="switchery" class="js-switch" data-color="#039cfd"/>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="forced_country">Override Country Restriction <i data-toggle="tooltip" data-placement="top" title="" data-original-title="This will override the general countrty restrictions set in the Settings page." class="mdi mdi-information"></i></label>
                                                            <div class="col-md-8">
                                                                <select name="forced_country" id="forced_country" class="form-control select2" data-toggle="select2">
                                                                    <?php foreach ($rCountries as $rCountry) { ?>
                                                                    <option <?php if (isset($rUser)) { if ($rUser["forced_country"] == $rCountry["id"]) { echo "selected "; } } ?>value="<?=$rCountry["id"]?>"><?=$rCountry["name"]?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row mb-4">
                                                            <label class="col-md-4 col-form-label" for="access_output">Access Output</label>
                                                            <div class="col-md-8">
                                                                <?php foreach (getOutputs() as $rOutput) { ?>
                                                                <div class="checkbox form-check-inline">
                                                                    <input data-size="large" type="checkbox" id="access_output_<?=$rOutput["access_output_id"]?>" name="access_output[]" value="<?=$rOutput["access_output_id"]?>"<?php if (isset($rUser)) { if (in_array($rOutput["access_output_id"], $rUser["outputs"])) { echo " checked"; } } else { echo " checked"; } ?>>
                                                                    <label for="access_output_<?=$rOutput["access_output_id"]?>"> <?=$rOutput["output_name"]?> </label>
                                                                </div>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Next</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            
                                            <div class="tab-pane" id="bouquets">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group row mb-4">
                                                            <?php foreach (getBouquets() as $rBouquet) { ?>
                                                            <div class="col-md-6">
                                                                <div class="custom-control custom-checkbox mt-1">
                                                                    <input type="checkbox" class="custom-control-input bouquet-checkbox" id="bouquet-<?=$rBouquet["id"]?>" name="bouquet[]" value="<?=$rBouquet["id"]?>"<?php if(isset($rUser)) { if(in_array($rBouquet["id"], json_decode($rUser["bouquet"], True))) { echo " checked"; } } ?>>
                                                                    <label class="custom-control-label" for="bouquet-<?=$rBouquet["id"]?>"><?=$rBouquet["bouquet_name"]?></label>
                                                                </div>
                                                            </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0">
                                                    <li class="previous list-inline-item">
                                                        <a href="javascript: void(0);" class="btn btn-secondary">Previous</a>
                                                    </li>
                                                    <li class="next list-inline-item float-right">
                                                        <a href="javascript: void(0);" onClick="selectAll()" class="btn btn-secondary">Select All</a>
                                                        <a href="javascript: void(0);" onClick="selectNone()" class="btn btn-secondary">Deselect All</a>
                                                        <input name="submit_user" type="submit" class="btn btn-primary" value="<?php if (isset($rUser)) { echo "Edit"; } else { echo "Add"; } ?>" />
                                                    </li>
                                                </ul>
                                            </div>
                                        </div> <!-- tab-content -->
                                    </div> <!-- end #basicwizard-->
                                </form>

                            </div> <!-- end card-body -->
                        </div> <!-- end card-->
                    </div> <!-- end col -->
                </div>
            </div> <!-- end container -->
        </div>
        <!-- end wrapper -->

        <!-- Footer Start -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12  text-center">Xtream Codes - Admin UI</div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->

        <!-- Vendor js -->
        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/libs/jquery-toast/jquery.toast.min.js"></script>
        <script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
        <script src="assets/libs/switchery/switchery.min.js"></script>
        <script src="assets/libs/select2/select2.min.js"></script>
        <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
        <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
        <script src="assets/libs/moment/moment.min.js"></script>
        <script src="assets/libs/daterangepicker/daterangepicker.js"></script>

        <!-- Plugins js-->
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>

        <!-- Tree view js -->
        <script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>

        <!-- App js-->
        <script src="assets/js/app.min.js"></script>
        
        <script>
        (function($) {
          $.fn.inputFilter = function(inputFilter) {
            return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
              if (inputFilter(this.value)) {
                this.oldValue = this.value;
                this.oldSelectionStart = this.selectionStart;
                this.oldSelectionEnd = this.selectionEnd;
              } else if (this.hasOwnProperty("oldValue")) {
                this.value = this.oldValue;
                this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
              }
            });
          };
        }(jQuery));
        
        function selectAll() {
            $(".bouquet-checkbox").each(function() {
                $(this).prop('checked', true);
            });
        }
        
        function selectNone() {
            $(".bouquet-checkbox").each(function() {
                $(this).prop('checked', false);
            });
        }
        function isValidDate(dateString) {
              var regEx = /^\d{4}-\d{2}-\d{2}$/;
              if(!dateString.match(regEx)) return false;  // Invalid format
              var d = new Date(dateString);
              var dNum = d.getTime();
              if(!dNum && dNum !== 0) return false; // NaN value, Invalid date
              return d.toISOString().slice(0,10) === dateString;
        }
        
        $(document).ready(function() {
            $('select.select2').select2({width: '100%'})
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            elems.forEach(function(html) {
              var switchery = new Switchery(html);
            });
            
            $('#exp_date').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                minDate: new Date(),
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });
            
            $("#no_expire").change(function() {
                if ($(this).prop("checked")) {
                    $("#exp_date").prop("disabled", true);
                } else {
                    $("#exp_date").removeAttr("disabled");
                }
            });
            
            $(document).keypress(function(event){
                if (event.which == '13') {
                    event.preventDefault();
                }
            });
            
            $("#max_connections").inputFilter(function(value) { return /^\d*$/.test(value); });
            $("form").attr('autocomplete', 'off');
        });
        </script>
    </body>
</html>
