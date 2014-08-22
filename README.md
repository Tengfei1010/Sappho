Sappho
======

    Sappho extends from CodeIgniter,but Sappho rewrite some part of CodeIgniter.
    Sappho adds web service API and CI for MongoDB Driver.


_User Guide_
=======
    Sappho is all coming from CodeIgniter.
    CodeIgniter's Manual is also suitable to Sappho. 
    @link:http://codeigniter.org.cn/user_guide/index.html 


_Sappho is different from CodeIgniter_
======
    I rename CI_controller as SH_Controller.
    If there is some problem,please contact me @email:tutengfei@126.com.

>  **1. Controller** 

>  - You can write Controller like this:

    class Example extends SH_Controller {
  
        function __construct() {
  
            parent::__construct();
    
            ......
        }
    }
    
> **2. MonoDB**

> - **2.1**  Please config your mongodb parameters in application/config/config.php.
   
    //  ::CONFIG/mongo_conn/string:: MongoDB Connection Parameters
    //  By default you will connect to whatever server is specified in your php.ini 
    //configuration, usually mongodb://localhost:27017
    //  Format: mongodb://[username:password@]hostname_or_ip[:port][/database]
    //  Skip the [username:password@] part if your server doesn't do authentication
    //  Skip the [:port] part to use the default port (27017)
    //  Skip the [/database] part if your user is a global admin, 
    // and does not belong to the specific database
    
    $config['mongo_conn']    = 'mongodb://root:root@localhost:27017';
    
    // ::CONFIG/mongo_db/string:: Database to connect to
    
    $config['mongo_db'] = 'TestMongoDB';
    
> - **2.2**   How to use use mongodb driver

    
    (1)One function: 'get'==> get(&$collection, $criteria = array(),
                    $sort = array(), $keys = array(), $limit = 0, 
                    $return_cursor = false)
    
        You can use like this: $this->mdb->get(${your_collection},
                                                ${your_data},...,);
    
    (2)Other functions:getfirst(),getbyid(),update(),updatebyid(),
                    delete(),deletebyid(),delbyid()....


> **3. CSS Bootstrap**

> - **3.1** Use Bootstrap css

    (1) You should first load helper:$this->load->helper('html');
        Or you can  config this in application/config/autoload.php 
        auto load this helper file.
    
    (2) If you want to use bootstrap css,you could write 
        'echo (cdn_bootstrap_css());' in your view.such as:
    
    //example_view.php
    <html>
        <head>...</head>
        <title>....</title>
        <!-- bootstrap css -->
        <?php $this->load->helper('html'); echo (cdn_bootstrap_css()); ?>
        .......
        <body>....</body>
    </html>
    
    (3) The default bootstrap css version is 2.3.0.
        If you want to use other version,you can write this: 
        'echo (cdn_bootstrap_css(3.2.0));'
        
    (4) You also can use cdn_boostrap_theme_css($version),
        cdn_jquery($version='1.10.0'),
        cdn_bootstrap_js($version='2.3.0').
        

> **4.  Web Service -- Json**

> - **4.1** web serivce api

    You can use this library to build Web services a application.
    
    
