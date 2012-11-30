<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 7/7/11
 * Time: 3:08 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class Config {

    Const
        USERAGENT     = 'Monk Agent 1.0',                                                           /* identify user agent */
        TIMEOUT       = 10,                                                                         /* timeout for remote call */
//        AUTH_URL      = 'http://localhost/transcribe/simulate.php',                            /* URL to go to, if person needs to be authorized */
        AUTH_URL      = '/ui/login.php',                            /* URL to go to, if person needs to be authorized */
        //AUTH_URL    = 'http://application01.target.rug.nl/cgi-bin/monkwebj?cmd=login-api&subcmd=login',
        APPID         = '541ee2bf',                                                                 /* id of the app, used to get a token */
        SALT          = 'fjy345hjhf834',                                                            /* salt used to store with token id in session */
        STORE_URL     = 'http://application01.target.rug.nl/cgi-bin/monkwebj',                      /* url where to store monk data remotely */
        DATA_STORE    = 'navis',                                                                    /* unused */
        COOKIE_TIME   = 43200,                                                                      /* duration of the cookie */
        COOKIE_NAME   = 'tokenhash',                                                                /* name of the cookie where the tokenhash is saved */
        PAGE_STORE    = 'pageStore',                                                                /* session variable to store page data as global variable */
//        TOKEN_URL     = 'http://localhost/t3/TranscribeV3/simulate.php?appid=541ee2bf',             /* url of the token retrieval */
        TOKEN_URL     = 'http://localhost/transcribe/simulate.php?appid=541ee2bf',             /* url of the token retrieval */
        //const TOKEN_URL = 'http://application01.target.rug.nl/cgi-bin/monkwebj';  /* url of the token retrieval */
        LINEIDS_URL   = 'http://application01.target.rug.nl/cgi-bin/monkwebj?cmd=getwordlabel-api', //&token=X&appid=Y&pageid=cliwoc-Adm_177_1177b_0100';
        SAVE_DIR      = 'trained',                                                                  /* location of local saved training data */
        DEFAULT_SHEAR = 45,                                                                         /* default shear */
        LINE          = 'raw',                                                                    /* string in JSON setting it as an intersection */
        SQUARE        = 'rect',                                                                /* string in JSON setting it as a square */
//        PTS_RAW       = 'raw',                                                                      /* raw points */
//        PTS_RECT      = 'rect',                                                                     /* points are rectangle */
        ROI           = '-roi=',
        PTS           = '-pts=',
        MAX_SHEAR     = 130,
        MIN_SHEAR     = 45,

        REST_SERVER   = 'http://s4aaas.target-imedia.nl',


        DUMMY         = 'dummy';                                                                    /* just here to make it easier adding a new constant */


     static $ROIkeys = array(                                                                       /* keys used for matching the URL, getting the values behind it */
         '-x=',
         '-y=',
         '-w=',
         '-h='
     );

}
