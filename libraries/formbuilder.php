<?php
class Formbuilder{


	public $CI;
	public $defaults = array();
	public $options = array();

	function __construct()
	{
		$this->CI = &get_instance();
		$this->CI->load->helper( array( 'form', 'string', 'inflector' ));
		$this->CI->load->library( array( 'form_validation' ));
		$this->options = $this->CI->config->item( 'formbuilder' );

		if( $this->options['error_class'] != NULL )
		{
			$this->CI->form_validation->set_error_delimiters( '<div class="'.$this->options['error_class'].'">', '</div>' );
		}
	}

	function table( $action, $table, $omit=array() )
	{

		$this->CI->load->database();

		$sql = "DESCRIBE `$table`";
		$desc = $this->CI->db->query( $sql )->result_array();



		$str = $this->open( $action );

		foreach ( $desc as $field )
		{

		if( !in_array( $field[ 'Key'], $omit ))
		{
			if( $field[ 'Key' ] == 'PRI' )
			{
				$str .= $this->hidden( $field[ 'Field' ] );
			}
			else
			{
				
				if( strpos( $field[ 'Type' ], '(' ) !== FALSE )
				{
					$arr = explode( '(', $field[ 'Type' ] );
					$type = $arr[ 0 ];
				}
				else
				{
					$type = $field[ 'Type' ];
				}
				
				switch ( $type ) 
				{
					case 'blob':
					case 'longtext':
					case 'text':
					$str .= $this->textarea( $field[ 'Field' ], humanize( $field[ 'Field' ]) );
					break;
					
					case 'enum':
					case 'tinyint':
					$default = FALSE;
					if( !empty($field[ 'Default']))
					{
						$default = TRUE;
					}
					$str .= $this->checkbox( $field[ 'Field' ], humanize( $field[ 'Field' ]), TRUE, $default );
					break;
					
					default:
					$str .=	$this->text( $field[ 'Field' ], humanize( $field[ 'Field' ] ));
					break;
				}
			}
			}}

			$str .= $this->close();
			return $str;

		}

		function open( $action, $multipart=FALSE, $params=array() )
		{

			if( $multipart == FALSE )
			{
				return "\n".form_open( $action, $params ) ."\n";
			}
			else
			{
				return "\n".form_open_multipart( $action, $params )."\n";
			}
		}

		function close()
		{	
			return  "</form>\n";
		}

		function text( $var, $label=null, $lblOptions=null, $fieldOptions=null )
		{

			$ret = $this->form_label( $label, $var, $lblOptions )."\n";
			$ret .=	"\t\t<input type=\"text\" name=\"$var\" value=\"".$this->get_val( $var )."\"".$this->attribute_string( $fieldOptions )."/>\n";
			$ret = $this->add_error( $var, $ret, 'text' );
			return  $ret;
		}

		function hidden( $var, $default='' )
		{
			if( !empty($default) ){
				$val = $default;
			}else{
				$val = $this->get_val( $var, $default );
			}

			$ret =	"\t\t<input type=\"hidden\" name=\"$var\" value=\"$val\" />\n";
			return  $ret;
		}


		function textarea($var, $label=null, $lblOptions=null, $fieldOptions=null )
		{
			$ret = $this->form_label( $label, $var, $lblOptions )."\n";
			$ret .=	"\t\t<textarea name=\"".$var."\"".$this->attribute_string( $fieldOptions ).">".$this->get_val( $var )."</textarea>\n";
			$ret = $this->add_error( $var, $ret, 'textarea' );
			return  $ret;
		}

		function form_label( $label_text=null, $id='', $attributes=array() )
		{


			if( $label_text == NULL ){
				return '';
			}

			$label = "\t\t<label";

			if ($id != ''){
				$label .= " for=\"$id\"";
			}

			if (is_array($attributes) AND count($attributes) > 0){
				foreach ($attributes as $key => $val){
					$label .= ' '.$key.'="'.$val.'"';
				}
			}

			$label .= ">$label_text</label>";

			return $label;
		}


		function get_val( $var, $default = null, $boolean=FALSE )
		{

			if( isset( $_POST[ $var] ) ){
				$return_val = form_prep( $this->CI->input->post( $var ));

			}elseif( isset( $this->defaults[ $var ])){
				$return_val =  form_prep($this->defaults[ $var ]);

			}elseif( $default != null ){
				$return_val = form_prep( $default );

			}else{
				$return_val =  '';
			}

			if( !$boolean )
			{
				return $return_val;

			}
			elseif( $return_val == '' or $return_val == '0' or $return_val == FALSE )
			{
				return FALSE;

			}
			else
			{
				return TRUE;
			}
		}

