<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use App\Models\ShopEmailTemplate;
use App\Notifications\SendNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use App\Notifications\OneSignalNotification;

class ShopUser extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'shop_user';
    protected $guarded = ['c_password'];
    private static $getList = null;
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token', 'activation_token'
    ];
    protected $appends = [
        'name',
    ];
    public function cart()
    {
        return $this->hasMany(Cart::class, 'userID', 'id')->orderBy('created_at', 'DESC');
    }
    public function orders()
    {
        return $this->hasMany(ShopOrder::class, 'user_id', 'id');
    }
    public function accessTokens()
    {
        return $this->hasMany(OauthAccessToken::class, 'user_id', 'id');
    }
    public function likes()
    {
        return $this->hasMany(ShopProductLike::class, 'user_id', 'id');
    }
    public function addresses()
    {
        return $this->hasMany(ShopUserAddress::class, 'user_id', 'id');
    }
    public static function getList()
    {
        if (self::$getList == null) {
            self::$getList = self::get()->keyBy('id');
        }
        return self::$getList;
    }
    public function branch()
    {
        return $this->belongsTo(AdminStore::class, 'branch_id', 'id');
    }
    public function peoplevine()
    {

        return $this->hasOne(PeopleVine::class, 'user_id', 'id');
    }
    public function events()
    {
        return $this->belongsToMany(\App\Models\Event::class, 'event_attendees', 'user_id', 'event_id')
            ->withPivot('status', 'created_at', 'updated_at');
    }
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class, 'user_id', 'id');
        
    }
    public function bookmarksByType($type)
    {
        return $this->bookmarks()->where('bookmarks.model_type', $type);
    }
    public function bookmark($object)
    {
        if ($this->isBookmarked($object)) {
            return $this->bookmarks()->where([
                ['bookmarks.model_type', get_class($object)],
                ['bookmarks.model_id', $object->id]
            ])->delete();
        }

        return $this->bookmarks()->create(['model_type' => get_class($object), 'model_id' => $object->id]);
    }
    public function isBookmarked($object)
    {
        return $this->bookmarks()->where([
            ['bookmarks.model_type', get_class($object)],
            ['bookmarks.model_id', $object->id]
        ])->exists();
    }
    /**
     * Send email reset password
     * @param  [type] $token [description]
     * @return [type]        [description]
     */
    public function sendPasswordResetNotification($token)
    {
        $checkContent = (new ShopEmailTemplate)->where('group', 'forgot_password')->where('status', 1)->first();
        if ($checkContent) {
            $content = $checkContent->text;
            $dataFind = [
                '/\{\{\$title\}\}/',
                '/\{\{\$reason_sendmail\}\}/',
                '/\{\{\$note_sendmail\}\}/',
                '/\{\{\$note_access_link\}\}/',
                '/\{\{\$reset_link\}\}/',
                '/\{\{\$reset_button\}\}/',
            ];
            $dataReplace = [
                trans('email.forgot_password.title'),
                trans('email.forgot_password.reason_sendmail'),
                trans('email.forgot_password.note_sendmail', ['site_admin' => config('mail.from.name')]),
                trans('email.forgot_password.note_access_link', ['reset_button' => trans('email.forgot_password.reset_button')]),
                route('password.reset', ['token' => $token]),
                trans('email.forgot_password.reset_button'),
            ];
            $content = preg_replace($dataFind, $dataReplace, $content);
            $data = [
                'content' => $content,
            ];

            $config = [
                'to' => $this->getEmailForPasswordReset(),
                'subject' => trans('email.forgot_password.reset_button'),
            ];

            sc_send_mail('mail.forgot_password', $data, $config, []);
        }
    }

    /*
    Full name
     */
    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    /* public function getTokenAttribute()
    {
        //return $this->createToken('MyApp')-> accessToken;
        return Auth::user()->token();
    } */

    /**
     * Update info customer
     * @param  [array] $dataUpdate
     * @param  [int] $id
     */
    public static function updateInfo($dataUpdate, $id)
    {
        $dataUpdate = sc_clean($dataUpdate, 'password');
        $obj = self::find($id);
        return $obj->update($dataUpdate);
    }

    /**
     * Create new customer
     * @return [type] [description]
     */
    public static function createCustomer($dataInsert)
    {
        $dataUpdate = sc_clean($dataInsert, 'password');
        return self::create($dataUpdate);
    }


    public function OneSignalNotification()
    {
        $this->notify(new OneSignalNotification($this)); //Pass the model data to the OneSignal Notificator
    }
    public function routeNotificationForOneSignal()
    {
        return 'ead4ef35-2bd8-4cbd-8369-c011e17a150b';
    }
}
