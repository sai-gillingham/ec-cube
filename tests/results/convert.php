<?php

$existence_check = [];
$fp = fopen(__DIR__ . "/error_csv.csv", "w");

for($i = 0; $i <= 2; $i++){
    $str = file_get_contents(__DIR__ . "/level_" . $i . ".json");
    $json = json_decode($str,true);
    $files = $json['files'];

    foreach($files as $error_file => $error_array){
        foreach($error_array['messages'] as $message){
            $validator = true;
            $line_data = [];
            $line_data["file_name"] = $error_file;
            $line_data["level"] = $i;
            $line_data["line"] = $message["line"];
            $line_data["message"] = $message["message"];
            foreach($existence_check as $check){
                if($line_data["file_name"] == $check['file_name']    &&
                   $line_data["line"]      == $check["line"]    &&
                   $line_data["message"]   == $check["message"] &&
                   $message["ignorable"] == $check['ignorable']
                ){
                    $validator = false;
                }
            }
            if($validator){
                fputcsv($fp, $line_data);
                $line_data["ignorable"] = $message["ignorable"];
                $existence_check[] = $line_data;
            }
        }
    }
}

fclose($fp);
