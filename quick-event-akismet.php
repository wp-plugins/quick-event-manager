<?php

class qem_akismet {
    private $version = '0.4';
    private $wordPressAPIKey;
    private $blogURL;
    private $comment;
    private $apiPort;
    private $akismetServer;
    private $akismetVersion;
    private $ignore = array(
        'HTTP_COOKIE', 
        'HTTP_X_FORWARDED_FOR', 
        'HTTP_X_FORWARDED_HOST', 
        'HTTP_MAX_FORWARDS', 
        'HTTP_X_FORWARDED_SERVER', 
        'REDIRECT_STATUS', 
        'SERVER_PORT', 
        'PATH',
        'DOCUMENT_ROOT',
        'SERVER_ADMIN',
        'QUERY_STRING',
        'PHP_SELF'
    );
    public function __construct($blogURL, $wordPressAPIKey) {
        $this->blogURL = $blogURL;
        $this->wordPressAPIKey = $wordPressAPIKey;
        $this->apiPort = 80;
        $this->akismetServer = 'rest.akismet.com';
        $this->akismetVersion = '1.1';
        $this->comment['blog'] = $blogURL;
        $this->comment['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        if(isset($_SERVER['HTTP_REFERER'])) $this->comment['referrer'] = $_SERVER['HTTP_REFERER'];
        $this->comment['user_ip'] = $_SERVER['REMOTE_ADDR'] != getenv('SERVER_ADDR') ? $_SERVER['REMOTE_ADDR'] : getenv('HTTP_X_FORWARDED_FOR');
    }
    public function isKeyValid() {
        $response = $this->sendRequest('key=' . $this->wordPressAPIKey . '&blog=' . $this->blogURL, $this->akismetServer, '/' . $this->akismetVersion . '/verify-key');
        return $response[1] == 'valid';
    }
	
	private function sendRequest($request, $host, $path) {
        $http_request  = "POST " . $path . " HTTP/1.0\r\n";
        $http_request .= "Host: " . $host . "\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n";
        $http_request .= "Content-Length: " . strlen($request) . "\r\n";
        $http_request .= "User-Agent: Akismet PHP5 Class " . $this->version . " | Akismet/1.11\r\n";
        $http_request .= "\r\n";
        $http_request .= $request;
        $socketWriteRead = new qem_SocketWriteRead($host, $this->apiPort, $http_request);
        $socketWriteRead->send();
        return explode("\r\n\r\n", $socketWriteRead->getResponse(), 2);
    }
    
    private function getQueryString() {
        foreach($_SERVER as $key => $value) {
            if(!in_array($key, $this->ignore)) {
                if($key == 'REMOTE_ADDR') {
                    $this->comment[$key] = $this->comment['user_ip'];
                } else {
                    $this->comment[$key] = $value;
                }
            }
        }
        $query_string = '';
        foreach($this->comment as $key => $data) {
            if(!is_array($data)) $query_string .= $key . '=' . urlencode(stripslashes($data)) . '&';
        }
        return $query_string;
    }
    
    public function isCommentSpam() {
        $response = $this->sendRequest($this->getQueryString(), $this->wordPressAPIKey . '.rest.akismet.com', '/' . $this->akismetVersion . '/comment-check');
        if($response[1] == 'invalid' && !$this->isKeyValid()) {
            throw new exception('The Wordpress API key passed to the Akismet constructor is invalid.  Please obtain a valid one from http://wordpress.com/api-keys/');
        }
        return ($response[1] == 'true');
    }

    public function submitSpam() {
        $this->sendRequest($this->getQueryString(), $this->wordPressAPIKey . '.' . $this->akismetServer, '/' . $this->akismetVersion . '/submit-spam');
    }
    public function submitHam() {
        $this->sendRequest($this->getQueryString(), $this->wordPressAPIKey . '.' . $this->akismetServer, '/' . $this->akismetVersion . '/submit-ham');
    }
    public function setUserIP($userip) {$this->comment['user_ip'] = $userip;}
    public function setReferrer($referrer) {$this->comment['referrer'] = $referrer;}
    public function setPermalink($permalink) {$this->comment['permalink'] = $permalink;}
    public function setCommentType($commentType) {$this->comment['comment_type'] = $commentType;}
    public function setCommentAuthor($commentAuthor) {$this->comment['comment_author'] = $commentAuthor;}
    public function setCommentAuthorEmail($authorEmail) {$this->comment['comment_author_email'] = $authorEmail;}
    public function setCommentAuthorURL($authorURL) {$this->comment['comment_author_url'] = $authorURL;}
    public function setCommentContent($commentBody) {$this->comment['comment_content'] = $commentBody;}
    public function setAPIPort($apiPort) {$this->apiPort = $apiPort;}
    public function setAkismetServer($akismetServer) {$this->akismetServer = $akismetServer;}
    public function setAkismetVersion($akismetVersion) {$this->akismetVersion = $akismetVersion;}
}

class qem_SocketWriteRead {
    private $host;
    private $port;
    private $request;
    private $response;
    private $responseLength;
    private $errorNumber;
    private $errorString;
    public function __construct($host, $port, $request, $responseLength = 1160) {
        $this->host = $host;
        $this->port = $port;
        $this->request = $request;
        $this->responseLength = $responseLength;
        $this->errorNumber = 0;
        $this->errorString = '';
    }
    public function send() {
        $this->response = '';
        $fs = fsockopen($this->host, $this->port, $this->errorNumber, $this->errorString, 3);
        if($this->errorNumber != 0) {
            throw new Exception('Error connecting to host: ' . $this->host . ' Error number: ' . $this->errorNumber . ' Error message: ' . $this->errorString);
        }
        if($fs !== false) {
            @fwrite($fs, $this->request);
            while(!feof($fs)) 
                $this->response .= fgets($fs, $this->responseLength);
        }
        fclose($fs);
    }
    public function getResponse() {return $this->response;}
    public function getErrorNumner() {return $this->errorNumber;}
    public function getErrorString() {return $this->errorString;}
}