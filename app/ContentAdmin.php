<?php
declare(strict_types=1);
namespace Spirit;

final class ContentAdmin
{
    public static function boot(string $permission='owner'): array { $user=Auth::requirePermission($permission);if (!Migrations::ready()) {if(Permissions::allows($user,'owner')){go('admin/updates.php');}http_response_code(503);exit('An owner must apply the pending website update before staff can continue.');}return $user; }
    public static function field(string $key,array $definition,mixed $value): void
    {
        [$label,$type,$options]=$definition;$id='field-'.$key;if(!is_scalar($value)&&$value!==null){$value='';}
        echo '<label for="'.e($id).'">'.e($label).'</label>';
        if ($type==='textarea') {echo '<textarea id="'.e($id).'" name="'.e($key).'" rows="6" maxlength="'.(int)$options.'">'.e($value).'</textarea>';return;}
        if ($type==='select' || $type==='timezone' || $type==='release' || str_starts_with($type,'media:')) {
            if ($type==='timezone') {$options=array_combine(\DateTimeZone::listIdentifiers(),\DateTimeZone::listIdentifiers());$value=$value?:'America/Chicago';}
            elseif ($type==='release') {$options=[''=>'Choose a release'];foreach(Content::listing('release') as $item){$options[$item['id']]=$item['title'];}if((int)$value>0&&!isset($options[(int)$value])){$selected=Content::get((int)$value);if($selected&&$selected['kind']==='release'){$options[$selected['id']]=$selected['title'];}}}
            elseif (str_starts_with($type,'media:')) {$options=[''=>'No file selected'];foreach(Media::all(substr($type,6)) as $item){$options[$item['id']]='#'.$item['id'].' · '.$item['original_name'];}if((int)$value>0&&!isset($options[(int)$value])){$selected=Media::get((int)$value);if($selected&&$selected['kind']===substr($type,6)){$options[$selected['id']]='#'.$selected['id'].' · '.$selected['original_name'];}}}
            $imagePicker=$type==='media:image';
            echo '<select id="'.e($id).'" name="'.e($key).'"'.($imagePicker?' data-media-picker="image"':'').'>';
            foreach($options as $option=>$text){
                $preview=$imagePicker&&ctype_digit((string)$option)?' data-media-src="'.e(Media::url((int)$option)).'"':'';
                echo '<option value="'.e($option).'"'.$preview.((string)$option===(string)$value?' selected':'').'>'.e($text).'</option>';
            }
            echo '</select>';
            if($imagePicker){
                $selected=(int)$value>0?Media::get((int)$value):null;
                $hasImage=$selected&&$selected['kind']==='image';
                echo '<div class="admin-media-preview" data-media-preview-for="'.e($id).'">';
                echo '<img data-media-preview-image alt="Selected image preview"'.($hasImage?' src="'.e(Media::url((int)$value)).'"':' hidden').'>';
                echo '<span data-media-empty'.($hasImage?' hidden':'').'>No image selected</span></div>';
            }
            if(str_starts_with($type,'media:')){echo '<small class="muted">Upload files in Media library. Files remain private until referenced by published content.</small>';}return;
        }
        $inputType=in_array($type,['date','datetime-local','url','email'],true)?$type:'text';
        echo '<input id="'.e($id).'" type="'.e($inputType).'" name="'.e($key).'" value="'.e($value).'"'.(is_int($options)&&$options>0?' maxlength="'.$options.'"':'').'>';
    }
}
