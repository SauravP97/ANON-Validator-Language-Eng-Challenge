<?php
?>

<html>

<head>
	<title>ANON Validate | Deepsource</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>

<style>
	body{
		background-color: #232323;
		color: white;
	}
	.validate-anon{
		margin-top: 10px;
		background-color: #000000;
		color: #fdfffd;
		font-size: 16px;
		font-weight: 600;
	}
	.validator-head{
		margin-bottom: 20px;
		font-family: 'Courier New', Courier, monospace;
	}
	.anon-string{
		background-color: black;
		color: #57ff84;
		font-size: 16px;
		font-family: 'Courier New', Courier, monospace;
	}
	.validated-object{
		background: black;
		border: 1px solid wheat;
		border-radius: 4px;
		margin-left: 15px;
		margin-right: 15px;
		width: 70%;
		padding: 10px;
		color: #57ff84;
		font-size: 16px;
		font-family: 'Courier New', Courier, monospace;
		margin-top: 20px;
	}
	.validated-response{
		font-size: 14px;
    	margin-top: 20px;
    	font-weight: 600;
		font-family: 'Courier New', Courier, monospace;
	}
</style>
<body>
	<div class="container">
		<div class="col-xs-12 validator-head">
			<h1>ANON Validator</h1>
		</div>
		<div class="col-xs-12">
			<textarea class="form-control anon-string" rows="10" placeholder="Enter your ANON String"></textarea>
		</div>
		<div class="col-xs-12">
			<button type="button" class="btn btn-primary validate-anon">Validate</button>
		</div>
		<div class="col-xs-12 validated-response"></div>
		<div class="col-xs-12 validated-object"> Validated Object </div>
	</div>
</body>

<script>
	var jsString = "";
	
	$(".validate-anon").on("click", function(){
		var anonString = $(".anon-string").val();
		var formData = new FormData();
		formData.append("anonString", anonString);
		jsString = "";

		$.ajax({
            url: './parser/AnonParser.php',
            contentType: false,
            processData: false,
            data: formData,
            type: 'POST',
            dataType: 'JSON',
            success: function (data) {
                if (data.status) {
					if(!data.valid){
						$(".validated-response").html("Status : <b style='color:#ea9d9d;'>Failed</b> <br> Invalid ANON String Passed");
						$(".validated-object").html(data.error);
					} else{
						var jsonObject = JSON.parse(data.object);
						displayJsObject(jsonObject,"");
						$(".validated-response").html("Status : Success ");
						$(".validated-object").html(jsString);
					}
				} else {
					$(".validated-response").html("Status : <b style='color:#ea9d9d;'>Failed</b> <br> Invalid ANON String Passed");
					$(".validated-object").html("--");
                }
            },
            error: function (data) {
                console.log(data);
            }
        });
	});

	// Function formats and displays the JSON validated object
	function displayJsObject(object, spaces){
		for(var key in object){
			key = key.replace(/</g, "&lt");
			key = key.replace(/>/g, "&gt");
			if(typeof object[key] === "object"){
				jsString += spaces + key + " :<br>";
				displayJsObject(object[key], spaces+"&nbsp;&nbsp");
			}
			else{
				object[key] = object[key].replace(/</g, "&lt");
				object[key] = object[key].replace(/>/g, "&gt");
				jsString += spaces + key + " : " + object[key] + "<br>";
			}
		}
	}
</script>

</html>