<?php
declare(strict_types=1);
namespace Spirit;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

/** Fixed structured fields preserve the approved layout; no executable templates. */
final class ContentSchema
{
    public static function kinds(): array { return ['page'=>'Pages','release'=>'Music releases','track'=>'Audio tracks','event'=>'Shows','member'=>'Band members','gallery'=>'Photo gallery','video'=>'Videos','press'=>'Press downloads']; }
    public static function fields(string $kind): array
    {
        $fields = match ($kind) {
            'page' => ['subtitle'=>['Subtitle','text',200],'body'=>['Page text','textarea',16000],'image_id'=>['Feature image','media:image',0],'focal'=>['Image focus','select',['center'=>'Center','top'=>'Top','left'=>'Left','right'=>'Right']]],
            'release' => ['release_type'=>['Release type','select',['album'=>'Album','single'=>'Single','ep'=>'EP']],'release_date'=>['Release date','date',0],'description'=>['About this release','textarea',8000],'cover_id'=>['Cover artwork','media:image',0],'spotify'=>['Spotify URL','url',2000],'apple'=>['Apple Music URL','url',2000],'youtube'=>['YouTube URL','url',2000],'amazon'=>['Amazon Music URL','url',2000],'bandcamp'=>['Bandcamp URL','url',2000],'credits'=>['Credits','textarea',4000]],
            'track' => ['release_id'=>['Release','release',0],'audio_id'=>['Authorized audio recording','media:audio',0],'duration'=>['Display duration, for example 3:42','duration',0],'description'=>['Track notes','textarea',2000]],
            'event' => ['starts_at'=>['Start date and local time','datetime-local',0],'timezone'=>['Event timezone','timezone',0],'venue'=>['Venue','text',180],'city'=>['City / region','text',180],'address'=>['Street address','text',300],'event_state'=>['Admission / event status','select',['scheduled'=>'Scheduled','free'=>'Free admission','coming_soon'=>'Tickets coming soon','sold_out'=>'Sold out','postponed'=>'Postponed','cancelled'=>'Cancelled']],'ticket_url'=>['Venue ticket seller URL','url',2000],'description'=>['Event details','textarea',8000],'image_id'=>['Event image','media:image',0]],
            'member' => ['role'=>['Musical role','text',180],'bio'=>['Approved biography','textarea',8000],'image_id'=>['Approved portrait','media:image',0]],
            'gallery' => ['image_id'=>['Photograph','media:image',0],'caption'=>['Caption','textarea',1500],'credit'=>['Photographer / credit','text',200]],
            'video' => ['url'=>['YouTube or Vimeo video URL','video',2000],'image_id'=>['Video poster image','media:image',0],'description'=>['Description','textarea',3000]],
            'press' => ['file_id'=>['Approved PDF document','media:pdf',0],'description'=>['Document description','textarea',2000]],
            default => throw new InvalidArgumentException('Unknown content section.'),
        };
        return $fields;
    }
    public static function url(string $value): string
    {
        if ($value === '') { return ''; }
        $parts = parse_url($value);
        if (!$parts || !filter_var($value,FILTER_VALIDATE_URL) || ($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass']) || preg_match('/[\x00-\x20\x7f]/',$value)) { throw new InvalidArgumentException('External links must be complete HTTPS URLs without credentials or spaces.'); }
        return $value;
    }
    public static function slug(string $value, string $title): string
    {
        if ($value === '') { $value = trim(preg_replace('/[^a-z0-9]+/','-',strtolower($title)) ?? '', '-'); }
        if (!preg_match('/^[a-z0-9][a-z0-9-]{0,89}$/D',$value)) { throw new InvalidArgumentException('Use a URL name with lowercase letters, numbers and hyphens, up to 90 characters.'); }
        return $value;
    }
    public static function validate(string $kind, array $input, bool $publishing): array
    {
        $data = [];
        foreach (self::fields($kind) as $key => [$label,$type,$option]) {
            $raw=$input[$key]??'';
            if((str_starts_with($type,'media:')||$type==='release')&&is_int($raw)){$raw=(string)$raw;}
            if(!is_string($raw)||!preg_match('//u',$raw)){throw new InvalidArgumentException('Invalid '.$label.'.');}
            $value=trim($raw);
            if (str_contains($value,"\0")) { throw new InvalidArgumentException($label . ' contains an invalid character.'); }
            if (in_array($type,['text','textarea','url','video'],true) && strlen($value) > $option) { throw new InvalidArgumentException($label . ' is too long.'); }
            if ($type === 'select') { $value = $value ?: array_key_first($option); if (!array_key_exists($value,$option)) { throw new InvalidArgumentException('Invalid ' . $label . '.'); } }
            if ($type === 'url' || $type === 'video') { $value = self::url($value); }
            if ($type === 'video' && $value !== '') { $host = strtolower((string)parse_url($value,PHP_URL_HOST)); if (!in_array($host,['youtube.com','www.youtube.com','youtu.be','vimeo.com','www.vimeo.com'],true)) { throw new InvalidArgumentException('Use a YouTube or Vimeo video link.'); } }
            if ($type === 'date' && $value !== '') { $date = DateTimeImmutable::createFromFormat('!Y-m-d',$value); if (!$date || $date->format('Y-m-d') !== $value) { throw new InvalidArgumentException('Invalid release date.'); } }
            if ($type === 'datetime-local' && $value !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/D',$value)) { throw new InvalidArgumentException('Use a complete event date and time.'); }
            if ($type === 'timezone') { $value = $value ?: 'America/Chicago'; if (!in_array($value,DateTimeZone::listIdentifiers(),true)) { throw new InvalidArgumentException('Select a valid named timezone.'); } }
            if ($type === 'duration' && $value !== '' && !preg_match('/^\d{1,3}:[0-5]\d$/D',$value)) { throw new InvalidArgumentException('Duration must look like 3:42.'); }
            if (str_starts_with($type,'media:') || $type === 'release') {
                if ($value !== '' && !ctype_digit($value)) { throw new InvalidArgumentException('Choose a valid ' . $label . '.'); }
                $value = $value===''?0:Input::integer([$key=>$value],$key,0,PHP_INT_MAX,0);
            }
            $data[$key] = $value;
        }
        if ($kind === 'event' && $data['starts_at'] !== '') {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$data['starts_at'],new DateTimeZone($data['timezone']));
            if (!$date || $date->format('Y-m-d\TH:i') !== $data['starts_at']) { throw new InvalidArgumentException('The event time is invalid, including a possible daylight-saving time change.'); }
            $data['starts_utc'] = $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        }
        if ($publishing) {
            $required = match($kind) { 'track'=>['release_id','audio_id'], 'event'=>['starts_at','venue','city'], 'gallery'=>['image_id'], 'video'=>['url'], 'press'=>['file_id'], default=>[] };
            foreach ($required as $key) { if (empty($data[$key])) { throw new InvalidArgumentException(self::fields($kind)[$key][0] . ' is required before publishing.'); } }
        }
        return $data;
    }
    public static function mediaIds(string $kind, array $data): array
    {
        $ids = []; foreach (self::fields($kind) as $key => [$label,$type]) { if (str_starts_with($type,'media:') && !empty($data[$key])) { $ids[(int)$data[$key]] = substr($type,6); } } return $ids;
    }
}
