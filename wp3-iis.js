/*
 * Script for WP3 IIS plugin
 * Version:  1.0
 * Author : Dennison+Wolfe Internet Group
 */ 
 
(
	function(wp3iis){
		wp3iisTabs =
		{
		siteUrl: '',
		init : function(){
                            if(wp3iis("#wp3iis-admin").html() == null){
                                return;
                            }
                            wp3iis("#wp3iis-admin").tabs({selected: 1 //default tab
                                                    });
                            return;
					
                        }
		};
		
		wp3iis(document).ready(function(){
                                wp3iisTabs.init();
                        })
	}
)(jQuery);