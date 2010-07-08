/*
 * Script for managing inline popup Category Links
 * Version:  1.0
 * Author : Dennison+Wolfe Internet Group
 */ 
 
 (
	function($_a){
		inlineCategoryLinksList = 
		{
		init : function(){
					if($_a("#relinkme-list").html() == null){
						return;
					}
					var b=this,c=$_a("#inline-list");
					b.type=$_a("#relinkme-list").attr("className").substr(5);
					b.what="#"+b.type+"-";
					b.rows=$_a("tr.list_item");
					c.keyup(function(d){
								if(d.which==27){
									return inlineCategoryLinksList.revert()
								}
							});
					$_a("a.close",c).click(function(){
											return inlineCategoryLinksList.revert()
										});
					b.addEvents(b.rows);
					$_a('#posts-filter input[type="submit"]').click(function(d){
																	if($_a("form#posts-filter tr.inline-list").length>0){
																		b.revert()
																	}
																})
				},
		toggle : function(c){
					var b=this;
					$_a(b.what+b.getId(c)).css("display")=="none"?b.revert():b.list(c)
				},
		addEvents : function(b){
						b.each(function(){
							$_a(this).find("a.listinline").click(function(){
																inlineCategoryLinksList.list(this);
																return false
															})
							})
					},
		list : function(d){
					var c=this,b;
					c.revert();
					if(typeof(d)=="object"){
						d=c.getId(d)
					}
					b=$_a("#inline-list").clone(true),rowData=$_a("#inline_list_"+d);
					$_a("td",b).attr("colspan",$_a(".widefat:first thead th:visible").length);
					if($_a(c.what+d).hasClass("alternate")){
						$_a(b).addClass("alternate")
					}
					$_a(c.what+d).hide().after(b);
					$_a("#link_container").html(rowData.html());
					
					var links=$_a("#link_container").find("ul.category_links_list:first");
					$_a(links).attr("id", "#category_links_list_"+d+"_");
					$_a(links).sortable({ 
						placeholder: "ui-state-highlight", 
						revert: true,
						tolerance: "pointer",
						update : function () { 
							debugger;
							var linkOrderData = $_a(this).sortable('serialize'); 
							var linkOrder = $_a("#relinkme_category_link_order_"+d);
							if(linkOrder.html() !== null){
								$_a(linkOrder).attr("value", linkOrderData);
							}
							else{
								var err = $_a("#links-list-"+d).find("span.error_message:first");
								err.html("The changes cannot be saved since the category check box has not been checked");
								$_a(err).show(); 
							}
						}
					});
					
					$_a(b).attr("id","links-list-"+d).addClass("inline-list").show();
					return false
				},
		revert : function(){
					var b=$_a("table.widefat tr.inline-list").attr("id");
					if(b){$_a("table.widefat .inline-list-close .waiting").hide();
					$_a("#"+b).remove();
					b=b.substr(b.lastIndexOf("-")+1);
					$_a(this.what+b).show()}return false
				},
		getId : function(c){
					var d=c.tagName=="TR"?c.id:$_a(c).parents("tr").attr("id"),b=d.split("-");
					return b[b.length-1]
				}
		};
		
		$_a(document).ready(function(){
							inlineCategoryLinksList.init()
						})
	}
)(jQuery);