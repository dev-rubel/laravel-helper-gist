<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Stichoza\GoogleTranslate\GoogleTranslate;

if (!function_exists('fileExist')) {

    function fileExist($value, $with_public_path=false) {
        
        if($with_public_path) {

            return $value != null ? file_exists( public_path().'/'. $value ) : false;
        } else {

            return $value != null ? file_exists( $value ) : false;
        }
    }
}

if (!function_exists('checkvideoext')) {

    function checkvideoext($value) {
        $last = \Str::of($value)->explode('.')->last();
        if(in_array($last, ['mp4','ogg','webm'])) {
            return $last;
        }
        return false;
    }
}

if (!function_exists('activeMenu')) {

    function activeMenu($value, $paramName=null, $paramValue=null) {
        $r = request()->route()->getName();
        if($paramName!='' && $paramValue!='') {
            return ((request()->query($paramName) == $paramValue) && ($r==$value))?'active':'';
        } else {
            return $r==$value?'active':'';
        }
    }
}

if (!function_exists('cmsValue')) {

    function cmsValue($section, $name, $type, $mediaType='single') {
        
        $page = \Modules\Page\Entities\Page::where('section', $section)->first();
        if($page) {
            if($type == 'titles') {
                $value = $page->titles()->where('level', $name)->first();
                if($value) {
                    return $value->text;
                }
            }
            if($type == 'descriptions') {
                $value = $page->descriptions()->where('level', $name)->first();
                if($value) {
                    return $value->text;
                }
            }
            if($type == 'media') {
                if($mediaType == 'single') {
                    return $page->media;
                }
                if($mediaType == 'multi') {
                    $value = $page->images()->where('level', $name)->first();
                    if($value) {
                        return $value->url;
                    }
                }
            }
        }
        return '';
    }
}

if (!function_exists('gTrans')) {

    function gTrans($value, $admin=false) {
        // return $value;
        $lang = app()->getLocale();
        if($admin) {
            $lang = session('backend') ?? app()->getLocale();
        }
        // dump($lang);
        $check = DB::table('languages')->where('pointer', $value)->first();
        if($check) {
            return $check->$lang;
        } else {
            $data['ar'] = GoogleTranslate::trans($value, 'ar', 'en');    
            $data['en'] = $value;
            $data['pointer'] = $value;
            $id = DB::table('languages')->insertGetId($data);
            $data = DB::table('languages')->where('id', $id)->first();
            return $data->$lang;
        }
    }

}

if (!function_exists('deleteStorageFile')) {
    function deleteStorageFile($filePath){
        if(Storage::disk('public')->has( $filePath ) ){
            Storage::disk('public')->delete( $filePath );
        }
    }
}

if (!function_exists('gSlugLast')) {
    function gSlugLast($data){
        $tmp = explode('-', $data);
        return end($tmp);
    }
}

if (!function_exists('getGeocodeData')) {
    function getGeocodeData($address) {
        if($address) {

            $address = urlencode($address);
            $googleMapUrl = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key=AIzaSyCKVoBHFYpGH9aBzIxQVnsEUGiYqBAauMc";
            $geocodeResponseData = file_get_contents($googleMapUrl);
            $responseData = json_decode($geocodeResponseData, true);
            if($responseData['status']=='OK') {
                $latitude = isset($responseData['results'][0]['geometry']['location']['lat']) ? $responseData['results'][0]['geometry']['location']['lat'] : "";
                $longitude = isset($responseData['results'][0]['geometry']['location']['lng']) ? $responseData['results'][0]['geometry']['location']['lng'] : "";
                $formattedAddress = isset($responseData['results'][0]['formatted_address']) ? $responseData['results'][0]['formatted_address'] : "";         
                if($latitude && $longitude && $formattedAddress) {         
                    $geocodeData = array();                         
                    array_push(
                        $geocodeData, 
                        $latitude, 
                        $longitude, 
                        $formattedAddress
                    );             
                    return $geocodeData;             
                } else {
                    return false;
                }   
            } else {
                echo "ERROR: {$responseData['status']}";
                return false;
            }
        }      
    }

}

