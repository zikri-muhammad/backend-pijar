<?php

/**
 * KDI Helpers
 *
 * @package     PIJAR App
 * @subpackage  Helpers
 * @category    KDigital's
 * @author      Mohamad Febrian Mosii <febrianaries@gmail.com
 * 
 * !! Every function must be written in english     !!
 * !! Please read coding standards references below !!
    * @link https://www.laravelbestpractices.com/
    * @link https://codeigniter.com/userguide3/general/styleguide.html
 * !! End !!
 */

 /**
 * Return and draw return for requests
 *
 * @param   string  $message    Message input
 * @param   array   $data       Array data payload to be returned
 * @param   string  $http_code  HTTP Header response Code (Read ENV Files for lists)
 * @return  JSON Response
 */
if (! function_exists('set_response')) {
    function set_response($message = '', $data = array(), $http_code = 'HTTP_OK') {
        $response['status']  = env($http_code);
        $response['message'] = $message;

        if ($data) {
            if ($http_code != 'HTTP_OK') {
                $response['errors'] = $data;
            } else {
                $response['results'] = $data;
            }
        } 

        http_response_code(env($http_code));
        header('Content-Type: application/json');
        echo json_encode($response);exit();
    }
}

/**
 * Return and draw return for error requests
 *
 * @param   string  $message    Message input
 * @param   string  $http_code  HTTP Header response Code (Read ENV Files for lists)
 * @return  JSON Response
 */
if (! function_exists('set_error_response')) {
    function set_error_response($message = '', $http_code = 'HTTP_INTERNAL_SERVER_ERROR') {
        set_response($message, [], $http_code);
    }
}

/**
 * Return and draw filters of request
 *
 * @param   array  $data   Payload data to be loaded
 * @return  Array Data
 */
if (! function_exists('filters')) {
    function filters($data = []) {
        $filter = [];

        $filter['limit']      = ! empty($data['limit']) ? $data['limit']          : 10; 
        $filter['order_by']   = ! empty($data['order_by']) ? $data['order_by']    : 'id'; 
        $filter['order_type'] = ! empty($data['order_type']) ? $data['order_type']: 'desc'; 
        $filter['page']       = ! empty($data['page']) ? $data['page']            : 'desc'; 

        $filter['search'] = ! empty($data['search']) ? $data['search'] : [];

        return $filter;
    }
}

/**
 * Return random string for code
 *
 * @param   array  $data   Payload data to be loaded
 * @return  Array Data
 */
if (! function_exists('generateRandomString')) {
    function generateRandomString($length = 12, $noPrefix = 0, $prefix = 'PJR') {
        $characters       = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString     = '';
        $prefix           = $noPrefix == 0 ? $prefix.'-' : "";

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $prefix.$randomString;
    }
}