		function password( $var, $label, $lblOptions=null, $fieldOptions=null )
		{
			$ret = $this->form_label( $label, $var, $lblOptions )."\n";
			$ret .=	"\t\t<input type=\"password\" name=\"$var\" value=\"".$this->get_val( $var ) . '" '. $this->attribute_string( $fieldOptions ) . '/>'."\n";
			$ret = $this->add_error( $var, $ret, 'password' );
			return  $ret;
		}


		function add_error( $var, $str, $type )
		{

			if( $this->options['error_position'] == 1 )
			{
				$str = form_error( $var ) . $str;
			}
			else if( $this->options['error_position'] == 2 )
			{
				$str = str_replace( '</label>', '</label>'. form_error( $var ),  $str );
			}
			else
			{
				$str .= form_error( $var );
			}

			if( $this->options[ 'container_class' ] != NULL ){
				return "\t<div class=\"" . $this->options[ 'container_class' ] . " $type\">\n$str\t</div>\n";
			}
			else
			{
				return $str;
			}

		}

		function attribute_string( $attributes=null )
		{
			$str = '';
			if (is_array($attributes) AND count($attributes) > 0)
			{
				foreach ($attributes as $key => $val ):
				$str .= ' '.$key.'="'.$val.'"';
				endforeach;
			}
			return $str;
		}

		function checkbox( $var, $label, $value, $default=FALSE, $lblOptions=null, $fieldOptions=null )
		{

			$atts = $this->attribute_string($fieldOptions);
			$rand = random_string( 'alnum', 5 );
			//$this->CI = &get_instance(); // not needed since it's declared in constructor, all the methods inherited CI instance

			if( isset( $this->defaults[ $var ] ) and $this->defaults[ $var ] == $value )
			{
				$atts.=' checked="checked"';
			}
			else if( isset( $_POST[ $var ] ) and $_POST[ $var ] == $value )
			{
				$atts.=' checked="checked"';
			}
			elseif( $default == TRUE )
			{
				$atts.=' checked="checked"';
			}

			$ret = "\t\t<input id=\"$var$rand\" type=\"checkbox\" name=\"$var\" value=\"$value\" $atts/>\n";
			$ret .= $this->form_label( $label, $var.$rand, $lblOptions )."\n";
			$ret = $this->add_error( $var, $ret, 'checkbox' );
			return  $ret;
		}

		function radio( $var, $label, $value, $default=FALSE, $lblOptions=null, $fieldOptions=null )
		{
			//$this->CI = &get_instance(); // not needed since it's declared in constructor, all the methods inherited CI instance
			$atts = $this->attribute_string($fieldOptions);
			$rand = random_string('alnum', 5 );

			//trace( $this->defaults, 'formbuilder' );

			if( isset( $this->defaults[ $var ] ) and $this->defaults[ $var ] == $value )
			{
				$atts.=' checked="checked"';
			}
			else if( isset( $_POST[ $var ] ) and $_POST[ $var ] == $value )
			{
				$atts.=' checked="checked"';
			}
			elseif( $default == TRUE )
			{
				$atts.=' checked="checked"';
			}


			$ret = "\t\t<input id=\"$var$rand\" type=\"radio\" name=\"$var\" value=\"$value\" $atts/>\n";
			$ret .= $this->form_label( $label, $var.$rand, $lblOptions )."\n";
			$ret = $this->add_error( $var, $ret, 'radio' );
			return  $ret;
		}


		function drop_down( $var, $label, $options=array(), $default='', $lblOptions=null, $fieldOptions=null )
		{

			$fieldOpts = $this->attribute_string( $fieldOptions );

			if( isset( $_POST[ $var ] ))
			{
				$default = $_POST[ $var ];
			}
			elseif( isset( $this->defaults[ $var ] ))
			{
				$default = $this->defaults[ $var ];
			}

			$ret = '';

			if( $label != NULL )
			{
				$ret .= $this->form_label( $label, $var, $lblOptions )."\n";
			}
			$ret .= form_dropdown( $var, $options, $default, $fieldOpts )."\n";

			$ret = $this->add_error( $var, $ret, 'drop_down' );
			return  $ret;
		}


	} // end Formbuilder class