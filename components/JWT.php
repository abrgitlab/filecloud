<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 10.06.16
 * Time: 22:49
 */

namespace app\components;

use ArrayAccess;
use \DomainException;
use \DateTime;
use yii\base\Object;

/**
 * JSON Web Token implementation, based on this spec:
 * http://tools.ietf.org/html/draft-ietf-oauth-json-web-token-06
 *
 * PHP version 5
 *
 * @category Authentication
 * @package  Authentication_JWT
 * @author   Neuman Vong <neuman@twilio.com>
 * @author   Anant Narayanan <anant@php.net>
 * @license  http://opensource.org/licenses/BSD-3-Clause 3-clause BSD
 * @link     https://github.com/firebase/php-jwt
 */
class JWT extends Object
{

    private static $supported_algs = [
        'HS256' => ['hash_hmac', 'SHA256'],
        'HS512' => ['hash_hmac', 'SHA512'],
        'HS384' => ['hash_hmac', 'SHA384'],
        'RS256' => ['openssl', 'SHA256'],
    ];

    public $error = '';

    private $header;
    public $payload;
    private $key;
    private $signature;

    public $token;

    /**
     * Decodes a JWT string into a PHP object.
     *
     * @param string            $jwt            The JWT
     *                                          If the algorithm used is asymmetric, this is the public key
     * @param array             $allowed_algs   List of supported verification algorithms
     *                                          Supported algorithms are 'HS256', 'HS384', 'HS512' and 'RS256'
     *
     * @return bool
     *
     * @uses jsonDecode
     * @uses urlsafeB64Decode
     */
    public function decode($jwt, $allowed_algs = []) {
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            $this->error = 'Wrong number of segments';
            return false;
        }
        list($headb64, $bodyb64, $this->signature) = $tks;
        if (null === ($this->header = $this->jsonDecode($this->urlsafeB64Decode($headb64)))) {
            $this->error = 'Invalid header encoding';
            return false;
        }
        if (null === $this->payload = $this->jsonDecode($this->urlsafeB64Decode($bodyb64))) {
            $this->error = 'Invalid claims encoding';
            return false;
        }

        $this->signature = $this->urlsafeB64Decode($this->signature);

        if (empty($this->header->alg)) {
            $this->error = 'Empty algorithm';
            return false;
        }
        if (empty(self::$supported_algs[$this->header->alg])) {
            $this->error = 'Algorithm not supported';
            return false;
        }
        if (!is_array($allowed_algs) || !in_array($this->header->alg, $allowed_algs)) {
            $this->error = 'Algorithm not allowed';
            return false;
        }

