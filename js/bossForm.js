function submitForm() {
	 $.ajax({
		type: "POST",
		url: "saveBossNeeds.php",
		cache:false,
        data: $('form#lootNeeds').serialize(),
        
		success: function(response) {
            window.parent.successMsg(response);
        },
        
		error: function(request, state, error){
			alert(request.responseText);
		}
	});
}