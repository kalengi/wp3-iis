# README #

WP3-IIS is a regular Wordpress plugin that is accessed via Super **Admin > WP3 IIS**. 
It is configured it as follows: 

* i) Website description: example.com (this is the site name as it appears on IIS) 

* ii) Server IP: 192.168.0.10 (the IP address assigned to the site on IIS)

If an error occurs in the process of updating IIS, there will be an entry on the plugin's admin page under the **Pending Headers** tab showing the error message.
The IIS update is carried out automatically as you add and remove domains. 
For the entries showing up under **Pending Headers**, the update is done by clicking on **Execute** after the error condition has been addressed.