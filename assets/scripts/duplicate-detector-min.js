function htmlEntities(e){return String(e).replace(/\&/g,"&amp;").replace(/</g,"&lt;").replace(/\>/g,"&gt;").replace(/\"/g,"&quot;").replace(/\'/g,"&#039;").replace(/\‘/g,"&lsquo;").replace(/\’/g,"&rsquo;").replace(/\“/g,"&ldquo;").replace(/\”/g,"&rdquo;")}jQuery(document).ready(function(){function e(e){return e?"addClass":"removeClass"}function t(e,t,d){object_DD.debug?console.log("Starting ajax call to WP to find dupe titles"):"";var s={action:"title_check",post_title:e,post_type:d,post_id:t};object_DD.debug?console.log("action: title_check, post_title: "+e+", post_type: "+d+", post_id: "+t):"",jQuery.ajax({cache:!1,type:"POST",url:ajaxurl,data:s,dataType:"json",mimeType:"application/json"}).done(function(e){object_DD.debug?console.log("logging response: "+JSON.stringify(e,null,4)):"",i(e)}).fail(function(e,t,d){object_DD.debug&&(console.log("logging error:  :: "+e+" :: "+t+" :: "+d),console.log("title: "+s.post_title+" post type: "+s.post_type+" post id: "+s.post_id));var l={status:"error",html:'<div id="duplicate-error">'+object_DD.error_message+" "+d+".</div>"};i(l)})}function i(e){if(jQuery(".duptitles").remove(),jQuery("#titlediv #title").css("background-image","").prop("readonly",!1).addClass("active").removeClass("disabled"),jQuery("button.duplicates").prop("disabled",!1).removeClass("disabled"),"true"===e.status){var t;t='<div id="duplicate-warning"><h3>'+e.notice.head_notice+"</h3>",t+="<p><strong>"+e.notice.head_text+"</strong></p>",t+="<ul>";for(var i=e.posts,d=0;d<i.length;d++)t+="<li>",t+='<a href="'+i[d].link+'" >'+i[d].title+"</a>",t+="</li>";t+="</ul><p>"+e.notice.foot+"</p></div>",jQuery("#titlediv .inside").prepend('<div id="dd-message" class="notice notice-error fade duptitles"><p>'+t+"</p></div>").slideDown("slow",function(){jQuery("#titlediv #title").removeClass("dd_spinner dd_warning dd_check").addClass("dd_halt")})}if("too-short"===e.status){var t;t="<p><strong>"+e.notice+"</strong></p>",t+="</p></div>",jQuery("#titlediv .inside").prepend('<div id="dd-message" class="notice notice-warning fade duptitles"><p>'+t+"</p></div>").slideDown("slow",function(){jQuery("#titlediv #title").removeClass("dd_spinner dd_warning dd_check").addClass("dd_warning")})}"false"===e.status?(jQuery("#titlediv #title").removeClass("dd_spinner dd_warning dd_halt").addClass("dd_check"),object_DD.debug&&jQuery("#titlediv .inside").prepend('<div id="dd-message" class="notice notice-success fade duptitles"><p><strong>'+e.notice+"</strong></p></div>").slideDown("slow")):"error"!==e.status&&"undefined"!==e.status||jQuery("#titlediv #title").removeClass("dd_spinner dd_warning dd_check").addClass("dd_halt").delay(600,function(){jQuery("#titlediv .inside").prepend('<div id="dd-message" class="notice notice-error fade duptitles"><p>'+e.html+"</p></div>").slideDown("slow")}),object_DD.debug&&(jQuery("#titlediv .inside #dd-message").append('<hr /><div id="dd_debug" class="duptitles"><p>WP_DEBUG is enabled: <a href="#" class="dd_expand">SHOW/HIDE</a></p></br><pre class="dd_collapsible">'+JSON.stringify(e,null,4)+"</pre></div>"),jQuery(".dd_collapsible").hide(),jQuery(".dd_expand").click(function(){jQuery(".dd_collapsible").slideToggle("slow")}))}jQuery("#titlediv #title").after('<button type="button" title="'+object_DD.button_notice+'" value="clickme" onclick="jQuery.dupetitles();" id="duplicates" class="duplicates button">D</button>'),jQuery(document).on("input","#titlediv #title",function(){jQuery(this)[e(this.value)]("x")}).on("mousemove",".x",function(t){jQuery(this)[e(this.offsetWidth-40<t.clientX-this.getBoundingClientRect().left)]("onX")}).on("click",".onX",function(){jQuery(this).removeClass(function(){return object_DD.debug?console.log("Handler for .change() called."):"",jQuery.dupetitles(),jQuery(this).attr("class")}).change()}).on("keyup",function(){jQuery(this)[e(this.value)]("x"),jQuery("#titlediv #title").addClass("dd_warning").removeClass("dd_spinner dd_halt dd_check")}),jQuery.dupetitles=function e(){var i=htmlEntities(jQuery("#title").val().trim()),d=jQuery("#post_ID").val(),s=jQuery("#post_type").val();if(i){var l=object_DD.plugin_url+"assets/imgs/wpspin_light.gif";jQuery("#titlediv #title").css({"background-image":'url("'+l+'")',"background-repeat":"no-repeat","background-position":"97% 50%"}),jQuery("#titlediv #title").addClass("disabled dd_spinner").removeClass("active dd_check dd_warning dd_halt").prop("readonly",!0),jQuery("button.duplicates").addClass("disabled").prop("disabled",!0),t(i,d,s)}i||(jQuery("#title").val("").blur(),jQuery("#titlediv #title").prop("readonly",!1).addClass("active").removeClass("disabled dd_check dd_warning dd_halt"),jQuery("button.duplicates").prop("disabled",!1).removeClass("disabled"))}});
//# sourceMappingURL=./duplicate-detector-min.js.map