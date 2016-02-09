<?php
/**
 * Created by PhpStorm.
 * User: yonman
 * Date: 09/02/2016
 * Time: 9:51 AM
 */
namespace ZRay\Requests;

class Curl {

    /**
     * @var array
     */
    private $knownOtps = ['CURLOPT_BINARYTRANSFER','CURLOPT_COOKIESESSION','CURLOPT_CERTINFO','CURLOPT_CONNECT_ONLY','CURLOPT_CRLF','CURLOPT_DNS_USE_GLOBAL_CACHE','CURLOPT_FAILONERROR','CURLOPT_FILETIME','CURLOPT_FOLLOWLOCATION','CURLOPT_FORBID_REUSE','CURLOPT_FRESH_CONNECT','CURLOPT_FTP_USE_EPRT','CURLOPT_FTP_USE_EPSV','CURLOPT_FTP_CREATE_MISSING_DIRS','CURLOPT_FTPAPPEND','CURLOPT_TCP_NODELAY','CURLOPT_FTPASCII','CURLOPT_FTPLISTONLY','CURLOPT_HEADER','CURLINFO_HEADER_OUT','CURLOPT_HTTPGET','CURLOPT_HTTPPROXYTUNNEL','CURLOPT_MUTE','CURLOPT_NETRC','CURLOPT_NOBODY','CURLOPT_NOPROGRESS','CURLOPT_NOSIGNAL','CURLOPT_POST','CURLOPT_PUT','CURLOPT_RETURNTRANSFER','CURLOPT_SAFE_UPLOAD','CURLOPT_SSL_VERIFYPEER','CURLOPT_TRANSFERTEXT','CURLOPT_UNRESTRICTED_AUTH','CURLOPT_UPLOAD','CURLOPT_VERBOSE','CURLOPT_BUFFERSIZE','CURLOPT_CLOSEPOLICY','CURLOPT_CONNECTTIMEOUT','CURLOPT_CONNECTTIMEOUT_MS','CURLOPT_DNS_CACHE_TIMEOUT','CURLOPT_FTPSSLAUTH','CURLOPT_HTTP_VERSION','CURLOPT_HTTPAUTH','CURLAUTH_ANY','CURLAUTH_ANYSAFE','CURLOPT_INFILESIZE','CURLOPT_LOW_SPEED_LIMIT','CURLOPT_LOW_SPEED_TIME','CURLOPT_MAXCONNECTS','CURLOPT_MAXREDIRS','CURLOPT_PORT','CURLOPT_POSTREDIR','CURLOPT_PROTOCOLS','CURLOPT_PROXYAUTH','CURLOPT_PROXYPORT','CURLOPT_PROXYTYPE','CURLOPT_REDIR_PROTOCOLS','CURLOPT_RESUME_FROM','CURLOPT_SSL_VERIFYHOST','CURLOPT_SSLVERSION','CURLOPT_TIMECONDITION','CURLOPT_TIMEOUT','CURLOPT_TIMEOUT_MS','CURLOPT_TIMEVALUE','CURLOPT_MAX_RECV_SPEED_LARGE','CURLOPT_MAX_SEND_SPEED_LARGE','CURLOPT_SSH_AUTH_TYPES','CURLOPT_IPRESOLVE','CURLOPT_CAINFO','CURLOPT_CAPATH','CURLOPT_COOKIE','CURLOPT_COOKIEFILE','CURLOPT_COOKIEJAR','CURLOPT_CUSTOMREQUEST','CURLOPT_EGDSOCKET','CURLOPT_ENCODING','CURLOPT_FTPPORT','CURLOPT_INTERFACE','CURLOPT_KEYPASSWD','CURLOPT_KRB4LEVEL','CURLOPT_POSTFIELDS','CURLOPT_PROXY','CURLOPT_PROXYUSERPWD','CURLOPT_RANDOM_FILE','CURLOPT_RANGE','CURLOPT_REFERER','CURLOPT_SSH_HOST_PUBLIC_KEY_MD5','CURLOPT_SSH_PUBLIC_KEYFILE','CURLOPT_SSH_PRIVATE_KEYFILE','CURLOPT_SSL_CIPHER_LIST','CURLOPT_SSLCERT','CURLOPT_SSLCERTPASSWD','CURLOPT_SSLCERTTYPE','CURLOPT_SSLENGINE','CURLOPT_SSLENGINE_DEFAULT','CURLOPT_SSLKEY','CURLOPT_SSLKEYPASSWD','CURLOPT_SSLKEYTYPE','CURLOPT_URL','CURLOPT_USERAGENT','CURLOPT_USERPWD','CURLOPT_HTTP200ALIASES','CURLOPT_HTTPHEADER','CURLOPT_POSTQUOTE','CURLOPT_QUOTE','CURLOPT_FILE','CURLOPT_INFILE','CURLOPT_STDERR','CURLOPT_WRITEHEADER','CURLOPT_HEADERFUNCTION','CURLOPT_PASSWDFUNCTION','CURLOPT_PROGRESSFUNCTION','CURLOPT_READFUNCTION','CURLOPT_WRITEFUNCTION','CURLOPT_SHARE',    ];

