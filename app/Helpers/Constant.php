<?php
define('NAME_MAX',70);
define('NAME_MIN',2);
define('PINCODE_MAX',6);
define('PINCODE_MIN',6);
define('MOBILE_MAX',10);
define('MOBILE_MIN',10);
define('AADHAR_MAX',16);
define('AADHAR_MIN',12);
define('DEFAULT_OTP',1234);
define('DATEOFBIRTH',7);
define('OTP_EXPIRATION_TIME',1);
define('TDS_DEDUCT_PERMISSION','yes');
define('TDS_PERCENT_AMOUNT',10);
define('IT_DEDUCT_PERMISSION','yes');
define('IT_PERCENT_AMOUNT',2);
define('PLEASE_CONTACT_ADMIN',			'Please Contact Admin');
define('PLEASE_TRY_AGAIN',			'Please Try Again');
define('SOMETHING_WENT_WRONG',			'Something went wrong .Please Try Again');
define('CONNECTION_TIMEOUT_MSG',			'Connection Timeout');
define('CONNECTION_TIMEOUT_MSG_RESPONSE',			'cURL error 28: Operation timed out');
//Max IP whitelist limit based on service
define('LIMIT_IP_WHITELIST', 5);

define('SELECT2','no');
define('AEPS_SUCCESS_RESPONSE','MC000123123');
define('AEPS_FAILED_RESPONSE','MC012345678');
define('AEPS_ERROR_RESPONSE','MC011223344');
define('MAX_LIMIT','2');
define('CANCEL_CHARGE','1000');
define('BULK_IMPORT_COLUMN_COUNT',16);

define('MAX_LIMITS','9');
define('MAX_LIMIT_NOTIFICATION','10');
define('ACCOUNT__MODE_PENDING_CUSTOMER',     'Your email not registerd. '.PLEASE_CONTACT_ADMIN);
define('SAVE_SMS_LOG','YES');
// define('PERMISSION_SENT_SMS', 'no');
//Regex START
define("MOBILE_FORMAT", 			'/^((?!(0))[0-9]{10})$/');
define("PINCODE_FORMAT",			'/^\d{6}$/');
define("PASSWORD_FORMAT",			'/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])[^\s]{8,12}$/');
define('ADMIN',	  					"admin");
define('USER',	  					"user");
define('Reseller',	  					"reseller");
define('PAYOUT',	  					"payout");
define('DEFAULT_BANK_IMAGE',	  					"images/mobile_recharge_logo/default.png");

define('PAYOUT_SERVICE_ID',	  					"srv_1626077095");
define('AEPS_SERVICE_ID',	  					"srv_1626077390");
define('RECHARGE_SERVICE_ID',	  				"srv_1626077505");
define('UPI_SERVICE_ID',	  					"srv_1626344088");
define('DMT_SERVICE_ID',	  					"srv_1674209568");

define('INSURANCE_SERVICE_ID', "srv_1679139459");
define('MATM_SERVICE_ID',	  					"srv_1680249103");
define('AUTO_COLLECT_SERVICE_ID', "srv_1639475949");
define('PAN_CARD_SERVICE_ID', "srv_1681458055");


define('AUTO_SETTLEMENT_SERVICE_ID', "srv_1656398681");
define('RECHARGE_SERVICE_SLUG',	"recharge");
define('PAN_CARD_SERVICE_SLUG',	"pan");
define('VALIDATE_SERVICE_SLUG', "verification");
define('DMT_SERVICE_SLUG', "dmt");
define('VALIDATE_SERVICE_ID', "srv_1652770831");
define('PARTNER_VAN_SERVICE_ID', "srv_1635429299");
define('LOAD_MONEY_SERVICE_ID', "srv_1640687279");
define('VA_SERVICE_ID', "srv_1652443818");
define('PAYOUT_SERVICE_ACC_PREFIX',	  					"1000");
define('AEPS_SERVICE_ACC_PREFIX',	  					"2000");
define('MICRO_SERVICE_ACC_PREFIX',	  					"3000");
define('RECHARGE_SERVICE_ACC_PREFIX',	  				"4000");
define('DMT_SERVICE_ACC_PREFIX',	  				"9000");
define('MAIN_ACC_PREFIX',	  				"556");

