<?php

/**
 * Ccheckin master template.
 * 
 * @author      Daniel A. Koepke (dkoepke@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Ccheckin_Master_Template extends Bss_Master_Template
{
    protected function initTemplate ()
    {
        parent::initTemplate();
        $this->setMasterTemplate(Bss_Core_PathUtils::path(dirname(__FILE__), 'resources', 'master.html.tpl'));
        
        $this->registerPlugin('modifier', 'formatted_date', array($this, 'formattedDate'));
        $this->registerPlugin('function', 'select', array($this, 'select'));

    }

    protected function isKiosk ()
    {
        $cookieName = 'cc-kiosk';
        $cookieValue = 'kiosk';
        return (isset($_COOKIE[$cookieName]) && $_COOKIE[$cookieName] == $cookieValue);
    }
  
    public function formattedDate ($date, $format = 'medium', $time = false, $empty = '')
    {
        $timestamp = $date;
        
        if (is_object($date))
        {
            $timestamp = $date->getTimestamp();
        }
        elseif (!intval($date))
        {
            $timestamp = strtotime($date);
        }
        elseif (is_array($date))
        {
            $dt = new DateTime;
            $dt->setDate($date['year'], $date['month'], $date['day']);
            $timestamp = $dt->getTimestamp();
        }
        
        if (!$timestamp) return $empty;
        
        // Default to medium
        $dateformat = 'M. j, Y' . ($time ? ' g:i A' : ''); // Nov. 7, 2011, 12:00 PM
        
        switch ($format)
        {
            case 'short': // 11/7/2011, 12:00pm
                $dateformat = 'n/j/Y' . ($time ? ' g:ia' : '');
                break;
            case 'long': // November 7, 2011, 12:00 PM
                $dateformat = 'F j, Y' . ($time ? ' g:i A' : '');
                break;
            case 'input': // 2011-11-07
                $dateformat = 'Y-m-d';
                break;
        }
        
        return date($dateformat, $timestamp);
    }

    public function select ($params, $smarty)
    {
        $tag = 'select';
        $openTag = "<{$tag}";

        foreach ($params as $key => $value)
        {
            $openTag .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }

        // ADD SELECT OPTIONS IN HERE!!!

        return $openTag . '>' . $text . "</{tag}>";
    }
}
