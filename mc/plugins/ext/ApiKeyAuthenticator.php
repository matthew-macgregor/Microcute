<?php
/*
* Project: Microcute
* Author: Matthew MacGregor
* Copyright: 2015 by Matthew MacGregor
* License: MIT (see LICENSE.txt for full details).
*/
class ApiKeyAuthenticator {

    private $private_key;

    public function __construct( $private_key ) {
        $this->private_key = $private_key;
    }

    /**
    * The is_auth method returns true if it is able to generate a match for
    * $hash using $userkey:$private_key.
    *
    * @param $ts string: UNIX timestamp for the request.
    * @param $hash string: Hash of "$userkey:$private_key".
    */
    public function is_auth( $id, $hash ) {
        $is_auth = false;

        if( ($id || $hash ) == false ) {
            return $is_auth;
        }

        if( md5("$id:{$this->private_key}") == $hash ) {
            $is_auth = true;
        }

        return $is_auth;
    }

    /**
    * Helper function. Gets the private key from the config ('api_key'), gets
    * $userkey and $hash from the query string, authenticates, and aborts the
    * request with a 401/unauthorized unless authorized.
    *
    * Given this request: api/kitty?meow=1234&key=9741d4ea7f8da0be8509d1126fad01ce
    *
    * ApiKeyAuthenticator::auto_auth('meow', 'key', 'Get outta here!');
    *
    * @param $userkey string: The name of the key on the query string.
    * @param $hash string: The name of the hash key on the query string.
    * @param $func Function: An optional function that will be executed if
    * authentication fails, instead of the default action.
    */
    public static function auto_auth( $userkey, $hash, $func = null ) {
        $api_key = Config::get_key("api_key");
        if( $api_key ) {
            $id = null;
            $auth = null;

            $authenticator = new ApiKeyAuthenticator($api_key);

            if( isset($_GET[$userkey]) && isset($_GET[$hash]) ) {
                $id = $_GET[$userkey];
                $auth = $_GET[$hash];
            }

            if( $authenticator->is_auth( $id, $auth ) == false ) {
                if( $func ) {
                    $func();
                } else {
                    self::unauthorized("Not authorized.");
                }
            }
        }
    }

    /**
    * Kills the request with a 401/unauthorized.
    * @param $message: Message echoed to the user.
    */
    public static function unauthorized( $message ) {
        header('HTTP/1.1 401 Unauthorized', true, 401);
        die($message);
    }

}
