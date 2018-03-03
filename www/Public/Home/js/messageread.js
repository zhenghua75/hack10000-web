function openmessage(msgid){
	$.get("userReadMessage?id="+msgid,function(data){
		$('#messageread .am-popup-title').html(data['title']);
		$('#messageread .am-popup-bd').html(data['comment']);
		$('#messageread').modal('open');
	});
}