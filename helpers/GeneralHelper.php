<?php

use App\Facades\S3BucketServiceFacade;
use Illuminate\Support\Str;
use Twilio\Rest\Client;
use Intervention\Image\ImageServiceProvider;
use App\Models\User;

if (!function_exists('api_success')) {
	function api_success($message, $data, $response = 200) {
		$data = response()->json(['status' => true, 'response' => array('message' => $message, 'detail' => $data)], $response);
		return $data;
	}
}

if (!function_exists('api_success1')) {
	function api_success1($message) {
		$data = response()->json(['status' => true, 'response' => array('message' => $message)]);
		return $data;
	}
}

if (!function_exists('api_error')) {
	function api_error($message = 'There is some error!', $error_code = 400) {
		$data = response()->json(['status' => false, 'error' => array('message' => $message)], $error_code);
		return $data;
	}
}
if (!function_exists('api_error1')) {
	function api_error1($message = 'There is some error!', $error_code = 400) {
		$data = response()->json(['status' => false, 'message' => $message], $error_code);
		return $data;
	}
}

if (!function_exists('api_error_array')) {
	function api_error_array($errors = array(), $error_code = 400) {
		$data = response()->json(['status' => false, 'error' => $errors], $error_code);
		return $data;
	}
}

if (!function_exists('api_validation_error')) {
	function api_validation_error($message, $data)
	{
		$data = response()->json(['status' => false, 'error' => array('message' => $message, 'detail' => $data)]);
		return $data;
	}
}

if (!function_exists('custom_public_url')) {
    function custom_public_url($string)
    {
        echo url('public/' . $string);
    }
}

if (!function_exists('prefix')) {
    function prefix()
    {
        $prefix = substr(\Request::route()->getPrefix().'/', 0, strpos(\Request::route()->getPrefix(), '/'));
        substr($prefix.'/', 0, strpos($prefix, '/'));
        if (\Request::route()->getPrefix() == 'api' || $prefix == 'api')
            return true;
        return false;
    }
}

if (!function_exists('api_validation_error')) {
    function api_validation_error($message, $data)
    {
        $data = response()->json(['status' => false, 'error' => array('message' => $message, 'detail' => $data)]);
        return $data;
    }
}

if (!function_exists('getTokenWeb')) {
    function getTokenWeb()
    {
        $token  = Session::get('usertoken');
        if ($token) {
            return $token;
        }
        return false;
    }
}

if (!function_exists('getToken')) {
    function getToken($request)
    {
        if (preg_match('/Bearer\s(\S+)/', $request->header('Authorization'), $matches)) {
            return $matches[1];
        }
        return false;
    }
}

if (!function_exists('addFile')) {
    function addFile($file, $path, $width = '1000', $height = '1000', $resize = false)
    {
        $fileUpload = S3BucketServiceFacade::uploadFile($file);
        return $fileUpload;
        // $destinationPath = $path;
        // $file = $file;
        // $name =  rand(99, 9999999) . '.' . $file->extension();

        // if ($file->move($destinationPath, $name)) {
        //     return $name;
        // }
    }
}

if (!function_exists('generate_token')) {
    function generate_token($customer)
    {
        $token_params = [
            time(),
            $customer->email,
            "access_token",
        ];
        return base64_encode(md5(\implode("_", $token_params)));
    }
}
if (!function_exists('notification_core')) {
    function notification_core($data)
    {
        $SERVER_API_KEY = 'AAAAogqWWBs:APA91bHoUAa9yYkV4kpXM3FzqgzgbtQCYghSZogBYBl4w8uwFe6WkCvL9_YhDu62FlQFd1Es4r0esncwEq6iH746lQS77-Ms4Po46oHueyO2ay-48_dwbnvLAOkkMd9YE4j6NDRAgpd-';

        $dataString = json_encode($data);
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json'
        ];
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        $response = curl_exec($ch);
        curl_close($ch);
        return response()->json(['response' => $response], 200);
    }
}

if (!function_exists('csvToArray')) {
    function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename)) return [];

        $header = null;
        $data = array();
        $counter = 0;
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                $counter++;

                if (!$header) {
                    if ($counter == 2) {
                        return $header = $row;
                    }
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }
        return $data;
    }
}

if (!function_exists('nicetime')) {
    function nicetime($date)
    {
        if (empty($date)) {
            return "No date provided";
        }

        $periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
        $lengths         = array("60", "60", "24", "7", "4.35", "12", "10");

        $now             = time();
        $unix_date       = strtotime($date);

        // check validity of date
        if (empty($unix_date)) {
            return "Bad date";
        }

        // is it future date or past date
        if ($now > $unix_date) {
            $difference     = $now - $unix_date;
            $tense         = "ago";
        } else {
            $difference     = $unix_date - $now;
            $tense         = "from now";
        }

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if ($difference != 1) {
            $periods[$j] .= "s";
        }

        return "$difference $periods[$j] {$tense}";
    }
}

if (!function_exists('generate_unique_username')) {
    function generate_unique_username($name){
        $new_username = strtolower(str_replace(' ', '_', $name));
        (int) $count = \App\Models\User::where('username', 'LIKE', '%'.$new_username.'%')->count();
        if (empty($count)) $new_username = $new_username .'_1';
        else $new_username = $new_username .'_'. ++$count; 
        return $new_username;
    }
}

if (!function_exists('messages')) {
    function messages($errors)
    {
        if (session()->has('req_error')) { ?>
            <div class="alert custom-alert alert-danger d-flex align-items-center" role="alert">
                <ul>
                    <li><i class="fas fa-exclamation-triangle"></i>&nbsp;<?php echo session()->get('req_error') ?></li>
                </ul>
            </div>
        <?php session()->forget('req_error');
        } else if (session()->has('req_success')) { ?>
            <div class="alert custom-alert alert-success d-flex align-items-center" role="alert">
                <ul>
                    <li><i class="fas fa-solid fa-circle-check"></i>&nbsp; <?php echo session()->get('req_success') ?></li>
                </ul>
            </div>
        <?php session()->forget('req_success');
        } else if ($errors->any()) { ?>
            <div class="alert custom-alert alert-danger d-flex align-items-center" role="alert">
                <ul>
                    <?php foreach ($errors->all() as $key => $value) { ?>
                        <li><i class="fas fa-exclamation-triangle"></i> <?php echo $value ?></li>
                    <?php } ?>
                </ul>
            </div>
        <?php }
    }
}