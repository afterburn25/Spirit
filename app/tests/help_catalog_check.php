<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
$categories=Spirit\HelpCatalog::categories();
$errors=[];$seen=[];
foreach($categories as $slug=>$category){
 if(!preg_match('/^[a-z][a-z-]*$/D',(string)$slug)){$errors[]='Invalid topic: '.$slug;}
 if(count($category)!==3||!is_string($category[0])||!is_string($category[1])||!is_array($category[2])){$errors[]='Invalid topic schema: '.$slug;continue;}
 foreach($category[2] as $guide){
  if(count($guide)!==3||!is_string($guide[0])||!is_string($guide[1])||!is_string($guide[2])){$errors[]='Invalid guide schema: '.$slug;continue;}
  if(trim($guide[0])===''||trim($guide[1])===''){$errors[]='Empty title or steps in '.$slug;}
  $path=$guide[2];
  if(!preg_match('~^admin/[a-z0-9/_?.=&-]*$~D',$path)||str_contains($path,'..')){$errors[]='Unsafe guide destination: '.$path;continue;}
  $pathname=explode('?',substr($path,6),2)[0];
  if($pathname===''||str_ends_with($pathname,'/')){$pathname.='index.php';}
  if(!str_ends_with($pathname,'.php')){$errors[]='Not a PHP endpoint: '.$path;continue;}
  if(!is_file(dirname(__DIR__,2).'/admin/'.$pathname)){$errors[]='Missing help destination: '.$path;}
  $key=$slug.'|'.$guide[0];if(isset($seen[$key])){$errors[]='Duplicate article: '.$key;}$seen[$key]=true;
 }
}
$documentedDestinations=Spirit\AdminNavigation::documentedDestinations();
$guidePaths=[];
foreach($categories as $category){
    foreach($category[2] as $guide){
        $guidePaths[$guide[2]]=true;
        if(!isset($documentedDestinations[$guide[2]])){
            $errors[]='Help guide has no matching admin navigation destination: '.$guide[2];
        }
    }
}
foreach($documentedDestinations as $path=>$permission){
    if(!isset($guidePaths[$path])){$errors[]='Missing Help Center coverage for: '.$path;}
    if(!is_string($permission)||$permission===''){$errors[]='Missing permission rule for: '.$path;}
}
// Contextual Help must never link an unauthorized screen even when a user
// manually supplies a valid but forbidden ?screen= value.
$helpOwner=['active'=>1,'role'=>'owner'];
$helpEditor=['active'=>1,'role'=>'content_editor'];
$helpStore=['active'=>1,'role'=>'store_manager'];
$helpDisabled=['active'=>0,'role'=>'owner'];
foreach([
 ['payment-settings',$helpOwner,'admin/orders/payments.php'],
 ['payment-settings',$helpEditor,null],
 ['payment-settings',$helpStore,null],
 ['member',$helpEditor,'admin/content/?kind=member'],
 ['member',$helpStore,null],
 ['settings',$helpOwner,'admin/settings.php'],
 ['settings',$helpEditor,null],
 ['dashboard',$helpStore,'admin/'],
 ['help',$helpEditor,'admin/help/'],
 ['not-a-screen',$helpOwner,null],
 ['payment-settings',$helpDisabled,null],
] as [$screen,$staff,$expectedHelpPath]){
 $actualHelpPath=Spirit\HelpCatalog::visibleDestinationForAdmin($screen,$staff);
 if($actualHelpPath!==$expectedHelpPath){$errors[]='Contextual Help permission mismatch for '.$screen.' / '.$staff['role'];}
}
if($errors){fwrite(STDERR,implode("\n",$errors)."\n");exit(1);}
echo "Help catalog checked: ".count($categories)." categories, ".count($seen)." guides.\n";

foreach(array_keys($documentedDestinations) as $path){
    if($path==='admin/'){ $active='dashboard'; }
    else { $active=null; foreach(['page','member','library','gallery','video','press','release','track','event','analytics','analytics-visitors','analytics-geography','bookings','subscribers','mail','communication','store','store-categories','customers','orders','store-settings','delivery-policy','payment-settings','payment-notifications','settings','discovery','staff','account','help','launch','installation','backups','upgrades','updates','maintenance','activity','roadmap'] as $key){if(Spirit\AdminNavigation::destinationForActive($key)===$path){$active=$key;break;}} }
    if($active===null){fwrite(STDERR,'No contextual help link for: ' . $path."\n");exit(1);}
}
