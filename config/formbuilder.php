<?php

/*
* the class of the div wrapping the label and input element.
* if you would not like it contained in a div, set the variable to NULL
*/
$config[ 'formbuilder' ][ 'container_class' ] = 'formRow';
/*
* field error class is the class of the div wrapping field level error on validation failing
*/
$config[ 'formbuilder' ][ 'error_class' ] = 'fieldError';
/*
* error position determines where the error is shown.
* 1 is before the field label
* 2 is between the label and the input
* 3 is after the input
*/
$config[ 'formbuilder' ][ 'error_position' ] = 3;
/*
* if auto_id is active and you set an html id for your form specifying
* $params parameter in the open() method, all fields ids will be
* automatically assigned as <form-id>-<field-name>, so that field "name"
* in form which id is "myform" will have id "myform-name"
*/
$config[ 'formbuilder' ][ 'auto_id' ] = TRUE;