(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	 $(function() {

		 function labelize(string) {
			 // Capitalize first letter and convert underscores to spaces
			 return string.charAt(0).toUpperCase() + string.slice(1).replace(/_/g," ");
		 }

		 function createTableReport(response) {
			 var maxStatLength = 100;
			 var $table = $('<table width="100%" cellspacing="0"/>');
			 var propId = 0;
			 var icon1 = 'dashicons-arrow-right';// closed
			 var icon2 = 'dashicons-arrow-down';// open
			//  var toggleScript = 'if(e.hasClass(\''+icon1+'\')) { elm.show(); summary.hide(); e.removeClass(\''+icon1+'\'); e.addClass(\''+icon2+'\'); } else { elm.hide();  summary.show(); e.removeClass(\''+icon2+'\'); e.addClass(\''+icon1+'\'); }';// assumes e and elm vars defined before usage
			var toggleScript = 'if(e.hasClass(\''+icon1+'\')) { elm.show(); e.removeClass(\''+icon1+'\'); e.addClass(\''+icon2+'\'); } else { elm.hide(); e.removeClass(\''+icon2+'\'); e.addClass(\''+icon1+'\'); }';// assumes e and elm vars defined before usage

			 for(var prop in response) {
				 var label = labelize(prop);
				 var summary = (response[prop] instanceof Object && response[prop].hasOwnProperty('summary'))? response[prop]['summary'] : "";
				 var description = (response[prop] instanceof Object && response[prop].hasOwnProperty('description'))? response[prop]['description'] : "";
				 var reportId = 'lca-' + prop;
				 var onClickScript = 'var summary = jQuery(this).find(\'.summary\'); var elm = jQuery(\'.' + reportId + '\'); var e = jQuery(this).find(\'span.lca-toggle\'); ' + toggleScript;

				 var help_icon = description.length ? '<span class="lca-muted dashicons dashicons-editor-help"></span>' : '';

				 $table.append('<tr class="lca-hdr" onclick="' + onClickScript + '" style="cursor:pointer;"><th>' + label + '</th><td style="text-align: right;">'
												+ '<span class="summary">' + summary + "</span>"
												+ '<span class="lca-fr lca-toggle dashicons ' + icon1 + '"></span>'

												+ '</th></tr>');
				if(description.length) {
					$table.append('<tr class="'+ reportId +' description" style="display:none;"><td class="lca-detailed" colspan="2">' + description + '</td></tr>');
				}

				 if(response[prop] instanceof Object){
					 for(var subprop in response[prop]){
						 if(subprop == "summary" || subprop == "description") {
							 continue;
						 }
						 var value = response[prop][subprop];
						 if(typeof value == "string" && value.length > maxStatLength){
							propId += 1;
							var onClickScript = 'var summary = jQuery(this).find(\'.summary\'); var elm = jQuery(\'#lca-stat-'+ propId +'\'); var e = jQuery(this).find(\'span.lca-subtoggle\'); ' + toggleScript;;

							$table.append('<tr class="'+ reportId +'" style="cursor:pointer; display:none;" onclick="' + onClickScript + '"><td>' + labelize(subprop) + '</td><td class="lca-stat">' + '<span class="lca-subtoggle dashicons ' + icon1 + '"></span>' + '</td></tr>');
							$table.append('<tr id="lca-stat-'+ propId +'" style="display:none;"><td class="lca-detailed" colspan="2">' + value + '</td></tr>');
						} else {
							var subprop_label = (prop !== "keywords") ? labelize(subprop) : subprop;
							$table.append('<tr class="'+ reportId +'" style="display:none;"><td>' + subprop_label + '</td><td class="lca-stat">'+ value +'</td></tr>');
						 }
					 }
				 } else {
					 $table.append('<tr class="'+ reportId +'" style="display:none;"><td class="lca-detailed" colspan="2">' + response[prop] + '</td></tr>');
				 }
			 }

			 return $table;
		 }

		 function displayResponse(container, response, showHeader=true) {
			 container.empty();
			 for(var report in response){
				 if(report == "reports" || report == "updated") {
					 continue;
				 }
				 if(showHeader){
					 var $reportHeader = $('<div class="lca-report-hdr" id="#lca-'+report+'">' + labelize(report) + '<span class="lca-refresh-link" style="float: right;"><i class="fa fa-refresh fa-lg fa-fw"></i></span></div>').bind('click',function(e){
						 var elm = $(this);
						 var reportId = elm.attr('id').replace("#lca-","");
						 refreshReport(reportId, elm);
					 });
					 container.append($reportHeader);
				 }
				 container.append(createTableReport(response[report]));
			 }
		 }

		var loaderHtml = '<div class="lca-loader"><img class="lca-img-loader"/></div>';
		var loaderHtmlSmall = '<div class="lca-loader"><img class="lca-img-loader-sm"/></div>';

		 var getPostData = function() {
			 var data = {};
			 jQuery('#post input').each(
			     function( i, el ){
						 var elm = jQuery( el );
						 data[elm.attr('name')] = elm.val();
			     }
			 );
			 return data;
		 }

		 var getContent = function() {
			 return jQuery('#content').val();
		 }

		 var refreshReport = function(reportId, elm) {
			 var data = {
				action: 'content_analytics',
				reports: reportId,
				post: getPostData(),
				content: getContent()
			}

			var elm_data = elm.next();
			elm_data.html(loaderHtmlSmall);

			 jQuery.post(ajaxurl, data, function(response) {
				displayResponse(elm_data, response, false);
			 });
		 }

		 var loadReports = function() {
			 	$('.lca-table').html(loaderHtml);
				var data = {
					action: 'content_analytics',
					content: getContent(),
					post: getPostData()
				}
				jQuery.post(ajaxurl, data, function(response) {
					displayResponse($('.lca-table'), response);
					$('#lca-results').html(response);
				});
		 }

		 $('#lca-refresh-btn').bind('click',function(e){
				e.preventDefault();
				loadReports();
		 });

		 $(function() {
			 // After DOM is ready, initial page load
			 loadReports();
		 });

	 });

})( jQuery );
