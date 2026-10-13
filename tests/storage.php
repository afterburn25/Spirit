<?php
declare(strict_types=1);
// Independent storage regressions. No database and no application config used.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(1); }
umask(0077);
$base = sys_get_temp_dir() . '/spirit-storage-' . bin2hex(random_bytes(8));
mkdir($base,0700); mkdir($base.'/home',0700); mkdir($base.'/home/public_html',0700); mkdir($base.'/home/public_html/addon',0700);
define('SPIRIT_ROOT', $base.'/home/public_html/addon');
require dirname(__DIR__).'/app/HostingException.php';
require dirname(__DIR__).'/app/Paths.php';
require dirname(__DIR__).'/app/Filesystem.php';
use Spirit\{Paths,Filesystem};
$_SERVER['DOCUMENT_ROOT'] = SPIRIT_ROOT;
putenv('SPIRIT_ACCOUNT_HOME='.$base.'/home');
putenv('SPIRIT_PUBLIC_ROOTS');
$passed=0; $failed=0;
function test(string $name, callable $fn): void { global $passed,$failed; try { if (!$fn()) throw new RuntimeException('Assertion failed'); $passed++; echo 'PASS '.$name."\n"; } catch(Throwable $e) { $failed++; echo 'FAIL '.$name.' ['.get_class($e)."]\n"; } }
function denied(callable $fn): bool { try { $fn(); return false; } catch(RuntimeException $e) { return true; } }
function clean(string $path): void { if (is_link($path)||is_file($path)) { unlink($path); return; } chmod($path,0700); foreach(array_diff(scandir($path),['.','..']) as $name) clean($path.'/'.$name); rmdir($path); }
register_shutdown_function(static function() use($base): void { if (is_dir($base)) clean($base); });
$home=$base.'/home';
$runtime=Paths::runtime();
test('Addon runtime is placed at account level, not beside nested document root', fn()=>dirname($runtime)===$home && !Paths::within($runtime,$home.'/public_html'));
test('Primary public_html is recognized when current root is an addon', fn()=>in_array($home.'/public_html',Paths::publicRoots($home),true));
test('Configured public base is rejected', function() use($home) { putenv('SPIRIT_ACCOUNT_HOME='.$home.'/public_html'); $ok=denied(fn()=>Paths::accountHome()); putenv('SPIRIT_ACCOUNT_HOME='.$home); return $ok; });
test('Host-declared alternate public root is honored', function() use($base,$home) { putenv('SPIRIT_PUBLIC_ROOTS='.$home); $ok=denied(fn()=>Paths::accountHome()); putenv('SPIRIT_PUBLIC_ROOTS'); return $ok; });
test('Invalid host-declared public root fails closed', function() use($base) { putenv('SPIRIT_PUBLIC_ROOTS='.$base.'/missing'); $ok=denied(fn()=>Paths::runtime()); putenv('SPIRIT_PUBLIC_ROOTS'); return $ok; });
test('Symlink account base is rejected', function() use($base,$home) { symlink($home,$base.'/alias'); putenv('SPIRIT_ACCOUNT_HOME='.$base.'/alias'); $ok=denied(fn()=>Paths::runtime()); putenv('SPIRIT_ACCOUNT_HOME='.$home); return $ok; });
test('Dot-segment account path is rejected', function() use($home) { putenv('SPIRIT_ACCOUNT_HOME='.$home.'/../home'); $ok=denied(fn()=>Paths::runtime()); putenv('SPIRIT_ACCOUNT_HOME='.$home); return $ok; });
test('HTTP header cannot configure private storage', function() use($base,$home) { putenv('SPIRIT_ACCOUNT_HOME'); $old=getenv('HOME'); putenv('HOME='); $_SERVER['HTTP_SPIRIT_ACCOUNT_HOME']=$home; $ok=denied(fn()=>Paths::runtime()); unset($_SERVER['HTTP_SPIRIT_ACCOUNT_HOME']); if($old!==false)putenv('HOME='.$old); putenv('SPIRIT_ACCOUNT_HOME='.$home); return $ok; });
test('Runtime has actual writable private subdirectories', function() use($runtime) { Filesystem::prepare($runtime,Paths::documentRoot()); foreach(['sessions','logs','media','backups','cache'] as $child) { if (!Filesystem::probe($runtime.'/'.$child) || (fileperms($runtime.'/'.$child)&0777)!==0700) return false; } return true; });
test('Private runtime includes defensive web access rules', fn()=>file_get_contents($runtime.'/.htaccess')==="Require all denied\n");
test('Private subdirectory access rules are installed', fn()=>file_get_contents($runtime.'/sessions/.htaccess')==="Require all denied\n");
test('Repeat provisioning is safe', function() use($runtime) { Filesystem::prepare($runtime,Paths::documentRoot()); return true; });
test('Probe temporary files are removed', fn()=>glob($runtime.'/.spirit-probe-*')===[]);
test('Private PHP writes round-trip safely', function() use($runtime) { $value=['installed'=>true,'secret'=>"quote '\n<?php test"]; Filesystem::atomicPhp($runtime.'/config.php',$value); return Paths::config()===$value; });
test('Config file is mode 0600', fn()=>(fileperms($runtime.'/config.php')&0777)===0600);
test('Broad config permissions are rejected', function() use($runtime) { chmod($runtime.'/config.php',0644); clearstatcache(); $ok=denied(fn()=>Paths::config()); chmod($runtime.'/config.php',0600); return $ok; });
test('Installed config blocks setup independently of marker', fn()=>Paths::installed());
test('Marker blocks setup independently of config', function() use($runtime) { unlink($runtime.'/config.php'); Filesystem::atomicPhp($runtime.'/installed.lock',['installed'=>true]); return Paths::installed(); });
test('Broken configuration symlink is rejected', function() use($runtime,$base) { symlink($base.'/does-not-exist',$runtime.'/config.php'); $ok=denied(fn()=>Paths::config()); unlink($runtime.'/config.php'); return $ok; });
test('Unsafe config write does not alter a symlink target', function() use($runtime,$base) { file_put_contents($base.'/victim','keep'); symlink($base.'/victim',$runtime.'/alias.php'); return denied(fn()=>Filesystem::atomicPhp($runtime.'/alias.php',['bad'=>true])) && file_get_contents($base.'/victim')==='keep'; });
test('Private subdirectory symlink is refused', function() use($runtime,$base) { unlink($runtime.'/cache/.htaccess'); rmdir($runtime.'/cache'); symlink($base,$runtime.'/cache'); $ok=denied(fn()=>Filesystem::prepare($runtime,Paths::documentRoot())); unlink($runtime.'/cache'); return $ok; });
test('Installer unknown content is preserved', function() use($base) { mkdir($base.'/site'); mkdir($base.'/site/install'); file_put_contents($base.'/site/install/customer-file','keep'); return !Filesystem::cleanup($base.'/site')['removed'] && file_get_contents($base.'/site/install/customer-file')==='keep'; });
test('Installer symlink target is preserved', function() use($base) { mkdir($base.'/other'); symlink($base.'/site/install',$base.'/other/install'); return !Filesystem::cleanup($base.'/other')['removed'] && is_file($base.'/site/install/customer-file'); });
if (!function_exists('posix_geteuid') || posix_geteuid()!==0) {
    test('Unreadable installer directory is reported without deletion', function() use($base) { chmod($base.'/site/install',0000); $ok=!Filesystem::cleanup($base.'/site')['removed']; chmod($base.'/site/install',0700); return $ok; });
    test('Read-only runtime write is rejected safely', function() use($base) { mkdir($base.'/readonly',0500); $ok=!Filesystem::probe($base.'/readonly'); chmod($base.'/readonly',0700); return $ok; });
    test('Parent-directory deletion restriction is reported', function() use($base) { mkdir($base.'/locked'); mkdir($base.'/locked/install'); file_put_contents($base.'/locked/install/index.php','test'); chmod($base.'/locked',0500); $ok=!Filesystem::cleanup($base.'/locked')['removed'] && is_dir($base.'/locked/install'); chmod($base.'/locked',0700); return $ok && Filesystem::cleanup($base.'/locked')['removed']; });
}
echo ($passed+$failed).' storage checks; '.$failed." failures.\n";
exit($failed?1:0);
