<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace plagiarism_uwrite\library\OAuth;

/**
 * Class OAuthUtil
 *
 * @package plagiarism_uwrite\library\OAuth
 */
class OAuthUtil {
    /**
     * @param      $header
     * @param bool $only_allow_oauth_parameters
     *
     * @return array
     */
    public static function split_header($header, $only_allow_oauth_parameters = true) {
        $pattern = '/(([-_a-z]*)=("([^"]*)"|([^,]*)),?)/';
        $offset = 0;
        $params = array();
        while (preg_match($pattern, $header, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
            $match = $matches[0];
            $header_name = $matches[2][0];
            $header_content = (isset($matches[5])) ? $matches[5][0] : $matches[4][0];
            if (preg_match('/^oauth_/', $header_name) || !$only_allow_oauth_parameters) {
                $params[$header_name] = OAuthUtil::urldecode_rfc3986($header_content);
            }
            $offset = $match[1] + strlen($match[0]);
        }
        if (isset($params['realm'])) {
            unset($params['realm']);
        }

        return $params;
    }
    // This decode function isn't taking into consideration the above
    // modifications to the encoding process. However, this method doesn't
    // seem to be used anywhere so leaving it as is.

    /**
     * @param $string
     *
     * @return string
     */
    public static function urldecode_rfc3986($string) {
        return urldecode($string);
    }
    // Utility function for turning the Authorization: header into
    // parameters, has to do some unescaping
    // Can filter out any non-oauth parameters if needed (default behaviour).

    /**
     * @return array|false
     */
    public static function get_headers() {
        $headers = OAuthUtil::get_headers_internal();
        if (!is_array($headers)) {
            return $headers;
        }
        if ((!isset($headers['Authorization'])) && isset($headers['X-Oauth1-Authorization'])) {
            $headers['Authorization'] = $headers['X-Oauth1-Authorization'];
        }

        return $headers;
    }

    // Helper to try to sort out headers for people who aren't running apache.
    /**
     * @return array|false
     */
    public static function get_headers_internal() {
        if (function_exists('apache_request_headers')) {
            // We need this to get the actual Authorization: header
            // because apache tends to tell us it doesn't exist.
            return apache_request_headers();
        }
        // Otherwise we don't have apache and are just going to have to hope
        // that $_SERVER actually contains what we need.
        $out = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == "HTTP_") {
                // This is chaos, basically it is just there to capitalize the first
                // letter of every word that is not an initial HTTP and strip HTTP
                // code from przemek.
                $key = str_replace(
                        " ",
                        "-",
                        ucwords(strtolower(str_replace("_", " ", substr($key, 5))))
                );
                $out[$key] = $value;
            }
        }

        return $out;
    }
    // Helper to deal with proxy configurations that "eat" the Authorization:
    // header on our behalf - fall back to the alternate Authorization header.
    /**
     * @param $input
     *
     * @return array
     */
    public static function parse_parameters($input) {
        if (!isset($input) || !$input) {
            return array();
        }
        $pairs = explode('&', $input);
        $parsed_parameters = array();
        foreach ($pairs as $pair) {
            $split = explode('=', $pair, 2);
            $parameter = OAuthUtil::urldecode_rfc3986($split[0]);
            $value = isset($split[1]) ? OAuthUtil::urldecode_rfc3986($split[1]) : '';
            if (isset($parsed_parameters[$parameter])) {
                // We have already recieved parameter(s) with this name, so add to the list
                // of parameters with this name.
                if (is_scalar($parsed_parameters[$parameter])) {
                    // This is the first duplicate, so transform scalar (string) into an array
                    // so we can add the duplicates.
                    $parsed_parameters[$parameter] = array($parsed_parameters[$parameter]);
                }
                $parsed_parameters[$parameter][] = $value;
            } else {
                $parsed_parameters[$parameter] = $value;
            }
        }

        return $parsed_parameters;
    }
    // This function takes a input like a=b&a=c&d=e and returns the parsed
    // parameters like this
    // array('a' => array('b','c'), 'd' => 'e')

    /**
     * @param $params
     *
     * @return string
     */
    public static function build_http_query($params) {
        if (!$params) {
            return '';
        }
        // Urlencode both keys and values.
        $keys = OAuthUtil::urlencode_rfc3986(array_keys($params));
        $values = OAuthUtil::urlencode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);
        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($params, 'strcmp');
        $pairs = array();
        foreach ($params as $parameter => $value) {
            if (is_array($value)) {
                // If two or more parameters share the same name, they are sorted by their value
                // Ref: Spec: 9.1.1 (1)
                natsort($value);
                foreach ($value as $duplicate_value) {
                    $pairs[] = $parameter . '=' . $duplicate_value;
                }
            } else {
                $pairs[] = $parameter . '=' . $value;
            }
        }
        // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
        // Each name-value pair is separated by an '&' character (ASCII code 38)
        return implode('&', $pairs);
    }

    /**
     * @param $input
     *
     * @return array|mixed|string
     */
    public static function urlencode_rfc3986($input) {
        if (is_array($input)) {
            return array_map(array(__NAMESPACE__ . '\OAuthUtil', 'urlencode_rfc3986'), $input);
        } else {
            if (is_scalar($input)) {
                return str_replace(
                        '+',
                        ' ',
                        str_replace('%7E', '~', rawurlencode($input))
                );
            } else {
                return '';
            }
        }
    }
}