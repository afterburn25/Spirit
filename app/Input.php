<?php
declare(strict_types=1);
namespace Spirit;
use DateTimeImmutable;
use InvalidArgumentException;

final class Input
{
    public static function text(array $input,string $key,int $maximum,bool $required=false): string
    {
        $value=$input[$key]??'';
        if(!is_string($value)||str_contains($value,"\0")||!preg_match('//u',$value)){throw new InvalidArgumentException('Invalid value for '.str_replace('_',' ',$key).'.');}
        $value=trim($value);
        if(strlen($value)>$maximum||($required&&$value==='')){throw new InvalidArgumentException(ucfirst(str_replace('_',' ',$key)).($required?' is required and':'').' must be at most '.$maximum.' bytes.');}
        return $value;
    }
    public static function integer(array $input,string $key,int $min,int $max,int $default=0): int
    {
        $raw=$input[$key]??$default;
        if(!is_int($raw)&&!is_string($raw)){throw new InvalidArgumentException('Invalid '.str_replace('_',' ',$key).'.');}
        $value=filter_var($raw,FILTER_VALIDATE_INT,['options'=>['min_range'=>$min,'max_range'=>$max]]);
        if($value===false){throw new InvalidArgumentException(ucfirst(str_replace('_',' ',$key)).' must be a whole number from '.$min.' to '.$max.'.');}return $value;
    }
    public static function date(string $value,bool $required=false): string
    {
        if($value===''&&!$required){return '';}
        $date=DateTimeImmutable::createFromFormat('!Y-m-d',$value);
        if(!$date||$date->format('Y-m-d')!==$value){throw new InvalidArgumentException('Enter a valid date in year-month-day format.');}return $value;
    }
    public static function choice(array $input,string $key,array $allowed,string $default): string
    {
        $value=self::text($input,$key,100);$value=$value===''?$default:$value;
        if(!in_array($value,$allowed,true)){throw new InvalidArgumentException('Choose a valid '.str_replace('_',' ',$key).'.');}return $value;
    }
}
