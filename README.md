# README #

WP3-IIS is a Wordpress plugin that works in conjuction with IISBroker (https://bitbucket.org/KalenGi/iisbroker) to automatically update website host headers on IIS. It is accessed via the menu **Super Admin > WP3 IIS** and 
configured as follows: 

* i) Website description: example.com (this is the site name as it appears on IIS) 

* ii) Server IP: 192.168.0.10 (the IP address assigned to the site on IIS)

If an error occurs in the process of updating IIS, there will be an entry on the plugin's admin page under the **Pending Headers** tab showing the error message.
The IIS update is carried out automatically as you add and remove domains. 
For the entries showing up under **Pending Headers**, the update is done by clicking on **Execute** after the error condition has been addressed.