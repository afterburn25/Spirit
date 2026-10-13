<?php
declare(strict_types=1);
namespace Spirit;

final class ContentAdmin
{
    public static function boot(): array { $user=Auth::requireOwner();if (!Migrations::ready()) {go('admin/updates.php');}return $user; }
    public static function field(string $key,array $definition,mixed $value): void
    {
        [$label,$type,$options]=$definition;$id='field-'.$key;
        echo '<label for="'.e($id).'">'.e($label).'</label>';
        if ($type==='textarea') {echo '<textarea id="'.e($id).'" name="'.e($key).'" rows="6" maxlength="'.(int)$options.'">'.e($value).'</textarea>';return;}
        if ($type==='select' || $type==='timezone' || $type==='release' || str_starts_with($type,'media:')) {
            if ($type==='timezone') {$options=array_combine(\DateTimeZone::listIdentifiers(),\DateTimeZone::listIdentifiers());$value=$value?:'America/Chicago';}
            elseif ($type==='release') {$options=[''=>'Choose a release'];foreach(Content::listing('release') as $item){$options[$item['id']]=$item['title'];}}
            elseif (str_starts_with($type,'media:')) {$options=[''=>'No file selected'];foreach(Media::all(substr($type,6)) as $item){$options[$item['id']]='#'.$item['id'].' · '.$item['original_name'];}}
            echo '<select id="'.e($id).'" name="'.e($key).'">';foreach($options as $option=>$text){echo '<option value="'.e($option).'"'.((string)$option===(string)$value?' selected':'').'>'.e($text).'</option>';}echo '</select>';
            if(str_starts_with($type,'media:')){echo '<small class="muted">Upload files in Media library. Files remain private until referenced by published content.</small>';}return;
        }
        $inputType=in_array($type,['date','datetime-local','url','email'],true)?$type:'text';
        echo '<input id="'.e($id).'" type="'.e($inputType).'" name="'.e($key).'" value="'.e($value).'"'.(is_int($options)&&$options>0?' maxlength="'.$options.'"':'').'>';
    }
}