if (!function_exists('gNavActive')) {

    function gNavActive($expression, $param=null, $value=null) 
    {   
        $routeName = request()->route()->getName();
        if($param != null) {
            if(strpos($routeName, $expression) !== false && request()->$param == $value) {
                echo 'active';
            } else {
                echo null;
            }
        } else {
            echo strpos($routeName, $expression) !== false ? ' active' : null;
        }
    }

}

if (!function_exists('gReferral')) {
    function gReferral() {
        $bytes = random_bytes(6);
        return bin2hex($bytes);
    }

}

if (!function_exists('gOrderNumber')) {

	function gOrderNumber()
	{
		$orderObj = \DB::table('orders')->select('order_no')->latest('id')->first();
        if ($orderObj) {
            $orderNr = $orderObj->order_no;
            preg_match("/([a-zA-Z]+)(\\d+)/", $orderNr, $matches);
            list($orderNo, $first, $last) = array_pad($matches, 3, null);
            if($first != null && $last != null) {
                if($last == '1999999999') {
                   $first = ++$first; // AA -> AB -> AC ...
                    $generateOrder_nr = $first.'1' . str_pad(1, 9, "0", STR_PAD_LEFT);
                } else {
                    $generateOrder_nr = $first. str_pad($last + 1, 9, "0", STR_PAD_LEFT);
                }
            } else {
                $generateOrder_nr = 'AA1' . str_pad(1, 9, "0", STR_PAD_LEFT);
            }
        } else {
            $generateOrder_nr = 'AA1' . str_pad(1, 9, "0", STR_PAD_LEFT);
        }
        return $generateOrder_nr;
	}

}


/*
 * Global helpers file with misc functions.
 */
if (!function_exists('app_name')) {
    /**
     * Helper to grab the application name.
     *
     * @return mixed
     */
    function app_name()
    {
        return config('app.name');
    }
}

/*
 * Global helpers file with misc functions.
 */
if (!function_exists('user_registration')) {
    /**
     * Helper to grab the application name.
     *
     * @return mixed
     */
    function user_registration()
    {
        $user_registration = false;

        if (env('USER_REGISTRATION') == 'true') {
            $user_registration = true;
        }

        return $user_registration;
    }
}

/*
 *
 * label_case
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('label_case')) {

    /**
     * Prepare the Column Name for Lables.
     */
    function label_case($text)
    {
        $order = ['_', '-'];
        $replace = ' ';

        $new_text = trim(\Illuminate\Support\Str::title(str_replace('"', '', $text)));
        $new_text = trim(\Illuminate\Support\Str::title(str_replace($order, $replace, $text)));
        $new_text = preg_replace('!\s+!', ' ', $new_text);

        return $new_text;
    }
}

/*
 *
 * show_column_value
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('show_column_value')) {
    /**
     * Return Column values as Raw and formatted.
     *
     * @param string $valueObject   Model Object
     * @param string $column        Column Name
     * @param string $return_format Return Type
     *
     * @return string Raw/Formatted Column Value
     */
    function show_column_value($valueObject, $column, $return_format = '')
    {
        $column_name = $column->Field;
        $column_type = $column->Type;

        $value = $valueObject->$column_name;

        if ($return_format == 'raw') {
            return $value;
        }

        if (($column_type == 'date') && $value != '') {
            $datetime = \Carbon\Carbon::parse($value);

            return $datetime->isoFormat('LL');
        } elseif (($column_type == 'datetime' || $column_type == 'timestamp') && $value != '') {
            $datetime = \Carbon\Carbon::parse($value);

            return $datetime->isoFormat('LLLL');
        } elseif ($column_type == 'json') {
            $return_text = json_encode($value);
        } elseif ($column_type != 'json' && \Illuminate\Support\Str::endsWith(strtolower($value), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) {
            $img_path = asset($value);

            $return_text = '<figure class="figure">
                                <a href="'.$img_path.'" data-lightbox="image-set" data-title="Path: '.$value.'">
                                    <img src="'.$img_path.'" style="max-width:200px;" class="figure-img img-fluid rounded img-thumbnail" alt="">
                                </a>
                                <figcaption class="figure-caption">Path: '.$value.'</figcaption>
                            </figure>';
        } else {
            $return_text = $value;
        }

        return $return_text;
    }
}

