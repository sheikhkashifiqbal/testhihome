<?php
/**
 * File function process image
 * @author Naruto <lanhktc@gmail.com>
 * From version: S-cart 3.0
 */

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

/**
 * Function upload image
 */
function sc_image_upload($fileContent, $path = null, $name = null, $options = ['unique_name' => true, 'thumb' => false, 'watermark' => false])
{
    $disk = 'public';
    $pathFile = null;
    try {
        $fileName = false;
        if ($name) {
            $fileName = $name . '.' . $fileContent->getClientOriginalExtension();
        } elseif (empty($options['unique_name'])) {
            $fileName = $fileContent->getClientOriginalName();
        }

        //Save as file
        if ($fileName) {
            $pathFile = Storage::disk($disk)->putFileAs(($path ?? ''), $fileContent, $fileName);
        }
        //Save file id unique
        else {
            $pathFile = Storage::disk($disk)->putFile(($path ?? ''), $fileContent);
        }
    } catch (\Exception $e) {
        return null;
    }

    if ($pathFile && $disk == 'public') {
        //generate thumb
        if (!empty($options['thumb']) && sc_config('upload_image_thumb_status')) {
            sc_image_generate_thumb($pathFile, $widthThumb = 250, $heightThumb = null, $disk);
        }

        //insert watermark
        if (!empty($options['watermark']) && sc_config('upload_watermark_status')) {
            sc_image_insert_watermark($pathFile);
        }
    }
    if ($disk == 'public') {
        return 'storage/' . $pathFile;
    } else {
        return Storage::disk($disk)->url($pathFile);
    }
}

/**
 * Function insert watermark
 */
function sc_image_insert_watermark($pathFile)
{
    $pathWatermark = sc_config('upload_watermark_path');
    if (empty($pathWatermark)) {
        return false;
    }
    $pathReal = config('filesystems.disks.public.root') . '/' . $pathFile;
    Image::make($pathReal)
        ->insert(public_path($pathWatermark), 'bottom-right', 10, 10)
        ->save($pathReal);
}

/**
 * Function generate thumb
 */
function sc_image_generate_thumb($pathFile, $widthThumb = null, $heightThumb = null, $disk = 'public')
{
    $widthThumb = $widthThumb ?? sc_config('upload_image_thumb_width', 250);
    if (!Storage::disk($disk)->has('tmp')) {
        Storage::disk($disk)->makeDirectory('tmp');
    }

    $pathReal = config('filesystems.disks.public.root') . '/' . $pathFile;
    $image_thumb = Image::make($pathReal);
    $image_thumb->resize($widthThumb, $heightThumb, function ($constraint) {
        $constraint->aspectRatio();
    });
    $tmp = '/tmp/' . time() . rand(10, 100);

    $image_thumb->save(config('filesystems.disks.public.root') . $tmp);
    if (Storage::disk($disk)->exists('/thumb/' . $pathFile)) {
        Storage::disk($disk)->delete('/thumb/' . $pathFile);
    }
    Storage::disk($disk)->move($tmp, '/thumb/' . $pathFile);
}

/**
 * Function rener image
 */

function sc_image_render($path, $width = null, $height = null, $alt = null, $title = null, $url = null, $options = '')
{
    $image = sc_image_get_path($path, $url);
    $style = '';
    $style .= ($width) ? ' width:' . $width . ';' : '';
    $style .= ($height) ? ' height:' . $height . ';' : '';
    return '<img  alt="' . $alt . '" title="' . $title . '" ' . (($options) ?? '') . ' src="' . asset($image) . '"   ' . ($style ? 'style="' . $style . '"' : '') . '   >';
}

/**
 * Function rener image from CDN
 */

function cdn_image_render($path, $width = null, $height = null, $alt = null, $title = null, $url = null, $options = '')
{
    $style = '';
    $image = '';
    if($width && $height){
      $image = CDN_URL . $width . 'x' . $height . "/" . $path;

      $style .= ($width) ? ' width:' . $width . 'px;' : '';
      $style .= ($height) ? ' height:' . $height . 'px;' : '';
    }else{
      $image = CDN_URL . $path;
    }

    return '<img  alt="' . $alt . '" title="' . $title . '" ' . (($options) ?? '') . ' src="' . asset($image) . '"   ' . ($style ? 'style="' . $style . '"' : '') . '   >';
}

/*
Return path image
 */
function sc_image_get_path($path, $urlDefault = null)
{
    $noimage_path = asset('images/no-image.jpg');
    $image = $urlDefault ?? sc_config('no_image', $noimage_path);
    $path=trim($path,'/');

    if ($path) {
        if (file_exists(public_path($path)) || filter_var(str_replace(' ','%20', $path), FILTER_VALIDATE_URL)) {
            $image = env('APP_URL').'/'.$path;

        } else {
            $image = $image;
        }
    }
    return $image;
}

/*
Return CDN PATH
 */
function sc_image_cdn_get_path($path, $urlDefault = null)
{
    if(!Storage::disk('s3')->exists($path)){
     return 'common/no-image.jpg';
    }
    return $path;
}

/*
Function get path thumb of image if saved in storage
 */
function sc_image_get_path_thumb($pathFile)
{
    if (strpos($pathFile, "/storage/") === 0) {
        $arrPath = explode('/', $pathFile);
        $fileName = end($arrPath);
        $pathThumb = substr($pathFile, 0, -strlen($fileName)) . 'thumbs/' . $fileName;
        if (file_exists(public_path($pathThumb))) {
            return $pathThumb;
        } else {
            return sc_image_get_path($pathFile);
        }
    } else {
        return $pathFile;
    }
}
/*
If image exist
 */
function sc_check_image_exist($path, $urlDefault = null)
{
    $image = $urlDefault ?? sc_config('no_image', "");
    $path=trim($path,'/');

    if ($path) {
        if (file_exists(public_path($path)) || filter_var(str_replace(' ','%20', $path), FILTER_VALIDATE_URL)) {
            $image = env('APP_URL').'/'.$path;

        }
    }
    return $image;
}

/*
Upload the image to s3 from the local storage, file manager
 */
function cdn_image_upload_from_storage($path, $type)
{
  $new_path = explode('data', $path);

  if(count($new_path) > 1){
    $image = Storage::disk('uploads')->get(end($new_path));

    $extension = \File::extension($path);
    $value_array = explode("/", $path);

    if($type == 'logo' || $type=='logo2'){
      $file_name = env('AWS_LOGOS_FOLDER_PATH').'/logo_photo_' . time() . '.' . $extension;
    }else if($type == 'store_featured'){
      $file_name = env('AWS_STORE_PATH').'/store_featured_' . time() . '.' . $extension;
    }else if($type == 'license_photo'){
      $file_name = env('AWS_LICENSE_FOLDER_PATH').'/license_photo_' . time() . '.' . $extension;
    }else if($type == 'products'){
      $file_name = env('AWS_PRODUCT_FOLDER_PATH').'/product_' . time() . '.' . $extension;
    }else if($type == 'banners'){
      $file_name = env('AWS_BANNERS_PATH').'/banner_' . time() . '.' . $extension;
    }else if($type == 'categories'){
      $file_name = env('AWS_CATEGORIES_PATH').'/category_' . time() . '.' . $extension;
    }

    Storage::disk('s3')->put($file_name,$image);
    return $file_name;
  }
  return $path;
}
