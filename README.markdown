# Formbuilder
## Lets you build forms easily, pre-populate them, show errors, and retain $_POST data

- Build a div wrapper, form label and input field with one php method call
- Pre-Populate the form by setting formbuilder->defaults from your controller
- Retain POST values on page refresh, form is auto populated with POST
- Show each field error inline.
- All of the above happens "automagically"
- Or... build an entire form with one method, showing all table's fields

## Configuration sparks/formbuilder/x.x/config/formbuilder.php

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


## Basic usage in your controller

    function edit()
    {
      /*
      * load the spark
      * set default form values if pre-population is desired
      */

      $this->load->spark( 'formbuilder/x.x');

      /*
      * prepopulate with your data.
      * in this example we are editing a user
      */

      $q = $this->db->where( 'id', 3 )->limit( 1 )->get( 'users' );
      $this->formbuilder->defaults = $q->row_array();

      $this->load->view( 'user/edit' );
    }

## Now setup your view

    <?php
    /*
    * the formbuilder->defaults array pre-populates the form
    * if there are form validation errors on submit, they will be shown as well.
    */
    echo $this->formbuilder->open( 'user/edit_save', FALSE, array('id' => 'myform') );
    echo $this->formbuilder->text( 'username', 'Username' );
    echo $this->formbuilder->text( 'first_name', 'Your First Name' );
    echo $this->formbuilder->text( 'last_name', 'Your Last Name' );
    echo $this->formbuilder->password( 'password', 'Password' );
    echo $this->formbuilder->close();
    ?>

The above produces the following markup...

    <form action="http://sparks.local:8888/index.php/user/edit_save" method="post" accept-charset="utf-8" id="myform">
      <div class="formRow text">
        <label for="myform-username">Username</label>
        <input type="text" name="username" value="" id="myform-username"/>
      </div>

      <div class="formRow text">
        <label for="myform-first_name">Your First Name</label>
        <input type="text" name="first_name" value="" id="myform-first_name"/>
      </div>
      <div class="formRow text">
        <label for="myform-last_name">Your Last Name</label>
        <input type="text" name="last_name" value="" id="myform-last_name"/>
      </div>

      <div class="formRow password">
        <label for="myform-password">Password</label>
        <input type="password" name="password" value=""  id="myform-password"/>
      </div>
    </form> <!-- closing myform -->

If no form id is specified, form ids will have to be specified manually, otherwise fields won't have id attribute and related labels won't have for attributes for them.


# Formbuilder Method Documentation
## open method
    /*
    * $action the controller/action url the form will post to
    * $multipart ( optional ) if you are posting files, set to true
    * $params ( optional ) additional params for the <form> tag
    */

    function open( $action, $multipart=FALSE, $params=array() )...
    // example
      echo $this->formbuilder->open( 'user/edit_save', FALSE, array( 'id'=>'myUserEditForm', 'class'=>'userForm' ));

## text method ( produces an &lt;input type="text"&gt; form field )
    /*
    * $var the field you are editing
    * $label ( optional ) the label shown on the <label>. If NULL <label> will be omited.
    * $lblOptions ( optional ) additional params for the <label> tag
    * $fieldOptions ( optional ) additional params for the <input> tag
    */

    text( $var, $label=null, $lblOptions=null, $fieldOptions=null )...
    // example
    echo $this->formbuilder->text( 'username', 'Username', array( 'id'=>'usernameLabel', 'class'=>'userLbl' ), array( 'id'=>'usernameInput', 'class'=>'userInput' ));

## hidden method ( produces an &lt;input type="hidden"&gt; form field )
    /*
    * $var the field you are editing
    * $default ( optional ) will override any formbuilder->defaults if any and set to this value
    */

    function hidden( $var, $default='' )...
    // example
    echo $this->formbuilder->hidden( 'id', $user[ 'id' ] );

## textarea method ( produces an &lt;textarea&gt; form field )
    /*
    * $var the field you are editing
    * $label ( optional ) the label shown on the <label>. If NULL <label> will be omited.
    * $lblOptions ( optional ) additional params for the <label> tag
    * $fieldOptions ( optional ) additional params for the <input> tag
    */

    textarea($var, $label=null, $lblOptions=null, $fieldOptions=null )...
    // example
    echo $this->formbuilder->textarea( 'user_bio', 'User Biography' );

## password method ( produces an &lt;input type="password"&gt; form field )
    /*
    * $var the field you are editing
    * $label ( optional ) the label shown on the <label>. If NULL <label> will be omited.
    * $lblOptions ( optional ) additional params for the <label> tag
    * $fieldOptions ( optional ) additional params for the <input> tag
    */

    password($var, $label=null, $lblOptions=null, $fieldOptions=null )...
    // example
    echo $this->formbuilder->password( 'password', 'Password' );

## checkbox method ( produces an &lt;input type="checkbox"&gt; form field )
    /*
    * $var the field you are editing
    * $label ( optional ) the label shown on the <label>. If NULL <label> will be omited.
    * $value the value of the field usually TRUE / FALSE
    * $default ( optional ) if this should be initial checked if POST or defaults have not overriden
    * $lblOptions ( optional ) additional params for the <label>
    * $fieldOptions ( optional ) additional params for the <input>
    */

    function checkbox( $var, $label, $value, $default=FALSE, $lblOptions=null, $fieldOptions=null )...
    // example
    echo $this->formbuilder->checkbox( 'opt_in_email', 'Email Updates?', TRUE, TRUE );

## radio method ( produces an &lt;input type="radio"&gt; form field )
    /*
    * $var the field you are editing
    * $label ( optional ) the label shown on the <label>. If NULL <label> will be omited.
    * $value the value of the field usually TRUE / FALSE
    * $default ( optional ) if this should be initial checked if POST or defaults have not overriden
    * $lblOptions ( optional ) additional params for the <label>
    * $fieldOptions ( optional ) additional params for the <input>
    */

    function radio( $var, $label, $value, $default=FALSE, $lblOptions=null, $fieldOptions=null )...
    // example
    echo $this->formbuilder->radio( 'how_you_heard', 'Radio', 'radio', TRUE );
    echo $this->formbuilder->radio( 'how_you_heard', 'Television', 'television');

## radio method ( produces an &lt;input type="radio"&gt; form field )
    /*
    * $var the field you are editing
    * $label ( optional ) the label shown on the <label>. If NULL <label> will be omited.
    * $options array of options for the <option> tags
    * $default ( optional ) if this is set and there are no $_POST or $default values, this item will be selected
    * $lblOptions ( optional ) additional params for the <label>
    * $fieldOptions ( optional ) additional params for the <input>
    */

    function drop_down( $var, $label, $options=array(), $default='', $lblOptions=null, $fieldOptions=null )...
    // example
    echo $this->formbuilder->drop_down( 'country', 'Country', array( 'us'=>'United States', 'ca'=>'Canada' ), 'ca' );

## table method ( produces entire form based on a Database table )
    /*
    * $action the form action, where the form will post to.
    * $table the database table you are building a form for.
    * $omit ( optional ) fields you would not like to show up in the form
    */

    function table( $action, $table, $omit=array() )...
    // example
    echo $this->formbuilder->table( 'user/edit_save', 'media_items', array( 'created_at', 'updated_at' ) );

- [Log Issues or Suggestions](https://github.com/dperrymorrow/formbuilder/issues)
- [Follow me on Twitter](http://twitter.com/dperrymorrow)
