<?php
/**
 * Created by PhpStorm.
 * User: abdulali
 * Date: 12/18/16
 * Time: 1:09 PM
 */
namespace AppBundle;
class AppConstant
{
    //Activity Log Constants
    const  ACTIVITY_LOGIN = 'Login';
    const  ACTIVITY_LOGOUT = 'Logout';

    //related to cookies
    const  COOKIE_EXPIRY = 2592000; //'86400*30';
    const  COOKIE_LOCALE = 'c_locale';
    const  COOKIE_COUNTRY = 'c_country';

    //webservice main url
    const WEBAPI_URL = 'http://150.150.101.26:8080/iktserv2_2/web/';

    //ikt user languages
    const IKT_USER_LANG_EN_EN = 'English';
    const IKT_USER_LANG_EN_AR = 'Arabic';
    const IKT_USER_LANG_AR_EN = 'الإنجليزية';
    const IKT_USER_LANG_AR_AR = 'العربية';

    const IKT_REG_SCENERIO_1 = 'SCENERIO1';
    const IKT_REG_SCENERIO_2 = 'SCENERIO2';
}