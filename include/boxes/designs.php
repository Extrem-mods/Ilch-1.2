<?php
// Copyright by Manuel
// Support www.ilch.de
defined( 'main' ) or die( 'no direct access' );
// -----------------------------------------------------------|
if ( !empty( $_POST[ 'temp_ch' ] ) ) {
    $_SESSION[ 'authgfx' ] = $_POST[ 'temp_ch' ];
    wd( '', '', 0 );
} else {
    echo '<form action="index.php?' . $menu->get_complete() . '" method="POST">';
    echo '<div align="center">';
    echo '<select name="temp_ch" onchange="this.form.submit();">';
    $o = opendir( 'include/designs' );
    while ( $f = readdir( $o ) ) {
        if ( !preg_match("/\\..*/", $f) AND is_dir( 'include/designs/' . $f ) ) {
            $s = ( $f == $_SESSION[ 'authgfx' ] ? ' selected' : '' );
            echo '<option' . $s . '>' . $f . '</option>';
        }
    }
    echo '</select></div></form>';
}

?>