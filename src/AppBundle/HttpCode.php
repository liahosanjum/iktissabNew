<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 1/17/17
 * Time: 8:16 AM
 */

namespace AppBundle;


class HttpCode
{
    const  HTTP_OK                 = 200;
    const  HTTP_CREATED            = 201;
    const  HTTP_ACCEPTED           = 202;
    const  HTTP_NON_AUTHORITATIVE  = 203;
    const  HTTP_NO_CONTENT         = 204;

    const  HTTP_BADE_REQUEST       = 400;
    const  HTTP_UN_AUTHORIZED      = 401;
    const  HTTP_FORBIDDEN          = 403;
    const  HTTP_NOT_FOUND          = 404;
    const  HTTP_METHOD_NOT_ALLOWED = 405;

}