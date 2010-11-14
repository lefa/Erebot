<?php

/**
 * Extra package.xml settings such as dependencies.
 * More information: http://pear.php.net/manual/en/pyrus.commands.make.php#pyrus.commands.make.packagexmlsetup
 */

$package->license = 'GPL';
$compatible->license = 'GPL';

$deps = array(
    'pear.erebot.net/Erebot_Module_IrcConnector',
    'pear.erebot.net/Erebot_Module_AutoJoin',
    'pear.erebot.net/Erebot_Module_AutoConnect',
);

foreach ($deps as $dep) {
    $package->dependencies['required']->package[$dep]->save();
    $compatible->dependencies['required']->package[$dep]->save();
}

?>