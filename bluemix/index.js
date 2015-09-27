console.log("STARTED index.js");
var STRING;
$.ajax({
	url: 'UNIX',
	success: function(data) {
		STRING = data;
	}
});


$.post(
	"http://localhost:2003/",
	{ key1: STRING },
	function(data) {
		console.log(JSON.stringify(data,null,2));
	}
);
