Try this simple PHP function.

<?php

function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
    $output = NULL;
    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($deep_detect) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    }
    
    $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
    $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
    $continents = array(
        "AF" => "Africa",
        "AN" => "Antarctica",
        "AS" => "Asia",
        "EU" => "Europe",
        "OC" => "Australia (Oceania)",
        "NA" => "North America",
        "SA" => "South America"
    );
    if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
        $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
        if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
            switch ($purpose) {
                case "location":
                    $output = array(
                        "city"           => @$ipdat->geoplugin_city,
                        "state"          => @$ipdat->geoplugin_regionName,
                        "country"        => @$ipdat->geoplugin_countryName,
                        "country_code"   => @$ipdat->geoplugin_countryCode,
                        "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                        "continent_code" => @$ipdat->geoplugin_continentCode
                    );
                    break;
                case "address":
                    $address = array($ipdat->geoplugin_countryName);
                    if (@strlen($ipdat->geoplugin_regionName) >= 1)
                        $address[] = $ipdat->geoplugin_regionName;
                    if (@strlen($ipdat->geoplugin_city) >= 1)
                        $address[] = $ipdat->geoplugin_city;
                    $output = implode(", ", array_reverse($address));
                    break;
                case "city":
                    $output = @$ipdat->geoplugin_city;
                    break;
                case "state":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "region":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "country":
                    $output = @$ipdat->geoplugin_countryName;
                    break;
                case "countrycode":
                    $output = @$ipdat->geoplugin_countryCode;
                    break;
            }
        }
    }
    return $output;
}

?>
<?php

echo ip_info("Visitor", "Country"); // India
echo ip_info("Visitor", "Country Code"); // IN
echo ip_info("Visitor", "State"); // Andhra Pradesh
echo ip_info("Visitor", "City"); // Proddatur
echo ip_info("Visitor", "Address"); // Proddatur, Andhra Pradesh, India

print_r(ip_info("Visitor", "Location")); // Array ( [city] => Proddatur [state] => Andhra Pradesh [country] => India [country_code] => IN [continent] => Asia [continent_code] => AS )

?>
<?php