/*
 *
 * fielf_required
 * Show a * if field is required
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('fielf_required')) {

    /**
     * Prepare the Column Name for Lables.
     */
    function fielf_required($required)
    {
        $return_text = '';

        if ($required != '') {
            $return_text = '<span class="text-danger">*</span>';
        }

        return $return_text;
    }
}

/*
 * Get or Set the Settings Values
 *
 * @var [type]
 */
if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        if (is_null($key)) {
            return new App\Models\Setting();
        }

        if (is_array($key)) {
            return App\Models\Setting::set($key[0], $key[1]);
        }

        $value = App\Models\Setting::get($key);

        return is_null($value) ? value($default) : $value;
    }
}

/*
 * Show Human readable file size
 *
 * @var [type]
 */
if (!function_exists('humanFilesize')) {
    function humanFilesize($size, $precision = 2)
    {
        $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $step = 1024;
        $i = 0;

        while (($size / $step) > 0.9) {
            $size = $size / $step;
            $i++;
        }

        return round($size, $precision).$units[$i];
    }
}

/*
 *
 * Encode Id to a Hashids\Hashids
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('encode_id')) {

    /**
     * Prepare the Column Name for Lables.
     */
    function encode_id($id)
    {
        $hashids = new Hashids\Hashids(config('app.salt'), 3, 'abcdefghijklmnopqrstuvwxyz1234567890');
        $hashid = $hashids->encode($id);

        return $hashid;
    }
}

/*
 *
 * Decode Id to a Hashids\Hashids
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('decode_id')) {

    /**
     * Prepare the Column Name for Lables.
     */
    function decode_id($hashid)
    {
        $hashids = new Hashids\Hashids(config('app.salt'), 3, 'abcdefghijklmnopqrstuvwxyz1234567890');
        $id = $hashids->decode($hashid);

        if (count($id)) {
            return $id[0];
        } else {
            abort(404);
        }
    }
}

/*
 *
 * Prepare a Slug for a given string
 * Laravel default str_slug does not work for Unicode
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('slug_format')) {

    /**
     * Format a string to Slug.
     */
    function slug_format($string)
    {
        $base_string = $string;

        $string = preg_replace('/\s+/u', '-', trim($string));
        $string = str_replace('/', '-', $string);
        $string = str_replace('\\', '-', $string);
        $string = strtolower($string);

        $slug_string = $string;

        return $slug_string;
    }
}

if (!function_exists('html_entity_rm')) {

    /**
     * Format a string to Slug.
     */
    function html_entity_rm($string)
    {
        $string = strip_tags($string);
        $string = preg_replace("/&#?[a-z0-9]+;/i"," ",$string);
        return $string;
    }
}

/*
 *
 * icon
 * A short and easy way to show icon fornts
 * Default value will be check icon from FontAwesome
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('icon')) {

    /**
     * Format a string to Slug.
     */
    function icon($string = 'fas fa-check')
    {
        $return_string = "<i class='".$string."'></i>";

        return $return_string;
    }
}

/*
 *
 * logUserAccess
 * Get current user's `name` and `id` and
 * log as debug data. Additional text can be added too.
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('logUserAccess')) {

    /**
     * Format a string to Slug.
     */
    function logUserAccess($text = "")
    {
        $auth_text = "";

        if (\Auth::check()) {
            $auth_text = "User:".\Auth::user()->name." (ID:".\Auth::user()->id.")";
        }

        \Log::debug(label_case($text)." | $auth_text");
    }
}

/*
 *
 * bn2enNumber
 * Convert a Bengali number to English
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('bn2enNumber')) {

    /**
     * Prepare the Column Name for Lables.
     */
    function bn2enNumber($number)
    {
        $search_array = ['১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০'];
        $replace_array = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];

        $en_number = str_replace($search_array, $replace_array, $number);

        return $en_number;
    }
}

/*
 *
 * bn2enNumber
 * Convert a English number to Bengali
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('en2bnNumber')) {

    /**
     * Prepare the Column Name for Lables.
     */
    function en2bnNumber($number)
    {
        $search_array = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
        $replace_array = ['১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০'];

        $bn_number = str_replace($search_array, $replace_array, $number);

        return $bn_number;
    }
}