define('PAYOUT_SERVICE_DEFAULT_ACC',	  					"100000000001");
define('VALIDATION_SERVICE_DEFAULT_ACC', "800000000001");
define('DMT_SERVICE_DEFAULT_ACC', "900000000001");
define('AEPS_SERVICE_DEFAULT_ACC',	  					"200000000001");
define('MICRO_SERVICE_DEFAULT_ACC',	  					"300000000001");
define('RECHARGE_SERVICE_DEFAULT_ACC',	  				"400000000001");
define('PAN_CARD_SERVICE_DEFAULT_ACC',	  				"600000000001");
define('MAIN_DEFAULT_ACC',	  				"556000000001");
define('VAN_API_DEFAULT_ACC', 11);
define('AEPS_MID_ID', 442020227389319);
define('DEFAULT_VALUE',			   	'<span class="label label-danger">N/A</span>');
define('ACCOUNT__MODE_INACTIVE',    'Your account is inactive. '.PLEASE_CONTACT_ADMIN);
define('ACCOUNT__MODE_TRASHED',    'Your account is trashed. '.PLEASE_CONTACT_ADMIN);
define('ACCOUNT__MODE_PENDING',     'Your mobile not registerd. '.PLEASE_CONTACT_ADMIN);
define('OTO_EXPIRE',     			'OTP has been expired. '.PLEASE_TRY_AGAIN);
define('OTP_NOT_MATCH',     		'OTP not matched');
define('LOGIN_SUCCESSFULL',     	'Login Successfull');
define('DETAIL_SUCCESS',     		'Details has been Successfully fetched');
define('LIST_SUCCESS',     			'List Success');
define('NO_RECORD_AVAILABLE',     	'No record available');
define('DETAIL_AVAILABLE',     		'Detail available');
define('DETAIL_NOT_AVAILABLE',     	'Detail not available');
define('ACCOUNT_NOT_EXISTS', 		'Your account is not valid');
define('PROFILE_UPDATED_SUCCESS', 	'Profile has been updated successfully');
define('SERVICE_NOT_AVAILABLE', 	'This Service currently not available.');
define('SERVICE_ACCOUNT_ACTIVE_HEADING', 'Congratulation!');
define('SERVICE_ACCOUNT_ACTIVE_DESCRIPTION', "Your service activation request received. It will take up to 24 hours to get your service activated.");

define('BUSINEES_PROFILE_INFO_UPDATE_HEADING', 	'We need your attention!');
define('BUSINEES_PROFILE_INFO_UPDATE_DESCRIPTION', 	'Your business profile not updated. Please update business profile. ');
define('BUSINEES_PROFILE_INFO_UPDATE_BUTTON', 	' Update Business Profile');

/** @var Page $page */
define('NEW_API_KEY', 	' New API Key');
define('GENERATE_NEW_API_KEY', 	'Generate Key');

// Error Field Messages
define('FIELD_REQUIRED',    ' field is required');
define('FIELD_INTEGER',    ' field is must be integer');
define('FIELD_STRING',    ' field is must be string');
define('FIELD_MIN_LENGTH',    ' must be greater than ');
define('FIELD_MAX_LENGTH',    ' must be less than ');
define('FIELD_EMAIL_NOT_VALID',    ' email is not valid  ');
define('FIELD_IFSC',    ' ifsc code is not valid ');
define('COLUMN_MISSMATCH',    ' column length miss match');
define('COLUMN_UNKNOWN',    ' unknown column ');
define('VALUE_UNKNOWN',    ' unknown value ');
define('ACCOUNT_AND_IFSC_REQUIRED',    'Account number and ifsc code is required ');
define('IFSC_LENGTH',    'IFSC Code must be 11 characters in ifsc ');
define('IFSC_FIRST_FOUR_CHAR',    'IFSC Code must be first four characters ');
define('IFSC_FITH_CHAR',    'IFSC Code must be fifth character contain is 0 ');
define('IFSC_CODE_NOT_VALID',    'IFSC Code is not valid ');

define('ROOT_TYPE_VA', 'ibl_tpv');
define('SRV_SLUG_VA', 'va');
define('OPEN_BANK_VAN', 'ob_van');

//User Signup Status
define('SIGNUP_STATUS_VIDEO_UPLOADED', '4');
define('SIGNUP_STATUS_VIDEO_PENDING', '5');
define('SIGNUP_STATUS_COMPLETE', '10');