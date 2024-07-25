<?php

namespace App\Http\Controllers;

use App\Traits\ActivationClass;
use App\Traits\UnloadedHelpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Mockery\Exception;
use Modules\BusinessManagement\Entities\FirebasePushNotification;
use Modules\BusinessManagement\Service\Interface\BusinessSettingServiceInterface;
use Modules\UserManagement\Entities\User;
use Illuminate\Support\Facades\Schema;
use Modules\UserManagement\Entities\WithdrawRequest;

class UpdateController extends Controller
{
    use UnloadedHelpers;
    use ActivationClass;

    protected $businessSetting;

    public function __construct(BusinessSettingServiceInterface $businessSetting)
    {
        $this->businessSetting = $businessSetting;
    }

    public function update_software_index()
    {
        $modules = ['AdminModule','AuthManagement','BusinessManagement','ChattingManagement','FareManagement',
            'Gateways','ParcelManagement','PromotionManagement','ReviewModule','TransactionManagement','TripManagement',
            'UserManagement','VehicleManagement','ZoneManagement',
        ];
        foreach ($modules as $module) {
            Artisan::call('module:enable', ['module' => $module]);
        }
        return view('update.update-software');
    }

    public function update_software(Request $request)
    {
        $this->setEnvironmentValue('SOFTWARE_ID', 'MTAwMDAwMDA=');
        $this->setEnvironmentValue('BUYER_USERNAME', $request['username']);
        $this->setEnvironmentValue('PURCHASE_CODE', $request['purchase_key']);
        $this->setEnvironmentValue('SOFTWARE_VERSION', '1.5');
        $this->setEnvironmentValue('APP_ENV', 'local');
        $this->setEnvironmentValue('APP_MODE', 'live');
        $this->setEnvironmentValue('APP_URL', url('/'));
        $this->setEnvironmentValue('PUSHER_APP_ID', 'drivemond');
        $this->setEnvironmentValue('PUSHER_APP_KEY', 'drivemond');
        $this->setEnvironmentValue('PUSHER_APP_SECRET', 'drivemond');
        $this->setEnvironmentValue('PUSHER_HOST', getMainDomain(url('/')));
        $this->setEnvironmentValue('PUSHER_PORT', 6001);
        $this->setEnvironmentValue('PUSHER_APP_CLUSTER', 'mt1');
        $this->setEnvironmentValue('PUSHER_SCHEME', 'http');
        $this->setEnvironmentValue('REVERB_APP_ID', 'drivemond');
        $this->setEnvironmentValue('REVERB_APP_KEY', 'drivemond');
        $this->setEnvironmentValue('REVERB_APP_SECRET', 'drivemond');
        $this->setEnvironmentValue('REVERB_HOST', getMainDomain(url('/')));
        $this->setEnvironmentValue('REVERB_PORT', 6001);
        $this->setEnvironmentValue('REVERB_SCHEME', 'http');

        


        Artisan::call('migrate', ['--force' => true]);

        $previousRouteServiceProvider = base_path('app/Providers/RouteServiceProvider.php');
        $newRouteServiceProvider = base_path('app/Providers/RouteServiceProvider.txt');
        copy($newRouteServiceProvider, $previousRouteServiceProvider);

        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:cache');
        Artisan::call('config:clear');
        Artisan::call('optimize:clear');
        if (FirebasePushNotification::where(['name' => 'identity_image_approved'])->first() == false) {
            FirebasePushNotification::updateOrCreate(['name' => 'identity_image_approved'], [
                'value' => 'Your identity image has been successfully reviewed and approved.',
                'status' => 1
            ]);
        }
        if (FirebasePushNotification::where(['name' => 'identity_image_rejected'])->first() == false) {
            FirebasePushNotification::updateOrCreate(['name' => 'identity_image_rejected'], [
                'value' => 'Your identity image has been rejected during our review process.',
                'status' => 1
            ]);
        }
        if (FirebasePushNotification::where(['name' => 'review_from_customer'])->first() == false) {
            FirebasePushNotification::updateOrCreate(['name' => 'review_from_customer'], [
                'value' => 'New review from a customer! See what they had to say about your service.',
                'status' => 1
            ]);
        }
        if (FirebasePushNotification::where(['name' => 'review_from_driver'])->first() == false) {
            FirebasePushNotification::updateOrCreate(['name' => 'review_from_driver'], [
                'value' => 'New review from a driver! See what he had to say about your trip.',
                'status' => 1
            ]);
        }
        $withdrawRequests = WithdrawRequest::get();
        foreach ($withdrawRequests as $withdrawRequest) {
            if ($withdrawRequest->is_approved == null) {
                $withdrawRequest->status = PENDING;
            } elseif ($withdrawRequest->is_approved == 1) {
                $withdrawRequest->status = SETTLED;
            } else {
                $withdrawRequest->status = DENIED;
            }
            $withdrawRequest->save();
        }
        $users = User::withTrashed()->get();
        foreach ($users as $user) {
            if (is_null($user->full_name)){
                $user->full_name = $user->first_name . ' ' . $user->last_name;
                $user->save();
            }
        }
        return redirect(env('APP_URL'));
    }
}