        return true;
    }

    /**
     * Converts and signs a PHP object or array into a JWT string.
     *
     * @param object|array  $payload    PHP object or array
     * @param string        $key        The secret key.
     *                                  If the algorithm used is asymmetric, this is the private key
     * @param string        $alg        The signing algorithm.
     *                                  Supported algorithms are 'HS256', 'HS384', 'HS512' and 'RS256'
     * @param array         $head       An array with header elements to attach
     *
     * @return bool
     *
     * @uses jsonEncode
     * @uses urlsafeB64Encode
     */
    public function encode($payload, $key, $alg = 'HS256', $keyId = null, $head = null) {
        $this->token = null;

        $this->payload = $payload;
        $this->key = $key;
        $this->header = ['typ' => 'JWT', 'alg' => $alg];
        if ($keyId !== null) {
            $this->header['kid'] = $keyId;
        }
        if (isset($head) && is_array($head)) {
            $this->header = array_merge($head, $this->header);
        }

        $segments = [];
        $segments[] = $this->urlsafeB64Encode($this->jsonEncode($this->header));
        $segments[] = $this->urlsafeB64Encode($this->jsonEncode($this->payload));

        if ($this->sign($alg) !== null) {
            $segments[] = $this->urlsafeB64Encode($this->signature);

            $this->token = implode('.', $segments);
            return true;
        } else
            return false;
    }

    /**
     * Sign a string with a given key and algorithm.
     *
     * @param string            $alg    The signing algorithm.
     *                                  Supported algorithms are 'HS256', 'HS384', 'HS512' and 'RS256'
     *
     * @return bool
     */
    private function sign($alg = 'HS256') {
        $segments = [];
        $segments[] = $this->urlsafeB64Encode($this->jsonEncode($this->header));
        $segments[] = $this->urlsafeB64Encode($this->jsonEncode($this->payload));
        $signing_input = implode('.', $segments);

        list($function, $algorithm) = self::$supported_algs[$alg];
        switch($function) {
            case 'hash_hmac':
                $this->signature = hash_hmac($algorithm, $signing_input, $this->key, true);
                return true;
            case 'openssl':
                $this->signature = '';
                $success = openssl_sign($signing_input, $this->signature, $this->key, $algorithm);
                if (!$success) {
                    $this->error = 'OpenSSL unable to sign data';
                    return false;
                }
                return true;
            default:
                $this->error = 'Algorithm not supported';
                return false;
        }
    }

    /**
     * Verify a signature with the message, key and method. Not all methods
     * are symmetric, so we must have a separate verify and sign method.
     *
     * @param string|resource   $key        For HS*, a string key works. for RS*, must be a resource of an openssl public key
     * @param string            $alg        The algorithm
     *
     * @return bool
     */
    public function verify($key, $alg) {
        if (empty($key)) {
            $this->error = 'Key may not be empty';
            return false;
        }

        if (is_array($key) || $key instanceof ArrayAccess) {
            if (isset($this->header->kid)) {
                $key = $key[$this->header->kid];
            } else {
                $this->error = '"kid" empty, unable to lookup correct key';
                return false;
            }
        }

        if (empty(self::$supported_algs[$alg])) {
            $this->error = 'Algorithm not supported';
            return false;
        }

        // Check if the nbf if it is defined. This is the time that the
        // token can actually be used. If it's not yet that time, abort.
        if (isset($this->payload->nbf) && $this->payload->nbf > time()) {
            $this->error = 'Cannot handle token prior to ' . date(DateTime::ISO8601, $this->payload->nbf);
            return false;
        }

        // Check that this token has been created before 'now'. This prevents
        // using tokens that have been created for later use (and haven't
        // correctly used the nbf claim).
        if (isset($this->payload->iat) && $this->payload->iat > time()) {
            $this->error = 'Cannot handle token prior to ' . date(DateTime::ISO8601, $this->payload->iat);
            return false;
        }

        // Check if this token has expired.
        if (isset($this->payload->exp) && time() >= $this->payload->exp) {
            $this->error = 'Expired token';
            return false;
        }

        $segments = [];
        $segments[] = $this->urlsafeB64Encode($this->jsonEncode($this->header));
        $segments[] = $this->urlsafeB64Encode($this->jsonEncode($this->payload));
        $signing_input = implode('.', $segments);

        list($function, $algorithm) = self::$supported_algs[$alg];
        switch($function) {
            case 'openssl':
                $success = openssl_verify($signing_input, $this->signature, $key, $algorithm);
                if (!$success)
                    $this->error = 'OpenSSL unable to verify data: ' . openssl_error_string();
                return $success === 1;
            case 'hash_hmac':
            default:
                $hash = hash_hmac($algorithm, $signing_input, $key, true);
                if (function_exists('hash_equals')) {
                    return hash_equals($this->signature, $hash);
                }
                $len = min($this->safeStrlen($this->signature), $this->safeStrlen($hash));

                $status = 0;
                for ($i = 0; $i < $len; $i++) {
                    $status |= (ord($this->signature[$i]) ^ ord($hash[$i]));
                }
                $status |= ($this->safeStrlen($this->signature) ^ $this->safeStrlen($hash));

                return ($status === 0);
        }
    }

    /**
     * Decode a JSON string into a PHP object.
     *
     * @param string $input JSON string
     *
     * @return object|null Object representation of JSON string
     *
     * @throws DomainException Provided string was invalid JSON
     */
    private function jsonDecode($input) {
        if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
            /** In PHP >=5.4.0, json_decode() accepts an options parameter, that allows you
             * to specify that large ints (like Steam Transaction IDs) should be treated as
             * strings, rather than the PHP default behaviour of converting them to floats.
             */
            $obj = json_decode($input, false, 512, JSON_BIGINT_AS_STRING);
        } else {
            /** Not all servers will support that, however, so for older versions we must
             * manually detect large ints in the JSON string and quote them (thus converting
             *them to strings) before decoding, hence the preg_replace() call.
             */
            $max_int_length = strlen((string) PHP_INT_MAX) - 1;
            $json_without_bigints = preg_replace('/:\s*(-?\d{'.$max_int_length.',})/', ': "$1"', $input);
            $obj = json_decode($json_without_bigints);
        }

        if (function_exists('json_last_error') && $errno = json_last_error()) {
            $this->handleJsonError($errno);
            return null;
        } elseif ($obj === null && $input !== 'null') {
            $this-> error = 'Null result with non-null input';
            return null;
        }
        return $obj;
    }

    /**
     * Encode a PHP object into a JSON string.
     *
     * @param object|array $input A PHP object or array
     *
     * @return string|null JSON representation of the PHP object or array
     *
     * @throws DomainException Provided object could not be encoded to valid JSON
     */
    private function jsonEncode($input) {
        $json = json_encode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            $this->handleJsonError($errno);
            return null;
        } elseif ($json === 'null' && $input !== null) {
            $this-> error = 'Null result with non-null input';
            return null;
        }
        return $json;
    }

    /**
     * Decode a string with URL-safe Base64.
     *
     * @param string $input A Base64 encoded string
     *
     * @return string A decoded string
     */
    private function urlsafeB64Decode($input) {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Encode a string with URL-safe Base64.
     *
     * @param string $input The string you want encoded
     *
     * @return string The base64 encode of what you passed in
     */
    private function urlsafeB64Encode($input) {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * Helper method to create a JSON error.
     *
     * @param int $errno An error number from json_last_error()
     *
     * @return void
     */
    private function handleJsonError($errno) {
        $messages = array(
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON'
        );
        $this-> error =
            isset($messages[$errno])
                ? $messages[$errno]
                : 'Unknown JSON error: ' . $errno;
    }

    /**
     * Get the number of bytes in cryptographic strings.
     *
     * @param string
     *
     * @return int
     */
    private function safeStrlen($str) {
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, '8bit');
        }
        return strlen($str);
    }
}
