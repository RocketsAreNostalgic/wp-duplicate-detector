function htmlEntities(e){return String(e).replace(/\&/g,"&amp;").replace(/</g,"&lt;").replace(/\>/g,"&gt;").replace(/\"/g,"&quot;").replace(/\'/g,"&#039;").replace(/\‘/g,"&lsquo;").replace(/\’/g,"&rsquo;").replace(/\“/g,"&ldquo;").replace(/\”/g,"&rdquo;")}jQuery(document).ready(function(){function e(e){return e?"addClass":"removeClass"}function t(e,t,i){object_DD.debug?console.log("Starting ajax call to WP to find dupe titles"):"";var l={action:"title_check",post_title:e,post_type:i,post_id:t};object_DD.debug?console.log("action: title_check, post_title: "+e+", post_type: "+i+", post_id: "+t):"",jQuery.ajax({cache:!1,type:"POST",url:ajaxurl,data:l,dataType:"json",mimeType:"application/json"}).done(function(e){object_DD.debug?console.log("logging response: "+JSON.stringify(e,null,4)):"",d(e)}).fail(function(e,t,i){object_DD.debug&&(console.log("logging error:  :: "+e+" :: "+t+" :: "+i),console.log("title: "+l.post_title+" post type: "+l.post_type+" post id: "+l.post_id));var s={status:"error",html:'<div id="duplicate-error" style="color:red;">'+object_DD.error_message+" "+i+".</div>"};d(s)})}function d(e){if(jQuery(".duptitles").remove(),jQuery("#titlediv #title").css("background-image","").prop("readonly",!1).addClass("active").removeClass("disabled"),jQuery("button.duplicates").prop("disabled",!1).removeClass("disabled"),"true"===e.status){var t;t='<div id="duplicate-warning"><h3 style="padding-left: 0; color:red">'+e.notice.head+"</h3>",t+="<ul>";for(var d=e.posts,i=0;i<d.length;i++)t+="<li>",t+='<a href="'+d[i].link+'" >'+d[i].title+"</a>",t+="</li>";t+="<p>"+e.notice.foot+"</p></ul></div>",jQuery("#titlediv .inside").prepend('<div id="dd-message" class="notice notice-warning fade duptitles"><p>'+t+"</p></div>").slideDown("slow",function(){jQuery("#titlediv #title").removeClass("dd_spinner dd_warning dd_check").addClass("dd_halt")})}"false"===e.status?(jQuery("#titlediv #title").removeClass("dd_spinner dd_warning dd_halt").addClass("dd_check"),object_DD.debug&&jQuery("#titlediv .inside").prepend('<div id="dd-message" class="notice notice-success fade duptitles"><p>'+e.notice+"</p></div>").slideDown("slow")):("error"===e.status||"undefined"===e.status)&&jQuery("#titlediv #title").removeClass("dd_spinner dd_warning dd_check").addClass("dd_halt").delay(600,function(){jQuery("#titlediv .inside").prepend('<div id="dd-message" class="notice notice-error fade duptitles"><p>'+e.html+"</p></div>").slideDown("slow")}),object_DD.debug&&(jQuery("#titlediv .inside #dd-message").append('<hr /><div id="dd_debug" class="duptitles"><p>WP_DEBUG is enabled: <a href="#" class="dd_expand">SHOW/HIDE</a></p></br><pre class="dd_collapsible">'+JSON.stringify(e,null,4)+"</pre></div>"),jQuery(".dd_collapsible").hide(),jQuery(".dd_expand").click(function(){jQuery(".dd_collapsible").slideToggle("slow")}))}jQuery("#titlediv #title").after('<button type="button" title="'+object_DD.button_notice+'" value="clickme" onclick="jQuery.dupetitles();" id="duplicates" class="duplicates button">D</button>'),jQuery(document).on("input","#titlediv #title",function(){jQuery(this)[e(this.value)]("x")}).on("mousemove",".x",function(t){jQuery(this)[e(this.offsetWidth-40<t.clientX-this.getBoundingClientRect().left)]("onX")}).on("click",".onX",function(){jQuery(this).removeClass(function(){return object_DD.debug?console.log("Handler for .change() called."):"",jQuery.dupetitles(),jQuery(this).attr("class")}).change()}).on("keyup",function(){jQuery(this)[e(this.value)]("x"),jQuery("#titlediv #title").addClass("dd_warning").removeClass("dd_spinner dd_halt dd_check")}),jQuery.dupetitles=function i(){var e=htmlEntities(jQuery("#title").val().trim()),d=jQuery("#post_ID").val(),i=jQuery("#post_type").val();if(e){var l="../../../../../wp-admin/images/wpspin_light.gif";jQuery("#titlediv #title").css({"background-image":'url("'+l+'")',"background-repeat":"no-repeat","background-position":"97% 50%"}),jQuery("#titlediv #title").addClass("disabled dd_spinner").removeClass("active dd_check dd_warning dd_halt").prop("readonly",!0),jQuery("button.duplicates").addClass("disabled").prop("disabled",!0),t(e,d,i)}e||(jQuery("#title").val("").blur(),jQuery("#titlediv #title").prop("readonly",!1).addClass("active").removeClass("disabled dd_check dd_warning dd_halt"),jQuery("button.duplicates").prop("disabled",!1).removeClass("disabled"))}});