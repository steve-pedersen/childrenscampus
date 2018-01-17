<?php

/**
 * The service functionality to connect to ClassData/SIS data.
 *
 * @author Charles O'Sullivan <chsoney@sfsu.edu>
 */
class Ccheckin_ClassData_Service
{
    private $urlBase;

    private $apiKey;

    private $apiSecret;

    private $channel;

    public function __construct($app, $channel = 'raw')
    {
        $siteSettings = $app->siteSettings;
        $this->urlBase = $siteSettings->getProperty('ccheckin-api-url');
        $this->apiKey = $siteSettings->getProperty('ccheckin-api-key');
        $this->apiSecret = $siteSettings->getProperty('ccheckin-api-secret');
        $this->channel = $channel;
    }
    
    protected function signResource ($resource, $paramMap)
    {
        $url = $this->urlBase . $resource;

        $paramMap['a'] = $this->apiKey; //die($paramMap['a']);
        $paramMap['channel'] = (!isset($paramMap['channel']) ? $this->channel : $paramMap['channel']);
        uksort($paramMap, 'strcmp');
        
        $params = array();
        foreach ($paramMap as $k => $v) { $params[] = urlencode($k) . '=' . urlencode($v); }
        $url .= '?' . implode('&', $params);
        
        return $url . '&s=' . sha1($this->apiSecret . $url);
    }
    
    public function getEnrollments ($semester, $role = null)
    {
        //$paramMap = array('ids' => true);
        $paramMap = array();
        
        if ($role)
        {
            $paramMap['role'] = $role;
        }
        
        $url = $this->signResource("enrollments/{$semester}", $paramMap);
        //die($url);
        $req = new HttpRequest($url, HTTP_METH_GET);
        $req->send();
        
        $body = $req->getResponseBody();
        $data = null;
        
        if (!empty($body))
        {
            $data = @json_decode($body, true);
        }
        return array($req->getResponseCode(), $data);
    }

    public function getUserEnrollments ($userid, $semester, $role = null)
    {
        $paramMap = array();
        if ($role)
        {
            $paramMap['role'] = $role;
        }

        $url = $this->signResource("users/{$userid}/semester/{$semester}", $paramMap);
        //die($url);
        $req = new HttpRequest($url, HTTP_METH_GET);
        $req->send();
        
        $body = $req->getResponseBody();
        $data = null;
        
        if (!empty($body))
        {
            $data = @json_decode($body, true);
        }

        return array($req->getResponseCode(), $data['courses']);
    }
    
    public function getChanges ($semester, $since)
    {
        $url = $this->signResource("changes/{$semester}", array('since' => $since));
        $req = new HttpRequest($url, HTTP_METH_GET);
        $req->send();
        
        $body = $req->getResponseBody();
        $data = null;
        
        if (!empty($body))
        {
            $data = @json_decode($body, true);
        }

        return array($req->getResponseCode(), $data);
    }

    public function getCourse ($id)
    {
        $url = $this->signResource('courses/' . $id, array('include' => 'description,prerequisites,students,instructors,userdata'));
        $req = new HttpRequest($url, HTTP_METH_POST);
        $req->send();
        
        $body = $req->getResponseBody();
        $data = null;
        
        if (!empty($body))
        {
            $data = @json_decode($body, true);
        }
        
        return array($req->getResponseCode(), $data);
    }
    
    public function getCourses ($idList)
    {
        $url = $this->signResource('courses', array('include' => 'description,prerequisites'));
        $req = new HttpRequest($url, HTTP_METH_POST);
        $req->setPostFields(array('ids' => implode(',', $idList)));
        $req->send();
        
        $body = $req->getResponseBody();
        $data = null;
        
        if (!empty($body) && $req->getResponseCode() === 200)
        {
            $data = @json_decode($body, true);
            return $data['courses'];
        }
        
        return false;
    }
    
    public function getUsers ($idList)
    {
        $url = $this->signResource('users', array('include' => 'description,prerequisites'));
        $req = new HttpRequest($url, HTTP_METH_POST);
        $req->setPostFields(array('ids' => implode(',', $idList)));
        $req->send();
        
        $body = $req->getResponseBody();
        $data = null;
        
        if (!empty($body) && $req->getResponseCode() === 200)
        {
            $data = @json_decode($body, true);
            return $data['users'];
        }
        
        return false;
    }
}