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
        $this->urlBase = $siteSettings->getProperty('classdata-api-url');
        $this->apiKey = $siteSettings->getProperty('classdata-api-key');
        $this->apiSecret = $siteSettings->getProperty('classdata-api-secret');
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
    
    // NOTE: This function doesn't seem to be working. The API freezes up
    public function getEnrollments ($semester, $role = null)
    {
        // echo "<pre>"; var_dump('in getEnrollments(). this function needs testing.'); die;
        $paramMap = array();
        
        if ($role)
        {
            $paramMap['role'] = $role;
        }
        
        $url = $this->signResource("enrollments/{$semester}", $paramMap);
        list($code, $data) = $this->request($url);
        $returnData = $data !== null ? array_shift($data) : $data;

        return array($code, $returnData);     // TODO: Test if array_shift works for this function ***************
    }

    public function getUserEnrollments ($userid, $semester, $role = null)
    {
        $paramMap = array();
        if ($role)
        {
            $paramMap['role'] = $role;
        }

        $url = $this->signResource("users/{$userid}/semester/{$semester}", $paramMap);
        list($code, $data) = $this->request($url);
        $returnData = $data !== null ? array_shift($data) : $data;

        return array($code, $returnData);
    }
    
    public function getChanges ($semester, $since)
    {
        $url = $this->signResource("changes/{$semester}", array('since' => $since));
        list($code, $data) = $this->request($url);
        $returnData = $data !== null ? array_shift($data) : $data;

        return array($code, $returnData);     // TODO: Test if array_shift works for this function ***************
    }

    public function getCourse ($id)
    {
        $url = $this->signResource('courses/' . $id, array('include' => 'description,prerequisites,students,instructors,userdata'));
        list($code, $data) = $this->request($url);

        return array($code, $data); 
    }
    
    // TODO: POST needs testing of implementation
    public function getCourses ($idList)
    {
        $url = $this->signResource('courses', array('include' => 'description,prerequisites'));
        list($code, $data) = $this->request($url, true, array('ids' => implode(',', $idList)));
        
        if (!empty($data) && $code === 200)
        {
            return $data['courses'];
        }
        
        return false;
    }
    
    // TODO: POST needs testing of implementation
    public function getUsers ($idList)
    {
        $url = $this->signResource('users', array('include' => 'description,prerequisites'));
        list($code, $data) = $this->request($url, true, array('ids' => implode(',', $idList)));
        
        if (!empty($data) && $code === 200)
        {
            return $data['users'];
        }
        
        return false;
    }

    public function getOrganizations ()
    {
        $paramMap = array('include' => 'college');
        $url = $this->signResource('organizations', $paramMap);
        list($code, $data) = $this->request($url);
        $returnData = $data !== null ? array_shift($data) : $data;

        return array($code, $returnData);
    }

    public function getDepartments ()
    {
        $paramMap = array();
        $url = $this->signResource('departments', $paramMap);
        list($code, $data) = $this->request($url);
        $returnData = $data !== null ? array_shift($data) : $data;

        return array($code, $returnData);
    }

    // TODO: POST needs testing of implementation
    protected function request ($url, $post=false, $postData=array())
    {
        $data = null;
        
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        if ($post) 
        { 
            curl_setopt($ch, CURLOPT_POST, true); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); 
        } 
        $rawData = curl_exec($ch);
        
        if (!curl_error($ch)) {
            $data = json_decode($rawData, true);
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        
        return array($httpCode, $data);
    }
}