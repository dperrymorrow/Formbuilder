<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Formbuilder{


	public $CI;
	public $defaults = array();
	public $options = array();
	private $form_id = FALSE;

	function __construct($config = array())
	{
		$this->CI = &get_instance();
		$this->CI->load->helper( array( 'form', 'string', 'inflector' ));
		$this->CI->load->library( array( 'form_validation' ));

		if ( ! empty($config))
		{
			$this->initialize($config);
		}
	}

	function initialize($config = array())
	{
		foreach ($config as $key => $val)
		{
			$key = preg_replace("/^formbuilder_/i", '', $key);
			$this->options[$key] = $val;
		}

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
						$str .= $this->text( $field[ 'Field' ], humanize( $field[ 'Field' ] ));
						break;
					}
				}
			}
		}
		
		$str .= $this->submit( 'submit', 'Save '. ucwords(singular(humanize( $table ))));
		$str .= $this->close();
		return $str;

	}

	function open( $action, $multipart=FALSE, $params=array() )
	{

		if ($this->options['auto_id'] == TRUE && isset($params['id']) && !empty($params['id']))
		{
			$this->form_id = $params['id'];
		}

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
		$output = form_close()."\n";
		if( $this->form_id )
		{
			$output .= "<!-- closing ". $this->form_id ." -->\n";
		}
		return $output;

	}

	function text( $var, $label=null, $lblOptions=null, $fieldOptions=null )
	{

		$fieldOptions['id'] = $this->_auto_id($fieldOptions, $var);

		$ret = $this->form_label( $label, $fieldOptions['id'], $lblOptions )."\n";
		$ret .= "\t\t<input type=\"text\" name=\"$var\" value=\"".$this->get_val( $var )."\"".$this->attribute_string( $fieldOptions )."/>\n";
		$ret = $this->add_error( $var, $ret, 'text' );
		return  $ret;
	}

	function hidden( $var, $default='' )
	{
		
		$attr = '';
		
		if ($fieldOptions['id'] = $this->_auto_id( NULL, $var ))
			$attr = $this->attribute_string($fieldOptions);

		if( !empty( $default ))
		{
			$val = $default;
		}
		else
		{
			$val = $this->get_val( $var, $default );
		}

		$ret =  "\t\t<input type=\"hidden\" name=\"$var\" value=\"$val\" $attr />\n";
		return  $ret;
	}


	function textarea($var, $label=null, $lblOptions=null, $fieldOptions=null )
	{

		$fieldOptions['id'] = $this->_auto_id($fieldOptions, $var);

		$ret = $this->form_label( $label, $fieldOptions['id'], $lblOptions )."\n";
		$ret .= "\t\t<textarea name=\"".$var."\"".$this->attribute_string( $fieldOptions ).">".$this->get_val( $var )."</textarea>\n";
		$ret = $this->add_error( $var, $ret, 'textarea' );
		return  $ret;
	}

	function form_label( $label_text=null, $id='', $attributes=array() )
	{


		if( $label_text == NULL )
		{
			return '';
		}

		$label = "\t\t<label";

		if ($id != '')
		{
			$label .= " for=\"$id\"";
		}

		if (is_array($attributes) AND count($attributes) > 0)
		{
			foreach ($attributes as $key => $val)
			{
				$label .= ' '.$key.'="'.$val.'"';
			}
		}

		$label .= ">$label_text</label>";

		return $label;
	}


	function get_val( $var, $default = null, $boolean=FALSE )
	{

		if( isset( $_POST[ $var] ) )
		{
			$return_val = form_prep( $this->CI->input->post( $var ));

		}
		elseif( $default != null )
		{
			$return_val = form_prep( $default );

		}
		else
		{
			$return_val =  '';
		}
		
		if( is_array($this->defaults) )
		{
			if(isset( $this->defaults[ $var ])) {
				$return_val =  form_prep($this->defaults[ $var ]);
			} else {
				$return_val = '';
			}
		} 
		elseif( is_object($this->defaults) )
		{
			$return_val = form_prep($this->defaults->$var);
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

		$fieldOptions['id'] = $this->_auto_id($fieldOptions, $var);

		$ret = $this->form_label( $label, $fieldOptions['id'], $lblOptions )."\n";
		$ret .= "\t\t<input type=\"password\" name=\"$var\" value=\"".$this->get_val( $var ) . '" '. $this->attribute_string( $fieldOptions ) . '/>'."\n";
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
			if (!empty($val))
				$str .= ' '.$key.'="'.$val.'"';
			endforeach;
		}
		return $str;
	}

	function checkbox( $var, $label, $value, $default=FALSE, $lblOptions=null, $fieldOptions=null )
	{

		$fieldOptions['id'] = $this->_auto_id( $fieldOptions, $var );

		$atts = $this->attribute_string( $fieldOptions );
		// no longer need the rand
		// $rand = random_string( 'alnum', 5 );

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

		$ret = "\t\t<input id=\"".$fieldOptions['id']."\" type=\"checkbox\" name=\"$var\" value=\"$value\" $atts/>\n";
		$ret .= $this->form_label( $label, $fieldOptions['id'], $lblOptions )."\n";
		$ret = $this->add_error( $var, $ret, 'checkbox' );
		return  $ret;
	}

	function radio( $var, $label, $value, $default=FALSE, $lblOptions=null, $fieldOptions=null )
	{

		$fieldOptions['id'] = $this->_auto_id($fieldOptions, $var);

		$atts = $this->attribute_string($fieldOptions);
		//$rand = random_string('alnum', 5 );

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


		$ret = "\t\t<input id=\"".$fieldOptions['id']."\" type=\"radio\" name=\"$var\" value=\"$value\" $atts/>\n";
		$ret .= $this->form_label( $label, $fieldOptions['id'], $lblOptions )."\n";
		$ret = $this->add_error( $var, $ret, 'radio' );
		return  $ret;
	}


	function drop_down( $var, $label, $options=array(), $default='', $lblOptions=null, $fieldOptions=null )
	{

		$fieldOptions['id'] = $this->_auto_id($fieldOptions, $var);

		$fieldOpts = $this->attribute_string( $fieldOptions );

		if( isset( $_POST[ $var ] ))
		{
			$default = $_POST[ $var ];
		}
		if(is_array($this->defaults))	{
			if( isset( $this->defaults[ $var ] ))
			{
				$default = $this->defaults[ $var ];
			}
		} else {
		if( isset( $this->defaults->$var ))
			{
				$default = $this->defaults->$var;
			}
		}

		$ret = '';

		if( $label != NULL )
		{
			$ret .= $this->form_label( $label, $fieldOptions['id'], $lblOptions )."\n";
		}
		$ret .= form_dropdown( $var, $options, $default, $fieldOpts )."\n";

		$ret = $this->add_error( $var, $ret, 'drop_down' );
		return  $ret;
	}

	private function _auto_id ($options = NULL, $var = NULL)
	{

		if( is_array($options) && isset($options['id']) && !empty($options['id']) )
		{
			return $options['id'];
		}
		else if( $this->form_id && !empty( $var )) 
		{
			return $this->form_id . "-" . $var;
		}
		else 
		{
			return FALSE;
		}
	}
	
	public function submit( $var, $label, $options=array() )
	{
		$atts = $this->attribute_string( $options );
		return "\t<input type=\"submit\" name=\"$var\" value=\"$label\" $atts/>\n";
	}

} // end Formbuilder class