    /**
     * @var array
     */
    private $opts = [];

    /**
     * @var
     */
    private $responseBody;

    public function __construct() {
        /// collect more options we're not sure exist in the current version of PHP
        foreach ($this->knownOtps as $opt) {
            if (defined($opt)) {
                $this->opts[constant($opt)] = $opt;
            }
        }
    }

    public function collectOpts($context, &$storage) {
        $resource = $context['functionArgs'][0];
        $this->opts[$this->resourceKey($resource)][$this->optName($context['functionArgs'][1])] = $context['functionArgs'][2];
    }

    public function resetOpts($context, &$storage) {
        $resource = $context['functionArgs'][0];
        /// this is not really a good idea when reusing curl resources
        $this->opts[$this->resourceKey($resource)] = [];
    }

    public function collectOptsArray($context, &$storage) {
        $resource = $context['functionArgs'][0];
        $options = $context['functionArgs'][1];
        $key = $this->resourceKey($resource);
        $this->opts[$key] = isset($this->opts[$key]) ? $this->opts[$key] : [];

        if (! is_array($options)) {
            return ;
        }

        foreach ($options as $opt => $value) {
            $this->opts[$key][$this->optName($opt)] = $value;
        }
    }

    public function storeRequest($context, &$storage) {
        $resource = $context['functionArgs'][0];
        $responsePayload = $context['returnValue'];
        $curlInfo = curl_getinfo($resource);
        $storage['curl'][] = [
            'method' => '',
            'url' => isset($curlInfo[CURLINFO_EFFECTIVE_URL]) ? $curlInfo[CURLINFO_EFFECTIVE_URL] : '',
            'headers' => isset($curlInfo[CURLOPT_HTTPHEADER]) ? $curlInfo[CURLOPT_HTTPHEADER] : [],
            'params' => [],
            'responseRawPayload' => $responsePayload,
            'responseCode' => isset($curlInfo[CURLINFO_HTTP_CODE]) ? $curlInfo[CURLINFO_HTTP_CODE] : '',
            'duration' => isset($curlInfo[CURLINFO_TOTAL_TIME]) ? $curlInfo[CURLINFO_TOTAL_TIME] : '',
            'options' => $this->opts[$this->resourceKey($resource)]
        ];
    }

    private function optName($opt) {
        return isset($this->opts[$opt]) ? $this->opts[$opt] : $opt;
    }

    /**
     * @param $resource string
     * @return string
     */
    private function resourceKey($resource) {
        return (string)$resource;
    }
}

$curl = new Curl();

$zre = new \ZRayExtension('Requests');
$zre->setEnabledAfter('curl_init');

$zre->traceFunction('curl_setopt', function($context, &$storage){}, array($curl, 'collectOpts'));
$zre->traceFunction('curl_setopt_array', function($context, &$storage){}, array($curl, 'collectOptsArray'));
$zre->traceFunction('curl_reset', function($context, &$storage){}, array($curl, 'resetOpts'));
$zre->traceFunction('curl_exec', function($context, &$storage){}, array($curl, 'storeRequest'));