$json='{
    "d": "{\"responseCode\":null,\"message\":null,\"isError\":false,\"methodFailed\":null,\"extendedMessage\":null,\"reason\":null,\"returnObject\":[{\"user\":{\"master_customer_no\":2400297,\"last_login\":\"\/Date(1591155470000)\/\",\"business_location_no\":0,\"employee_id\":\"\",\"send_from\":false,\"welcome_message\":\"\",\"userpin\":\"\",\"last_accessed\":\"\/Date(1591198430777)\/\",\"password_expires\":\"\/Date(1598884504627)\/\",\"failed_attempts\":0,\"company_no\":1034,\"user_no\":4169,\"first_name\":\"Mostafa\",\"last_name\":\"Bayumi\",\"mobile_number\":\"\",\"email\":\"mostafa@mccollinsmedia.com\",\"username\":\"mostafa@mccollinsmedia.com\",\"password\":\"\",\"permission_level\":\"admin\",\"status\":\"active\",\"created_on\":\"\/Date(1591108504647)\/\",\"modified_on\":\"\/Date(1591198670728)\/\",\"created_by\":0},\"subscription\":{\"fx\":{},\"resetNextDate\":false,\"card_updated\":\"\/Date(-62135596800000)\/\",\"renewal_date\":\"\/Date(-2208988800000)\/\",\"membership_nos\":[],\"business_location_no\":0,\"master_reference_no\":0,\"totalPayments\":0,\"remainingPayments\":0,\"card\":{\"updateFields\":\"\",\"owner_customer_no\":0,\"currency\":\"\",\"customer_reference\":\"\",\"validateCard\":false,\"allowDuplicate\":false,\"card_use\":\"\",\"external_id\":\"\",\"swipe_data\":null,\"country\":\"\",\"giftcards\":[],\"passes\":[],\"reference_type\":\"\",\"reference_no\":0,\"number_field\":\"\",\"payment_processor_no\":0,\"charge_amount\":0,\"email\":\"\",\"company_name\":\"\",\"transaction_no\":0,\"payment_id\":null,\"payment_type\":null,\"company_no\":0,\"card_no\":0,\"card_name\":\"\",\"card_number\":\"\",\"card_month\":0,\"card_year\":0,\"customer_no\":0,\"card_type\":\"\",\"status\":\"\",\"card_description\":\"\",\"created_on\":\"\/Date(1591198670728)\/\",\"modified_on\":\"\/Date(1591198670728)\/\",\"ip_address\":\"\",\"sessionid\":\"\",\"user_info\":\"\",\"device_info\":\"\",\"address1\":\"\",\"address2\":\"\",\"city\":\"\",\"state\":\"\",\"zip_code\":\"\",\"card_security\":\"\"},\"service\":{\"fx\":{},\"category_name\":\"\",\"category_no\":0,\"deposit_fee\":0,\"deposit_type\":\"\",\"initiation_fee\":0,\"business_location_no\":0,\"auto_renew_credits\":\"Y\",\"enable_payments\":\"N\",\"auto_renew\":true,\"starting_at\":\"\",\"linkedObjects\":[],\"allow_renew_override\":false,\"credit_card_fee\":0,\"allow_cancel\":true,\"allow_change\":true,\"survey_no\":0,\"details\":null,\"allow_trial\":false,\"trial_length\":0,\"totalRevenue\":0,\"backend_process_new\":\"\",\"backend_process_renewal\":\"\",\"enable_multiple\":false,\"credit\":{\"fx\":{},\"credit_no\":0,\"company_no\":0,\"component_no\":0,\"category_no\":0,\"credits\":0,\"credit_cost\":0,\"created_on\":\"\/Date(1591198670728)\/\",\"component_name\":\"\",\"category_name\":\"\",\"totalPurchases\":0,\"totalRedemptions\":0,\"totalUnitsAvailable\":0,\"schema\":null,\"totalFavorite\":0,\"skipTriggers\":false,\"includeObjects\":false,\"includeStats\":false,\"includeTotalRecords\":false,\"totalRecords\":0,\"rows_per_page\":0,\"page_number\":0,\"updateFields\":\"\",\"includeCompany\":false,\"lightweight\":false,\"itemDetailsFields\":null,\"includeItemDetails\":false,\"search_value\":null,\"sort_by\":null,\"return_total\":0,\"from_date\":\"\/Date(-2208988800000)\/\",\"to_date\":\"\/Date(-2208988800000)\/\",\"includeDetails\":false,\"timezone_offset\":0,\"timezone_id\":null},\"credit_no\":0,\"service_redirect\":\"\",\"totalActiveSubscriptions\":0,\"totalExpiredSubscriptions\":0,\"totalCancelledSubscriptions\":0,\"totalFailedSubscriptions\":0,\"totalPendingSubscriptions\":0,\"totalSuspendedSubscriptions\":0,\"totalSubscriptions\":0,\"service_no\":0,\"service_title\":\"\",\"service_description\":\"\",\"service_sku\":\"\",\"price_daily\":0,\"price_weekly\":0,\"price_monthly\":0,\"price_quarterly\":0,\"price_semi\":0,\"price_annually\":0,\"created_on\":\"\/Date(1591198670728)\/\",\"company_no\":0,\"service_status\":\"\",\"schema\":null,\"totalFavorite\":0,\"skipTriggers\":false,\"includeObjects\":false,\"includeStats\":false,\"includeTotalRecords\":false,\"totalRecords\":0,\"rows_per_page\":0,\"page_number\":0,\"updateFields\":\"\",\"includeCompany\":false,\"lightweight\":false,\"itemDetailsFields\":null,\"includeItemDetails\":false,\"search_value\":null,\"sort_by\":null,\"return_total\":0,\"from_date\":\"\/Date(-2208988800000)\/\",\"to_date\":\"\/Date(-2208988800000)\/\",\"includeDetails\":false,\"timezone_offset\":0,\"timezone_id\":null},\"customer_address_no\":0,\"shipping_method\":\"\",\"shipping_courier\":\"\",\"quantity\":1,\"service_no\":10321,\"customer\":{\"membership_title\":\"\",\"points\":0,\"tag\":\"\",\"cc_email\":\"\",\"customer_source\":\"\",\"username\":\"\",\"gender\":\"U\",\"company_title\":\"\",\"anniversary_date\":\"\/Date(-2208988800000)\/\",\"affiliate_no\":0,\"membership_id\":\"\",\"password\":\"\",\"passbook_enabled\":\"N\",\"birthdate\":\"\/Date(-2208988800000)\/\",\"opt_in_email\":false,\"opt_in_sms\":false,\"customer_no\":0,\"first_name\":\"\",\"last_name\":\"\",\"address\":\"\",\"address2\":\"\",\"city\":\"\",\"state\":\"\",\"zip_code\":\"\",\"country\":\"\",\"company_name\":\"\",\"mobile_number\":\"\",\"phone_number\":\"\",\"email\":\"\",\"latitude\":\"\",\"longitude\":\"\",\"profile_photo\":\"\",\"customer_reference\":\"\"},\"company_name\":\"\",\"isTrial\":false,\"reference_item\":\"Professional Plan\",\"first_name\":\"\",\"last_name\":\"\",\"subscription_no\":83169,\"card_no\":93082,\"transaction_no\":0,\"customer_no\":2340677,\"reference_type\":\"service\",\"reference_no\":10321,\"subscription_frequency\":\"monthly\",\"subscribed_on\":\"\/Date(1585180800000)\/\",\"last_date\":\"\/Date(1590998465700)\/\",\"next_date\":\"\/Date(1593590465700)\/\",\"subscription_rate\":1500,\"affiliate_no\":0,\"modified_on\":\"\/Date(1591198670728)\/\",\"company_no\":0,\"user_no\":0,\"auto_charge\":\"Y\",\"subscription_status\":\"active\",\"subscription_details\":\"\",\"requested_cancel_date\":\"\/Date(-2208988800000)\/\",\"schema\":null,\"totalFavorite\":0,\"skipTriggers\":false,\"includeObjects\":false,\"includeStats\":false,\"includeTotalRecords\":false,\"totalRecords\":0,\"rows_per_page\":0,\"page_number\":0,\"updateFields\":\"\",\"includeCompany\":false,\"lightweight\":false,\"itemDetailsFields\":null,\"includeItemDetails\":false,\"search_value\":null,\"sort_by\":null,\"return_total\":0,\"from_date\":\"\/Date(-2208988800000)\/\",\"to_date\":\"\/Date(-2208988800000)\/\",\"includeDetails\":false,\"timezone_offset\":0,\"timezone_id\":null},\"service\":{\"fx\":{},\"category_name\":\"\",\"category_no\":0,\"deposit_fee\":0,\"deposit_type\":\"\",\"initiation_fee\":0,\"business_location_no\":0,\"auto_renew_credits\":\"Y\",\"enable_payments\":\"N\",\"auto_renew\":false,\"starting_at\":\"\",\"linkedObjects\":[],\"allow_renew_override\":false,\"credit_card_fee\":0,\"allow_cancel\":false,\"allow_change\":false,\"survey_no\":0,\"details\":null,\"allow_trial\":false,\"trial_length\":0,\"totalRevenue\":0,\"backend_process_new\":\"\",\"backend_process_renewal\":\"\",\"enable_multiple\":false,\"credit\":null,\"credit_no\":73,\"service_redirect\":\"\",\"totalActiveSubscriptions\":0,\"totalExpiredSubscriptions\":0,\"totalCancelledSubscriptions\":0,\"totalFailedSubscriptions\":0,\"totalPendingSubscriptions\":0,\"totalSuspendedSubscriptions\":0,\"totalSubscriptions\":0,\"service_no\":10321,\"service_title\":\"Professional Plan\",\"service_description\":\"\",\"service_sku\":\"\",\"price_daily\":0,\"price_weekly\":0,\"price_monthly\":1500,\"price_quarterly\":0,\"price_semi\":0,\"price_annually\":16200,\"created_on\":\"\/Date(1591198670728)\/\",\"company_no\":0,\"service_status\":\"active\",\"schema\":null,\"totalFavorite\":0,\"skipTriggers\":false,\"includeObjects\":false,\"includeStats\":false,\"includeTotalRecords\":false,\"totalRecords\":0,\"rows_per_page\":0,\"page_number\":0,\"updateFields\":\"\",\"includeCompany\":false,\"lightweight\":false,\"itemDetailsFields\":null,\"includeItemDetails\":false,\"search_value\":null,\"sort_by\":null,\"return_total\":0,\"from_date\":\"\/Date(-2208988800000)\/\",\"to_date\":\"\/Date(-2208988800000)\/\",\"includeDetails\":false,\"timezone_offset\":0,\"timezone_id\":null},\"company\":{\"updateFields\":\"\",\"enableActivityTracking\":false,\"auths\":[],\"parent_company_no\":0,\"domain_name\":\"\",\"timezone_id\":\"Arabian Standard Time\",\"timezone_offset\":4,\"settings\":null,\"subscription_no\":83169,\"customer_no\":2340677,\"company_logo_byte\":null,\"company_icon_byte\":null,\"user_no\":300,\"company_no\":1034,\"foregroundcolor\":\"000000\",\"backgroundcolor\":\"FFFFFF\",\"company_name\":\"Doors Freestyle Grill\",\"company_logo\":\"https://peoplevine.blob.core.windows.net/company/1034/company/logo.png\",\"company_icon\":\"https://peoplevine.blob.core.windows.net/company/1034/company/icon.png\",\"company_bio\":\"Doors Freestyle Grill is a renowned steak and seafood restaurant located in the beautiful city of Dubai, UAE. We’re focused on one thing – transforming your dining experience by immersing you in a freestyle grill environment that no steakhouse in Dubai can offer.\",\"web_site\":\"http://doorsdubai.com/\",\"company_email\":\"connect@doorsdubai.com\",\"company_phone\":\"971507000375\",\"address\":\"8/5 - 314 Al Seef St\",\"address2\":\"\",\"city\":\"Dubai\",\"state\":\"United Arab Emirates\",\"zip_code\":\".\",\"country\":\"US\",\"facebook_url\":\"https://www.facebook.com/doors.dubai/\",\"google_url\":\"\",\"twitter_handle\":\"@doors_dubai\",\"created_on\":\"\/Date(1585253185257)\/\"},\"customer\":{\"cc_email\":\"\",\"customer_reference\":\"\",\"profile_photo\":null,\"customer_no\":2340677,\"first_name\":\"Farzad\",\"last_name\":\"Farzad\",\"address\":\"\",\"address2\":\"\",\"city\":\"\",\"state\":\"\",\"zip_code\":\"\",\"country\":\"\",\"phone_number\":\"\",\"mobile_number\":\"\",\"email\":\"farzad@doorsdubai.com\",\"password\":\"Almighty@123\",\"created_on\":\"\/Date(1591198670728)\/\",\"device_type\":\"\",\"passbook_enabled\":\"\",\"wallet_enabled\":\"\",\"opt_in_email\":\"Y\",\"opt_in_sms\":\"Y\",\"opt_in_updates\":\"Y\"},\"domain\":null}],\"responseTime\":0.0312862,\"totalRecords\":0,\"minPrice\":0,\"maxPrice\":0}"
}';
$json=json_decode($json)->d;
echo "<pre>";print_r($json) ;
?>