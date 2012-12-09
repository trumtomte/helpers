<?php
/**
 * Cloudlib
 *
 * @author      Sebastian Book <cloudlibframework@gmail.com>
 * @copyright   Copyright (c) 2012 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace cloudlib\helpers;

use ReflectionMethod;
use RuntimeException;

/**
 * The Form Class
 *
 * @copyright   Copyright (c) 2012 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
abstract class Form
{
    /**
     * Security token for validation
     *
     * @access  public
     * @var     string
     */
    public $token = null;

    /**
     * Array of request arguments
     *
     * @access  public
     * @var     array
     */
    public $arguments = null;

    /**
     * Current classname
     *
     * @access  public
     * @var     string
     */
    public $classname = null;

    /**
     * Set the request arguments, set the security token
     *
     * @access  public
     * @param   array   $arguments  The request arguments
     * @param   string  $token      The security token
     * @return  void
     */
    public function __construct($arguments, $token = null)
    {
        $this->token = $token;
        $this->arguments = $arguments;
        
        $classname = explode('\\', get_class($this));
        $this->classname = end($classname);
    }

    /**
     * Magic method that checks if a property has been defined,
     * then checks if the corresponding fieldtype method exists,
     * then invokes it with all the arguments
     *
     * @access  public
     * @param   string  $name       The name of the property
     * @param   array   $arguments  The fieldtype method arguments
     * @return  string              Returns the HTML form element(s)
     */
    public function __call($name, $arguments = array())
    {
        if(isset($this->$name))
        {
            $method = $this->$name . 'Field';

            if(method_exists($this, $method))
            {
                $reflection = new ReflectionMethod($this, $method);
                array_unshift($arguments, $name);

                return $reflection->invokeArgs($this, $arguments);
            }
        }
    }

    /**
     * Validate the form, call validate methods if they exist
     *
     * @acceess public
     * @throws  RuntimeException    If the validation fails
     * @return  boolean
     */
    public function validate()
    {
        if( ! isset($this->arguments[$this->classname]))
        {
            return false;
        }

        $args = $this->arguments[$this->classname];

        if($args['token'] !== $this->token)
        {
            return false;
        }

        $validators = preg_grep('/^(validate)\w+/', get_class_methods($this));

        foreach($validators as $validator)
        {
            if(method_exists($this, $validator))
            {
                $field = str_replace('validate', '', strtolower($validator));

                if( ! isset($args[$field]))
                {
                    return false;
                }

                $message = 'Form validation error';

                $validated = $this->$validator($args[$field], $message);

                if( ! $validated)
                {
                    throw new RuntimeException($message);
                }
            }
        }

        return true;
    }

    /**
     * Gets an array of all the current accessible properties and their values
     *
     * @access  public
     * @return  array
     */
    public function getFields()
    {
        $properties = array_flip(
            preg_grep('/^(?!token|arguments|classname)/',
                array_keys(get_object_vars($this))
        ));

        $args = $this->arguments[$this->classname];

        foreach($properties as $key => $value)
        {
            $properties[$key] = isset($args[$key]) ? $args[$key] : '';
        }

        return $properties;
    }

    /**
     * Get the value of a property
     *
     * @access  public
     * @param   string  $name   The name of the property
     * @return  mixed           Returns the property value else false
     */
    public function getField($name)
    {
        $args = $this->arguments[$this->classname];

        return isset($args[$name]) ? $args[$name] : false;
    }

    /**
     * Get the value for the name attribute of the HTML element
     *
     * @access  public
     * @param   string  $name   The name of the HTML element
     * @return  string          Returns the converted name of the HTML element
     */
    public function fieldName($name)
    {
        return sprintf('%s[%s]', $this->classname, $name);
    }

    /**
     * Returns the opening HTML element of the form
     *
     * @access  public
     * @param   string  $action     The form action
     * @param   string  $method     The form method
     * @param   array   $attributes Array of HTML attributes
     * @return  string              The HTML element
     */
    public function open($action = null, $method = null, array $attributes = array())
    {
        $attributes['action'] = $action;

        $attributes['method'] = ($method === null) ? 'POST' : $method;

        if(isset($attributes['type']))
        {
            switch($attributes['type'])
            {
                case 'file':
                    $attributes['enctype'] = 'multipart/form-data';
                    break;
                default:
                    $attributes['enctype'] = 'application/x-www-form-urlencoded';
                    break;
            }

            unset($attributes['type']);
        }

        return sprintf('<form %s>%s',
            $this->getAttrStr($attributes),
            $this->inputField('token', array('type' => 'hidden', 'value' => $this->token))
        );
    }

    /**
     * Returns an input HTML element
     * 
     * @access  public
     * @param   string  $name       The element name
     * @param   array   $attributes Array of HTML attributes
     * @return  string              The HTML element
     */
    public function inputField($name, array $attributes = array())
    {
        $attributes['name'] = $this->fieldName($name);

        if( ! isset($attributes['type']))
        {
            $attributes['type'] = 'text';
        }

        $attrString = $this->getAttrStr($attributes);

        return sprintf('<input %s>', $attrString);
    }

    /**
     * Shorthand method for Form::inputField()
     *
     * @access  public
     * @param   string  $name       The element name
     * @param   array   $attributes Array of HTML attributes
     * @return  string              The HTML element
     */
    public function textField($name = null, array $attributes = array())
    {
        return $this->inputField($name, $attributes);
    }

    /**
     * Shorthand method for Form::inputField()
     *
     * @access  public
     * @param   string  $name       The element name
     * @param   array   $attributes Array of HTML attributes
     * @return  string              The HTML element
     */
    public function submitField($name = null, array $attributes = array())
    {
        $attributes['type'] = 'submit';
        return $this->inputField($name, $attributes);
    }

    /**
     * Shorthand method for Form::inputField()
     *
     * @access  public
     * @param   string  $name       The element name
     * @param   array   $attributes Array of HTML attributes
     * @return  string              The HTML element
     */
    public function passwordField($name = null, array $attributes = array())
    {
        $attributes['type'] = 'password';
        return $this->inputField($name, $attributes);
    }

    /**
     * Shorthand method for Form::inputField()
     *
     * @access  public
     * @param   string  $name       The element name
     * @param   array   $attributes Array of HTML attributes
     * @return  string              The HTML element
     */
    public function radioField($name = null, array $attributes = array())
    {
        $attributes['type'] = 'radio';
        return $this->inputField($name, $attributes);
    }

    /**
     * Shorthand method for Form::inputField()
     *
     * @access  public
     * @param   string  $name       The element name
     * @param   array   $attributes Array of HTML attributes
     * @return  string              The HTML element
     */
    public function checkboxField($name = null, array $attributes = array())
    {
        $attributes['type'] = 'checkbox';
        return $this->inputField($name, $attributes);
    }

    /**
     * Shorthand method for Form::inputField()
     *
     * @access  public
     * @param   string  $name       The element name
     * @param   array   $attributes Array of HTML attributes
     * @return  string              The HTML element
     */
    public function resetField($name = null, array $attributes = array())
    {
        $attributes['type'] = 'reset';
        return $this->inputField($name, $attributes);
    }

    /**
     * Shorthand method for Form::inputField()
     *
     * @access  public
     * @param   string  $name       The element name
     * @param   array   $attributes Array of HTML attributes
     * @return  string              The HTML element
     */
    public function hiddenField($name = null, array $attributes = array())
    {
        $attributes['type'] = 'hidden';
        return $this->inputField($name, $attributes);
    }

    /**
     * Shorthand method for Form::inputField()
     *
     * @access  public
     * @param   string  $name       The element name
     * @param   array   $attributes Array of HTML attributes
     * @return  string              The HTML element
     */
    public function fileField($name = null, array $attributes = array())
    {
        $attributes['type'] = 'file';
        return $this->inputField($name, $attributes);
    }

    /**
     * Shorthand method for Form::inputField()
     *
     * @access  public
     * @param   string  $name       The element name
     * @param   array   $attributes Array of HTML attributes
     * @return  string              The HTML element
     */
    public function uploadField($name = null, array $attributes = array())
    {
        $attributes['type'] = 'file';
        return $this->inputField($name, $attributes);
    }

    /**
     * Returns an textarea HTML element
     *
     * @access  public
     * @param   string  $name       The element name
     * @param   string  $text       The contents of the textarea
     * @param   array   $attributes Array of HTML attributes
     * @return  string              The HTML element
     */
    public function textareaField($name, $text = null, array $attributes = array())
    {
        $attributes['name'] = $this->fieldName($name);

        if( ! isset($attributes['rows']))
        {
            $attributes['rows'] = 8;
        }

        if( ! isset($attributes['cols']))
        {
            $attributes['cols'] = 25;
        }

        $attributes = $this->getAttrStr($attributes);

        return sprintf('<textarea %s>%s</textarea>', $attributes, $text);
    }

    /**
     * Returns a select field HTML element
     *
     * @access  public
     * @param   string  $name       The element name
     * @param   array   $items      Array of option elements (can be 2 dimensional)
     * @param   array   $attributes Array of HTML attributes
     * @return  string              The HTML element
     */
    public function selectField($name = null, array $items = array(), array $attributes = array())
    {
        $attributes['name'] = $this->fieldName($name);

        $selectList = null;

        foreach($items as $key => $item)
        {
            if(is_array($item))
            {
                $selectList .= sprintf('<optgroup label="%s">', $key);

                foreach($item as $key => $item)
                {
                    $selectList .= sprintf('<option value="%s">%s</option>',
                        $key, $item);
                }

                $selectList .= '</optgroup>';
            }
            else
            {
                $selectList .= sprintf('<option value="%s">%s</option>',
                    $key, $item);
            }
        }

        $attributes = $this->getAttrStr($attributes);

        return sprintf('<select %s>%s</select>', $attributes, $selectList);
    }

    /**
     * Returns a button HTML element
     *
     * @access  public
     * @param   string  $name       The element name
     * @param   string  $value      The button text
     * @param   array   $attributes Array of HTML attributes
     * @return  string              The HTML element
     */
    public function buttonField($name = null, $value = null, array $attributes = array())
    {
        $attributes['name'] = $this->fieldName($name);

        if( ! isset($attributes['type']))
        {
            $attributes['type'] = 'submit';
        }

        $value = ($value === null) ? 'Submit' : $value;

        $attributes = $this->getAttrStr($attributes);

        return sprintf('<button %s>%s</button>', $attributes, $value);
    }

    /**
     * Gets an string of attribute:value pairs
     *
     * @access  public
     * @param   array   $attributes Array of attribute:value pairs
     * @return  string              The string of attribute:value pairs
     */
    public function getAttrStr(array $attributes)
    {
        $attributeString = '';

        foreach($attributes as $key => $value)
        {
            $attributeString .= sprintf('%s="%s" ', $key, $value);
        }

        return trim($attributeString);
    }
}
