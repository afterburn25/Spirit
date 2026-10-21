<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/includes/bootstrap.php';
use Spirit\{Paths,Filesystem,Security,Database};

$base = sys_get_temp_dir() . '/spirit-unit-' . bin2hex(random_bytes(8));
mkdir($base, 0700); mkdir($base . '/public', 0700);
$_SERVER['DOCUMENT_ROOT'] = $base . '/public';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SCRIPT_FILENAME'] = Paths::root() . '/install/index.php';
$_SERVER['SCRIPT_NAME'] = '/install/index.php';
$_SESSION = [];
$results = [];
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) { return false; }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
function check(string $name, callable $test): void { global $results; try { if ($test() !== true) { throw new RuntimeException('Assertion failed'); } $results[] = [true, $name]; } catch (Throwable $e) { $results[] = [false, $name . ': ' . $e->getMessage()]; } }
function rejects(callable $call): bool { try { $call(); return false; } catch (InvalidArgumentException|RuntimeException $e) { return true; } }
function removeFixture(string $path): void { if (is_link($path) || is_file($path)) { unlink($path); return; } foreach (array_diff(scandir($path), ['.','..']) as $name) { removeFixture($path . '/' . $name); } rmdir($path); }

check('HTML escaping blocks markup', fn() => e('<script>"&') === '&lt;script&gt;&quot;&amp;');
check('CSRF token is unpredictable length', fn() => strlen(Security::csrf()) === 64);
check('CSRF accepts exact token', fn() => Security::validCsrf(Security::csrf()));
check('CSRF rejects absent or wrong token', fn() => !Security::validCsrf('') && !Security::validCsrf(str_repeat('a',64)));
check('Password hash verifies', fn() => password_verify('Correct Password 123!', Security::password('Correct Password 123!')));
check('Short password is rejected', fn() => rejects(fn() => Security::password('short')));
check('Overlong bcrypt password is rejected', fn() => rejects(fn() => Security::password(str_repeat('a',73))));
check('Null-byte password is rejected', fn() => rejects(fn() => Security::password("abcdefghijklm\0n")));
check('Email normalization', fn() => Security::email('Owner@Example.COM') === 'owner@example.com');
check('Malformed email rejected', fn() => rejects(fn() => Security::email('not an email')));
check('Loopback is not an HTTPS bypass outside the local development server', fn() => !Security::isLocal());
check('Root URL prefix', fn() => Paths::basePath() === '');
$_SERVER['SCRIPT_NAME'] = '/band/install/index.php';
check('Subfolder URL prefix', fn() => Paths::basePath() === '/band');
check('Subfolder link composition', fn() => app_url('admin/login.php') === '/band/admin/login.php');
check('Matching HTTPS site URL accepted', fn() => Security::siteUrl('https://example.com/band/') === 'https://example.com/band');
check('Mismatched site path rejected', fn() => rejects(fn() => Security::siteUrl('https://example.com/other')));
check('URL credentials rejected', fn() => rejects(fn() => Security::siteUrl('https://user:password@example.com/band')));
check('URL query rejected', fn() => rejects(fn() => Security::siteUrl('https://example.com/band?x=1')));
$_SERVER['REMOTE_ADDR'] = '203.0.113.1';
check('Remote HTTP rejected', fn() => !Security::secureTransport());
check('Forwarded HTTPS cannot bypass transport checks', function() { $_SERVER['HTTP_X_FORWARDED_PROTO']='https'; return !Security::secureTransport(); });
check('Remote insecure canonical URL rejected', fn() => rejects(fn() => Security::siteUrl('http://example.com/band')));
$_SERVER['HTTPS'] = 'on';
check('Native HTTPS accepted', fn() => Security::secureTransport());
$_SERVER['HTTPS'] = '';
check('Database prefix injection rejected', fn() => rejects(fn() => Database::validate(['host'=>'localhost','name'=>'db','user'=>'u','prefix'=>'sg_;DROP'])));
check('Database DSN injection rejected', fn() => rejects(fn() => Database::validate(['host'=>'localhost;dbname=other','name'=>'db','user'=>'u'])));
check('Invalid database port rejected', fn() => rejects(fn() => Database::validate(['host'=>'localhost','name'=>'db','user'=>'u','port'=>0])));
check('Valid shared-host database configuration', fn() => Database::validate(['host'=>'localhost','name'=>'account_db','user'=>'account_user'])['prefix'] === 'sg_');
check('Path boundary does not confuse sibling prefix', fn() => !Paths::within('/home/site-other', '/home/site') && Paths::within('/home/site/file','/home/site'));
check('Private runtime is outside web root', fn() => !Paths::within(Paths::runtime(), Paths::documentRoot()));
check('Private runtime is provisioned and probed', function() { Filesystem::prepare(Paths::runtime(), Paths::documentRoot()); return is_dir(Paths::runtime().'/sessions') && Filesystem::probe(Paths::runtime()); });
check('Probe leaves no files behind', fn() => glob(Paths::runtime().'/.spirit-probe-*') === []);
check('Private directory permissions are restricted', fn() => (fileperms(Paths::runtime()) & 0077) === 0);
check('Public storage location refused', fn() => rejects(fn() => Filesystem::prepare($base.'/public/private', $base.'/public')));
check('Atomic PHP configuration preserves quotes and code-like strings', function() { $value=['secret'=>"'; phpinfo(); //\n<&"]; Filesystem::atomicPhp(Paths::runtime().'/sample.php',$value); return (require Paths::runtime().'/sample.php') === $value; });
check('Atomic configuration mode is private', fn() => (fileperms(Paths::runtime().'/sample.php') & 0077) === 0);
check('Configuration symlink target is refused', function() use($base) { file_put_contents($base.'/victim','safe'); symlink($base.'/victim',Paths::runtime().'/link.php'); return rejects(fn()=>Filesystem::atomicPhp(Paths::runtime().'/link.php',['bad'=>true])) && file_get_contents($base.'/victim') === 'safe'; });
check('Installer known files are removed with the folder', function() use($base) { mkdir($base.'/case1'); mkdir($base.'/case1/install'); foreach(['index.php','access.php','.htaccess','README.md'] as $f) file_put_contents($base.'/case1/install/'.$f,'test'); return Filesystem::cleanup($base.'/case1')['removed'] && !file_exists($base.'/case1/install'); });
check('Installer cleanup is idempotent', fn() => Filesystem::cleanup($base.'/case1')['removed']);
check('Installer unknown files are preserved', function() use($base) { mkdir($base.'/case2'); mkdir($base.'/case2/install'); file_put_contents($base.'/case2/install/customer-file','keep'); return !Filesystem::cleanup($base.'/case2')['removed'] && is_file($base.'/case2/install/customer-file'); });
check('Installer symlink cannot delete outside target', function() use($base) { mkdir($base.'/case3'); symlink($base.'/case2/install',$base.'/case3/install'); return !Filesystem::cleanup($base.'/case3')['removed'] && is_file($base.'/case2/install/customer-file'); });
check('Missing configuration is not installed', fn() => !Paths::installed());
check('Completion lock blocks reinstall without configuration', function() { file_put_contents(Paths::runtime().'/installed.lock','locked'); return Paths::installed(); });
if (!function_exists('posix_geteuid') || posix_geteuid() !== 0) {
    check('Write probe fails safely on a read-only directory', function() use($base) { mkdir($base.'/readonly',0500); $ok=!Filesystem::probe($base.'/readonly'); chmod($base.'/readonly',0700); return $ok; });
    check('Installer deletion failure is reported truthfully', function() use($base) { mkdir($base.'/case4'); mkdir($base.'/case4/install'); file_put_contents($base.'/case4/install/index.php','safe'); chmod($base.'/case4/install',0500); $ok=!Filesystem::cleanup($base.'/case4')['removed']; chmod($base.'/case4/install',0700); return $ok; });
}
restore_error_handler();
removeFixture($base);
$failures=0;
foreach($results as [$ok,$name]) { echo ($ok?'PASS ':'FAIL ').$name."\n"; if(!$ok)$failures++; }
echo count($results).' checks; '.$failures." failures.\n";
exit($failures ? 1 : 0);
