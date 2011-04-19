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