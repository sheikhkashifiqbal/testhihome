<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;
use URL;

/**
 * @property int start_date
 */
class Event extends MyBaseModel
{
    use SoftDeletes;

    protected $dates = ['start_date', 'end_date', 'on_sale_date'];
    protected $appends = [

        'responded_by_auth_user',

    ];

    /**
     * The validation error messages.
     *
     * @var array $messages
     */
    protected $messages = [
        'title.required'                       => 'You must at least give a title for your event.',

        'event_image.mimes'                    => 'Please ensure you are uploading an image (JPG, PNG, JPEG)',
        'event_image.max'                      => 'Please ensure the image is not larger then 3MB',
        'location_venue_name.required_without' => 'Please enter a venue for your event',
        'venue_name_full.required_without'     => 'Please enter a venue for your event',
    ];

    /**
     * The validation rules.
     *
     * @return array $rules
     */
    public function rules()
    {
        $format = config('attendize.default_datetime_format');
        return [
            'title'               => 'required',
            'description'         => 'required',
            'location_venue_name' => 'required_without:venue_name_full',

            'start_date'          => 'required|date_format:"Y-m-d H:i"',
            'end_date'            => 'required|date_format:"Y-m-d H:i"',
            'event_image'         => 'nullable|mimes:jpeg,jpg,png|max:3000',
        ];
    }




    /**
     * The images associated with the event.
     *
     * @return HasMany
     */
    public function images()
    {
        return $this->hasMany(EventImage::class);
    }



    /**
     * Parse start_date to a Carbon instance
     *
     * @param  string  $date  DateTime
     */
    public function setStartDateAttribute($date)
    {
        $format = 'Y-m-d H:i';

        if ($date instanceof Carbon) {
            $this->attributes['start_date'] = $date->format($format);
        } else {
            $this->attributes['start_date'] = Carbon::createFromFormat($format, $date);
        }
    }

    /**
     * Format start date from user preferences
     * @return String Formatted date
     */
    public function startDateFormatted()
    {
        return $this->start_date->format('M d,Y H:i');
    }

    /**
     * Parse end_date to a Carbon instance
     *
     * @param  string  $date  DateTime
     */
    public function setEndDateAttribute($date)
    {
        $format = 'd M y H:i';

        if ($date instanceof Carbon) {
            $this->attributes['end_date'] = $date->format($format);
        } else {
            $this->attributes['end_date'] = Carbon::createFromFormat($format, $date);
        }
    }

    /**
     * Format end date from user preferences
     * @return String Formatted date
     */
    public function endDateFormatted()
    {
        return $this->end_date->format('M d,Y H:i');
    }

    /**
     * Indicates whether the event is currently happening.
     *
     * @return bool
     */
    public function getHappeningNowAttribute()
    {
        return Carbon::now()->between($this->start_date, $this->end_date);
    }


    public function attendees()
    {

        return $this->belongsToMany(\App\Models\ShopUser::class, 'event_attendees', 'user_id', 'event_id')
            ->withPivot('status', 'created_at', 'updated_at')->withTimestamps();
    }
    public function attendee()
    {

        return $this->hasMany(\App\Models\EventAttendee::class);
    }



    /**
     * Get a usable address for embedding Google Maps
     *
     */
    public function getMapAddressAttribute()
    {
        $string = $this->venue . ','
            . $this->location_address_line_1 . ','
            . $this->location_address_line_2 . ','
            . $this->location_state;

        return urlencode($string);
    }

    public function getFullAddressAttribute()
    {
        $addr = [];
        if ($this->venue_name) $addr[] = $this->venue_name;
        if ($this->location_address_line_1) $addr[] = $this->location_address_line_1;
        if ($this->location_address_line_2) $addr[] = $this->location_address_line_2;
        if ($this->location_state) $addr[] = $this->location_state;
        $addr = implode(', ', $addr);
        return $addr;
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @return array $dates
     */
    public function getDates()
    {
        return ['created_at', 'updated_at', 'start_date', 'end_date'];
    }

    public function getRespondedByAuthUserAttribute()
    {
        if (isset(auth()->user()->id) && auth()->user()->id) {
            $responde = $this->attendee->where('user_id', auth()->user()->id)->first();
            if ($responde) {

                return ['responde' => true, 'status' => $responde->status];
            } else {
                return ['responde' => false];
            }
        }


        return ['responde' => false];
    }
}
