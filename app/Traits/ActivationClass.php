<?php
namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ActivationClass
{
    public function dmvf($request)
    {
        if (self::is_local()) {
           
            $remove = array("http://","https://","www.");
            $url= str_replace($remove,"",url('/'));

            $post = [
                base64_decode('dXNlcm5hbWU=') => $request[base64_decode('dXNlcm5hbWU=')],//un
                base64_decode('cHVyY2hhc2Vfa2V5') => $request[base64_decode('cHVyY2hhc2Vfa2V5')],//pk
                base64_decode('c29mdHdhcmVfaWQ=') => base64_decode(env(base64_decode('U09GVFdBUkVfSUQ='))),//sid
                base64_decode('ZG9tYWlu') => $url
            ];

            try {
                $ch = curl_init(base64_decode('aHR0cHM6Ly9jaGVjay42YW10ZWNoLmNvbS9hcGkvdjEvZG9tYWluLWNoZWNr'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                $response = curl_exec($ch);
                //$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if (isset(json_decode($response, true)['active']) && base64_decode(json_decode($response, true)['active'])) {
                    session()->put(base64_decode('cHVyY2hhc2Vfa2V5'), $request[base64_decode('cHVyY2hhc2Vfa2V5')]);//pk
                    session()->put(base64_decode('dXNlcm5hbWU='), $request[base64_decode('dXNlcm5hbWU=')]);//un
                    return base64_decode('c3RlcDM=');//s3
                } else {
                    return base64_decode('aHR0cHM6Ly9hY3RpdmF0aW9uLmRyaXZlbW9uZC5hcHAv');
                }
            }  catch (\Exception $exception) {info($exception->getMessage());}
        }
         session()->put(base64_decode('cHVyY2hhc2Vfa2V5'), $request[base64_decode('cHVyY2hhc2Vfa2V5')]);//pk
                session()->put(base64_decode('dXNlcm5hbWU='), $request[base64_decode('dXNlcm5hbWU=')]);//un
                return base64_decode('c3RlcDM=');//s3
    }

    public function actch(): JsonResponse
    {
        if (!self::is_local()) {
            $remove = array("http://", "https://", "www.");
            $url = str_replace($remove, "", url('/'));

            $post = [
                base64_decode('dXNlcm5hbWU=') => env(base64_decode('QlVZRVJfVVNFUk5BTUU=')),//un
                base64_decode('cHVyY2hhc2Vfa2V5') => env(base64_decode('UFVSQ0hBU0VfQ09ERQ==')),//pk
                base64_decode('c29mdHdhcmVfaWQ=') => base64_decode(env(base64_decode('U09GVFdBUkVfSUQ='))),//sid
                base64_decode('ZG9tYWlu') => $url,
            ];
            try {
                $ch = Http::post(base64_decode('aHR0cHM6Ly9jaGVjay42YW10ZWNoLmNvbS9hcGkvdjEvYWN0aXZhdGlvbi1jaGVjaw=='), $post)->json();
                $status = $ch['active'] ?? base64_encode(1);
                return (int)base64_decode($status);
            } catch (\Exception $exception) {info($exception->getMessage());}
        }
        return true;
    }

    public function is_local(): bool
    {
        $whitelist = array(
            '127.0.0.1',
            '::1'
        );

        if (!in_array(request()->ip(), $whitelist)) {
            return false;
        }

        return true;
    }
}
