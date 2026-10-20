<?php
declare(strict_types=1);
$_SERVER['SCRIPT_NAME'] = '/tests/footer_settings_unit.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require dirname(__DIR__) . '/includes/bootstrap.php';
use Spirit\Settings;

$checks = 0;
function check(bool $condition, string $label): void
{
    global $checks;
    if (!$condition) { throw new RuntimeException($label); }
    echo 'PASS ' . $label . "\n";
    $checks++;
}
function rejectsCopyright(mixed $copyright, string $label): void
{
    try {
        Settings::save(['site_name' => 'Test band', 'tagline' => 'Test tagline', 'welcome' => 'Test welcome', 'footer_copyright' => $copyright], 1);
    } catch (InvalidArgumentException $error) {
        check(str_starts_with($error->getMessage(), 'Footer copyright'), $label);
        return;
    }
    throw new RuntimeException('Accepted invalid footer copyright: ' . $label);
}

$year = gmdate('Y');
check(Settings::footerCopyright(['site_name' => 'Test band']) === '© ' . $year . ' Test band.', 'An existing installation without the new row retains its automatic copyright without a database write');
check(Settings::footerCopyright(['site_name' => 'Test band', 'footer_copyright' => '']) === '© ' . $year . ' Test band.', 'Blank copyright uses the current year and website name');
check(Settings::footerCopyright(['site_name' => 'Renamed band', 'footer_copyright' => '   ']) === '© ' . $year . ' Renamed band.', 'Blank copyright follows a later site-name change');
check(Settings::footerCopyright(['site_name' => 'Test band', 'footer_copyright' => '  © 2020–2026 Custom rights holder.  ']) === '© 2020–2026 Custom rights holder.', 'A complete custom copyright is preserved as plain text with surrounding whitespace trimmed');
$markup = '<img src=x onerror=alert(1)> & "rights"';
check(e(Settings::footerCopyright(['footer_copyright' => $markup])) === '&lt;img src=x onerror=alert(1)&gt; &amp; &quot;rights&quot;', 'Copyright output uses the application HTML escaping boundary');
check(Settings::footerCopyright(['site_name' => 'Test band', 'footer_credit' => 'forged', 'footer_credit_url' => 'javascript:alert(1)', 'hide_footer_credit' => '1']) === '© ' . $year . ' Test band.', 'Unsupported developer-credit settings do not alter copyright selection');

foreach ([null, [], ['nested' => 'value'], 42, true] as $bad) {
    rejectsCopyright($bad, 'Non-string copyright is rejected before database access');
}
rejectsCopyright(str_repeat('x', Settings::FOOTER_COPYRIGHT_LIMIT + 1), 'Copyright exceeding the byte limit is rejected before database access');
rejectsCopyright(str_repeat('é', 121), 'The limit counts UTF-8 bytes rather than only characters');
foreach (["First\nSecond", "First\rSecond", "First\tSecond", "First\0Second", "First\x7FSecond", "\xC3\x28"] as $bad) {
    rejectsCopyright($bad, 'Control characters or invalid UTF-8 are rejected before database access');
}
echo $checks . " footer settings unit checks passed.\n";
