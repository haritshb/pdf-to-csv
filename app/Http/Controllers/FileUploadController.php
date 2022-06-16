<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function index(){
        return view('file-upload.index');
    }

    public function store(Request $request)
    {
        /* 
        $validatedData = $request->validate([
            'file' => 'required|csv,txt,xlx,xls,pdf|max:2048', 
        ]);
 
        $name = $request->file('file')->getClientOriginalName(); 
        $path = $request->file('file')->store('public/files'); 
 
        $save = new File; 
        $save->name = $name;
        $save->path = $path;
        */

        // Get submitted form data
        
        $apiKey = 'haritsbalfas10@gmail.com_a268e9159e6046a9f2798f908b510ab2da4036c97802e0c51bdebdf1d45d30e1a400c934'; // The authentication key (API Key). Get your own by registering at https://app.pdf.co/documentation/api


        // 1. RETRIEVE THE PRESIGNED URL TO UPLOAD THE FILE.
        // * If you already have the direct PDF file link, go to the step 3.

        // Create URL
        $url = "https://api.pdf.co/v1/file/upload/get-presigned-url" . 
            "?name=" . $request->file('file')->getClientOriginalName() .
            "&contenttype=application/octet-stream";
            
        // Create request
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("x-api-key: " . $apiKey));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //for solving certificate issue laravel

        // Execute request
        $result = curl_exec($curl);

        if (curl_errno($curl) == 0)
        {
            $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            if ($status_code == 200)
            {
                $json = json_decode($result, true);
                
                // Get URL to use for the file upload
                $uploadFileUrl = $json["presignedUrl"];
                // Get URL of uploaded file to use with later API calls
                $uploadedFileUrl = $json["url"];
                
                // 2. UPLOAD THE FILE TO CLOUD.
                
                $localFile = $request->file('file');//$_FILES["fileInput"]["tmp_name"];
                $fileHandle = fopen($localFile, "r");
                
                curl_setopt($curl, CURLOPT_URL, $uploadFileUrl);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("content-type: application/octet-stream"));
                curl_setopt($curl, CURLOPT_PUT, true);
                curl_setopt($curl, CURLOPT_INFILE, $fileHandle);
                curl_setopt($curl, CURLOPT_INFILESIZE, filesize($localFile));
                
                // Execute request
                curl_exec($curl);
                
                fclose($fileHandle);
                
                if (curl_errno($curl) == 0)
                {
                    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    
                    if ($status_code == 200)
                    {
                        // Read all template texts
                        $templateText = "";//file_get_contents($_FILES["fileTemplate"]["tmp_name"]);

                        // 3. PARSE UPLOADED PDF DOCUMENT
                        $this->ParseDocument($apiKey, $uploadedFileUrl, $templateText);
                    }
                    else
                    {
                        // Display request error
                        echo "<p>Status code: " . $status_code . "</p>"; 
                        echo "<p>" . $result . "</p>"; 
                    }
                }
                else
                {
                    // Display CURL error
                    echo "Error: " . curl_error($curl);
                }
            }
            else
            {
                // Display service reported error
                echo "<p>Status code: " . $status_code . "</p>"; 
                echo "<p>" . $result . "</p>"; 
            }
            
            curl_close($curl);
        }
        else
        {
            // Display CURL error
            echo "Error: " . curl_error($curl);
        }
        //return redirect('file-upload.index')->with('status', 'File Has been uploaded successfully in laravel 8');
 
    }

    function ParseDocument($apiKey, $uploadedFileUrl, $templateText) 
    {
        // (!) Make asynchronous job
        $async = TRUE;

        // Prepare URL for Document parser API call.
        // See documentation: https://apidocs.pdf.co/?#1-pdfdocumentparser
        $url = "https://api.pdf.co/v1/pdf/documentparser";

        // Prepare requests params
        $parameters = array();
        $parameters["url"] = $uploadedFileUrl;
        $parameters["template"] = $templateText;
        $parameters["async"] = $async;

        // Create Json payload
        $data = json_encode($parameters);

        // Create request
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("x-api-key: " . $apiKey, "Content-type: application/json"));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //for solving certificate issue laravel

        // Execute request
        $result = curl_exec($curl);
        //echo $result . "<br/>";

        if (curl_errno($curl) == 0)
        {
            $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            if ($status_code == 200)
            {
                $json = json_decode($result, true);
            
                if (!isset($json["error"]) || $json["error"] == false)
                {
                    // URL of generated JSON file that will available after the job completion
                    $resultFileUrl = $json["url"];
                    
                    // Asynchronous job ID
                    $jobId = $json["jobId"];
                    
                    // Check the job status in a loop
                    do
                    {
                        $status = $this->CheckJobStatus($jobId, $apiKey); // Possible statuses: "working", "failed", "aborted", "success".
                        
                        // Display timestamp and status (for demo purposes)
                        //echo "<p>" . date(DATE_RFC2822) . ": " . $status . "</p>";
                        
                        if ($status == "success")
                        {
                            // Display link to JSON file with information about parsed fields
                            //echo "<div><h2>Parsing Result:</h2><a href='" . $resultFileUrl . "' target='_blank'>" . $resultFileUrl . "</a></div>";
                            
                            //Calls InsertToDb function to get key value pair from JSON file 
                            //$this->InsertToDb($resultFileUrl);
                            $jsondata = file_get_contents($resultFileUrl);
                            print_r($jsondata);
                            break;
                        }
                        else if ($status == "working")
                        {
                            // Pause for a few seconds
                            sleep(3);
                        }
                        else 
                        {
                            echo $status . "<br/>";
                            break;
                        }
                    }
                    while (true);
                }
                else
                {
                    // Display service reported error
                    echo "<p>Error: " . $json["message"] . "</p>"; 
                }
            }
            else
            {
                // Display request error
                echo "<p>Status code: " . $status_code . "</p>"; 
                echo "<p>" . $result . "</p>"; 
            }
        }
        else
        {
            // Display CURL error
            echo "Error: " . curl_error($curl);
        }
    }

    function CheckJobStatus($jobId, $apiKey)
    {
        $status = null;
        
        // Create URL
        $url = "https://api.pdf.co/v1/job/check";
        
        // Prepare requests params
        $parameters = array();
        $parameters["jobid"] = $jobId;

        // Create Json payload
        $data = json_encode($parameters);

        // Create request
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("x-api-key: " . $apiKey, "Content-type: application/json"));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //for solving certificate issue laravel
        // Execute request
        $result = curl_exec($curl);
        
        if (curl_errno($curl) == 0)
        {
            $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            if ($status_code == 200)
            {
                $json = json_decode($result, true);

                $status = $json["status"];

                if ($json["status"] == "failed")
                {
                    // Display service reported error
                    echo "<p>Error: " . $json["message"] . "</p>"; 
                }
            }
            else
            {
                // Display request error
                echo "<p>Status code: " . $status_code . "</p>"; 
                echo "<p>" . $result . "</p>"; 
            }
        }
        else
        {
            // Display CURL error
            echo "Error: " . curl_error($curl);
        }
        
        // Cleanup
        curl_close($curl);
        
        return $status;
    }
}
