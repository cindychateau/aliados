$(document).ready(function () {
	$("#frm-login").submit(function (e){
		e.preventDefault();
		$.ajax({
			url: 'include/Login.php?accion=login',
			type:'POST',
			dataType: 'JSON',
			data: $(this).serialize(),
			error: function () {
				$('#login-msg').html("Experimentamos fallas técnicas.");
				$('#login-info').fadeIn();
			},
			success: function (result){
				if (result.auth){
					document.location.href="./home.php";
				}else {
					$('#login-msg').html(result.msg);
					$('#login-info').fadeIn();
				}
			}
		});
	});

	$("#frm-forgot").submit(function (e) {
		e.preventDefault();
		$.ajax({
			url: 'include/Login.php?accion=forgot',
			type: 'POST',
			dataType: 'JSON',
			data: $(this).serialize(),
			error: function() {
				$('#login-msg2').html(result.msg);
				$('#login-info2').fadeIn();
			}, success: function (result) {
				if (result.reset) {
					$('#login-info2').addClass('alert-info');
					$('#login-info2').removeClass('alert-danger');
				}else {
					$('#login-info2').removeClass('alert-info');
					$('#login-info2').addClass('alert-danger');
				}
				$('#login-msg2').html(result.msg);
				$('#login-info2').fadeIn();
			}
		});
	});

	$(document).on('click','.close', function (e) {
		e.preventDefault();
		var tipo = $(this).attr("data-dismiss");
		if (typeof tipo !== 'undefined' && tipo !== false && tipo == 'alerta') {
			$(this).parent().css("display","none");
			console.log("Hidden alert");
		} 
	});
});