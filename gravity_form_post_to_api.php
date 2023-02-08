<?php

function post_to_api($entry, $form) {

    //change the id's to match your fields
    $name = $entry['5'];
    $surname = $entry['7'];
    $cellnumber = $entry['8'];
    $vdn = $entry['3'];
    $department = $entry['4'];
    $brand = $entry['6'];
	$currentDate = date(DATE_ATOM);

    $insert = <<<EOT
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <soap:Header>
        <AuthenticationHeader xmlns="https://www.isservices.co.za/">
            <Username>UPSTREAM</Username>
            <Password>0busdJl0zVCE</Password>
        </AuthenticationHeader>
    </soap:Header>
    <soap:Body>
        <ProcessContact xmlns="https://www.isservices.co.za/"> 
            <details>
                <Reference />
                <Name>$name</Name>
                <Surname>$surname</Surname>
                <Email />
                <IDNumber />
                <CellNumber>$cellnumber</CellNumber>
                <WorkNumber />
                <HomeNumber />
                <Comments />
                <VDN>$vdn</VDN>
                <Sponsor />
                <Department>$department</Department>
                <Brand>$brand</Brand>
            </details> 
        </ProcessContact>
    </soap:Body> 
</soap:Envelope>
EOT;

    //uncomment line below to test insert before sending to API
//	$file = file_put_contents('logs.txt', $currentDate ." " . $insert."\n".PHP_EOL , FILE_APPEND | LOCK_EX);

	$crm = 'https://api.telesure.co.za/BannerService/BannerService.asmx?op=ProcessContact';
	$curl = curl_init($crm);
	curl_setopt($curl, CURLOPT_URL, $crm);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$headers = array(
   		"Content-Type: application/soap+xml",
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	curl_setopt($curl, CURLOPT_POSTFIELDS, $insert);

	//for debug only!
//	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

	$resp = curl_exec($curl);
	$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	
	$status = ($httpcode == '200') ? '+OK' : '-ERR';
    //If response is not 200 then dump error
	if($httpcode != '200'){
		$file = file_put_contents('logs.txt', $currentDate ." " . $insert."\n".PHP_EOL , FILE_APPEND | LOCK_EX);
	}
	//Put the success in the log
	$file = file_put_contents('logs.txt', $currentDate . " STATUS: " . $status . "\n" . $resp."\n".PHP_EOL , FILE_APPEND | LOCK_EX);

}

add_action('gform_after_submission', 'post_to_api', 10, 2);
