<?php
/**
 * This file used to build phar of this project
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

$pharName = 'mwExtUpgrader.phar';
$bootstrapFilename = 'run.php';
try {
    $phar = new Phar( $pharName );
    $includeRegex = '/\.php$/'; // Inculde all .php file
    $phar->buildFromDirectory( __DIR__, $includeRegex );
    $phar->delete( basename( __FILE__ ) ); // Ignore this build script
    // Set phar file head
    $pharHead = "#!/usr/bin/env php\n";
    $phar->setStub( $pharHead . $phar->createDefaultStub( $bootstrapFilename ) );
    // Set phar meta-data
    $metadata = [
        'BootstrapFile' => $bootstrapFilename,
        'Project Name' => 'mwExtUpgrader',
        'Project repository' => 'https://github.com/RazeSoldier/mwExtUpgrader'
    ];
    $phar->setMetadata( $metadata );
} catch ( PharException $e ) {
    echo 'Write operations failed on brandnewphar.phar: ', $e;
} catch ( UnexpectedValueException $e ) {
    if ( ini_get( 'phar.readonly' ) == 1 ) {
        $errorMsg = "phar.readonly is set to 1, build script does not work. (Please set phar.readonly to 0 in php.ini)\n";
        trigger_error( $errorMsg , E_USER_ERROR );
    }
    echo $e;
}

if ( !file_exists( $pharName ) ) {
    trigger_error( "Build failed, unknown error.\n" , E_USER_ERROR);
} else {
    echo "Build successfully, saved as $pharName.\n";
}