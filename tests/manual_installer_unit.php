<?php
declare(strict_types=1);
// Isolated test-only name resolution simulates both optional-extension states;
// it is never loaded by application requests or included in deployment ZIPs.
namespace Spirit {
    function extension_loaded(string $name): bool {
        if (array_key_exists($name, $GLOBALS['optional_extension_fixture'] ?? [])) {
            return $GLOBALS['optional_extension_fixture'][$name];
        }
        return \extension_loaded($name);
    }
}
namespace {
    require dirname(__DIR__) . '/includes/bootstrap.php';
    use Spirit\Installer;
    $checks = 0;
    function check(bool $value, string $message): void {
        global $checks;
        if (!$value) { throw new RuntimeException($message); }
        $checks++; echo 'PASS ' . $message . "\n";
    }
    foreach ([[false,false],[false,true],[true,false],[true,true]] as [$curl,$zip]) {
        $GLOBALS['optional_extension_fixture'] = ['curl'=>$curl,'zip'=>$zip];
        $rows = Installer::optionalChecks();
        check(array_column($rows,'name') === ['PHP cURL','PHP ZIP'], 'cURL and ZIP are individually identified');
        check(array_column($rows,'ok') === [$curl,$zip], 'Each optional result follows its own extension');
        check(array_column($rows,'required') === [false,false], 'Optional extensions cannot gate manual installation');
        foreach ($rows as $row) {
            check(str_contains($row['detail'], $row['ok'] ? 'Enabled' : 'Manual installation can continue'), 'Each result explains availability or limited features');
        }
    }
    unset($GLOBALS['optional_extension_fixture']);
    Installer::requireManualInput([]);
    Installer::requireManualInput(['db_mode'=>'existing']);
    check(true, 'No mode or legacy explicit manual mode is accepted');
    foreach ([['db_mode'=>'cpanel'],['db_mode'=>['existing']],['db_mode'=>'unknown'],['cpanel'=>[]],['cpanel_token'=>'synthetic-secret'],['cpanel_host'=>'example.invalid'],['cpanel_consent'=>'1']] as $input) {
        $rejected = false;
        try { Installer::requireManualInput($input); }
        catch (InvalidArgumentException $e) {
            $rejected = true;
            check(!str_contains($e->getMessage(),'synthetic-secret'), 'Retired hosting fields are never reflected in feedback');
        }
        check($rejected, 'Retired automatic fields cannot reach a manual installation');
    }
    foreach (['CpanelClient','CpanelProvisioner','CpanelRejected'] as $name) {
        check(is_file(dirname(__DIR__).'/app/'.$name.'.php') && !class_exists('Spirit\\'.$name), 'Inert upgrade-compatibility path exposes no retired hosting API class: '.$name);
    }
    echo $checks . " manual-installer checks passed.\n";
}
