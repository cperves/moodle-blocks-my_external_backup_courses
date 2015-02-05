var WAITING_TIME=15000;
function checkTokenAndSubmit(evt,args){
	evt.stopPropagation();
	evt.preventDefault();
	courseid=args[0];
	domainname=args[1];
	filetoken=args[2];
	url_dest=args[3];
	errmsg=args[4];

	$.ajax({
		type : 'POST',
		data : { 'domainname' : domainname, 'courseid' : courseid, 'filetoken' : filetoken},  
		url: url_dest,
		context: document.body
		})
		.done(function(data, textStatus, jqXHR) {
			//deactivate all submit button
			buttons= $("[type=submit]");
			for(var i=0;i<buttons.length;i++){
				$(buttons[i]).attr('disabled', 'disabled');
				//$('#submit_postcode').removeAttr('disabled');
			}
			$(".my_external_backup_course_notice").show();
			//launch checker to reactivate buttons
			setTimeout(function(){checkToken(domainname,courseid,filetoken,url_dest);}, WAITING_TIME);
			//submit document
			evt.currentTarget.getDOMNode().form.submit();
			
			})
		.fail(function(jqXHR, textStatus, errorThrown) {
			alert(errmsg);
		});
	return false;
}
function checkToken(domainname,courseid,filetoken,url_dest){
	$.ajax({
		type : 'POST',
		data : { 'domainname' : domainname, 'courseid' : courseid, 'filetoken' : filetoken},  
		url: url_dest,
		context: document.body
		})
		.done(function(data, textStatus, jqXHR) {
			//reactivate all submit button
			buttons= $("[type=submit]");
			for(var i=0;i<buttons.length;i++){
				$(buttons[i]).removeAttr('disabled');
			}
			$(".my_external_backup_course_notice").hide();
			})
		.fail(function(jqXHR, textStatus, errorThrown) {
			//wait and relauch checkToken
			setTimeout(function(){checkToken(domainname,courseid,filetoken,url_dest);}, WAITING_TIME);
		});
}