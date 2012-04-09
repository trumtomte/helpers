<?php
/**
 * Cloudlib
 *
 * @author      Sebastian Book <cloudlibframework@gmail.com>
 * @copyright   Copyright (c) 2011 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace cloudlib;

/**
 * The Form class
 *
 * @copyright   Copyright (c) 2011 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Form
{
    /**
     * String containing the created form
     *
     * @access  protected
     * @var     string
     */
    protected $form = null;

    /**
     * Constructor, open up a new form
     *
     * @access  public
     * @param   string  $action     Form action value
     * @param   array   $options    Array of form options (ex method/enctype etc)
     * @return  void
     */
    public function __construct($action = null, array $options = array())
    {
        $this->form = static::open($action, $options);
    }

    /**
     * Open the form
     *
     * @access  public
     * @param   string  $action     Form action value
     * @param   array   $options    Array of form options (ex method/enctype etc)
     * @return  string              Returns the opening element of a form
     */
    public static function open($action = null, array $options = array())
    {
        $options['action'] = $action;

        if( ! isset($options['method']))
        {
            $options['method'] = 'POST';
        }

        if(isset($options['type']))
        {
            switch($options['type'])
            {
                case 'file':
                    $options['enctype'] = 'multipart/form-data';
                    break;
                default:
                    $options['enctype'] = 'application/x-www-form-urlencoded';
                    break;
            }
            unset($options['type']);
        }

        $attributes = static::getAttrStr($options);

        return sprintf('<form %s>' . PHP_EOL, $attributes);
    }

    /**
     * Create an input field
     *
     * @access  public
     * @param   string  $name       Input name value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  string              Returns an input element
     */
    public static function input($name = null, array $options = array())
    {
        $options['name'] = $name;

        if( ! isset($options['type']))
        {
            $options['type'] = 'text';
        }

        if($options['name'] === null)
        {
            $options['name'] = $options['type'];
        }

        $label = null;

        if(isset($options['label']))
        {
            $label = static::label($options['name'], $options['label']);
            unset($options['label']);
        }

        $attributes = static::getAttrStr($options);

        return sprintf('%s<input %s/>' . PHP_EOL, $label, $attributes);
    }

    /**
     * Create an input field with the type 'submit'
     *
     * @access  public
     * @param   string  $name       Input name value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  string              Returns an input element
     */
    public static function submit($name = null, array $options = array())
    {
        $options['type'] = 'submit';
        return static::input($name, $options);
    }

    /**
     * Create an input field with the type 'password'
     *
     * @access  public
     * @param   string  $name       Input name value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  string              Returns an input element
     */
    public static function password($name = null, array $options = array())
    {
        $options['type'] = 'password';
        return static::input($name, $options);
    }

    /**
     * Create an input field with the type 'radio'
     *
     * @access  public
     * @param   string  $name       Input name value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  string              Returns an input element
     */
    public static function radio($name = null, array $options = array())
    {
        $options['type'] = 'radio';
        return static::input($name, $options);
    }

    /**
     * Create an input field with the type 'checkbox'
     *
     * @access  public
     * @param   string  $name       Input name value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  string              Returns an input element
     */
    public static function checkbox($name = null, array $options = array())
    {
        $options['type'] = 'checkbox';
        return static::input($name, $options);
    }

    /**
     * Create an input field with the type 'reset'
     *
     * @access  public
     * @param   string  $name       Input name value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  string              Returns an input element
     */
    public static function reset($name = null, array $options = array())
    {
        $options['type'] = 'reset';
        return static::input($name, $options);
    }

    /**
     * Create an input field with the type 'hidden'
     *
     * @access  public
     * @param   string  $name       Input name value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  string              Returns an input element
     */
    public static function hidden($name = null, array $options = array())
    {
        $options['type'] = 'hidden';
        return static::input($name, $options);
    }

    /**
     * Create an input field with the type 'file'
     *
     * @access  public
     * @param   string  $name       Input name value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  string              Returns an input element
     */
    public static function file($name = null, array $options = array())
    {
        $options['type'] = 'file';
        return static::input($name, $options);
    }

    /**
     * Shorthand function for creating a csrf token
     *
     * @access  public
     * @param   string  $value      Input name value
     * @param   string  $name       Input value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  string              Returns an input element
     */
    public static function token($value, $name = 'token', array $options = array())
    {
        $options['type'] = 'hidden';
        $options['value'] = $value;
        return static::input($name, $options);
    }

    /**
     * Create a button element
     *
     * @access  public
     * @param   string  $name       Button name value
     * @param   string  $text       Button content
     * @param   array   $options    Array of button options (ex type/class etc)
     * @return  string              Return an button element
     */
    public static function button($name = null, $text = null, array $options = array())
    {
        $options['name'] = $name;

        if( ! isset($options['type']))
        {
            $options['type'] = 'submit';
        }

        if($options['name'] === null)
        {
            $options['name'] = $options['type'];
        }

        if($text === null)
        {
            $text = $options['name'];
        }

        $label = null;

        if(isset($options['label']))
        {
            $label = static::label($options['name'], $options['label']);
            unset($options['label']);
        }

        $attributes = static::getAttrStr($options);

        return sprintf('%s<button %s>%s</button>' . PHP_EOL, $label, $attributes, $text);
    }

    /**
     * Create a textarea element
     *
     * @access  public
     * @param   string  $name       Textarea name value
     * @param   string  $text       Textarea content
     * @param   array   $options    Array of textarea options (ex row/cols etc)
     * @return  string              Returns a textarea element
     */
    public static function textarea($name = null, $text = null, array $options = array())
    {
        $options['name'] = $name;

        if($options['name'] === null)
        {
            $options['name'] = 'textarea';
        }

        if( ! isset($options['rows']))
        {
            $options['rows'] = 8;
        }
        if( ! isset($options['cols']))
        {
            $options['cols'] = 25;
        }

        $label = null;

        if(isset($options['label']))
        {
            $label = static::label($options['name'], $options['label']);
            unset($options['label']);
        }

        $attributes = static::getAttrStr($options);

        return sprintf('%s<textarea %s>%s</textarea>' . PHP_EOL,
            $label, $attributes, $text);
    }

    /**
     * Create a dropdown list (<select>)
     *
     * @access  public
     * @param   string  $name       Select name value
     * @param   array   $items      Select option items
     * @param   array   $options    Array of select options (ex class/id etc)
     * @return  string              Returns a select element
     */
    public static function select($name = null, array $items = array(), array $options = array())
    {
        $options['name'] = $name;

        if($options['name'] === null)
        {
            $options['name'] = 'select';
        }

        $selectList = null;

        foreach($items as $key => $value)
        {
            if(is_array($value))
            {
                $selectList .= sprintf('<optgroup label="%s">' . PHP_EOL, $key);

                foreach($value as $k => $v)
                {
                    $selectList .= sprintf('<option value="%s">%s</option>' . PHP_EOL,
                        $k, $v);
                }

                $selectList .= '</optgroup>' . PHP_EOL;
            }
            else
            {
                $selectList .= sprintf('<option value="%s">%s</option>' . PHP_EOL,
                    $key, $value);
            }
        }

        $label = null;

        if(isset($options['label']))
        {
            $label = static::label($options['name'], $options['label']);
            unset($options['label']);
        }

        $attributes = static::getAttrStr($options);

        return sprintf('%s<select %s>' . PHP_EOL . '%s</select>' . PHP_EOL,
            $label, $attributes, $selectList);
    }

    /**
     * Create a label element
     *
     * @access  public
     * @param   string  $for        For value (label)
     * @param   string  $text       Label content
     * @param   array   $options    Array of label options (ex class/id etc)
     * @return  string              Returns a label element
     */
    public static function label($for = null, $text = null, array $options = array())
    {
        $options['for'] = $for;

        if($text === null)
        {
            $text = $options['for'];
        }

        $attributes = static::getAttrStr($options);

        return sprintf('<label %s>%s</label>' . PHP_EOL, $attributes, $text);
    }

    /**
     * End a form, and (optional) close it with a button element
     *
     * @access  public
     * @param   string  $name       Button name value
     * @param   string  $text       Button content
     * @param   array   $options    Array of button options (ex type/class etc)
     * @return  string              Return the closing tag of the form and (optional) a button element
     */
    public static function close($name = null, $text = null, array $options = array())
    {
        if(func_num_args() == 0)
        {
            return '</form>' . PHP_EOL;
        }

        $options['type'] = 'submit';
        return sprintf('%s</form>' . PHP_EOL, static::button($name, $text, $options));
    }

    /**
     * Create an input field
     *
     * @access  public
     * @param   string  $name       Input name value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  object              Returns an input element
     */
    public function addInput($name = null, array $options = array())
    {
        $this->form .= static::input($name, $options);
        return $this;
    }

    /**
     * Create an input field with the type 'submit'
     *
     * @access  public
     * @param   string  $name       Input name value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  object              Returns an input element
     */
    public function addSubmit($name = null, array $options = array())
    {
        $this->form .= static::submit($name, $options);
        return $this;
    }

    /**
     * Create an input field with the type 'password'
     *
     * @access  public
     * @param   string  $name       Input name value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  object              Returns an input element
     */
    public function addPassword($name = null, array $options = array())
    {
        $this->form .= static::password($name, $options);
        return $this;
    }

    /**
     * Create an input field with the type 'radio'
     *
     * @access  public
     * @param   string  $name       Input name value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  object              Returns an input element
     */
    public function addRadio($name = null, array $options = array())
    {
        $this->form .= static::radio($name, $options);
        return $this;
    }

    /**
     * Create an input field with the type 'checkbox'
     *
     * @access  public
     * @param   string  $name       Input name value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  object              Returns an input element
     */
    public function addCheckbox($name = null, array $options = array())
    {
        $this->form .= static::checkbox($name, $options);
        return $this;
    }

    /**
     * Create an input field with the type 'reset'
     *
     * @access  public
     * @param   string  $name       Input name value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  object              Returns an input element
     */
    public function addReset($name = null, array $options = array())
    {
        $this->form .= static::reset($name, $options);
        return $this;
    }

    /**
     * Create an input field with the type 'hidden'
     *
     * @access  public
     * @param   string  $name       Input name value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  object              Returns an input element
     */
    public function addHidden($name = null, array $options = array())
    {
        $this->form .= static::hidden($name, $options);
        return $this;
    }

    /**
     * Create an input field with the type 'file'
     *
     * @access  public
     * @param   string  $name       Input name value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  object              Returns an input element
     */
    public function addFile($name = null, array $options = array())
    {
        $this->form .= static::file($name, $options);
        return $this;
    }

    /**
     * Shorthand function for creating a csrf token
     *
     * @access  public
     * @param   string  $value      Input name value
     * @param   string  $name       Input value
     * @param   array   $options    Array of input options (ex type/class etc)
     * @return  object              Returns an input element
     */
    public function addToken($value, $name = 'token', array $options = array())
    {
        $this->form .= static::token($value, $name, $options);
        return $this;
    }

    /**
     * Create a button
     *
     * @access  public
     * @param   string  $name       Button name value
     * @param   string  $text       Button content
     * @param   array   $options    Array of button options (ex type/class etc)
     * @return  object              Return an button element
     */
    public function addButton($name = null, $text = null, array $options = array())
    {
        $this->form .= static::button($name, $text, $options);
        return $this;
    }

    /**
     * Create a textarea element
     *
     * @access  public
     * @param   string  $name       Textarea name value
     * @param   string  $text       Textarea content
     * @param   array   $options    Array of textarea options (ex row/cols etc)
     * @return  object              Returns a textarea element
     */
    public function addTextarea($name = null, $text = null, array $options = array())
    {
        $this->form .= static::textarea($name, $text, $options);
        return $this;
    }

    /**
     * Create a dropdown list (<select>)
     *
     * @access  public
     * @param   string  $name       Select name value
     * @param   array   $items      Select option items
     * @param   array   $options    Array of select options (ex class/id etc)
     * @return  object              Returns a select element
     */
    public function addSelect($name = null, array $items = array(), array $options = array())
    {
        $this->form .= static::select($name, $items, $options);
        return $this;
    }

    /**
     * Create a label element
     *
     * @access  public
     * @param   string  $for        For value (label)
     * @param   string  $text       Label content
     * @param   array   $options    Array of label options (ex class/id etc)
     * @return  object              Returns a label element
     */
    public function addLabel($for = null, $text = null, array $options = array())
    {
        $this->form .= static::label($for, $text, $options);
        return $this;
    }

    /**
     * End a form, with the possibility for a button field
     *
     * @access  public
     * @param   string  $name       Button name value
     * @param   string  $text       Button content
     * @param   array   $options    Array of button options (ex type/class etc)
     * @return  object              Return the closing tag of the form and (optional) a button element
     */
    public function closeForm($name = null, $text = null, array $options = array())
    {
        $this->form .= static::close($name, $text, $options);
    }

    /**
     * Return the whole form
     *
     * @access  public
     * @return  string  Returns the form as a string
     */
    public function __toString()
    {
        return (string) $this->form;
    }

    /**
     * Take an array of attributes and return it as a string of HTML attribute:value pairs
     *
     * @access  protected
     * @param   array       $attributes     Array of attribute:value pairs
     * @return  string                      Returns a string of attribute:value pairs
     */
    protected static function getAttrStr(array $attributes)
    {
        $string = null;

        foreach($attributes as $key => $value)
        {
            $string .= sprintf('%s="%s" ', $key, $value);
        }

        return $string;
    }
}
