<?php
    if (!function_exists('custom_secure_url')) {
        function custom_secure_url($path, $parameters = [], $secure = null)
        {
            // Check if the app is running on localhost
            if (app()->environment('local')) {
                // Use url() for localhost without HTTPS
                return url($path, $parameters);
            }
    
            // Use secure_url() for other environments
            return secure_url($path, $parameters, $secure);
        }
    }
   
?>