<?php
/*
    This file is part of Erebot.

    Erebot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Erebot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Erebot.  If not, see <http://www.gnu.org/licenses/>.
*/

$metadata['pear.erebot.net/' . $packageName] += array(
    'requires' => array(
        'php' => '>= 5.2.2',
        'pear.erebot.net/DependencyInjection',
        'pear.erebot.net/Erebot_Module_AutoConnect',
        'pear.erebot.net/Erebot_Module_IrcConnector',
        'pear.erebot.net/Erebot_Module_PingReply',
        'pear.php.net/Console_CommandLine',
        'pear.php.net/File_Gettext',
        'pecl.php.net/ctype',
        'pecl.php.net/dom',
        'pecl.php.net/intl',
        'pecl.php.net/libxml',
        'pecl.php.net/pcre',
        'pecl.php.net/Reflection',
        'pecl.php.net/SimpleXML',
        'pecl.php.net/sockets',
        'pecl.php.net/SPL',
        'pecl.php.net/XML',
        'virt-Erebot_API' => '0.2.*',
        'virt-log',
    ),
    'suggests' => array(
        'pecl.php.net/openssl',
        'pecl.php.net/pcntl',
        'pecl.php.net/posix',
    ),
    'license' => array(
        'GPL' => 'http://www.gnu.org/licenses/gpl-3.0.txt',
    )
);

$metadata += array(
    'pear.erebot.net/Erebot_API' => array(
        'version' => 'master-dev',
        'requires' => array(
            'php' => '>= 5.2.0',
        ),
        'provides' => array(
            'virt-Erebot_API' => '= 0.2.0',
        ),
        'license' => array(
            'GPL' => 'http://www.gnu.org/licenses/gpl-3.0.txt',
        ),
    ),

    'pear.erebot.net/Plop' => array(
        'version' => 'master-dev',
        'requires' => array(
            'php' => '>= 5.2.0',
        ),
        'provides' => array(
            'virt-log',
        ),
        'license' => array(
            'GPL' => 'http://www.gnu.org/licenses/gpl-3.0.txt',
        ),
    ),

    'pear.erebot.net/DependencyInjection' => array(
        'version' => 'master-dev',
        'requires' => array(
            'php' => '>= 5.2.0',
        ),
    ),

    'pear.php.net/Console_CommandLine' => array(
        'version' => 'master-dev',
        'requires' => array(
            'php' => '>= 5.2.0',
        ),
    ),

    'pear.php.net/File_Gettext' => array(
        'version' => 'master-dev',
        'requires' => array(
            'php' => '>= 5.2.0',
        ),
    ),
);