/*
 *
 * bn2enNumber
 * Convert a English number to Bengali
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('en2bnDate')) {

    /**
     * Convert a English number to Bengali.
     */
    function en2bnDate($date)
    {
        // Convert numbers
        $search_array = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
        $replace_array = ['১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০'];
        $bn_date = str_replace($search_array, $replace_array, $date);

        // Convert Short Week Day Names
        $search_array = ['Fri', 'Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu'];
        $replace_array = ['শুক্র', 'শনি', 'রবি', 'সোম', 'মঙ্গল', 'বুধ', 'বৃহঃ'];
        $bn_date = str_replace($search_array, $replace_array, $bn_date);

        // Convert Month Names
        $search_array = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $replace_array = ['জানুয়ারী', 'ফেব্রুয়ারী', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগষ্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];
        $bn_date = str_replace($search_array, $replace_array, $bn_date);

        // Convert Short Month Names
        $search_array = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $replace_array = ['জানুয়ারী', 'ফেব্রুয়ারী', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগষ্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];
        $bn_date = str_replace($search_array, $replace_array, $bn_date);

        // Convert AM-PM
        $search_array = ['am', 'pm', 'AM', 'PM'];
        $replace_array = ['পূর্বাহ্ন', 'অপরাহ্ন', 'পূর্বাহ্ন', 'অপরাহ্ন'];
        $bn_date = str_replace($search_array, $replace_array, $bn_date);

        return $bn_date;
    }
}

/*
 *
 * banglaDate
 * Get the Date of Bengali Calendar from the Gregorian Calendar
 * By default is will return the Today's Date
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('banglaDate')) {
    function banglaDate($date_input = '')
    {
        if ($date_input == '') {
            $date_input = date('Y-m-d');
        }

        $date_input = strtotime($date_input);

        $en_day = date('d', $date_input);
        $en_month = date('m', $date_input);
        $en_year = date('Y', $date_input);

        $bn_month_days = [30, 30, 30, 30, 31, 31, 31, 31, 31, 31, 29, 30];
        $bn_month_middate = [13, 12, 14, 13, 14, 14, 15, 15, 15, 16, 14, 14];
        $bn_months = ['পৌষ', 'মাঘ', 'ফাল্গুন', 'চৈত্র', 'বৈশাখ', 'জ্যৈষ্ঠ', 'আষাঢ়', 'শ্রাবণ', 'ভাদ্র', 'আশ্বিন', 'কার্তিক', 'অগ্রহায়ণ'];

        // Day & Month
        if ($en_day <= $bn_month_middate[$en_month - 1]) {
            $bn_day = $en_day + $bn_month_days[$en_month - 1] - $bn_month_middate[$en_month - 1];
            $bn_month = $bn_months[$en_month - 1];

            // Leap Year
            if (($en_year % 400 == 0 || ($en_year % 100 != 0 && $en_year % 4 == 0)) && $en_month == 3) {
                $bn_day += 1;
            }
        } else {
            $bn_day = $en_day - $bn_month_middate[$en_month - 1];
            $bn_month = $bn_months[$en_month % 12];
        }

        // Year
        $bn_year = $en_year - 593;
        if (($en_year < 4) || (($en_year == 4) && (($en_date < 14) || ($en_date == 14)))) {
            $bn_year -= 1;
        }

        $return_bn_date = $bn_day.' '.$bn_month.' '.$bn_year;
        $return_bn_date = en2bnNumber($return_bn_date);

        return $return_bn_date;
    }
}

/*
 *
 * Decode Id to a Hashids\Hashids
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('generate_rgb_code')) {

    /**
     * Prepare the Column Name for Lables.
     */
    function generate_rgb_code($opacity = '0.9')
    {
        $str = '';
        for ($i = 1; $i <= 3; $i++) {
            $num = mt_rand(0, 255);
            $str .= "$num,";
        }
        $str .= "$opacity,";
        $str = substr($str, 0, -1);

        return $str;
    }
}

/*
 *
 * Return Date with weekday
 *
 * ------------------------------------------------------------------------
 */
if (!function_exists('date_today')) {

    /**
     * Return Date with weekday
     *
     * Carbon Locale will be considered here
     * Example:
     * শুক্রবার, ২৪ জুলাই ২০২০
     * Friday, July 24, 2020
     */
    function date_today()
    {
        $str = \Carbon\Carbon::now()->format('M, D d, Y');
        // $str = \Carbon\Carbon::now()->isoFormat('dddd, LL');

        return $str;
    }
}
